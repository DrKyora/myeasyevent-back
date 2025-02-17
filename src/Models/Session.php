<?php

namespace App\Models;

class Session
{
    public ?string $id;
    public ?string $userId;
    public ?string $deviceId;
    public ?string $lastAction;
    public ?bool $isDeleted;

    public function __construct(
        ?string $id,
        ?string $userId,
        ?string $deviceId,
        ?string $lastAction,
        ?bool $isDeleted = false
    ){
        $this->id = $id;
        $this->userId = $userId;
        $this->deviceId = $deviceId;
        $this->lastAction = $lastAction;
        $this->isDeleted = $isDeleted;
    }
}
