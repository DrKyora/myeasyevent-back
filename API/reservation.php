<?php

require __DIR__ . '/../config.php';
require __DIR__ . '/../vendor/autoload.php';

use App\Responses\ResponseError;

$request = json_decode(json: file_get_contents(filename: 'php://input'));

$dependances = new \App\Services\DépendancesContainer();

switch($request->action){
    case 'addReservation':
        try {
            $reservation = $dependances->reservationFactory->createFromArray(data: [
                'eventId' => $request->eventId,
                'firstName' => $request->firstName,
                'lastName' => $request->lastName,
                'email' => $request->email,
                'birthDate' => $request->birthDate,
                'dateReservation' => date(format: 'Y-m-d H:i:s')
            ]);
            
            $newReservation = $dependances->reservationService->createReservation(
                reservation: $reservation, 
                eventId: $request->eventId
            );
            
            if (!$newReservation instanceof ResponseError) {
                $response = $dependances->responseFactory->createFromArray(data: ['status' => 'success', 'code' => null, 'message' => 'Réservation créée avec succès', 'data' => ['reservation' => $newReservation]]);
            } else {
                $error = $newReservation;
                $response = $dependances->responseFactory->createFromArray(data: ['status' => 'error', 'code' => $error->code, 'message' => $error->message]);
            }
            
        } catch (\Throwable $th) {
            $dependances->tools->myErrorHandler(errno: $th->getCode(), errstr: $th->getMessage(), errfile: $th->getFile(), errline: $th->getLine());
        }
        break;
        
    case 'getReservationsOfEvent':
        try {
            $reservations = $dependances->reservationService->getReservationsOfEvent(eventId: $request->eventId);
            
            if (!$reservations instanceof ResponseError) {
                $response = $dependances->responseFactory->createFromArray(data: ['status' => 'success', 'code' => null, 'message' => 'Réservations trouvées', 'data' => ['reservations' => $reservations]]);
            } else {
                $error = $reservations;
                $response = $dependances->responseFactory->createFromArray(data: ['status' => 'error', 'code' => $error->code, 'message' => $error->message]);
            }
            
        } catch (\Throwable $th) {
            $dependances->tools->myErrorHandler(errno: $th->getCode(), errstr: $th->getMessage(), errfile: $th->getFile(), errline: $th->getLine());
        }
        break;
        
    default:
        $response = $dependances->responseFactory->createFromArray(data: ['status' => 'error','code' => 2000,'message' => "Le service demandé: " . $request->action . " n'existe pas"]);
}

if (!isset($response)) {
    $response = $dependances->responseFactory->createFromArray(data: ['status' => 'error', 'code' => 500, 'message' => 'Une erreur est survenue']);
}
echo json_encode(value: $response);