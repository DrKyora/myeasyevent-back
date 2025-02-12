<?php

namespace App\Factories;

use App\Models\User;

class UserFactory
{
    public static function createFromArray(array $data): User
    {
        return new User(
            id: $data['id'] ?? null,
            lastName: $data['lastName'] ?? null,
            firstName: $data['firstName'] ?? null,
            email: $data['email'] ?? null,
            password: $data['password'] ?? null,
            isAdmin: $data['isAdmin'] ?? false,
            isDeleted: $data['isDeleted'] ?? false
        );
    }

    public static function createFromJson(string $json): User
    {
        $data = json_decode(json: $json, associative: true);
        if($data === null){
            throw new \Exception(message: "Invalid JSON format");
        }
        return self::createFromArray(data: $data);
    }
}