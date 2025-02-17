<?php

namespace App\Validators;

use App\ServiceModels\Email;

class EmailValidationService
{
    public function __construct(){}

    public function validate(Email $sendMail)
    {
        if(count(value: $sendMail->addressFrom) > 1){
            throw new \Exception(message:"Une seul adresse email d'expédition est requise", code: 5200);
        }
        if(empty($sendMail->addressFrom)){
            throw new \Exception(message:"Une adresse email d'expédition est requise", code: 5201);
        }
        if(empty($sendMail->addressA)){
            throw new \Exception(message:"Une adresse email de destination est requise", code: 5202);
        }
        if(empty($sendMail->subject)){
            throw new \Exception(message:"Un sujet est requis", code: 5203);
        }
        if(empty($sendMail->content)){
            throw new \Exception(message:"Un message est requis", code: 5204);
        }
        if($response = $this->EmailsIsInvalide(emails: $sendMail->addressFrom)){
            throw new \Exception(message: $response[0],code: $response[1]);
        }
        if($response = $this->EmailsIsInvalide(emails: $sendMail->addressA)){
            throw new \Exception(message: $response[0],code: $response[1]);
        }
        if(!empty($sendMail->addressCc)){
            if($response = $this->EmailsIsInvalide(emails: $sendMail->addressCc)){
                throw new \Exception(message: $response[0],code: $response[1]);
            }
        }
        if(!empty($sendMail->addressCci)){
            if($response = $this->EmailsIsInvalide(emails: $sendMail->addressCci)){
                throw new \Exception(message: $response[0],code: $response[1]);
            }
        }
        return true;
    }

    private function EmailsIsInvalide(array $emails): array|false
    {
        foreach($emails as $email){
            if(!preg_match(pattern:'/^[a-zA-Z0-9.%+-]+@[a-zA-Z0-9.-]+.[a-zA-Z]{2,}$/', subject:$email['address'])){
                return ['Adresse email invalide', 5205];
            }
            if(empty($email['name'])){
                return ['Un nom de destinataire est requis', 5206];
            }
        }
        return false;
    }
}