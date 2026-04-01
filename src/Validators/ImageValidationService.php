<?php

namespace App\Validators;

class ImageValidationService
{
    private int $maxSize;

    public function __construct(int $maxMaxSize = 2)
    {
        $this->maxSize = $maxMaxSize * 1024 * 1024;
    }

    public function isValide(string $base64Image): bool
    {
        if (str_contains(haystack : $base64Image, needle:',')){
            $base64Image = explode(separator : ',', string : $base64Image)[1];
        }

        $padding = substr_count(haystack: $base64Image, needle:'=');
        $sizeInBytes = (strlen(string: $base64Image) * 3) / 4 - $padding;

        return $sizeInBytes <= $this->maxSize;
    }
}