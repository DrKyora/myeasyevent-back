<?php

namespace App\Models;

class Category
{
    public ?string $id;
    public ?string $name;
    public ?bool $isDeleted;

    public function __construct(
        ?string $id,
        ?string $name,
        ?bool $isDeleted = false
    ){
        $this->id = $id;
        $this->name = $name;
        $this->isDeleted = $isDeleted;
    }
}