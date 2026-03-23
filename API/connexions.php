<?php

require __DIR__ . '/../config.php';
require __DIR__ . '/../vendor/autoload.php';

use App\Responses\ResponseError;

$request = json_decode(json: file_get_contents(filename: 'php://input'));

// ✅ Une seule ligne: le conteneur
$dependances = new \App\Services\DépendancesContainer();

switch($request->action) {
    case 'connectEmailPass':
        try{
            $ip = $_SERVER['REMOTE_ADDR'];
            $user = $dependances->userService->getUser(key: 'email', value: $request->email);
            if(!$user instanceof ResponseError && $user !== null){
                if(!$dependances->blacklistIpValidationService->isBlacklist(ip: $ip) OR !$dependances->blacklistUserValidationService->isBlacklist(userId: $user->id)){
                    if($dependances->logsBadService->countLogs(ip: $ip, email: $request->email) < 3){
                        if($dependances->userService->userIsValide(user: $user)){
                            if($result = $dependances->authorizedDeviceService->ConnectEmailPass(email: $request->email, password: $request->password,ip: $ip)){
                                $response = $result;
                            }else{
                                $response = $dependances->responseFactory->createFromArray(data: ['status' => 'error', 'code' => 5020, 'message' => "Erreur lors de la connexion avec login et mot de passe"]);
                            }
                        }else{
                            $response = $dependances->responseFactory->createFromArray(data: ['status' => 'error', 'code' => 5022, 'message' => "Utilisateur non validé, en attente de validation"]);
                        }
                    }else{
                        $response = $dependances->responseFactory->createFromArray(data: ['status' => 'error', 'code' => 5026, 'message' => "Trop de tentatives de connexion, compte bloque 5 minutes"]);
                    }
                }else{
                    $response = $dependances->responseFactory->createFromArray(data: ['status' => 'error', 'code' => 5027, 'message' => "IP ou utilisateur bloque"]);
                }
            }else{
                $response = $dependances->responseFactory->createFromArray(data: ['status' => 'error', 'code' => 5025, 'message' => "Cet utilisateur n'existe pas"]);
            }
        } catch (\Throwable $th) {
            $dependances->tools->myErrorHandler(errno: $th->getCode(), errstr: $th->getMessage(), errfile: $th->getFile(), errline: $th->getLine());
        }
        break;
    case'connectWToken':
    try{
        $string = $dependances->tools->encrypt_decrypt(action: 'decrypt', stringToTreat: $request->token);
        if($device = $dependances->authorizedDeviceFactory->createFromString(string: $string)){
            if($dependances->authorizedDeviceService->authorizedDeviceExist(authorizedDeviceId: $device->id)){
                // 🔥 CORRECTION : Récupérer le device depuis la BDD pour avoir validateDate à jour
                $deviceFromDB = $dependances->authorizedDeviceService->getAuthorizedDeviceById(deviceId: $device->id);
                
                if($dependances->authorizedDeviceService->authorizedDeviceIsValidate(authorizedDevice: $deviceFromDB)){
                    $dependances->sessionService->deleteSessionDevice(id: $deviceFromDB->id);
                    $lastAction = (new \DateTime())->format(format: 'Y-m-d H:i:s');
                    $session = $dependances->sessionService->createSession(userId: $deviceFromDB->userId, deviceId: $deviceFromDB->id, lastAction: $lastAction);
                    if($session instanceof App\Models\Session){
                        $dependances->authorizedDeviceService->refreshAuthorizedDevice(authorizedDeviceId: $deviceFromDB->id);
                        $tokenSession = $dependances->tools->encrypt_decrypt(action: 'encrypt', stringToTreat: json_encode(value: $session));
                        $response = $dependances->responseFactory->createFromArray(data: ['status' => 'success', 'code' => null, 'message' => "Token correct", 'data' => ['session' => $tokenSession]]);
                    } else {
                        $response = $dependances->responseFactory->createFromArray(data: ['status' => 'error', 'code' => 4953, 'message' => "Erreur lors de la création de la session"]);
                    }
                } else {
                    $response = $dependances->responseFactory->createFromArray(data: ['status' => 'error', 'code' => 5011, 'message' => "Ce device n'est pas valide"]);
                }
            } else {
                $response = $dependances->responseFactory->createFromArray(data: ['status' => 'error', 'code' => 5015, 'message' => "Ce device n'a pas encore été enregistré"]);
            }
        } else {
            $response = $dependances->responseFactory->createFromArray(data: ['status' => 'error', 'code' => 5021, 'message' => "Erreur lors de la vérification du device"]);
        }
    } catch (\Throwable $th) {
        $dependances->tools->myErrorHandler(errno: $th->getCode(), errstr: $th->getMessage(), errfile: $th->getFile(), errline: $th->getLine());
    }
    break;
    case'checkSession':
        try{
            $sessionIsValide = $dependances->sessionService->tokenSessionIsValide(tokenSession: $request->session);
            if($sessionIsValide){
                $stringToken = $dependances->tools->encrypt_decrypt(action: 'decrypt', stringToTreat: $request->session);
                $session = $dependances->sessionFactory->createFromString(string: $stringToken);
                $session = $dependances->sessionService->getSession(key: 'id', value: $session->id);
                if($device = $dependances->authorizedDeviceService->getAuthorizedDeviceById( deviceId: $session->deviceId)){
                    $dependances->authorizedDeviceService->refreshAuthorizedDevice(authorizedDeviceId: $device->id);
                    $user = $dependances->userService->getUser(key: 'id', value: $session->userId);
                    $response = $dependances->responseFactory->createFromArray(data: ['status' => 'success', 'code' => null, 'message' => "Session valide", 'data' => ['user' => $user]]);
                } else {
                    $response = $dependances->responseFactory->createFromArray(data: ['status' => 'error', 'code' => 5009, 'message' => "Ce token de device n'est pas valide! -> Utiliser login/pass et enregistrer + confirmer le device à nouveau"]);
                }
            } else {
                $response = $dependances->responseFactory->createFromArray(data: ['status' => 'error', 'code' => 5009, 'message' => "Ce token de device n'est pas valide! -> Utiliser login/pass et enregistrer + confirmer le device à nouveau"]);
            }
        } catch (\Throwable $th) {
            $dependances->tools->myErrorHandler(errno: $th->getCode(), errstr: $th->getMessage(), errfile: $th->getFile(), errline: $th->getLine());
        }
        break;
    case'subscription':
        try{
            $newUser = $dependances->userFactory->createFromJson(json: json_encode(value: $request->user));
            if($result = $dependances->userService->subscription(newUser: $newUser)){
                $response = $result;
            }else{
                $response = $dependances->responseFactory->createFromArray(data: ['status' => 'error', 'code' => 5020, 'message' => "Erreur lors de l'inscription"]);
            }
            
        } catch (\Throwable $th) {
            $dependances->tools->myErrorHandler(errno: $th->getCode(), errstr: $th->getMessage(), errfile: $th->getFile(), errline: $th->getLine());
        }
        break;
}
if (!isset($response)) {
    $response = $dependances->responseFactory->createFromArray(data: ['status' => 'error', 'code' => $th->getCode(), 'message' => $th->getMessage()]);
}
echo json_encode(value: $response);