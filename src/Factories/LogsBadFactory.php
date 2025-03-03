<?php

namespace App\Factories;

use App\Models\LogsBad;

class LogsBadFactory
{
    public function createFromArray(array $data): LogsBad
    {
        return new LogsBad(
            id: $data['id'] ?? null,
            ip: $data['ip'] ?? null,
            userId: $data['userId'] ?? null,
            date: $data['date'] ?? null
        );
    }

    public function createFromJson(string $json): LogsBad
    {
        $data = json_decode(json: $json, associative: true);
        if ($data === null) {
            throw new \Exception(message: "Invalid JSON format");
        }
        return $this->createFromArray(data: $data);
    }
}