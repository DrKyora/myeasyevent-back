<?php

namespace App\Models;

class Template{
    public $id;
    public $description;
    public $html;
    public $isDeleted;

    public function __construct(
        ?string $id,
        ?string $description,
        ?string $html,
        ?bool $isDeleted = false
    ){
        $this->id = $id;
        $this->description = $description;
        $this->html = $html;
        $this->isDeleted = $isDeleted;
    }
}