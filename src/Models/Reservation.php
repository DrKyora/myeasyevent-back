<?php

namespace App\Models;

class Reservation
{
    public ?string $id;
    public ?string $eventId;
    public ?string $lastName;
    public ?string $firstName;
    public ?string $email;
    public ?string $birthDate;
    public ?string $dateReservation;
    public ?bool $isDeleted;

    public function __construct(
        ?string $id,
        ?string $eventId,
        ?string $lastName,
        ?string $firstName,
        ?string $email,
        ?string $birthDate,
        ?string $dateReservation,
        ?bool $isDeleted = false
    ){
        $this->id = $id;
        $this->eventId = $eventId;
        $this->lastName = $lastName;
        $this->firstName = $firstName;
        $this->email = $email;
        $this->birthDate = $birthDate;
        $this->dateReservation = $dateReservation;
        $this->isDeleted = $isDeleted;
    }
}