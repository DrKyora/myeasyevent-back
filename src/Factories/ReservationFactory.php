<?php

namespace App\Factories;

use App\Models\Reservation;

class ReservationFactory
{
    public function createFromArray(array $data): Reservation
    {
        return new Reservation(
            id: $data['id'] ?? null,
            eventId: $data['eventId'] ?? null,
            lastName: $data['lastName'] ?? null,
            firstName: $data['firstName'] ?? null,
            email: $data['email'] ?? null,
            birthDate: $data['birthDate'] ?? null,
            dateReservation: $data['dateReservation'] ?? null,
            isDeleted: $data['isDeleted'] ?? false
        );
    }

    public function createFromJson(string $json): Reservation
    {
        $data = json_decode(json: $json, associative: true);
        if ($data === null) {
            throw new \Exception(message: "Invalid JSON format");
        }
        return $this->createFromArray(data: $data);
    }
}