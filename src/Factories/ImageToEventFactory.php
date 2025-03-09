<?php

namespace App\Factories;

use App\Models\ImageToEvent;

class ImageToEventFactory
{
    public function createFromArray(array $data): ImageToEvent
    {
        return new ImageToEvent(
            id: $data['id'] ?? null,
            eventId: $data['eventId'] ?? null,
            fileName: $data['fileName'] ?? null,
            isThumbnail: $data['isThumbnail'] ?? false,
            isDeleted: $data['isDeleted'] ?? false
        );
    }

    public function createFromJson(string $json): ImageToEvent
    {
        $data = json_decode(json: $json, associative: true);
        if($data === null){
            throw new \Exception(message: "Invalid JSON format");
        }
        return $this->createFromArray(data: $data);
    }
}