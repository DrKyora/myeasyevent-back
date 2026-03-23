<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config.php';

use App\Responses\ResponseError;

$request = json_decode(json: file_get_contents(filename: 'php://input'));

$dependances = new \App\Services\DépendancesContainer();

if ($dependances->sessionService->tokenSessionIsValide(tokenSession: $request->session)) {
    $sessionString = $dependances->tools->encrypt_decrypt(action: 'decrypt', stringToTreat: $request->session);
    $session = $dependances->sessionFactory->createFromJson(json: $sessionString);
    switch($request->action){
        case 'getDevicesOfUser':
            try{
                $device = $dependances->authorizedDeviceService->tokenDeviceObject(token: $request->token);
                if(!$device instanceof ResponseError){
                    $deviceList = $dependances->authorizedDeviceService->getDevicesOfUser(userId: $session->userId);
                    $response = $dependances->responseFactory->createFromArray(data: ['status' => 'success', 'code' => null, 'message' => "Récupération de la liste des appareils de l'utilisateur réussie", "data" => ['devices' => $deviceList, 'currentDeviceId' => $device->id]]);
                } else {
                    $error = $device;
                    $response = $dependances->responseFactory->createFromArray(data: ['status' => 'error', 'code' => $error->code, 'message' => $error->message]);
                }
            } catch (\Throwable $th) {
                $dependances->tools->myErrorHandler(errno: $th->getCode(), errstr: $th->getMessage(), errfile: $th->getFile(), errline: $th->getLine());
            }
            break;
        case 'deleteDevice':
            try{
                $reponse = $dependances->authorizedDeviceService->deleteAuthorizedDevice(id: $request->deviceId);
                if(!$reponse instanceof ResponseError){
                    $response = $dependances->responseFactory->createFromArray(data: ['status' => 'success', 'code' => null, 'message' => "Suppression de l'appareil réussie"]);
                } else {
                    $error = $reponse;
                    $response = $dependances->responseFactory->createFromArray(data: ['status' => 'error', 'code' => $error->code, 'message' => $error->message]);
                }
            } catch (\Throwable $th) {
                $dependances->tools->myErrorHandler(errno: $th->getCode(), errstr: $th->getMessage(), errfile: $th->getFile(), errline: $th->getLine());
            }
            break;
            default:
            $response = $dependances->responseFactory->createFromArray(data: ['status' => 'error', 'code' => 2000, 'message' => "Le service demandé: " . $request->action . " n'existe pas"]);
            break;
    }
    $device = $dependances->authorizedDeviceService->getAuthorizedDeviceById(deviceId: $session->deviceId);
    $dependances->authorizedDeviceService->refreshAuthorizedDevice(authorizedDeviceId: $device->id);
} else {
    $response = $dependances->responseFactory->createFromArray(data: ['status' => 'error', 'code' => 5009, 'message' => "Pas de session valable, l'utilisateur doit se reconnecter"]);
}
echo json_encode(value: $response);