<?php

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
                case'getDeviceOfUser':
                    try{
                        $devices = $dependances->authorizedDeviceService->getDevicesOfUser(userId: $request->userId);
                        if(!$devices instanceof ResponseError){
                            $response = $dependances->responseFactory->createFromArray(data: ['status' => 'success', 'code' => null, 'message' => "Appareils de l'utilisateur récupérés avec succès", 'data' =>['devices'  => $devices]]);
                        } else {
                            $error = $devices;
                            $response = $dependances->responseFactory->createFromArray(data: ['status' => 'error', 'code' => $error->code, 'message' => $error->message]);
                        }
                    } catch (\Throwable $th) {
                        $dependances->tools->myErrorHandler(errno: $th->getCode(), errstr: $th->getMessage(), errfile: $th->getFile(), errline: $th->getLine());
                    }
                    break;
                case 'deleteDeviceOfUser':
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