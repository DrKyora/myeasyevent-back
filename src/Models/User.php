<?php

namespace App\Models;

class User
{
    public ?string $id;
    public ?string $lastName;
    public ?string $firstName;
    public ?string $email;
    public ?string $password;
    public ?bool $isAdmin;
    public ?bool $isDeleted;

    public function __construct(
        ?string $id,
        ?string $lastName,
        ?string $firstName,
        ?string $email,
        ?string $password,
        ?bool $isAdmin = false,
        ?bool $isDeleted = false
    ){
        $this->id = $id;
        $this->lastName = $lastName;
        $this->firstName = $firstName;
        $this->email = $email;
        $this->password = $password;
        $this->isAdmin = $isAdmin;
        $this->isDeleted = $isDeleted;
    }
}