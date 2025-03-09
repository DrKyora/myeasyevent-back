<?php

namespace App\Models;

class ImageToTemplate
{
    public string $id;
    public string $templateId;
    public string $fileName;
    public string $isThumbnail;
    public bool $isDeleted;

    public function __construct(
        ?string $id,
        ?string $templateId,
        ?string $fileName,
        bool $isThumbnail = false,
        ?bool $isDeleted = false
    ){
        $this->id = $id;
        $this->templateId = $templateId;
        $this->fileName = $fileName;
        $this->isThumbnail = $isThumbnail;
        $this->isDeleted = $isDeleted;
    }
}