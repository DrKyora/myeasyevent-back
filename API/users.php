<?php

require __DIR__ . '/../config.php';
require __DIR__ . '/../vendor/autoload.php';

$request = json_decode(json: file_get_contents(filename: 'php://input'));
/**
 * Libraries
 */
use App\Lib\Tools;
use App\Responses\ResponseError;
/**
 * Factories
 */
use App\Factories\ResponseErrorFactory;
use App\Factories\SessionFactory;
use App\Factories\ResponseFactory;
use App\Factories\AuthorizedDeviceFactory;
use App\Factories\UserFactory;
use App\Factories\EmailFactory;
use App\Factories\LogsBadFactory;
/**
 * Repositories
 */
use App\Repositories\SessionRepository;
use App\Repositories\AuthorizedDeviceRepository;
use App\Repositories\UserRepository;
use App\Repositories\LogsBadRepository;
/**
 * Validators
 */
use App\Validators\SessionValidationService;
use App\Validators\AuthorizedDeviceValidationService;
use App\Validators\UserValidationService;
use App\Validators\EmailValidationService;
/**
 * Services
 */
use App\Services\DBConnection;
use App\Services\SessionService;
use App\Services\AuthorizedDeviceService;
use App\Services\EmailService;
use App\Services\UserService;
/**
 * Libraries
 */
$db = new DBConnection();
$tools = new Tools();
/**
 * Factories
 */
$responseErrorFactory = new ResponseErrorFactory();
$sessionFactory = new SessionFactory();
$authorizedDeviceFactory = new AuthorizedDeviceFactory();
$responseFactory = new ResponseFactory();
$userFactory = new UserFactory();
$emailFactory = new EmailFactory();
$logsBadFactory = new LogsBadFactory();
/**
 * Repositories
 */
$sessionRepository = new SessionRepository(db: $db, tools: $tools, sessionFactory: $sessionFactory);
$authorizedDeviceRepository = new AuthorizedDeviceRepository(db: $db, tools: $tools, authorizedDeviceFactory: $authorizedDeviceFactory);
$userRepository = new UserRepository(db: $db, tools: $tools, userFactory: $userFactory);
$logsBadRepository = new LogsBadRepository(db: $db,tools: $tools,logsBadFactory: $logsBadFactory);
/**
 * Validators
 */
$sessionValidationService = new SessionValidationService();
$authorizedDeviceValidationService = new AuthorizedDeviceValidationService(tools: $tools,authorizedDeviceRepository: $authorizedDeviceRepository);
$userValidationService = new UserValidationService(userRepository: $userRepository);
$emailValidationService = new EmailValidationService();
/**
 * Services
 */
$sessionService = new SessionService(
    tools: $tools,
    sessionFactory: $sessionFactory,
    sessionRepository: $sessionRepository,
    sessionValidationService: $sessionValidationService,
    responseErrorFactory: $responseErrorFactory
);
$emailService = new EmailService(
    tools: $tools,
    emailFactory: $emailFactory,
    responseErrorFactory: $responseErrorFactory,
    emailValidationService: $emailValidationService
);
$authorizedDeviceService = new AuthorizedDeviceService(
    tools: $tools,
    authorizedDeviceFactory: $authorizedDeviceFactory,
    responseErrorFactory: $responseErrorFactory,
    responseFactory: $responseFactory,
    logsBadFactory: $logsBadFactory,
    authorizedDeviceRepository: $authorizedDeviceRepository,
    userRepository: $userRepository,
    logsBadRepository: $logsBadRepository,
    authorizedDeviceValidationService: $authorizedDeviceValidationService,
    userValidationService: $userValidationService,
    emailService: $emailService
    
);
$userService = new UserService(
    tools: $tools,
    userFactory: $userFactory,
    userRepository: $userRepository,
    responseFactory: $responseFactory,
    responseErrorFactory: $responseErrorFactory,
    userValidationService: $userValidationService,
    emailService: $emailService
);

