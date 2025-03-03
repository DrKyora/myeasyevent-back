<?php

namespace App\Factories;

use App\Models\BlacklistUser;

class BlacklistUserFactory
{
    public function createFromArray(array $data): BlacklistUser
    {
        return new BlacklistUser(
            id: $data['id'] ?? null,
            userId: $data['userId'] ?? null,
            date: $data['date'] ?? null
        );
    }

    public function createFromJson(string $json): BlacklistUser
    {
        $data = json_decode(json: $json, associative: true);
        if ($data === null) {
            throw new \Exception(message: "Invalid JSON format");
        }
        return $this->createFromArray(data: $data);
    }
}