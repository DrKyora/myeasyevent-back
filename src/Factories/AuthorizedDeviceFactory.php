<?php

namespace App\Factories;

use App\Models\AuthorizedDevice;

class AuthorizedDeviceFactory
{
    public function createFromArray(array $array): AuthorizedDevice
    {
        return new AuthorizedDevice(
            id: $array['id'] ?? null,
            name: $array['name'] ?? null,
            type: $array['type'] ?? null,
            model: $array['model'] ?? null,
            userId: $array['userId'] ?? null,
            validateDate: $array['validateDate'] ?? null,
            lastUsed: $array['lastUsed'] ?? null,
            isDeleted: $array['isDeleted'] ?? false
        );
    }

    public function createFromJson(string $json): AuthorizedDevice
    {
        $data = json_decode(json: $json, associative: true);
        if ($data === null) {
            throw new \Exception(message: "Invalid JSON format");
        }
        return $this->createFromArray( array: $data);
    }

    public function createFromString(string $string): AuthorizedDevice
    {
        if($string === null){
            throw new \Exception(message: "Invalid string format");
        }
        return $this->createFromJson(json: $string);
    }
}