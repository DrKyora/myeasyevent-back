<?php

namespace App\Models;

class BlacklistUser
{
    public $id;
    public $userId;
    public $date;

    public function __construct(
        ?string $id,
        ?string $userId,
        ?string $date
    ){
        $this->id = $id;
        $this->userId = $userId;
        $this->date = $date;
    }
}