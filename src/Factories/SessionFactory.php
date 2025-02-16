<?php

namespace App\Factories;

use App\Models\Session;

class SessionFactory
{
    public function createFromArray(array $data): Session
    {
        return new Session(
            id: $data['id'] ?? null,
            userId: $data['userId'] ?? null,
            lastAction: $data['lastAction'] ?? null,
            isDeleted: $data['isDeleted'] ?? false
        );
    }

    public function createFromJson(string $json): Session
    {
        $data = json_decode(json: $json, associative: true);
        if ($data === null) {
            throw new \Exception(message: "Invalid JSON format");
        }
        return $this->createFromArray(data: $data);
    }

    public function createFromString(string $string)
    {
        if($string === null){
            throw new \Exception(message: "Invalide String format");
        }
        return $this->createFromJson(json: $string);
    }
}