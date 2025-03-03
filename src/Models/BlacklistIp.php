<?php

namespace App\Models;

class BlacklistIp
{
    public $id;
    public $ip;
    public $date;

    public function __construct(
        ?string $id,
        ?string $ip,
        ?string $date
    ){
        $this->id = $id;
        $this->ip = $ip;
        $this->date = $date;
    }
}