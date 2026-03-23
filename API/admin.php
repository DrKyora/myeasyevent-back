<?php

require __DIR__ . '/../config.php';
require __DIR__ . '/../vendor/autoload.php';

use App\Responses\ResponseError;

$request = json_decode(json: file_get_contents(filename: 'php://input'));

$dependances = new \App\Services\DépendancesContainer();


if ($dependances->sessionService->tokenSessionIsValide(tokenSession: $request->session)) {
    $sessionString = $dependances->tools->encrypt_decrypt(action: 'decrypt', stringToTreat: $request->session);
    $session = $dependances->sessionFactory->createFromJson(json: $sessionString);
    $user = $dependances->userService->userIsAdmin($session->userId);
    if(!$user instanceof ResponseError){
        switch($request->action){
            case'getAllUsers':
                try{
                    $userFull = $dependances->userService->getAllUsers();
                    $filterdUsers = [];
                    foreach ($userFull as $user) {
                        $filterdUsers[] = $dependances->userFactory->createDynamic(user: $user, fields: ['id','lastName', 'firstName', 'email', 'validateDate', 'isAdmin', 'isDeleted']);
                    }
                    $response = $dependances->responseFactory->createFromArray(data: ['status' => 'success', 'code' => null, 'message' => "Utilisateurs récupérés avec succès", 'data' =>['users'  => $filterdUsers]]);
                } catch (\Exception $e) {
                    $dependances->tools->myErrorHandler(errno: $e->getCode(), errstr: $e->getMessage(), errfile: $e->getFile(), errline: $e->getLine());
                }
                break;
            case'updateUserPassword':
                try{
                    $user = $dependances->userService->getUser(key: 'id', value: $request->userId);
                    $user->password = $request->newPassword;
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
                    $user = $dependances->userService->getUser(key: 'id', value: $request->userId);
                    $user->email = $request->email;
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
        $response = $dependances->responseFactory->createFromArray(data: ['status' => 'error', 'code' => 5028, 'message' => "L'utilisateur n'est pas administrateur"]);
    }
} else {
    $response = $dependances->responseFactory->createFromArray(data: ['status' => 'error', 'code' => 5009, 'message' => "Pas de session valable, l'utilisateur doit se reconnecter"]);
}
echo json_encode(value: $response);