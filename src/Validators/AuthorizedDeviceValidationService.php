<?php

namespace App\Validators;

use App\Lib\Tools;

use App\Models\AuthorizedDevice;

use App\Repositories\AuthorizedDeviceRepository;

class AuthorizedDeviceValidationService
{
    private $tools;
    private $authorizedDeviceRepository;

    public function __construct(
        Tools $tools,
        AuthorizedDeviceRepository $authorizedDeviceRepository
    ){
        $this->tools = $tools;
        $this->authorizedDeviceRepository = $authorizedDeviceRepository;
    }

    public function validate(AuthorizedDevice $authorizedDevice): bool
    {
        $this->tools->logDebug(message: json_encode(value: $authorizedDevice));
        if(empty($authorizedDevice->userId)){
            throw new \Exception(message:"Veuillez renseigner l'ID de l'utilisateur",code: 5100 );
        }
        if(empty($authorizedDevice->lastUsed)){
            throw new \Exception(message: "Veuillez renseigner la date de derniere utilisation",code: 5101);
        }
        return true;
    }

    public function authorizedDeviceExists(string $authorizedDeviceId): bool
    {
        $checkDevice = $this->authorizedDeviceRepository->getAuthorizedDeviceById(deviceId: $authorizedDeviceId);
        if($checkDevice === null){
            throw new \Exception(message: "Veuillez renseigner l'ID de l'appareil autorisé",code: 5102);
        }
        return true;
    }
}