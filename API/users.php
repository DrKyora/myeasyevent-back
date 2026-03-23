<?php

require __DIR__ . '/../config.php';
require __DIR__ . '/../vendor/autoload.php';

use App\Responses\ResponseError;

$request = json_decode(json: file_get_contents(filename: 'php://input'));

$dependances = new \App\Services\DépendancesContainer();

if ($dependances->sessionService->tokenSessionIsValide(tokenSession: $request->session)) {
    $sessionString = $dependances->tools->encrypt_decrypt(action: 'decrypt', stringToTreat: $request->session);
    $session = $dependances->sessionFactory->createFromJson(json: $sessionString);
    switch($request->action){
        case'getUser':
            try{
                $userFull = $dependances->userService->getUser(key: 'id', value: $session->userId);
                $filterdUser = $dependances->userFactory->createDynamic(user: $userFull, fields: ['lastName', 'firstName', 'email']);
                $response = $dependances->responseFactory->createFromArray(data: ['status' => 'success', 'code' => null, 'message' => "Utilisateur récupéré avec succès", 'data' =>['user'  => $filterdUser]]);
            } catch (\Exception $e) {
                $dependances->tools->myErrorHandler(errno: $e->getCode(), errstr: $e->getMessage(), errfile: $e->getFile(), errline: $e->getLine());
            }
            break;
        case'updateUserPassword':
            try{
                $user = $dependances->userService->getUser(key: 'id', value: $session->userId);
                $user->password = $request->newPassword;
                $dependances->userValidationService->validateUpdateWithPassword(user: $user);
                $response = $dependances->userService->UpdateUserWithPassword(user: $user);
                if(!$response instanceof ResponseError){
                    $response = $dependances->responseFactory->createFromArray(data: ['status' => 'success', 'code' => null, 'message' => "Mise à jour du password de l'utilisateur réussie"]);
                } else {
                    $error = $response;
                    $response = $dependances->responseFactory->createFromArray(data: ['status' => 'error', 'code' => $error->code, 'message' => $error->message]);
                }
            } catch (\Exception $e) {
                $dependances->tools->myErrorHandler(errno: $e->getCode(), errstr: $e->getMessage(), errfile: $e->getFile(), errline: $e->getLine());
            }
            break;
        case'updateUserEmail':
            try{
                $user = $dependances->userService->getUser(key: 'id', value: $session->userId);
                $user->email = $request->email;
                $dependances->userValidationService->validateUpdate(user: $user);
                $response = $dependances->userService->updateUser(user: $user);
                if(!$response instanceof ResponseError){
                    $response = $dependances->responseFactory->createFromArray(data: ['status' => 'success', 'code' => null, 'message' => "Mise à jour de l'email de l'utilisateur réussie"]);
                } else {
                    $error = $response;
                    $response = $dependances->responseFactory->createFromArray(data: ['status' => 'error', 'code' => $error->code, 'message' => $error->message]);
                }
            } catch (\Exception $e) {
                $dependances->tools->myErrorHandler(errno: $e->getCode(), errstr: $e->getMessage(), errfile: $e->getFile(), errline: $e->getLine());
            }
            break;
    }
} else {
    $response = $dependances->responseFactory->createFromArray(data: ['status' => 'error', 'code' => 5009, 'message' => "Pas de session valable, l'utilisateur doit se reconnecter"]);
}
echo json_encode(value: $response);