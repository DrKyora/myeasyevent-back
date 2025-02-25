<?php

namespace App\Models;

class ImageToTemplate
{
    public string $id;
    public string $templateId;
    public string $fileName;
    public bool $isDeleted;

    public function __construct(
        ?string $id,
        ?string $templateId,
        ?string $fileName,
        ?bool $isDeleted = false
    ){
        $this->id = $id;
        $this->templateId = $templateId;
        $this->fileName = $fileName;
        $this->isDeleted = $isDeleted;
    }
}