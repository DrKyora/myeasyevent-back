<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config.php';

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
use App\Factories\ResponseFactory;
use App\Factories\AuthorizedDeviceFactory;
use App\Factories\LogsBadFactory;
use App\Factories\SessionFactory;
use App\Factories\EmailFactory;
use App\Factories\UserFactory;
/**
 * Repositories
 */
use App\Repositories\AuthorizedDeviceRepository;
use App\Repositories\SessionRepository;
use App\Repositories\LogsBadRepository;
use App\Repositories\UserRepository;
/**
 * Validators
 */
use App\Validators\AuthorizedDeviceValidationService;
use App\Validators\SessionValidationService;
use App\Validators\EmailValidationService;
use App\Validators\UserValidationService;
/**
 * Services
 */
use App\Services\SessionService;
use App\Services\AuthorizedDeviceService;
use App\Services\EmailService;
use App\Services\DBConnection;
/**
 * Libraries
 */
$db = new DBConnection();
$tools = new Tools();
/**
 * Factories
 */
$responseErrorFactory = new ResponseErrorFactory();
$responseFactory = new ResponseFactory();
$authorizedDeviceFactory = new AuthorizedDeviceFactory();
$logsBadFactory = new LogsBadFactory();
$sessionFactory = new SessionFactory();
$emailFactory = new EmailFactory();
$userFactory = new UserFactory();
/**
 * Repositories
 */
$authorizedDeviceRepository = new AuthorizedDeviceRepository(db: $db,tools: $tools,authorizedDeviceFactory: $authorizedDeviceFactory);
$sessionRepository = new SessionRepository(db: $db, tools: $tools, sessionFactory: $sessionFactory);
$logsBadRepository = new LogsBadRepository(db: $db,tools: $tools,logsBadFactory: $logsBadFactory);
$userRepository = new UserRepository(db: $db, tools: $tools, userFactory: $userFactory);
/**
 * Validators
 */
$authorizedDeviceValidationService = new AuthorizedDeviceValidationService(tools: $tools, authorizedDeviceRepository: $authorizedDeviceRepository);
$sessionValidationService = new SessionValidationService();
$emailValidationService = new EmailValidationService();
$userValidationService = new UserValidationService(userRepository: $userRepository);
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
if ($sessionService->tokenSessionIsValide(tokenSession: $request->session)) {
    $sessionString = $tools->encrypt_decrypt(action: 'decrypt', stringToTreat: $request->session);
    $session = $sessionFactory->createFromJson(json: $sessionString);
    switch($request->action){
        case 'getDevicesOfUser':
            try{
                $device = $authorizedDeviceService->tokenDeviceObject(token: $request->token);
                if(!$device instanceof ResponseError){
                    $deviceList = $authorizedDeviceService->getDevicesOfUser(userId: $session->userId);
                    $response = $responseFactory->createFromArray(data: ['status' => 'success', 'code' => null, 'message' => "Récupération de la liste des appareils de l'utilisateur réussie", "data" => ['devices' => $deviceList, 'currentDeviceId' => $device->id]]);
                } else {
                    $error = $device;
                    $response = $responseFactory->createFromArray(data: ['status' => 'error', 'code' => $error->code, 'message' => $error->message]);
                }
            } catch (\Throwable $th) {
                $tools->myErrorHandler(errno: $th->getCode(), errstr: $th->getMessage(), errfile: $th->getFile(), errline: $th->getLine());
            }
            break;
        case 'deleteDevice':
            try{
                $reponse = $authorizedDeviceService->deleteAuthorizedDevice( id: $request->deviceId);
                if(!$reponse instanceof ResponseError){
                    $response = $responseFactory->createFromArray(data: ['status' => 'success', 'code' => null, 'message' => "Suppression de l'appareil réussie"]);
                } else {
                    $error = $reponse;
                    $response = $responseFactory->createFromArray(data: ['status' => 'error', 'code' => $error->code, 'message' => $error->message]);
                }
            } catch (\Throwable $th) {
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