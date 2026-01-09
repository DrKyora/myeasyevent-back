<?php

namespace App\Validators;

use App\Lib\Tools;

use App\Models\Event;

use App\Repositories\EventRepository;

class EventValidationService
{
    private $eventRepository;

    public function __construct(
        EventRepository $eventRepository
    ){
        $this->eventRepository = $eventRepository;
    }

    public function validate(Event $event): bool
    {
        if(empty($event->userId)){
            throw new \Exception(message:"Veuillez renseigner l'ID de l'utilisateur",code: 5080 );
        }
        if(empty($event->title)){
            throw new \Exception(message:"Un titre est obligatoire",code: 5081 );
        }
        if(empty($event->description)){
            throw new \Exception(message:"Une description est obligatoire",code: 5082 );
        }
        if(empty($event->html)){
            throw new \Exception(message:"La selection d'un template est obligatoire",code: 5083 );
        }
        if(empty($event->startDate)){
            throw new \Exception(message:"Une date de début est obligatoire",code: 5084 );
        }
        if(empty($event->endDate)){
            throw new \Exception(message:"Une date de fin est obligatoire",code: 5085 );
        }
        if(empty($event->ageRestriction)){
            throw new \Exception(message:"Une restriction d'age est obligatoire",code: 5086);
        }
        if(empty($event->isOnline)){
            throw new \Exception(message:"Un status de publication est obligatoire",code: 5087);
        }
        return true;
    }
}