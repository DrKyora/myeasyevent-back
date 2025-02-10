<?php

namespace App\Responses;

/**
 * Fonctionnement de Response : 
 * Permet de créer un objet de retour en cas de susccess ou error
 * staus : contient le résultat (sueccess ou error)
 * message : contient le message d'erreur ou de success
 * data : contient les donnée renvoyée en cas de success
 */

class Response
{
    public ?string $status;
    public ?int $code = 0;
    public ?string $message = null;
    public ?array $data = [];
    public function __construct(string $status = null, ?int $code = 0, string $message = null, array $data = null)
    {
        $this->status = $status;
        $this->code = $code;
        $this->message = $message;
        $this->data = $data;
    }
}
