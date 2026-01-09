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
use App\Factories\ResponseFactory;
use App\Factories\ReservationFactory;
use App\Factories\EventFactory;
use App\Factories\EmailFactory; // ✅ AJOUTÉ
/**
 * Repositories
 */
use App\Repositories\ReservationRepository;
use App\Repositories\EventRepository;
/**
 * Validators
 */
use App\Validators\ReservationValidationService;
use App\Validators\EmailValidationService; // ✅ AJOUTÉ
/**
 * Services
 */
use App\Services\DBConnection;
use App\Services\ReservationService;
use App\Services\EmailService; // ✅ AJOUTÉ
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
$reservationFactory = new ReservationFactory();
$eventFactory = new EventFactory();
$emailFactory = new EmailFactory(); // ✅ AJOUTÉ
/**
 * Repositories
 */
$reservationRepository = new ReservationRepository(db: $db, tools: $tools, factory: $reservationFactory);
$eventRepository = new EventRepository(db: $db, tools: $tools, eventFactory: $eventFactory);
/**
 * Validators
 */
$reservationValidationService = new ReservationValidationService(
    tools: $tools,
    reservationRepository: $reservationRepository
);
$emailValidationService = new EmailValidationService();
/**
 * Services
 */
$emailService = new EmailService( // ✅ AJOUTÉ
    tools: $tools,
    emailFactory: $emailFactory,
    responseErrorFactory: $responseErrorFactory,
    emailValidationService: $emailValidationService
);

$reservationService = new ReservationService(
    reservationFactory: $reservationFactory,
    reservationRepository: $reservationRepository,
    eventRepository: $eventRepository,
    reservationValidationService: $reservationValidationService,
    responseErrorFactory: $responseErrorFactory,
    emailService: $emailService // ✅ AJOUTÉ
);

switch($request->action){
    case 'addReservation':
        try {
            // Créer l'objet Reservation depuis les données reçues
            $reservation = $reservationFactory->createFromArray(data: [
                'eventId' => $request->eventId,
                'firstName' => $request->firstName,
                'lastName' => $request->lastName,
                'email' => $request->email,
                'birthDate' => $request->birthDate,
                'dateReservation' => date(format: 'Y-m-d H:i:s')
            ]);
            
            // Créer la réservation via le service
            $newReservation = $reservationService->createReservation(
                reservation: $reservation, 
                eventId: $request->eventId
            );
            
            if (!$newReservation instanceof ResponseError) {
                $response = $responseFactory->createFromArray(data: [
                    'status' => 'success', 
                    'code' => null, 
                    'message' => 'Réservation créée avec succès', 
                    'data' => ['reservation' => $newReservation]
                ]);
            } else {
                $error = $newReservation;
                $response = $responseFactory->createFromArray(data: [
                    'status' => 'error', 
                    'code' => $error->code, 
                    'message' => $error->message
                ]);
            }
            
        } catch (\Throwable $th) {
            $tools->myErrorHandler(errno: $th->getCode(), errstr: $th->getMessage(), errfile: $th->getFile(), errline: $th->getLine());
        }
        break;
        
    case 'getReservationsOfEvent':
        try {
            $reservations = $reservationService->getReservationsOfEvent(eventId: $request->eventId);
            
            if (!$reservations instanceof ResponseError) {
                $response = $responseFactory->createFromArray(data: [
                    'status' => 'success', 
                    'code' => null, 
                    'message' => 'Réservations trouvées', 
                    'data' => ['reservations' => $reservations]
                ]);
            } else {
                $error = $reservations;
                $response = $responseFactory->createFromArray(data: [
                    'status' => 'error', 
                    'code' => $error->code, 
                    'message' => $error->message
                ]);
            }
            
        } catch (\Throwable $th) {
            $tools->myErrorHandler(errno: $th->getCode(), errstr: $th->getMessage(), errfile: $th->getFile(), errline: $th->getLine());
        }
        break;
        
    default:
        $response = $responseFactory->createFromArray(data: [
            'status' => 'error', 
            'code' => 2000, 
            'message' => "Le service demandé: " . $request->action . " n'existe pas"
        ]);
        break;
}

echo json_encode(value: $response);