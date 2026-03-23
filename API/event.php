<?php

require __DIR__ . '/../config.php';
require __DIR__ . '/../vendor/autoload.php';

use App\Responses\ResponseError;

$request = json_decode(json: file_get_contents(filename: 'php://input'));
$dependances = new \App\Services\DépendancesContainer();

switch($request->action){
    case 'addEvent':
        try{
            $session = $dependances->tools->encrypt_decrypt(action: 'decrypt', stringToTreat: $request->session);
            $sessionObj = $dependances->sessionFactory->createFromJson(json: $session);
            $event = $dependances->eventFactory->createFromArray(data: (array) $request->event);
            $event->userId = $sessionObj->userId;
            $newEvent = $dependances->eventService->createEvent(event: $event,images: $request->images, categories: $request->categories);
            if(!$newEvent instanceof ResponseError){
                $response = $dependances->responseFactory->createFromArray(data: ['status' => 'success', 'code' => null, 'message' => "Création de l'evenement réussie", 'data' => ['newEvent' => $newEvent]]);
            } else {
                $error = $newEvent;
                $response = $dependances->responseFactory->createFromArray(data: ['status' => 'error', 'code' => $error->code, 'message' => $error->message]);
            }
        } catch (\Throwable $th) {
            $dependances->tools->myErrorHandler(errno: $th->getCode(), errstr: $th->getMessage(), errfile: $th->getFile(), errline: $th->getLine());
        }
        break;
    case 'updateEvent':
        try{
            $event = $dependances->eventFactory->createFromJson(json: $request->event);
            $updateEvent = $dependances->eventService->updateEvent(event: $event);
            if(!$updateEvent instanceof ResponseError){
                $response = $dependances->responseFactory->createFromArray(data: ['status' => 'success', 'code' => null, 'message' => "Modification de l'évenement réussie", 'data' => ['updateEvent' => $updateEvent]]);
            } else {
                $error = $updateEvent;
                $response = $dependances->responseFactory->createFromArray(data: ['status' => 'error', 'code' => $error->code, 'message' => $error->message]);
            }
        } catch (\Throwable $th) {
            $dependances->tools->myErrorHandler(errno: $th->getCode(), errstr: $th->getMessage(), errfile: $th->getFile(), errline: $th->getLine());
        }
        break;
    case 'getEventById':
        try{
            $event = $dependances->eventService->getEventById(id: $request->id);
            if(!$event instanceof ResponseError){
                $response = $dependances->responseFactory->createFromArray(data: ['status' => 'success', 'code' => null, 'message' => "Event trouvé", 'data' => ['event' => $event]]);
            } else {
                $error = $event;
                $response = $dependances->responseFactory->createFromArray(data: ['status' => 'error', 'code' => $error->code, 'message' => $error->message]);
            }
        } catch (\Throwable $th) {
            $dependances->tools->myErrorHandler(errno: $th->getCode(), errstr: $th->getMessage(), errfile: $th->getFile(), errline: $th->getLine());
        }
        break;
    case'getEventsOfUser':
        try{
            $session = $dependances->tools->encrypt_decrypt(action: 'decrypt', stringToTreat: $request->session);
            $sessionObj = $dependances->sessionFactory->createFromJson(json: $session);
            $events = $dependances->eventService->getEventsOfUser(userId: $userId = $sessionObj->userId);
            if(!$events instanceof ResponseError){
                $response = $dependances->responseFactory->createFromArray(data: ['status' => 'success', 'code' => null, 'message' => "Events trouver", 'data' => ['events' => $events]]);
            } else {
                $error = $events;
                $response = $dependances->responseFactory->createFromArray(data: ['status' => 'error', 'code' => $error->code, 'message' => $error->message]);
            }
        } catch (\Throwable $th) {
            $dependances->tools->myErrorHandler(errno: $th->getCode(), errstr: $th->getMessage(), errfile: $th->getFile(), errline: $th->getLine());
        }
        break;
    case'getAllEvents':
        try{
            $events = $dependances->eventService->getAllEvents();
            if(!$events instanceof ResponseError){
                $response = $dependances->responseFactory->createFromArray(data: ['status' => 'success', 'code' => null, 'message' => "Tous les evenements trouves", 'data' => ['events' => $events]]);
            } else {
                $error = $events;
                $response = $dependances->responseFactory->createFromArray(data: ['status' => 'error', 'code' => $error->code, 'message' => $error->message]);
            }
        } catch (\Throwable $th) {
            $dependances->tools->myErrorHandler(errno: $th->getCode(), errstr: $th->getMessage(), errfile: $th->getFile(), errline: $th->getLine());
        }
        break;
    case'searchEvent':
        try{
            $events = $dependances->eventService->search(search: $request->search);
            if(!$events instanceof ResponseError){
                $response = $dependances->responseFactory->createFromArray(data: ['status' => 'success', 'code' => null, 'message' => "Evenements trouves", 'data' => ['events' => $events]]);
            } else {
                $error = $events;
                $response = $dependances->responseFactory->createFromArray(data: ['status' => 'error', 'code' => $error->code, 'message' => $error->message]);
            }
        } catch (\Throwable $th) {
            $dependances->tools->myErrorHandler(errno: $th->getCode(), errstr: $th->getMessage(), errfile: $th->getFile(), errline: $th->getLine());
        }
        break;

    default:
        $response = $dependances->responseFactory->createFromArray(data: ['status' => 'error', 'code' => 2000, 'message' => "Le service demandé: " . $request->action . " n'existe pas"]);
        break;
}

echo json_encode(value: $response);