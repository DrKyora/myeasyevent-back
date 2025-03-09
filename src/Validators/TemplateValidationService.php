<?php

namespace App\Validators;

use App\Models\Template;

class TemplateValidationService
{
    public function validate(Template $template): bool
    {
        if(empty($template->title)){
            throw new \Exception(message: "Veuillez renseigner le titre du template",code: 5050);
        }
        if(empty($template->html)) {
            throw new \Exception(message: "Veuillez renseigner le HTML du template",code: 5051);
        }
        return true;
    }
}