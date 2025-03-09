<?php

namespace App\DTOModels;


class DTOEvent
{
    public readonly ?string $id;
    public readonly ?string $userName;
    public readonly ?string $title;
    public readonly ?string $description;
    public readonly ?string $html;
    public readonly ?array $address;
    public readonly ?string $startDate;
    public readonly ?string $endDate;
    public readonly ?string $publishDate;
    public readonly ?string $openReservation;
    public readonly ?int $maxReservation;
    public readonly ?int $price;
    public readonly ?string $ageRestriction;
    public readonly ?array $reservation;
    public readonly ?array $user;
    public readonly ?array $categories;
    public readonly ?array $images;

    public function __construct(
        ?string $id,
        ?string $userName,
        ?string $title,
        ?string $description,
        ?string $html,
        ?array $address,
        ?string $startDate,
        ?string $endDate,
        ?string $publishDate,
        ?string $openReservation,
        ?int $maxReservation,
        ?int $price,
        ?string $ageRestriction,
        ?array $reservation,
        ?array $user,
        ?array $categories,
        ?array $images
    ){
        $this->id = $id;
        $this->userName = $userName;
        $this->title = $title;
        $this->description = $description;
        $this->html = $html;
        $this->address = $address;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->publishDate = $publishDate;
        $this->openReservation = $openReservation;
        $this->maxReservation = $maxReservation;
        $this->price = $price;
        $this->ageRestriction = $ageRestriction;
        $this->reservation = $reservation;
        $this->user = $user;
        $this->categories = $categories;
        $this->images = $images;
    }
}