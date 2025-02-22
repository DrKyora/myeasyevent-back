<?php

namespace App\Services;

use App\Lib\Tools;

use App\Models\User;

use App\Factories\UserFactory;
use App\Factories\ResponseErrorFactory;
use App\Factories\ResponseFactory;

use App\Repositories\UserRepository;

use App\Responses\ResponseError;
use App\Responses\Response;

use App\Validators\UserValidationService;

use App\Services\EmailService;

class UserService
{
    private Tools $tools;
    private UserRepository $userRepository;
    private UserValidationService $userValidationService;
    private UserFactory $userFactory;
    private ResponseErrorFactory $responseErrorFactory;
    private ResponseFactory $responseFactory;
    private EmailService $emailService;
    public function __construct(
        Tools $tools,
        UserRepository $userRepository,
        UserValidationService $userValidationService,
        UserFactory $userFactory,
        ResponseFactory $responseFactory,
        ResponseErrorFactory $responseErrorFactory,
        EmailService $emailService
    ){
        $this->tools = $tools;
        $this->userRepository = $userRepository;
        $this->userValidationService = $userValidationService;
        $this->userFactory = $userFactory;
        $this->responseFactory = $responseFactory;
        $this->responseErrorFactory = $responseErrorFactory;
        $this->emailService = $emailService;
    }

    public function getUser(string $key, string $value): User|ResponseError
    {
        try {
            switch ($key) {
                case 'id':
                    $user = $this->userRepository->getUserById(id: $value);
                    break;
                case 'email':
                    $user = $this->userRepository->getUserByEmail(email: $value);
                    break;
            }
            return $user;
        } catch (\Exception $e) {
            return $this->responseErrorFactory->createFromArray(data: ['code' => $e->getCode(), 'message' => $e->getMessage()]);
        }
    }

    public function search(string $search): array|ResponseError
    {
        try{
            $users = $this->userRepository->search(search: $search);
            return $users;
        } catch (\Exception $e) {
            return $this->responseErrorFactory->createFromArray(data: ['code' => $e->getCode(), 'message' => $e->getMessage()]);
        }
    }

    public function subscription(User $newUser): Response
    {
        try{
            if($this->userValidationService->validateCreate(user: $newUser)){
                $user = $this->userRepository->addUser(user: $newUser);
                $token = $this->tools->encrypt_decrypt(action: 'encrypt', stringToTreat: json_encode(value: $user));
                $this->emailService->sendMail(
                    addressFrom :[
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
                    subject: 'My easy event inscription',
                    contentsEmails:[
                        '{{UserName}}' => $user->lastName . ' ' . $user->firstName,
                        '{{UserId}}' => $this->tools->encrypt_decrypt(action: 'encrypt', stringToTreat: $user->id),
                        '{{URLConfirm}}' => $_ENV['CONFIRM_SUBSCRIPTION_PATH']
                    ],
                    urlTemplate: __DIR__ . '/../../templates/emails/validateSubscription.html'
                );
                return $this->responseFactory->createFromArray(data: ['status' => 'success', 'code' => null, 'message' => "Utilisateur enregistré", 'data' => ['token' => $token]]);
            } else {
                return $this->responseFactory->createFromArray(data: ['status' => 'error', 'code' => 5023, 'message' => "Les données de l'utilisateur ne sont pas valide"]);
            }
        } catch (\Exception $e) {
            return $this->responseFactory->createFromArray(data: ['code' => $e->getCode(), 'message' => $e->getMessage()]);
        }
    }

    public function userIsValide(User $user): bool|ResponseError
    {
        try{
            if($user->validateDate){
                return true;
            }else{
                return false;
            }
        }catch(\Exception $e){
            return $this->responseErrorFactory->createFromArray(data: ['code' => $e->getCode(), 'message' => $e->getMessage()]);
        }
    }

    public function confirmSubscriptionUser(string $userId): bool|ResponseError
    {
        try{
            if($user = $this->userExist(userId: $userId)){
                if(!$this->userIsValide(user: $user)){
                    $now = new \DateTime();
                    $user->validateDate = $now->format(format: 'Y-m-d H:i:s.u');
                    $this->userRepository->updateUser(user: $user);
                }
                return true;
            }else{
                return false;
            }
        }catch(\Exception $e){
            return $this->responseErrorFactory->createFromArray(data: ['code' => $e->getCode(), 'message' => $e->getMessage()]);
        }
    }

    public function userExist(string $userId = null): User|ResponseError|null
    {
        try{
            if($user = $this->userRepository->getUserById(id: $userId)){
                return $user;
            }
            return null;
        }catch(\Exception $e){
            return $this->responseErrorFactory->createFromArray(data: ['code' => $e->getCode(), 'message' => $e->getMessage()]);
        }
    }

    public function updateUser(User $user): bool|ResponseError
    {
        try{
            $this->userValidationService->validateUpdate(user: $user);
            $this->userRepository->updateUser(user: $user);
            return true;
        } catch (\Exception $e) {
            return $this->responseErrorFactory->createFromArray(data: ['code' => $e->getCode(), 'message' => $e->getMessage()]);
        }
    }

    public function UpdateUserWithPassword(User $user): bool|ResponseError
    {
        try{
            $this->userValidationService->validateUpdateWithPassword(user: $user);
            $this->userRepository->updateUser(user: $user);
            return true;
        } catch (\Exception $e) {
            return $this->responseErrorFactory->createFromArray(data: ['code' => $e->getCode(), 'message' => $e->getMessage()]);
        }
    }

    public function DeleteUser(string $id): bool|ResponseError
    {
        try{
            $user = $this->userRepository->getUserById( id: $id);
            if($user){
                $user->isDeleted = true;
                $this->userRepository->updateUser(user: $user);
            }
            return true;
        } catch (\Exception $e) {
            return $this->responseErrorFactory->createFromArray(data: ['code' => $e->getCode(), 'message' => $e->getMessage()]);
        }
    }
}