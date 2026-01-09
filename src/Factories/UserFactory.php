<?php

namespace App\Factories;

use App\Models\User;
use App\DTOModels\DTOUser;

class UserFactory
{
    public function createFromArray(array $data): User
    {
        return new User(
            id: $data['id'] ?? null,
            lastName: $data['lastName'] ?? null,
            firstName: $data['firstName'] ?? null,
            email: $data['email'] ?? null,
            password: $data['password'] ?? null,
            validateDate: $data['validateDate'] ?? null,
            isAdmin: $data['isAdmin'] ?? false,
            isDeleted: $data['isDeleted'] ?? false
        );
    }

    public function createFromJson(string $json): User
    {
        $data = json_decode(json: $json, associative: true);
        if($data === null){
            throw new \Exception(message: "Invalid JSON format");
        }
        return $this->createFromArray(data: $data);
    }

    public function createDynamic(User $user, array $fields): DTOUser
    {
        $filteredData = array_intersect_key((array) $user,array_flip($fields));
        return new DTOUser(
            id: $filteredData['id'] ?? null,
            lastName: $filteredData['lastName'] ?? null,
            firstName: $filteredData['firstName'] ?? null,
            email: $filteredData['email'] ?? null,
            validateDate: $filteredData['validateDate'] ?? null,
            isAdmin: $filteredData['isAdmin'] ?? null
        );
    }
}