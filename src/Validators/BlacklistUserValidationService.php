<?php

namespace App\Validators;

use App\Models\BlacklistUser;

use App\Repositories\BlacklistUserRepository;

class BlacklistUserValidationService
{
    public function __construct(
        private BlacklistUserRepository $blacklistUserRepository
    ){
        $this->blacklistUserRepository = $blacklistUserRepository;
    }

    public function validate(BlacklistUser $blacklistUser): bool
    {
        if(empty($blacklistUser->userId)){
            throw new \Exception(message: "L'userId ne peut pas être vide", code: 5501);
        }
        if(empty($blacklistUser->date)){
            throw new \Exception(message: "La date ne peut pas être vide", code: 5502);
        }
        return true;
    }

    public function isBlacklist($userId): bool
    {
        if($this->blacklistUserRepository->getBlacklistUsersByUserId(userId: $userId)){
            throw new \Exception(message: "Cet utilisateur est blacklisté", code: 5501);
        }
        return false;
    }
}