<?php

namespace App\Services;
// Require
require_once __DIR__ . '\..\..\config.php';
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
use App\Factories\LogsBadFactory;

// Repositories
use App\Repositories\AuthorizedDeviceRepository;
use App\Repositories\UserRepository;
use App\Repositories\LogsBadRepository;

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
    private LogsBadFactory $logsBadFactory;
    private AuthorizedDeviceRepository $authorizedDeviceRepository;
    private UserRepository $userRepository;
    private LogsBadRepository $logsBadRepository;
    private AuthorizedDeviceValidationService $authorizedDeviceValidationService;
    private UserValidationService $userValidationService;
    private EmailService $emailService;

    public function __construct(
        Tools $tools,
        AuthorizedDeviceFactory $authorizedDeviceFactory,
        ResponseErrorFactory $responseErrorFactory,
        ResponseFactory $responseFactory,
        LogsBadFactory $logsBadFactory,
        AuthorizedDeviceRepository $authorizedDeviceRepository,
        UserRepository $userRepository,
        LogsBadRepository $logsBadRepository,
        AuthorizedDeviceValidationService $authorizedDeviceValidationService,
        UserValidationService $userValidationService,
        EmailService $emailService
    ){
        $this->tools = $tools;
        $this->authorizedDeviceFactory = $authorizedDeviceFactory;
        $this->responseErrorFactory = $responseErrorFactory;
        $this->responseFactory = $responseFactory;
        $this->logsBadFactory = $logsBadFactory;
        $this->authorizedDeviceRepository = $authorizedDeviceRepository;
        $this->userRepository = $userRepository;
        $this->logsBadRepository = $logsBadRepository;
        $this->authorizedDeviceValidationService = $authorizedDeviceValidationService;
        $this->userValidationService = $userValidationService;
        $this->emailService= $emailService;
    }

    public function getAuthorizedDeviceById(string $deviceId): AuthorizedDevice|ResponseError|null
    {
        try{
            $device = $this->authorizedDeviceRepository->getAuthorizedDeviceById(deviceId: $deviceId);
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
                case (preg_match(pattern: '/Windows/i', subject: $user_agent)):
                    $defaultName = 'Ordinateur';
                    $os = 'Windowws';
                    break;
                case (preg_match(pattern: '/Android/i', subject: $user_agent)):
                    $defaultName = 'Smartphone-tablette';
                    $os = 'Android';
                    break;
                case (preg_match(pattern: '/(iPhone|iPad|iPod)/i', subject: $user_agent));
                    $defaultName = 'Smartphone-tablette';
                    $os = 'iOS';
                    break;
                case (preg_match(pattern: '/Macintosh|Mac OS X/i', subject: $user_agent)):
                    $defaultName = 'Ordinateur';
                    $os = 'macOS';
                    break;
                default:
                    $defaultName = '';
                    $os = 'Autres';
            }
            preg_match(pattern: '/mobile/i', subject: $user_agent) ? $type = 'mobile' : $type = 'computer';
            $now = new \DateTime();
            $authorizedDevice = $this->authorizedDeviceFactory->createFromArray( array: [
                'name' => $defaultName,
                'type' => $type,
                'model' => $os,
                'userId' => $userId,
                'lastUsed' => $now->format(format: 'Y-m-d H:i:s.u')
            ]);
            $this->authorizedDeviceValidationService->validate(authorizedDevice: $authorizedDevice);
            $authorizedDevice = $this->authorizedDeviceRepository->addAuthorizedDevice(authorizedDevice: $authorizedDevice);
            return $authorizedDevice;
        }catch(\Exception $e){
            return $this->responseErrorFactory->createFromArray(data: ['code' => $e->getCode(), 'message' => $e->getMessage()]);
        }
    }

    public function authorizedDeviceExist(?string $authorizedDeviceId = null): AuthorizedDevice|ResponseError|null
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

    public function confirmAuthorizedDevice(string $authorizedDeviceId): bool|ResponseError
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

    public function ConnectEmailPass(string $email, string $password, string $ip): Response
    {
        try {
            $user = $this->userRepository->getUserByEmail(email: $email);
            if ($user) {
                if (password_verify(password: $password, hash: $user->password)) {
                    $device = $this->registerNewAuthorizedDevice(userId: $user->id);
                    $token = $this->tools->encrypt_decrypt(action: 'encrypt', stringToTreat: json_encode(value: $device));
                    $this->emailService->sendMail(
                        addressFrom: [
                            'address' => $_ENV['MAIL_DEFAULT_FROM_ADDRESSE'],
                            'name' => $_ENV['MAIL_DEFAULT_FROM_NAME'],
                        ],
                        addressA: [
                            [
                                'address' => $user->email,
                                'name' => $user->lastName . ' ' . $user->firstName
                            ]
                        ],
                        addressCc: null,
                        addressCci: null,
                        subject: 'My easy event connexion',
                        contentsEmails:[
                            '{{UserName}}' => $user->lastName . ' ' . $user->firstName,
                            '{{DeviceID}}' => $this->tools->encrypt_decrypt(action: 'encrypt', stringToTreat: $device->id),
                            '{{URLConfirm}}' => $_ENV['CONFIRM_DEVICES_PATH']
                        ],
                        urlTemplate: __DIR__ . '/../../templates/emails/validateDevice.html'
                    );
                    return $this->responseFactory->createFromArray(data: ['status' => 'success', 'code' => null, 'message' => "Device enregistré", 'data' => ['token' => $token]]);
                } else {
                    try {
                        $newLog = $this->logsBadFactory->createFromArray(data: [
                            'ip' => $ip,
                            'userId' => $user->id,
                            'date' => (new \DateTime())->format(format: 'Y-m-d H:i:s')
                        ]);
                        $this->logsBadRepository->addLog(logsBad: $newLog);
                    } catch (\Exception $e) {
                        return $this->responseFactory->createFromArray(data: ['status' => 'error', 'code' => 5503, 'message' => "Erreur lors de l'ajout du logbad"]);
                    }
                    return $this->responseFactory->createFromArray(data: ['status' => 'error', 'code' => 5017, 'message' => "Le mot de passe ne correspond pas"]);
                }
            } else {
                try{
                    $newLog = $this->logsBadFactory->createFromArray( data: [
                        'ip' => $ip,
                        'date' => (new \DateTime())->format(format: 'Y-m-d H:i:s')
                    ]);
                    $this->logsBadRepository->addLog(logsBad: $newLog);
                } catch (\Exception $e) {
                    return $this->responseFactory->createFromArray(data: ['status' => 'error', 'code' => 5503, 'message' => "Erreur lors de l'ajout du logbad"]);
                }
                return $this->responseFactory->createFromArray(data: ['status' => 'error', 'code' => 5016, 'message' => "Cet utilisateur n'existe pas"]);
            }
        } catch (\Exception $e) {
            return $this->responseFactory->createFromArray(data: ['status' => 'error', 'code' => 5020, 'message' => "Erreur lors de la connexion avec login et mot de passe"]);
        }
    }

    public function refreshAuthorizedDevice(string $authorizedDeviceId): bool|ResponseError
    {
        try{
            $authorizedDevice = $this->authorizedDeviceRepository->getAuthorizedDeviceById(deviceId: $authorizedDeviceId);
            $now = new \DateTime();
            $authorizedDevice->lastUsed = $now->format(format: 'Y-m-d H:i:s.u');
            $this->authorizedDeviceRepository->updateAuthorizedDevice(authorizedDevice: $authorizedDevice);
            return true;
        } catch (\Exception $e) {
            return $this->responseErrorFactory->createFromArray(data: ['code' => $e->getCode(), 'message' => $e->getMessage()]);
        }
    }

    public function deleteAuthorizedDevice(string $id): bool|ResponseError
    {
        try{
            $device = $this->authorizedDeviceRepository->getAuthorizedDeviceById(deviceId: $id);
            if($device){
                $device->isDeleted = true;
                $this->authorizedDeviceRepository->updateAuthorizedDevice(authorizedDevice: $device);
            }
            return true;
        } catch (\Exception $e) {
            return $this->responseErrorFactory->createFromArray(data: ['code' => $e->getCode(), 'message' => $e->getMessage()]);
        }
    }
}