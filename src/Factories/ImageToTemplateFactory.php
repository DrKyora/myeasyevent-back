<?php

namespace App\Factories;

use App\Models\ImageToTemplate;

class ImageToTemplateFactory
{
    public function createFromArray(array $data, bool $isThumbnail = false): ImageToTemplate
    {
        return new ImageToTemplate(
            id: $data['id'] ?? null,
            templateId: $data['templateId'] ?? null,
            fileName: $data['fileName'] ?? null,
            isThumbnail: $isThumbnail,
            isDeleted: $data['isDeleted'] ?? false
        );
    }

    public function createFromJson(string $json, bool $isThumbnail = false): ImageToTemplate
    {
        $data = json_decode(json: $json, associative: true);
        if($data === null){
            throw new \Exception(message: "Invalid JSON format");
        }
        return $this->createFromArray(data: $data);
    }
}