<?php

namespace App\Models;

class Template{
    public ?string $id;
    public ?string $title;
    public ?string $description;
    public ?string $html;
    public bool $isDeleted;

    public function __construct(
        ?string $id,
        ?string $title,
        ?string $description,
        ?string $html,
        ?bool $isDeleted = false
    ){
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->html = $html;
        $this->isDeleted = $isDeleted;
    }
}