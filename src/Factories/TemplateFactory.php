<?php

namespace App\Factories;

use App\Models\Template;

class TemplateFactory
{
    public function createFromArray(array $data): Template
    {
        return new Template(
            id: $data['id'] ?? null,
            description: $data['description'] ?? null,
            html: $data['html'] ?? null,
            isDeleted: $data['isDeleted'] ?? false
        );
    }

    public function createFromJson(string $json): Template
    {
        $data = json_decode(json: $json, associative: true);
        if ($data === null) {
            throw new \Exception(message: "Invalid JSON format");
        }
        return $this->createFromArray(data: $data);
    }
}