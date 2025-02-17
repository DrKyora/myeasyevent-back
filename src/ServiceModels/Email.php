<?php

namespace App\ServiceModels;

class Email
{
    public ?array $addressFrom;
    public ?array $addressA;
    public ?array $addressCc;
    public ?array $addressCci;
    public ?array $subject;
    public ?array $content;

    public function __construct(
        array $addressFrom = null,
        array $addressA = null,
        array $addressCc = null,
        array $addressCci = null,
        array $subject = null,
        array $content = null
    ){
        $this->addressFrom = $addressFrom;
        $this->addressA = $addressA;
        $this->addressCc = $addressCc;
        $this->addressCci = $addressCci;
        $this->subject = $subject;
        $this->content = $content;
    }
}