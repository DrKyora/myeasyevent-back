<?php

require __DIR__ . '/../config.php';
require __DIR__ . '/../vendor/autoload.php';

$request = json_decode(json: file_get_contents(filename: 'php://input'));
// Library
use App\Lib\Tools;

// Factories
use App\Factories\ResponseFactory;
use App\Factories\ResponseErrorFactory;
use App\Factories\AuthorizedDeviceFactory;
use App\Factories\SessionFactory;
use App\Factories\UserFactory;
use App\Factories\EmailFactory;
use App\Factories\BlacklistUserFactory;
use App\Factories\BlacklistIpFactory;
use App\Factories\LogsBadFactory;

// Repositories
use App\Repositories\UserRepository;
use App\Repositories\AuthorizedDeviceRepository;
use App\Repositories\SessionRepository;
use App\Repositories\BlacklistUserRepository;
use App\Repositories\BlacklistIpRepository;
use App\Repositories\LogsBadRepository;


// Validators
use App\Validators\AuthorizedDeviceValidationService;
use App\Validators\UserValidationService;
use App\Validators\SessionValidationService;
use App\Validators\EmailValidationService;
use App\Validators\LogsBadValidationService;
use App\Validators\BlacklistUserValidationService;
use App\Validators\BlacklistIpValidationService;

// Services
use App\Services\DBConnection;
use App\Services\SessionService;
use App\Services\UserService;
use App\Services\AuthorizedDeviceService;
use App\Services\EmailService;
use App\Services\LogsBadService;
use App\Services\BlacklistUserService;
use App\Services\BlacklistIpService;
//Responses
use App\Responses\ResponseError;
$tools = new Tools();
$db = new DBConnection();
// Factories
$responseFactory = new ResponseFactory();
$responseErrorFactory = new ResponseErrorFactory();
$authorizedDeviceFactory = new AuthorizedDeviceFactory();
$sessionFactory = new SessionFactory();
$userFactory = new UserFactory();
$emailFactory = new EmailFactory();
$blacklistUserFactory = new BlacklistUserFactory();
$blacklistIpFactory = new BlacklistIpFactory();
$logsBadFactory = new LogsBadFactory();

// Repositories
$userRepository = new UserRepository(db: $db,tools: $tools,userFactory: $userFactory);
$authorizedDeviceRepository = new AuthorizedDeviceRepository(db: $db,tools: $tools,authorizedDeviceFactory: $authorizedDeviceFactory);
$sessionRepository = new SessionRepository(db: $db,tools: $tools,sessionFactory: $sessionFactory);
$blacklistUserRepository = new BlacklistUserRepository(db: $db,tools: $tools,blacklistUserFactory: $blacklistUserFactory);
$blacklistIpRepository = new BlacklistIpRepository(db: $db,tools: $tools,blacklistIpFactory: $blacklistIpFactory);
$logsBadRepository = new LogsBadRepository(db: $db,tools: $tools,logsBadFactory: $logsBadFactory);

// Validators
$authorizedDeviceValidationService = new AuthorizedDeviceValidationService(tools: $tools,authorizedDeviceRepository: $authorizedDeviceRepository);
$userValidationService = new UserValidationService(userRepository: $userRepository);
$sessionValidationService = new SessionValidationService();
$emailValidationService = new EmailValidationService();
$logsBadValidationService = new LogsBadValidationService(logsBadRepository: $logsBadRepository);
$blacklistUserValidationService = new BlacklistUserValidationService(blacklistUserRepository: $blacklistUserRepository);
$blacklistIpValidationService = new BlacklistIpValidationService(blacklistIpRepository: $blacklistIpRepository);


