<?php

namespace App\Validators;

use App\Lib\Tools;

use App\Models\Reservation;
use App\Models\Event;

use App\Repositories\ReservationRepository;

class ReservationValidationService
{
    private $tools;
    private $reservationRepository;

    public function __construct(
        Tools $tools,
        ReservationRepository $reservationRepository
    ){
        $this->tools = $tools;
        $this->reservationRepository = $reservationRepository;
    }

    public function validateCreate(Reservation $reservation, Event $event): bool
    {
        $this->reservationContainMandatoryProperties(reservation: $reservation);
        $this->emailIsValid(email: $reservation->email);
        $this->reservationExistToCreate(reservation: $reservation);
        $this->ageIsPresent(reservation: $reservation);
        $this->verifyAge(reservation: $reservation, event: $event);
        return true;
    }

    public function validateUpdate(Reservation $reservation, Event $event): bool
    {
        $this->reservationContainMandatoryProperties(reservation: $reservation);
        $this->emailIsValid(email: $reservation->email);
        $this->reservationExistToUpdate(reservation: $reservation);
        $this->ageIsPresent(reservation: $reservation);
        $this->verifyAge(reservation: $reservation,event: $event);
        return true;
    }

    public function reservationContainMandatoryProperties(Reservation $reservation): bool
    {
        if (empty($reservation->firstName) && empty($reservation->lastName)) {
            throw new \Exception(message: "Veuillez renseigner le prénom et le prénom", code: 5300);
        }
        return true;
    }

    public function emailIsValid(string $email): bool
    {
        if (empty($email)) {
            throw new \Exception(message: "Veuillez renseigner l'email", code: 5301);
        }
        if (!filter_var(value: $email, filter: FILTER_VALIDATE_EMAIL)) {
            throw new \Exception(message: "Veuillez renseigner un email valide", code: 5302);
        }
        return true;
    }

    public function reservationExistToCreate(Reservation $reservation): bool
    {
        if($this->reservationRepository->reservationExist(emailToVerif: $reservation->email)){
            throw new \Exception(message: "Une reservation avec cet email existe deja", code: 5303);
        }
        return true;
    }

    public function reservationExistToUpdate(Reservation $reservation): bool
    {
        if($this->reservationRepository->reservationExist(emailToVerif: $reservation->email,excludedId: $reservation->id)){
            throw new \Exception(message: "Une reservation avec cet email existe deja", code: 5303);
        }
        return true;
    }

    public function ageIsPresent(Reservation $reservation): bool
    {
        if (empty($reservation->age)) {
            throw new \Exception(message: "Veuillez renseigner votre age", code: 5304);
        }
        return true;
    }

    public function verifyAge(Reservation $reservation, Event $event): bool
    {
        if ($this->tools->calculAge(dateNaissance: $reservation->birthDate) < $event->ageRestriction) {
            throw new \Exception(message: "Vous devez avoir au moins " . $event->ageRestriction . " ans", code: 5305);
        }
        return true;
    }
}