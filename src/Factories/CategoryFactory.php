<?php

namespace App\Factories;

use App\Models\Category;

class CategoryFactory
{
    public function createFromArray(array $data): Category
    {
        return new Category(
            id: $data['id'] ?? null,
            name: $data['name'] ?? null,
            isDeleted: $data['isDeleted'] ?? false
        );
    }

    public function createFromJson(string $json): Category
    {
        $data = json_decode(json: $json, associative: true);
        if($data === null){
            throw new \Exception(message: "Invalid JSON format");
        }
        return $this->createFromArray(data: $data);
    }
}