if ($sessionService->tokenSessionIsValide(tokenSession: $request->session)) {
    $sessionString = $tools->encrypt_decrypt(action: 'decrypt', stringToTreat: $request->session);
    $session = $sessionFactory->createFromJson(json: $sessionString);
    switch($request->action){
        case'getUser':
            try{
                $userFull = $userService->getUser(key: 'id', value: $session->userId);
                $filterdUser = $userFactory->createDynamic(user: $userFull, fields: ['lastName', 'firstName', 'email']);
                $response = $responseFactory->createFromArray(data: ['status' => 'success', 'code' => null, 'message' => "Utilisateur récupéré avec succès", 'data' =>['user'  => $filterdUser]]);
            } catch (\Exception $e) {
                $tools->myErrorHandler(errno: $th->getCode(), errstr: $th->getMessage(), errfile: $th->getFile(), errline: $th->getLine());
            }
            break;
        case'updateUserPassword':
            try{
                $user = $userService->getUser(key: 'id', value: $session->userId);
                $user->password = $request->newPassword;
                $userValidationService->validateUpdateWithPassword(user: $user);
                $response = $userService->UpdateUserWithPassword(user: $user);
                if(!$response instanceof ResponseError){
                    $response = $responseFactory->createFromArray(data: ['status' => 'success', 'code' => null, 'message' => "Mise à jour du password de l'utilisateur réussie"]);
                } else {
                    $error = $response;
                    $response = $responseFactory->createFromArray(data: ['status' => 'error', 'code' => $error->code, 'message' => $error->message]);
                }
            } catch (\Exception $e) {
                $tools->myErrorHandler(errno: $th->getCode(), errstr: $th->getMessage(), errfile: $th->getFile(), errline: $th->getLine());
            }
            break;
        case'updateUserEmail':
            try{
                $user = $userService->getUser(key: 'id', value: $session->userId);
                $user->email = $request->email;
                $userValidationService->validateUpdate(user: $user);
                $response = $userService->updateUser(user: $user);
                if(!$response instanceof ResponseError){
                    $response = $responseFactory->createFromArray(data: ['status' => 'success', 'code' => null, 'message' => "Mise à jour de l'email de l'utilisateur réussie"]);
                } else {
                    $error = $response;
                    $response = $responseFactory->createFromArray(data: ['status' => 'error', 'code' => $error->code, 'message' => $error->message]);
                }
            } catch (\Exception $e) {
                $tools->myErrorHandler(errno: $th->getCode(), errstr: $th->getMessage(), errfile: $th->getFile(), errline: $th->getLine());
            }
            break;
        case'updateUserProfile':
            try{
                $userToUpdate = $userService->getUser(key: 'id', value: $session->userId);
                $user = $userFactory->createFromJson(json: json_encode(value: $request->user));
                $user->id = $userToUpdate->id;
                $user->email = $userToUpdate->email;
                $userValidationService->validateUpdate(user: $user);
                $response = $userService->updateUser(user: $user);
                if(!$response instanceof ResponseError){
                    $response = $responseFactory->createFromArray(data: ['status' => 'success', 'code' => null, 'message' => "Mise à jour du profil de l'utilisateur réussie"]);
                } else {
                    $error = $response;
                    $response = $responseFactory->createFromArray(data: ['status' => 'error', 'code' => $error->code, 'message' => $error->message]);
                }
            } catch (\Exception $e) {
                $tools->myErrorHandler(errno: $th->getCode(), errstr: $th->getMessage(), errfile: $th->getFile(), errline: $th->getLine());
            }
            break;
        default:
            $response = $responseFactory->createFromArray(data: ['status' => 'error', 'code' => 2000, 'message' => "Le service demandé: " . $request->action . " n'existe pas"]);
            break;
    }
    $device = $authorizedDeviceService->getAuthorizedDeviceById( deviceId: $session->deviceId);
    $authorizedDeviceService->refreshAuthorizedDevice(authorizedDeviceId: $device->id);
} else {
    $response = $responseFactory->createFromArray(data: ['status' => 'error', 'code' => 5009, 'message' => "Pas de session valable, l'utilisateur doit se reconnecter"]);
}
echo json_encode(value: $response);