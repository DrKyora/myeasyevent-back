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
use App\Factories\EventFactory;
use App\Factories\UserFactory;
/**
 * Repositories
 */
use App\Repositories\SessionRepository;
use App\Repositories\EventRepository;
use App\Repositories\UserRepository;
/**
 * Validators
 */
use App\Validators\SessionValidationService;
use App\Validators\EventValidationService;
use App\Validators\UserValidationService;
/**
 * Services
 */
use App\Services\DBConnection;
use App\Services\SessionService;
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
$responseFactory = new ResponseFactory();
$eventFactory = new EventFactory();
$userFactory = new UserFactory();
/**
 * Repositories
 */
$sessionRepository = new SessionRepository(db: $db, tools: $tools, sessionFactory: $sessionFactory);
$eventRepository = new EventRepository(db: $db, tools: $tools, eventFactory: $eventFactory);
$userRepository = new UserRepository(db: $db, tools: $tools, userFactory: $userFactory);
/**
 * Validators
 */
$sessionValidationService = new SessionValidationService();
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
$eventService = new EventService(
    eventRepository: $eventRepository,
    eventValidationService: $eventValidationServicen,
    eventFactory: $eventFactory,
    responseErrorFactory: $responseErrorFactory
);
if ($sessionService->tokenSessionIsValide(tokenSession: $request->session)) {
    $sessionString = $tools->encrypt_decrypt(action: 'decrypt', stringToTreat: $request->session);
    $session = $sessionFactory->createFromJson(json: $sessionString);
    switch($request->action){
        case 'addEvent':
            try{
                $event = $eventFactory->createFromJson(json: $request->event);
                $newEvent = $eventService->createEvent(event: $event);
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
        case'getEventByUserId':
            try{
                $event = $eventService->getEventsByUserId(userId: $request->userId);
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
    }
} else {
    $response = $responseFactory->createFromArray(data: ['status' => 'error', 'code' => 5009, 'message' => "Pas de session valable, l'utilisateur doit se reconnecter"]);
}
echo json_encode(value: $response);