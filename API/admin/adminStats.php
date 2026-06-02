<?php
$response = null;
require __DIR__ . '/../../config.php';
require __DIR__ . '/../../vendor/autoload.php';

use App\Responses\ResponseError;

$request = json_decode(json: file_get_contents(filename: 'php://input'));

$dependances = new \App\Services\DépendancesContainer();


if ($dependances->sessionService->tokenSessionIsValide(tokenSession: $request->session)) {
    $sessionString = $dependances->tools->encrypt_decrypt(action: 'decrypt', stringToTreat: $request->session);
    $session = $dependances->sessionFactory->createFromJson(json: $sessionString);
    $user = $dependances->userService->userIsAdmin($session->userId);
    if(!$user instanceof ResponseError){
        if($user === true){
            switch($request->action){
                case'getAllStats':
                    try{
                        $stats = $dependances->statsService->getAllStats();
                        if(!$stats instanceof ResponseError){
                            $response = $dependances->responseFactory->createFromArray(data: ['status' => 'success', 'code' => null, 'message' => "Statistiques récupérées avec succès", 'data' =>['stats'  => $stats]]);
                        } else {
                            $error = $stats;
                            $response = $dependances->responseFactory->createFromArray(data: ['status' => 'error', 'code' => $error->code, 'message' => $error->message]);
                        }
                    } catch (\Exception $e) {
                        $dependances->tools->myErrorHandler(errno: $e->getCode(), errstr: $e->getMessage(), errfile: $e->getFile(), errline: $e->getLine());
                    }
                }
        } else {
            $response = $dependances->responseFactory->createFromArray(data: ['status' => 'error', 'code' => 5028, 'message' => "L'utilisateur n'est pas administrateur"]);
        }
    } else {
    $response = $dependances->responseFactory->createFromArray(data: ['status' => 'error', 'code' => $user->code, 'message' => $user->message]);
    }
} else {
    $response = $dependances->responseFactory->createFromArray(data: ['status' => 'error', 'code' => 5009, 'message' => "Pas de session valable, l'utilisateur doit se reconnecter"]);
}
echo json_encode(value: $response);