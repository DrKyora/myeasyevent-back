<?php

namespace App\DTOModels;

use App\Models\Event;
use App\Models\Reservation;

class DTOEventReservation
{
    public readonly Event $event;
    public readonly ?Reservation $reservation;

    public function __construct(
        Event $event,
        ?Reservation $reservation
    ){
        $this->event = $event;
        $this->reservation = $reservation;
    }
}