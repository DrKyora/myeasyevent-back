<?php

require __DIR__ . '/../config.php';
require __DIR__ . '/../vendor/autoload.php';

use App\Responses\ResponseError;

$request = json_decode(json: file_get_contents(filename: 'php://input'));

$dependances = new \App\Services\DépendancesContainer();

$GOOGLE_API_KEY = $_ENV['GOOGLE_ADDRESS_VALIDATION_API_KEY'] ?? null;

if (!$GOOGLE_API_KEY) {
    $response = $dependances->responseFactory->createFromArray(data: [
        'status' => 'error', 
        'code' => 500, 
        'message' => 'Configuration Google API manquante'
    ]);
    echo json_encode($response);
    exit;
}

if ($dependances->sessionService->tokenSessionIsValide(tokenSession: $request->session)) {
    $sessionString = $dependances->tools->encrypt_decrypt(action: 'decrypt', stringToTreat: $request->session);
    $session = $dependances->sessionFactory->createFromJson(json: $sessionString);
    
    switch($request->action){
        case 'validateAddress':
            try {
                $addressData = [
                    'address' => [
                        'regionCode' => $request->regionCode ?? 'FR',
                        'addressLines' => [$request->fullAddress]
                    ]
                ];
                
                $ch = curl_init("https://addressvalidation.googleapis.com/v1:validateAddress?key={$GOOGLE_API_KEY}");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($addressData));
                
                $result = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curlError = curl_error($ch);
                curl_close($ch);
                
                if ($httpCode === 200) {
                    $googleResponse = json_decode($result);
                    $response = $dependances->responseFactory->createFromArray(data: [
                        'status' => 'success', 
                        'code' => null, 
                        'message' => 'Validation effectuée avec succès', 
                        'data' => $googleResponse
                    ]);
                } else {
                    $response = $dependances->responseFactory->createFromArray(data: [
                        'status' => 'error', 
                        'code' => $httpCode, 
                        'message' => 'Erreur lors de la validation de l\'adresse',
                        'error' => $curlError
                    ]);
                }
            } catch (\Throwable $th) {
                $dependances->tools->myErrorHandler(errno: $th->getCode(), errstr: $th->getMessage(), errfile: $th->getFile(), errline: $th->getLine());
            }
            break;
    }
} else {
    $response = $dependances->responseFactory->createFromArray(data: ['status' => 'error', 'code' => 5009, 'message' => "Pas de session valable, l'utilisateur doit se reconnecter"]);
}
echo json_encode(value: $response);