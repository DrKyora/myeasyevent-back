<?php

namespace App\Services;

/**
 * Models
 */
use App\Models\Reservation;
use App\Models\Event;
use App\Responses\ResponseError;
/**
 * Factories
 */
use App\Factories\ReservationFactory;
use App\Factories\ResponseErrorFactory;
/**
 * Repositories
 */
use App\Repositories\ReservationRepository;
use App\Repositories\EventRepository;
/**
 * Validators
 */
use App\Validators\ReservationValidationService;

class ReservationService
{
    private $reservationFactory;
    private $reservationRepository;
    private $eventRepository;
    private $reservationValidationService;
    private $responseErrorFactory;

    public function __construct(
        ReservationFactory $reservationFactory,
        ReservationRepository $reservationRepository,
        EventRepository $eventRepository,
        ReservationValidationService $reservationValidationService,
        ResponseErrorFactory $responseErrorFactory
    ){
        $this->reservationFactory = $reservationFactory;
        $this->reservationRepository = $reservationRepository;
        $this->eventRepository = $eventRepository;
        $this->reservationValidationService = $reservationValidationService;
        $this->responseErrorFactory = $responseErrorFactory;
    }

    public function getReservationsOfEvent(string $eventId): array|ResponseError
    {
        try{
            $reservations = $this->reservationRepository->getResevationsOfEvent(eventId: $eventId);
            return $reservations;
        } catch (\Exception $e) {
            return $this->responseErrorFactory->createFromArray(data: ['code' => $e->getCode(), 'message' => $e->getMessage()]);
        }
    }

    public function createReservation(Reservation $reservation, string $eventId): Reservation|ResponseError
    {
        try{
            $event = $this->eventRepository->getEventById( id: $eventId);
            $this->reservationValidationService->validateCreate(reservation: $reservation,event: $event);
            $reservation = $this->reservationRepository->addReservation(reservation: $reservation);
            return $reservation;
        } catch (\Exception $e) {
            return $this->responseErrorFactory->createFromArray(data: ['code' => $e->getCode(), 'message' => $e->getMessage()]);
        }
    }

    public function updateReservation(Reservation $reservation, Event $event): Reservation|ResponseError
    {
        try{
            $this->reservationValidationService->validateUpdate(reservation: $reservation, event: $event);
            $this->reservationRepository->updateReservation(reservation: $reservation);
            return $reservation;
        } catch (\Exception $e) {
            return $this->responseErrorFactory->createFromArray(data: ['code' => $e->getCode(), 'message' => $e->getMessage()]);
        }
    }

    public function deleteReservation($reservationId): bool|ResponseError
    {
        try{
            $reservation = $this->reservationRepository->getReservationById( id: $reservationId);
            if($reservation){
                $reservation->isDeleted = true;
                $this->reservationRepository->updateReservation(reservation: $reservation);
            }
            return true;
        } catch (\Exception $e) {
            return $this->responseErrorFactory->createFromArray(data: ['code' => $e->getCode(), 'message' => $e->getMessage()]);
        }
    }
}