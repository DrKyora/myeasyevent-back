<?php

namespace App\DTOModels;

use App\Models\Event;
use App\Models\User;

class DTOEventUser
{
    public User $user;
    public Event $event;

    public function __construct(
        User $user,
        Event $event
    ){
        $this->user = $user;
        $this->event = $event;
    }
}