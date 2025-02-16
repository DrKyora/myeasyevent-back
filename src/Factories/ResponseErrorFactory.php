<?php

namespace App\Factories;

use App\Responses\ResponseError;

class ResponseErrorFactory
{
    public function createFromArray(array $data): ResponseError
    {
        $response = new ResponseError(
            code: $data['code'] ?? null,
            message: $data['message'] ?? null,
        );
        if (!$_ENV['DEV_MODE']) {
            unset($response->message);
        }
        return $response;
    }

    public function createFromJson(string $json): ResponseError
    {
        $data = json_decode(json: $json, associative: true);

        if ($data === null) {
            throw new \Exception(message: "Invalid JSON format");
        }

        return $this->createFromArray(data: $data);
    }
}
