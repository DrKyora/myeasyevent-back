<?php

namespace App\Factories;

use App\Models\BlacklistIp;

class BlacklistIpFactory
{
    public function createFromArray(array $data): BlacklistIp
    {
        return new BlacklistIp(
            id: $data['id'] ?? null,
            ip: $data['ip'] ?? null,
            date: $data['date'] ?? null
        );
    }

    public function createFromJson(string $json): BlacklistIp
    {
        $data = json_decode(json: $json, associative: true);
        if ($data === null) {
            throw new \Exception(message: "Invalid JSON format");
        }
        return $this->createFromArray(data: $data);
    }
}