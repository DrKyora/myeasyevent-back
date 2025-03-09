<?php

namespace App\DTOModels;


class DTOTemplate
{
    public readonly ?string $id;
    public readonly ?string $title;
    public readonly ?string $description;
    public readonly ?string $html;
    public readonly ?array $images;
    public readonly ?array $categories;

    public function __construct(
        ?string $id,
        ?string $title,
        ?string $description,
        ?string $html,
        ?array $images,
        ?array $categories
    ){
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->html = $html;
        $this->images = $images;
        $this->categories = $categories;
    }
}