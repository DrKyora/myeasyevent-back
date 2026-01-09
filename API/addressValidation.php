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
use App\Factories\LogsBadFactory;
use App\Factories\EmailFactory;
use App\Factories\UserFactory;
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
use App\Validators\EmailValidationService;
use App\Validators\UserValidationService;
/**
 * Services
 */
use App\Services\DBConnection;
use App\Services\SessionService;
use App\Services\AuthorizedDeviceService;
use App\Services\EmailService;
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
$logsBadFactory = new LogsBadFactory();
$emailFactory = new EmailFactory();
$userFactory = new UserFactory();
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

/**
 * Google API Key from .env
 */
$GOOGLE_API_KEY = $_ENV['GOOGLE_ADDRESS_VALIDATION_API_KEY'] ?? null;

if (!$GOOGLE_API_KEY) {
    $response = $responseFactory->createFromArray(data: [
        'status' => 'error', 
        'code' => 500, 
        'message' => 'Configuration Google API manquante'
    ]);
    echo json_encode($response);
    exit;
}

if ($sessionService->tokenSessionIsValide(tokenSession: $request->session)) {
    $sessionString = $tools->encrypt_decrypt(action: 'decrypt', stringToTreat: $request->session);
    $session = $sessionFactory->createFromJson(json: $sessionString);
    
    switch($request->action){
        case 'validateAddress':
            try {
                // Construire la requête Google
                $addressData = [
                    'address' => [
                        'regionCode' => $request->regionCode ?? 'FR',
                        'addressLines' => [$request->fullAddress]
                    ]
                ];
                
                // Appel à Google Address Validation API
                $ch = curl_init("https://addressvalidation.googleapis.com/v1:validateAddress?key={$GOOGLE_API_KEY}");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($addressData));
                
                $result = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curlError = curl_error($ch);
                curl_close($ch);
                
                if ($httpCode === 200) {
                    $googleResponse = json_decode($result);
                    
                    $response = $responseFactory->createFromArray(data: [
                        'status' => 'success', 
                        'code' => null, 
                        'message' => 'Validation effectuée avec succès', 
                        'data' => ['validation' => $googleResponse]
                    ]);
                } else {
                    $response = $responseFactory->createFromArray(data: [
                        'status' => 'error', 
                        'code' => $httpCode, 
                        'message' => "Erreur lors de l'appel à Google API: " . ($curlError ?: 'Code HTTP ' . $httpCode)
                    ]);
                }
            } catch (\Throwable $th) {
                $tools->myErrorHandler(errno: $th->getCode(), errstr: $th->getMessage(), errfile: $th->getFile(), errline: $th->getLine());
            }
            break;
            
        default:
            $response = $responseFactory->createFromArray(data: ['status' => 'error', 'code' => 2000, 'message' => "Le service demandé: " . $request->action . " n'existe pas"]);
            break;
    }
    
    // ✅ Mise à jour de la session et du authorized device
    $device = $authorizedDeviceService->getAuthorizedDeviceById(deviceId: $session->deviceId);
    $authorizedDeviceService->refreshAuthorizedDevice(authorizedDeviceId: $device->id);
} else {
    $response = $responseFactory->createFromArray(data: ['status' => 'error', 'code' => 5009, 'message' => "Pas de session valable, l'utilisateur doit se reconnecter"]);
}

echo json_encode(value: $response);