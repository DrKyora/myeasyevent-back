<?php

namespace App\Services;
// Require
require __DIR__ . '/../config.php';
// Library
use App\Lib\Tools;

// Responses
use App\Responses\ResponseError;
use App\Responses\Response;

// Models
use App\Models\AuthorizedDevice;

// Factories
use App\Factories\AuthorizedDeviceFactory;
use App\Factories\ResponseErrorFactory;
use App\Factories\ResponseFactory;

// Repositories
use App\Repositories\AuthorizedDeviceRepository;
use App\Repositories\UserRepository;

// Validators
use App\Validators\AuthorizedDeviceValidationService;
use App\Validators\UserValidationService;

//Service
use App\Services\EmailService;

class AuthorizedDeviceService
{
    private Tools $tools;
    private AuthorizedDeviceFactory $authorizedDeviceFactory;
    private ResponseErrorFactory $responseErrorFactory;
    private ResponseFactory $responseFactory;
    private AuthorizedDeviceRepository $authorizedDeviceRepository;
    private UserRepository $userRepository;
    private AuthorizedDeviceValidationService $authorizedDeviceValidationService;
    private UserValidationService $userValidationService;
    private EmailService $emailService;

    public function __construct(
        Tools $tools,
        AuthorizedDeviceFactory $authorizedDeviceFactory,
        ResponseErrorFactory $responseErrorFactory,
        ResponseFactory $responseFactory,
        AuthorizedDeviceRepository $authorizedDeviceRepository,
        UserRepository $userRepository,
        AuthorizedDeviceValidationService $authorizedDeviceValidationService,
        UserValidationService $userValidationService,
        EmailService $emailService
    ){
        $this->tools = $tools;
        $this->authorizedDeviceFactory = $authorizedDeviceFactory;
        $this->responseErrorFactory = $responseErrorFactory;
        $this->responseFactory = $responseFactory;
        $this->authorizedDeviceRepository = $authorizedDeviceRepository;
        $this->userRepository = $userRepository;
        $this->authorizedDeviceValidationService = $authorizedDeviceValidationService;
        $this->userValidationService = $userValidationService;
        $this->emailService= $emailService;
    }

    public function getAuthorizedDeviceById(string $id): AuthorizedDevice|ResponseError|null
    {
        try{
            $device = $this->authorizedDeviceRepository->getAuthorizedDeviceById(deviceId: $id);
            return $device;
        }catch(\Exception $e){
            return $this->responseErrorFactory->createFromArray(data: ['code' => $e->getCode(), 'message' => $e->getMessage()]);
        }
    }

    public function registerNewAuthorizedDevice(string $userId): AuthorizedDevice|ResponseError
    {
        try{
            $user_agent = $_SERVER['HTTP_USER_AGENT'];
            switch(true){
                case(preg_match(pattern: '/Windows/i', subject: $user_agent)):
                    $defaultName = 'Ordinateur';
                    $os = 'Windowws';
                    break;
                case(preg_match(pattern: '/Android/i', subject: $user_agent)):
                    $defaultName = 'Smartphone-tablette';
                    $os = 'Android';
                    break;
                case(preg_match(pattern: '/(iPhone|iPad|iPod)/i', subject: $user_agent));
                    $defaultName = 'Smartphone-tablette';
                    $os = 'iOS';
                    break;
                case(preg_match(pattern: '/Macintosh|Mac OS X/i', subject: $user_agent)):
                    $defaultName = 'Ordinateur';
                    $os = 'macOS';
                    break;
                default:
                    $defaultName = '';
                    $os = '';
            }
            preg_match(pattern: '/mobile/i', subject: $user_agent) ? $type = 'mobile' : $type = 'computer';
            $now = new \DateTime();
            $authorizedDevice = $this->authorizedDeviceFactory->createFromArray( array: [
                'name' => $defaultName,
                'type' => $type,
                'model' => $os,
                'userId' => $userId,
                'lastUsed' => $now->format(format: 'Y-m-q H:i:s.u')
            ]);
            $this->authorizedDeviceValidationService->validate(authorizedDevice: $authorizedDevice);
            $authorizedDevice = $this->authorizedDeviceRepository->addAuthorizedDevice(authorizedDevice: $authorizedDevice);
            return $authorizedDevice;
        }catch(\Exception $e){
            return $this->responseErrorFactory->createFromArray(data: ['code' => $e->getCode(), 'message' => $e->getMessage()]);
        }
    }

    public function authorizedDeviceExist(string $authorizedDeviceId = null): AuthorizedDevice|ResponseError|null
    {
        try{
            if($device = $this->authorizedDeviceRepository->getAuthorizedDeviceById(deviceId: $authorizedDeviceId)){
                return $device;
            }
            return null;
        }catch(\Exception $e){
            return $this->responseErrorFactory->createFromArray(data: ['code' => $e->getCode(), 'message' => $e->getMessage()]);
        }
    }

    public function authorizedDeviceIsValidate(AuthorizedDevice $authorizedDevice): bool|ResponseError
    {
        try{
            if($authorizedDevice->validateDate){
                return true;
            }else{
                return false;
            }
        }catch(\Exception $e){
            return $this->responseErrorFactory->createFromArray(data: ['code' => $e->getCode(), 'message' => $e->getMessage()]);
        }
    }

    public function confirmAuthorizedDevice(string $authorizedDeviceId)
    {
        try{
            if($authorizedDevice = $this->authorizedDeviceExist(authorizedDeviceId: $authorizedDeviceId)){
                if(!$this->authorizedDeviceIsValidate(authorizedDevice: $authorizedDevice)){
                    $authorizedDevice->validateDate = (new \DateTime())->format(format: 'Y-m-d H:i:s.u');
                    $this->authorizedDeviceRepository->updateAuthorizedDevice(authorizedDevice: $authorizedDevice);
                }
                return true;
            }else{
                return false;
            }
        }catch(\Exception $e){
            return $this->responseErrorFactory->createFromArray(data: ['code' => $e->getCode(), 'message' => $e->getMessage()]);
        }
    }

    public function gc_unusedAuthorizedDevice()
    {
        try{
            $this->authorizedDeviceRepository->deleteUnusedAuthorizedDevice(parameter: $_ENV['DEVICE_UNUSED_LIFETIME']);
        }catch(\Exception $e){
            return $this->responseErrorFactory->createFromArray(data: ['code' => $e->getCode(), 'message' => $e->getMessage()]);
        }
    }

    public function ConnectEmailPass(string $email,string $password): Response
    {
        try{
            $user = $this->userRepository->getUserByEmail(email: $email);
            if($user){
                if(password_verify( password: $password, hash: $user->password)){
                    $token = $this->tools->encrypt_decrypt(action: 'encrypt', stringToTreat: json_encode(value: $user));
                    return $this->responseFactory->createFromArray(data: ['status' => 'success', 'code' => null, 'message' => "Connection réussie", 'data' => ['token' => $token]]);
                } else {
                    return $this->responseFactory->createFromArray(data: ['status' => 'error', 'code' => 5017, 'message' => "Le mot de passe ne correspond pas"]);
                }
            } else {
                return $this->responseFactory->createFromArray(data: ['status' => 'error', 'code' => 5016, 'message' => "Cet utilisateur n'existe pas"]);
            }
        } catch (\Exception $e) {
            return $this->responseFactory->createFromArray(data: ['status' => 'error', 'code' => 5020, 'message' => "Erreur lors de la connexion avec login et mot de passe"]);
        }
    }
}