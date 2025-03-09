<?php
ini_set(option: 'display_errors', value: 1);

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config.php';
header(header: 'Access-Control-Allow-Origin: ' . $_ENV['FRONT_END_URL']);
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
use App\Factories\EventFactory;
use App\Factories\UserFactory;
use App\Factories\ImageToEventFactory;
use App\Factories\CategoryFactory;
use App\Factories\LogsBadFactory;
/**
 * Repositories
 */
use App\Repositories\SessionRepository;
use App\Repositories\AuthorizedDeviceRepository;
use App\Repositories\EventRepository;
use App\Repositories\UserRepository;
use App\Repositories\ImageToEventRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\LogsBadRepository;
/**
 * Validators
 */
use App\Validators\SessionValidationService;
use App\Validators\AuthorizedDeviceValidationService;
use App\Validators\EventValidationService;
use App\Validators\UserValidationService;
/**
 * Services
 */
use App\Services\DBConnection;
use App\Services\SessionService;
use App\Services\AuthorizedDeviceService;
use App\Services\EventService;
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
$eventFactory = new EventFactory();
$userFactory = new UserFactory();
$categoryFactory = new CategoryFactory();
$imageToEventFactory = new ImageToEventFactory();
$logsBadFactory = new LogsBadFactory();
/**
 * Repositories
 */
$sessionRepository = new SessionRepository(db: $db, tools: $tools, sessionFactory: $sessionFactory);
$authorizedDeviceRepository = new AuthorizedDeviceRepository(db: $db, tools: $tools, authorizedDeviceFactory: $authorizedDeviceFactory);
$eventRepository = new EventRepository(db: $db, tools: $tools, eventFactory: $eventFactory);
$userRepository = new UserRepository(db: $db, tools: $tools, userFactory: $userFactory);
$imageToEventRepository = new ImageToEventRepository(db: $db,tools: $tools,imageToEventFactory: $imageToEventFactory);
$categoryRepository = new CategoryRepository(db: $db,tools: $tools,categoryFactory: $categoryFactory);
$logsBadRepository = new LogsBadRepository(db: $db,tools: $tools,logsBadFactory: $logsBadFactory);
/**
 * Validators
 */
