<?php

namespace App\Validators;

use App\Models\Category;


class CategoryValidationService
{
    public function __construct(){}

    public function validate(Category $category)
    {
        if(empty($category->name)){
            throw new \Exception(message:"Veuillez renseigner le nom de la categorie",code: 5400 );
        }
    }
}