// Services
$sessionService = new SessionService(
    tools: $tools,
    sessionFactory: $sessionFactory,
    responseErrorFactory: $responseErrorFactory,
    sessionRepository: $sessionRepository,
    sessionValidationService: $sessionValidationService
);
$emailService = new EmailService(
    tools: $tools,
    emailFactory: $emailFactory,
    responseErrorFactory: $responseErrorFactory,
    emailValidationService: $emailValidationService
);
$userService = new UserService(
    tools: $tools,
    userRepository: $userRepository,
    userValidationService: $userValidationService,
    userFactory: $userFactory,
    responseFactory: $responseFactory,
    responseErrorFactory: $responseErrorFactory,
    emailService: $emailService
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
$logsBadService = new LogsBadService(
    tools: $tools,
    logsBadFactory: $logsBadFactory,
    responseErrorFactory: $responseErrorFactory,
    logsBadRepository: $logsBadRepository,
    userRepository: $userRepository,
    logsBadValidationService: $logsBadValidationService
);
$blacklistUserService = new BlacklistUserService(
    tools: $tools,
    responseErrorFactory: $responseErrorFactory,
    blacklistUserRepository: $blacklistUserRepository,
    blacklistUserValidationService: $blacklistUserValidationService
);
$blacklistIpService = new BlacklistIpService(
    tools: $tools,
    blacklistIpFactory: $blacklistIpFactory,
    responseErrorFactory: $responseErrorFactory,
    blacklistIpRepository: $blacklistIpRepository,
    blacklistIpValidationService: $blacklistIpValidationService
);

switch($request->action) {
    case 'connectEmailPass':
        try{
            $ip = $_SERVER['REMOTE_ADDR'];
            $user = $userService->getUser(key: 'email', value: $request->email);
            if(!$user instanceof ResponseError && $user !== null){
                if(!$blacklistIpValidationService->isBlacklist(ip: $ip) OR !$blacklistUserValidationService->isBlacklist(userId: $user->id)){
                    if($logsBadService->countLogs(ip: $ip, email: $request->email) < 3){
                        if($userService->userIsValide(user: $user)){
                            if($result = $authorizedDeviceService->ConnectEmailPass(email: $request->email, password: $request->password,ip: $ip)){
                                $response = $result;
                            }else{
                                $response = $responseFactory->createFromArray(data: ['status' => 'error', 'code' => 5020, 'message' => "Erreur lors de la connexion avec login et mot de passe"]);
                            }
                        }else{
                            $response = $responseFactory->createFromArray(data: ['status' => 'error', 'code' => 5022, 'message' => "Utilisateur non validé, en attente de validation"]);
                        }
                    }else{
                        $response = $responseFactory->createFromArray(data: ['status' => 'error', 'code' => 5026, 'message' => "Trop de tentatives de connexion, compte bloque 5 minutes"]);
                    }
                }else{
                    $response = $responseFactory->createFromArray(data: ['status' => 'error', 'code' => 5027, 'message' => "IP ou utilisateur bloque"]);
                }
            }else{
                $response = $responseFactory->createFromArray(data: ['status' => 'error', 'code' => 5025, 'message' => "Cet utilisateur n'existe pas"]);
            }
        } catch (\Throwable $th) {
            $tools->myErrorHandler(errno: $th->getCode(), errstr: $th->getMessage(), errfile: $th->getFile(), errline: $th->getLine());
        }
        break;
    case'connectWToken':
        try{
            $string = $tools->encrypt_decrypt(action: 'decrypt', stringToTreat: $request->token);
            if($device = $authorizedDeviceFactory->createFromString(string: $string)){
                if($authorizedDeviceService->authorizedDeviceExist(authorizedDeviceId: $device->id)){
                    if($authorizedDeviceService->authorizedDeviceIsValidate(authorizedDevice: $device)){
                        $sessionService->deleteSessionDevice(id: $device->id);
                        $lastAction = (new \DateTime())->format(format: 'Y-m-d H:i:s');
                        $session = $sessionService->createSession(userId: $device->userId, deviceId: $device->id, lastAction: $lastAction);
                        if($session instanceof App\Models\Session){
                            $authorizedDeviceService->refreshAuthorizedDevice(authorizedDeviceId: $device->id);
                            $tokenSession = $tools->encrypt_decrypt(action: 'encrypt', stringToTreat: json_encode(value: $session));
                            $response = $responseFactory->createFromArray(data: ['status' => 'success', 'code' => null, 'message' => "Token correct", 'data' => ['session' => $tokenSession]]);
                        } else {
                            $response = $responseFactory->createFromArray(data: ['status' => 'error', 'code' => 4953, 'message' => "Erreur lors de la création de la session"]);
                        }
                    } else {
                        $response = $responseFactory->createFromArray(data: ['status' => 'error', 'code' => 5011, 'message' => "Ce device n'est pas valide"]);
                    }
                } else {
                    $response = $responseFactory->createFromArray(data: ['status' => 'error', 'code' => 5015, 'message' => "Ce device n'a pas encore été enregistré"]);
                }
            } else {
                $response = $responseFactory->createFromArray(data: ['status' => 'error', 'code' => 5021, 'message' => "Erreur lors de la vérification du device"]);
            }
        } catch (\Throwable $th) {
            $tools->myErrorHandler(errno: $th->getCode(), errstr: $th->getMessage(), errfile: $th->getFile(), errline: $th->getLine());
        }
        break;
    case'checkSession':
        try{
            $sessionIsValide = $sessionService->tokenSessionIsValide(tokenSession: $request->session);
            if($sessionIsValide){
                $stringToken = $tools->encrypt_decrypt(action: 'decrypt', stringToTreat: $request->session);
                $session = $sessionFactory->createFromString(string: $stringToken);
                $session = $sessionService->getSession(key: 'id', value: $session->id);
                if($device = $authorizedDeviceService->getAuthorizedDeviceById( deviceId: $session->deviceId)){
                    $authorizedDeviceService->refreshAuthorizedDevice(authorizedDeviceId: $device->id);
                    $user = $userService->getUser(key: 'id', value: $session->userId);
                    $response = $responseFactory->createFromArray(data: ['status' => 'success', 'code' => null, 'message' => "Session valide", 'data' => ['user' => $user]]);
                } else {
                    $response = $responseFactory->createFromArray(data: ['status' => 'error', 'code' => 5009, 'message' => "Ce token de device n'est pas valide! -> Utiliser login/pass et enregistrer + confirmer le device à nouveau"]);
                }
            } else {
                $response = $responseFactory->createFromArray(data: ['status' => 'error', 'code' => 5009, 'message' => "Ce token de device n'est pas valide! -> Utiliser login/pass et enregistrer + confirmer le device à nouveau"]);
            }
        } catch (\Throwable $th) {
            $tools->myErrorHandler(errno: $th->getCode(), errstr: $th->getMessage(), errfile: $th->getFile(), errline: $th->getLine());
        }
        break;
    case'subscription':
        try{
            $newUser = $userFactory->createFromJson(json: json_encode(value: $request->user));
            if($result = $userService->subscription(newUser: $newUser)){
                $response = $result;
            }else{
                $response = $responseFactory->createFromArray(data: ['status' => 'error', 'code' => 5020, 'message' => "Erreur lors de l'inscription"]);
            }
            
        } catch (\Throwable $th) {
            $tools->myErrorHandler(errno: $th->getCode(), errstr: $th->getMessage(), errfile: $th->getFile(), errline: $th->getLine());
        }
        break;
}
if (!isset($response)) {
    $response = $responseFactory->createFromArray(data: ['status' => 'error', 'code' => $th->getCode(), 'message' => $th->getMessage()]);
}
echo json_encode(value: $response);