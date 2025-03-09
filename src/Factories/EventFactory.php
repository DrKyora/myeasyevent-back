<?php

namespace App\Factories;

use App\DTOModels\DTOEvent;
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
        return $this->createFromArray(data: $data);
    }

    public function createDynamic(Event $event, array $fields,?string $userName = null, ?array $address = null, ?array $reservation = null, ?array $user = null, ?array $categories = null, ?array $images = null): DTOEvent
    {
        $filteredData = array_intersect_key((array) $event,array_flip($fields));
        return new DTOEvent(
            id: $filteredData['id'] ?? null,
            userName: $userName,
            title: $filteredData['title'] ?? null,
            description: $filteredData['description'] ?? null,
            html: $filteredData['html'] ?? null,
            address: $address,
            startDate: $filteredData['startDate'] ?? null,
            endDate: $filteredData['endDate'] ?? null,
            publishDate: $filteredData['publishDate'] ?? null,
            openReservation: $filteredData['openReservation'] ?? null,
            maxReservation: $filteredData['maxReservation'] ?? null,
            price: $filteredData['price'] ?? null,
            ageRestriction: $filteredData['ageRestriction'],
            reservation: $reservation,
            user: $user,
            categories: $categories,
            images: $images
        );
    }
}