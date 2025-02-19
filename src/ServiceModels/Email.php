<?php

namespace App\ServiceModels;

class Email
{
    public ?array $addressFrom;
    public ?array $addressA;
    public ?array $addressCc;
    public ?array $addressCci;
    public ?string $subject;
    public ?string $content;

    public function __construct(
        array $addressFrom = null,
        array $addressA = null,
        array $addressCc = null,
        array $addressCci = null,
        string $subject = null,
        string $content = null
    ){
        $this->addressFrom = $addressFrom;
        $this->addressA = $addressA;
        $this->addressCc = $addressCc;
        $this->addressCci = $addressCci;
        $this->subject = $subject;
        $this->content = $content;
    }
}