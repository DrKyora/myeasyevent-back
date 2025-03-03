<?php

namespace App\DTOModels;

use App\Models\Event;
use App\Models\User;

class DTOEventUser
{
    public readonly array $event;
    public readonly User $user;

    public function __construct(
        array $event,
        User $user
    ){
        $this->event = $event;
        $this->user = $user;
    }
}