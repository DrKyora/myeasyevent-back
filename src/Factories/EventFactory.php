<?php

namespace App\Factories;

use App\Models\Event;

class EventFactory
{
    public function createFromArray(array $data): Event
    {
        return new Event(
            id: $data["id"] ?? null,
            userId: $data["userId"] ?? null,
            title: $data["title"] ?? null,
            description: $data["description"] ?? null,
            html: $data["html"] ?? null,
            street: $data["street"] ?? null,
            streetNumber: $data["streetNumber"] ?? null,
            complement: $data["complement"] ?? null,
            zipCode: $data["zipCode"] ?? null,
            city: $data["city"] ?? null,
            country: $data["country"] ?? null,
            startDate: $data["startDate"] ?? null,
            endDate: $data["endDate"] ?? null,
            publishDate: $data["publishDate"] ?? null,
            openReservation: $data["openReservation"] ?? null,
            maxReservation: $data["maxReservation"] ?? null,
            price: $data["price"] ?? null,
            ageRestriction: $data["ageRestriction"] ?? null,
            isOneline: $data["isOneline"] ?? false,
            isDeleted: $data["isDeleted"] ?? false
        );
    }

    public function createFromJson(string $json): Event
    {
        $data = json_decode(json: $json, associative: true);
        if($data === null){
            throw new \Exception(message: "Invalid JSON format");
        }
        return self::createFromArray(data: $data);
    }
}