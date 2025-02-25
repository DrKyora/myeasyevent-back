<?php

namespace App\DTOModels;

use App\Models\Event;
use App\Models\Reservation;

class DTOEventReservation
{
    public readonly Event $event;
    public readonly ?array $reservation;

    public function __construct(
        Event $event,
        ?array $reservation
    ){
        $this->event = $event;
        $this->reservation = $reservation;
    }
}