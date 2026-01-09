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
    private $emailService; // ✅ AJOUTÉ

    public function __construct(
        ReservationFactory $reservationFactory,
        ReservationRepository $reservationRepository,
        EventRepository $eventRepository,
        ReservationValidationService $reservationValidationService,
        ResponseErrorFactory $responseErrorFactory,
        EmailService $emailService // ✅ AJOUTÉ
    ){
        $this->reservationFactory = $reservationFactory;
        $this->reservationRepository = $reservationRepository;
        $this->eventRepository = $eventRepository;
        $this->reservationValidationService = $reservationValidationService;
        $this->responseErrorFactory = $responseErrorFactory;
        $this->emailService = $emailService; // ✅ AJOUTÉ
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
        
        // ✅ ENVOI DE L'EMAIL DE CONFIRMATION
        $addressFrom = ['address' => $_ENV['EMAIL_FROM'] ?? 'noreply@myeasyevent.com', 'name' => 'MyEasyEvent'];
        $addressA = [['address' => $reservation->email, 'name' => $reservation->firstName . ' ' . $reservation->lastName]];
        $contentsEmails = [
            '{{firstName}}' => $reservation->firstName,
            '{{lastName}}' => $reservation->lastName,
            '{{eventTitle}}' => $event->title,
            '{{eventDate}}' => date('d/m/Y', strtotime($event->startDate)),  // ❌ dateEvent → ✅ startDate
            '{{eventTime}}' => date('H:i', strtotime($event->startDate)),    // ❌ dateEvent → ✅ startDate
            '{{eventAddress}}' => $event->streetNumber . ' ' . $event->street . ', ' . $event->zipCode . ' ' . $event->city,
        ];
        $urlTemplate = __DIR__ . '/../../templates/emails/reservations-confirmation.html';
        
        $emailResult = $this->emailService->sendMail(
            addressFrom: $addressFrom,
            addressA: $addressA,
            addressCc: null,
            addressCci: null,
            subject: 'Confirmation de votre réservation - ' . $event->title,
            contentsEmails: $contentsEmails,
            urlTemplate: $urlTemplate
        );
        
        // Note : On ne bloque pas si l'email échoue, la réservation est déjà créée
        if($emailResult instanceof ResponseError){
            error_log("Email sending failed for reservation {$reservation->id}: {$emailResult->message}");
        }
        
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