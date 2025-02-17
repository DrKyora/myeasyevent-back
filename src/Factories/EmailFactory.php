<?php

namespace App\Factories;

use App\ServiceModels\Email;

class EmailFactory
{
    public function createFromArray(array $data): Email
    {
        return new Email(
            addressFrom: $data["addressFrom"],
            addressA: $data["addressA"],
            addressCc: $data["addressCc"],
            addressCci: $data["addressCcci"],
            subject: $data["subject"],
            content: $data["content"]
        );
    }

    public function createFromJson(string $json): email
    {
        $data = json_decode(json: $json);
        if($data === null){
            throw new \Exception(message: "Invalide JSON format");
        }
        return $this->createFromJson(json: $data);
    }

    public function createEmpty(): Email
    {
        return new Email();
    }
}