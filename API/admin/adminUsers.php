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
            case'getAllUsers':
                try{
                    $userFull = $dependances->userService->getAllUsers();
                    if(!$userFull instanceof ResponseError){
                        $filterdUsers = [];
                    foreach ($userFull as $user) {
                        $filterdUsers[] = $dependances->userFactory->createDynamic(user: $user, fields: ['id','lastName', 'firstName', 'email', 'validateDate', 'isAdmin', 'isDeleted']);
                    }
                    $response = $dependances->responseFactory->createFromArray(data: ['status' => 'success', 'code' => null, 'message' => "Utilisateurs récupérés avec succès", 'data' =>['users'  => $filterdUsers]]);
                    } else {
                        $error = $userFull;
                        $response = $dependances->responseFactory->createFromArray(data: ['status' => 'error', 'code' => $error->code, 'message' => $error->message]);
                    }
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
            case'updateUserRole':
                try{
                    $user = $dependances->userService->getUser(key: 'id', value: $request->userId);
                    $user->isAdmin = $request->isAdmin;
                    $response = $dependances->userService->updateUser(user: $user);
                    if(!$response instanceof ResponseError){
                        $response = $dependances->responseFactory->createFromArray(data: ['status' => 'success', 'code' => null, 'message' => "Mise à jour du role de l'utilisateur réussie"]);
                    } else {
                        $error = $response;
                        $response = $dependances->responseFactory->createFromArray(data: ['status' => 'error', 'code' => $error->code, 'message' => $error->message]);
                    }
                } catch (\Exception $e) {
                    $dependances->tools->myErrorHandler(errno: $e->getCode(), errstr: $e->getMessage(), errfile: $e->getFile(), errline: $e->getLine());
                }
                break;
            case'blacklistUser':
                try{
                    $user = $dependances->userService->getUser(key:'id', value: $request->userId);
                    $bannedDate = new DateTime();
                    $bannedDate->format(format: 'Y-m-d H:i:s');
                    $bannedUser = $dependances->blacklistUserFactory->createFromArray(['userId' => $user->id, 'bannedDate' => $bannedDate]);
                    $response = $dependances->blacklistUserService->addBlacklistUser(blacklistUser: $bannedUser);
                    if(!$response instanceof ResponseError){
                        $response = $dependances->responseFactory->createFromArray(data: ['status' => 'success', 'code' => null, 'message' => "L'utilisateur a été banni avec succès"]);
                    } else {
                        $error = $response;
                        $response = $dependances->responseFactory->createFromArray(data: ['status' => 'error', 'code' => $error->code, 'message' => $error->message]);
                    }
                } catch (\Exception $e) {
                    $dependances->tools->myErrorHandler(errno: $e->getCode(), errstr: $e->getMessage(), errfile: $e->getFile(), errline: $e->getLine());
                }
                break;
            case'unBlacklistUser':
                try{
                    $response = $dependances->blacklistUserService->unBlacklistUser($request->userId);
                    if(!$response instanceof ResponseError){
                        $response = $dependances->responseFactory->createFromArray(data: ['status' => 'success', 'code' => null, 'message' => "L'utilisateur a été débanni avec succès"]);
                    } else {
                        $error = $response;
                        $response = $dependances->responseFactory->createFromArray(data: ['status' => 'error', 'code' => $error->code, 'message' => $error->message]);
                    }
                } catch (\Exception $e) {
                    $dependances->tools->myErrorHandler(errno: $e->getCode(), errstr: $e->getMessage(), errfile: $e->getFile(), errline: $e->getLine());
                }
                break;
            case'desactivateUser':
                try{
                    $response = $dependances->userService->desactivateUser(userId:$request->userId);
                    if(!$response instanceof ResponseError){
                        $response = $dependances->responseFactory->createFromArray(data: ['status' => 'success', 'code' => null, 'message' => "L'utilisateur a été désactivé avec succès"]);
                    } else {
                        $error = $response;
                        $response = $dependances->responseFactory->createFromArray(data: ['status' => 'error', 'code' => $error->code, 'message' => $error->message]);
                    }
                } catch (\Exception $e) {
                    $dependances->tools->myErrorHandler(errno: $e->getCode(), errstr: $e->getMessage(), errfile: $e->getFile(), errline: $e->getLine());
                }
                break;
            case'reactivateUser':
                try{
                    $response = $dependances->userService->reactivateUser(userId:$request->userId);
                    if(!$response instanceof ResponseError){
                        $response = $dependances->responseFactory->createFromArray(data: ['status' => 'success', 'code' => null, 'message' => "L'utilisateur a été réactivé avec succès"]);
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
    $response = $dependances->responseFactory->createFromArray(data: ['status' => 'error', 'code' => $user->code, 'message' => $user->message]);
    }
} else {
    $response = $dependances->responseFactory->createFromArray(data: ['status' => 'error', 'code' => 5009, 'message' => "Pas de session valable, l'utilisateur doit se reconnecter"]);
}
echo json_encode(value: $response);