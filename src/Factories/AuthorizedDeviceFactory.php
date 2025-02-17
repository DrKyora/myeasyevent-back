<?php

namespace App\Factories;

use App\Models\AuthorizedDevice;

class AuthorizedDeviceFactory
{
    public function createFromArray(array $array): AuthorizedDevice
    {
        return new AuthorizedDevice(
            id: $data['id'] ?? null,
            name: $data['name'] ?? null,
            type: $data['type'] ?? null,
            model: $data['model'] ?? null,
            userId: $data['userId'] ?? null,
            validateDate: $data['validateDate'] ?? null,
            lastUsed: $data['lastUsed'] ?? null,
            isDeleted: $data['isDeleted'] ?? false
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