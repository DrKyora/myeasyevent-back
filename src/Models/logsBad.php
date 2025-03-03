<?php

namespace App\Models;

class LogsBad
{
    public ?string $id;
    public ?string $ip;
    public ?string $userId;
    public ?string $date;

    public function __construct(
        ?string $id,
        ?string $ip,
        ?string $userId,
        ?string $date
    ){
        $this->id = $id;
        $this->ip = $ip;
        $this->userId = $userId;
        $this->date = $date;
    }
}