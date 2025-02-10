<?php

namespace App\Responses;

/**
 * Fonctionnement de ResponseError : 
 * Permet de créer un objet de retour en cas d'erreur
 * staus : contient le résultat (sueccess ou error)
 * message : contient le message d'erreur ou de success
 */

class ResponseError
{
    public ?int $code = 0;
    public ?string $message = null;
    public function __construct(int $code = 0, string $message = null)
    {
        $this->code = $code;
        $this->message = $message;
    }
}
