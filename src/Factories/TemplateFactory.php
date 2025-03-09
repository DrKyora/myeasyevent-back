<?php

namespace App\Factories;

use App\Models\Template;
use App\DTOModels\DTOTemplate;

class TemplateFactory
{
    public function createFromArray(array $data): Template
    {
        return new Template(
            id: $data['id'] ?? null,
            title: $data['title'] ?? null,
            html: $data['html'] ?? null,
            description: $data['description'] ?? null,
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

    public function createDynamic(Template $template, array $fields, ?array $images = null, ?array $categories = null): DTOTemplate
    {
        $filteredData = array_intersect_key((array) $template, array_flip($fields));
        return new DTOTemplate(
            id: $filteredData['id'] ?? null,
            title: $filteredData['title'] ?? null,
            description: $filteredData['description'] ?? null,
            html: $filteredData['html'] ?? null,
            images: $images,
            categories: $categories
        );
    }
}