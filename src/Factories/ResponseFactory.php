<?php

namespace App\Factories;

use App\Responses\Response;

class ResponseFactory
{
    public function createFromArray(array $data): Response
    {
        $response = new Response(
            status: $data['status'] ?? null,
            code: $data['code'] ?? null,
            message: $data['message'] ?? null,
            data: $data['data'] ?? null
        );
        if (!$_ENV['DEV_MODE']) {
            unset($response->message);
        }
        return $response;
    }

    public function createFromJson(string $json): Response
    {
        $data = json_decode(json: $json, associative: true);

        if ($data === null) {
            throw new \Exception(message: "Invalid JSON format");
        }

        return $this->createFromArray(data: $data);
    }
}
