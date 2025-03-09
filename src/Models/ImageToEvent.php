<?php

namespace App\Models;

class ImageToEvent
{
    public string $id;
    public string $eventId;
    public string $fileName;
    public bool $isThumbnail;
    public bool $isDeleted;

    public function __construct(
        ?string $id,
        ?string $eventId,
        ?string $fileName,
        ?bool $isThumbnail = false,
        ?bool $isDeleted = false
    ){
        $this->id = $id;
        $this->eventId = $eventId;
        $this->fileName = $fileName;
        $this->isThumbnail = $isThumbnail;
        $this->isDeleted = $isDeleted;
    }
}