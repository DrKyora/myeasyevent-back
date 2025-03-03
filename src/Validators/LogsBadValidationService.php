<?php

namespace App\Validators;

use App\Models\LogsBad;

use App\Repositories\LogsBadRepository;

class LogsBadValidationService
{
    private $logsBadRepository;
    
    public function __construct(
        LogsBadRepository $logsBadRepository
    ){
        $this->logsBadRepository = $logsBadRepository;
    }

    public function validate(LogsBad $logsBad): bool
    {
        if(empty($logsBad->ip)){
            throw new \Exception(message: "L'ip ne peut pas être vide", code: 5500);
        }
        if(empty($logsBad->userId)){
            throw new \Exception(message: "L'userId ne peut pas être vide", code: 5501);
        }
        if(empty($logsBad->logDate)){
            throw new \Exception(message: "La date ne peut pas être vide", code: 5502);
        }
        return true;
    }
}