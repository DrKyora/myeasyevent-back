<?php

namespace App\DTOModels;

class DTOUser
{
    public readonly ?string $id;
    public readonly ?string $lastName;
    public readonly ?string $firstName;
    public readonly ?string $email;
    public readonly ?string $validateDate;
    public readonly ?bool $isAdmin;
    public readonly ?bool $isDeleted;

    public function __construct(
        ?string $id,
        ?string $lastName,
        ?string $firstName,
        ?string $email,
        ?string $validateDate,
        ?bool $isAdmin,
        ?bool $isDeleted
    ){
        $this->id = $id;
        $this->lastName = $lastName;
        $this->firstName = $firstName;
        $this->email = $email;
        $this->validateDate = $validateDate;
        $this->isAdmin = $isAdmin;
        $this->isDeleted = $isDeleted;
    }
}