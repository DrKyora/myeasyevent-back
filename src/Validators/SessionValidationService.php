<?php

namespace App\Validators;

use App\Models\Session;

class SessionValidationService
{
    public function __construct(){}

    public function validate(Session $session): bool
    {
        if(empty($session->userId)){
            throw new \Exception(message:"L'id de l'utilisateur est manquant",code: 4950 );
        }
        if(empty($session->lastAction)){
            throw new \Exception(message:"La dernière action est manquante",code: 4951 );
        }
        return true;
    }
}