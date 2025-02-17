<?php

namespace App\Models;

class AuthorizedDevice
{
    public ?string $id;
    public ?string $name;
    public ?string $type;
    public ?string $model;
    public ?string $userId;
    public ?string $validateDate;
    public ?string $lastUsed;
    public ?bool $isDeleted = false;

    public function __construct(
        ?string $id,
        ?string $name,
        ?string $type,
        ?string $model,
        ?string $userId,
        ?string $validateDate,
        ?string $lastUsed,
        ?bool $isDeleted = false
    ){
        $this->id = $id;
        $this->name = $name;
        $this->type = $type;
        $this->model = $model;
        $this->userId = $userId;
        $this->validateDate = $validateDate;
        $this->lastUsed = $lastUsed;
        $this->isDeleted = $isDeleted;
    }
}