<?php

namespace App\Services;

use App\Factories\EventFactory;
use App\Models\Event;
use App\Models\Reservation;
use App\DTOModels\DTOEventReservation;

use App\Factories\ResponseErrorFactory;

use App\Repositories\EventRepository;
use App\Repositories\ReservationRepository;

use App\Responses\ResponseError;

use App\Validators\EventValidationService;

class EventService
{
    private EventRepository $eventRepository;
    private ReservationRepository $reservationRepository;
    private EventValidationService $eventValidationService;
    private EventFactory $eventFactory;
    private ResponseErrorFactory $responseErrorFactory;
    public function __construct(
        EventRepository $eventRepository,
        ReservationRepository $reservationRepository,
        EventValidationService $eventValidationService,
        EventFactory $eventFactory,
        ResponseErrorFactory $responseErrorFactory
    ){
        $this->eventRepository = $eventRepository;
        $this->reservationRepository = $reservationRepository;
        $this->eventValidationService = $eventValidationService;
        $this->eventFactory = $eventFactory;
        $this->responseErrorFactory = $responseErrorFactory;
    }

    public function getEventById(string $id): Event|ResponseError
    {
        try{
            $event = $this->eventRepository->getEventById(id: $id);
            return $event;
        } catch (\Exception $e) {
            return $this->responseErrorFactory->createFromArray(data: ['code' => $e->getCode(), 'message' => $e->getMessage()]);
        }
    }

    public function getEventsByUserId(string $userId): array|ResponseError
    {
        try{
            $events = $this->eventRepository->getEventByUserId(userId: $userId);
            $arrayDTOEventReservation = [];
            foreach($events as $event){
                $arrayReservations = [];
                $reservations = $this->reservationRepository->getResevationsOfEvent(eventId: $event->id);
                foreach($reservations as $reservation){
                    $arrayReservations[] = $reservation;
                }
                $arrayDTOEventReservation[] = new DTOEventReservation( event: $event,  reservation: $arrayReservations);
            }
            return $arrayDTOEventReservation;
        } catch (\Exception $e) {
            return $this->responseErrorFactory->createFromArray(data: ['code' => $e->getCode(), 'message' => $e->getMessage()]);
        }
    }

    public function getAllEvents(): array|ResponseError
    {
        try{
            $events = $this->eventRepository->getAllEvents();
            return $events;
        } catch (\Exception $e) {
            return $this->responseErrorFactory->createFromArray(data: ['code' => $e->getCode(), 'message' => $e->getMessage()]);
        }
    }

    public function search(string $search): array|ResponseError
    {
        try{
            $events = $this->eventRepository->search(search: $search);
            return $events;
        } catch (\Exception $e) {
            return $this->responseErrorFactory->createFromArray(data: ['code' => $e->getCode(), 'message' => $e->getMessage()]);
        }
    }

    public function createEvent(Event $event): Event|ResponseError
    {
        try{
            $this->eventValidationService->validate(event: $event);
            $newEvent = $this->eventRepository->addEvent(event: $event);
            return $newEvent;
        } catch (\Exception $e) {
            return $this->responseErrorFactory->createFromArray(data: ['code' => $e->getCode(), 'message' => $e->getMessage()]);
        }
    }

    public function updateEvent(Event $event): bool|ResponseError
    {
        try{
            $this->eventValidationService->validate(event: $event);
            $this->eventRepository->updateEvent(event: $event);
            return true;
        } catch (\Exception $e) {
            return $this->responseErrorFactory->createFromArray(data: ['code' => $e->getCode(), 'message' => $e->getMessage()]);
        }
    }

    public function deleteEvent(string $id): bool|ResponseError
    {
        try{
            $event = $this->eventRepository->getEventById( id: $id);
            if($event){
                $event->isDeleted = true;
                $this->eventRepository->updateEvent(event: $event);
            }
            return true;
        } catch (\Exception $e) {
            return $this->responseErrorFactory->createFromArray(data: ['code' => $e->getCode(), 'message' => $e->getMessage()]);
        }
    }
}