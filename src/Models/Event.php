<?php

namespace App\Models;

class Event
{
    public ?string $id;
    public ?string $userId;
    public ?string $title;
    public ?string $description;
    public ?string $html;
    public ?string $street;
    public ?string $streetNumber;
    public ?string $complement;
    public ?string $zipCode;
    public ?string $city;
    public ?string $country;
    public ?string $startDate;
    public ?string $endDate;
    public ?string $publishDate;
    public ?string $openReservation;
    public ?int $maxReservation;
    public ?int $price;
    public ?string $ageRestriction;
    public ?bool $isOneline;
    public ?bool $isDeleted;

    public function __construct(
        ?string $id,
        ?string $userId,
        ?string $title,
        ?string $description,
        ?string $html,
        ?string $street,
        ?string $streetNumber,
        ?string $complement,
        ?string $zipCode,
        ?string $city,
        ?string $country,
        ?string $startDate,
        ?string $endDate,
        ?string $publishDate,
        ?string $openReservation,
        ?int $maxReservation,
        ?int $price,
        ?string $ageRestriction,
        ?bool $isOneline = false,
        ?bool $isDeleted = false
    ){
        $this->id = $id;
        $this->userId = $userId;
        $this->title = $title;
        $this->description = $description;
        $this->html = $html;
        $this->street = $street;
        $this->streetNumber = $streetNumber;
        $this->complement = $complement;
        $this->zipCode = $zipCode;
        $this->city = $city;
        $this->country = $country;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->publishDate = $publishDate;
        $this->openReservation = $openReservation;
        $this->maxReservation = $maxReservation;
        $this->price = $price;
        $this->ageRestriction = $ageRestriction;
        $this->isOneline = $isOneline;
        $this->isDeleted = $isDeleted;
    }
}