$sessionValidationService = new SessionValidationService();
$authorizedDeviceValidationService = new AuthorizedDeviceValidationService(tools: $tools,authorizedDeviceRepository: $authorizedDeviceRepository);
$eventValidationService = new EventValidationService(eventRepository: $eventRepository);
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
$eventService = new EventService(
    eventRepository: $eventRepository,
    reservationRepository: $reservationRepository,
    userRepository: $userRepository,
    categoryRepository: $categoryRepository,
    imageToEventRepository: $imageToEventRepository,
    eventValidationService: $eventValidationService,
    eventFactory: $eventFactory,
    imageToEventFactory: $imageToEventFactory,
    categoryFactory: $categoryFactory,
    responseErrorFactory: $responseErrorFactory
);
if ($sessionService->tokenSessionIsValide(tokenSession: $request->session)) {
    $sessionString = $tools->encrypt_decrypt(action: 'decrypt', stringToTreat: $request->session);
    $session = $sessionFactory->createFromJson(json: $sessionString);
    switch($request->action){
        case 'addEvent':
            try{
                $event = $eventFactory->createFromJson(json: $request->event);
                $newEvent = $eventService->createEvent(event: $event,images: $request->images, categories: $request->categories);
                if(!$newEvent instanceof ResponseError){
                    $response = $responseFactory->createFromArray(data: ['status' => 'success', 'code' => null, 'message' => "Création de l'evenement réussie", 'data' => ['newEvent' => $newEvent]]);
                } else {
                    $error = $newEvent;
                    $response = $responseFactory->createFromArray(data: ['status' => 'error', 'code' => $error->code, 'message' => $error->message]);
                }
            } catch (\Throwable $th) {
                $tools->myErrorHandler(errno: $th->getCode(), errstr: $th->getMessage(), errfile: $th->getFile(), errline: $th->getLine());
            }
            break;
        case 'updateEvent':
            try{
                $event = $eventFactory->createFromJson(json: $request->event);
                $updateEvent = $eventService->updateEvent(event: $event);
                if(!$updateEvent instanceof ResponseError){
                    $response = $responseFactory->createFromArray(data: ['status' => 'success', 'code' => null, 'message' => "Modification de l'évenement réussie", 'data' => ['updateEvent' => $updateEvent]]);
                } else {
                    $error = $newFolder;
                    $response = $responseFactory->createFromArray(data: ['status' => 'error', 'code' => $error->code, 'message' => $error->message]);
                }
            } catch (\Throwable $th) {
                $tools->myErrorHandler(errno: $th->getCode(), errstr: $th->getMessage(), errfile: $th->getFile(), errline: $th->getLine());
            }
            break;
        case 'getEventById':
            try{
                $event = $eventService->getEventById(id: $request->id);
                if(!$event instanceof ResponseError){
                    $response = $responseFactory->createFromArray(data: ['status' => 'success', 'code' => null, 'message' => "Event trouvé", 'data' => ['event' => $event]]);
                } else {
                    $error = $newFolder;
                    $response = $responseFactory->createFromArray(data: ['status' => 'error', 'code' => $error->code, 'message' => $error->message]);
                }
            } catch (\Throwable $th) {
                $tools->myErrorHandler(errno: $th->getCode(), errstr: $th->getMessage(), errfile: $th->getFile(), errline: $th->getLine());
            }
            break;
        case'getEventsOfUser':
            try{
                $event = $eventService->getEventsOfUser(userId: $request->userId);
                if(!$event instanceof ResponseError){
                    $response = $responseFactory->createFromArray(data: ['status' => 'success', 'code' => null, 'message' => "Events trouver", 'data' => ['event' => $event]]);
                } else {
                    $error = $newFolder;
                    $response = $responseFactory->createFromArray(data: ['status' => 'error', 'code' => $error->code, 'message' => $error->message]);
                }
            } catch (\Throwable $th) {
                $tools->myErrorHandler(errno: $th->getCode(), errstr: $th->getMessage(), errfile: $th->getFile(), errline: $th->getLine());
            }
            break;
        case'getAllEvents':
            try{
                $events = $eventService->getAllEvents();
                if(!$events instanceof ResponseError){
                    $response = $responseFactory->createFromArray(data: ['status' => 'success', 'code' => null, 'message' => "Tous les evenements trouves", 'data' => ['events' => $events]]);
                } else {
                    $error = $newFolder;
                    $response = $responseFactory->createFromArray(data: ['status' => 'error', 'code' => $error->code, 'message' => $error->message]);
                }
            } catch (\Throwable $th) {
                $tools->myErrorHandler(errno: $th->getCode(), errstr: $th->getMessage(), errfile: $th->getFile(), errline: $th->getLine());
            }
            break;
        case'searchEvent':
            try{
                $events = $eventService->search(search: $request->search);
                if(!$events instanceof ResponseError){
                    $response = $responseFactory->createFromArray(data: ['status' => 'success', 'code' => null, 'message' => "Evenements trouves", 'data' => ['events' => $events]]);
                } else {
                    $error = $newFolder;
                    $response = $responseFactory->createFromArray(data: ['status' => 'error', 'code' => $error->code, 'message' => $error->message]);
                }
            } catch (\Throwable $th) {
                $tools->myErrorHandler(errno: $th->getCode(), errstr: $th->getMessage(), errfile: $th->getFile(), errline: $th->getLine());
            }
            break;
        case'deleteEvent':
            try{
                $event = $eventService->deleteEvent(id: $request->id);
                if(!$event instanceof ResponseError){
                    $response = $responseFactory->createFromArray(data: ['status' => 'success', 'code' => null, 'message' => "Event supprimé", 'data' => ['event' => $event]]);
                } else {
                    $error = $newFolder;
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