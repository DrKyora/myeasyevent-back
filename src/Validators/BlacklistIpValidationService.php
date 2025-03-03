<?php

namespace App\Validators;

use App\Models\BlacklistIp;

use App\Repositories\BlacklistIpRepository;

class BlacklistIpValidationService
{
    public function __construct(
        private BlacklistIpRepository $blacklistIpRepository
    ){
        $this->blacklistIpRepository = $blacklistIpRepository;
    }
    public function validate(BlacklistIp $blacklistIp): bool
    {
        if(empty($blacklistIp->ip)){
            throw new \Exception(message: "L'ip ne peut pas êtres vide", code: 5500);
        }
        if(empty($blacklistIp->date)){
            throw new \Exception(message: "La date ne peut pas être vide", code: 5502);
        }
        return true;
    }

    public function isBlacklist($ip): bool
    {
        if($this->blacklistIpRepository->getBlacklistIpByIp(ip: $ip)){
            throw new \Exception(message: "Cette adresse IP est blacklisté",code : 5500);
        }
        return true;
    }
}