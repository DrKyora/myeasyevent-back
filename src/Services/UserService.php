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


class UserService
{
    private Tools $tools;
    private UserRepository $userRepository;
    private UserValidationService $userValidationService;
    private UserFactory $userFactory;
    private ResponseErrorFactory $responseErrorFactory;
    private ResponseFactory $responseFactory;
    public function __construct(
        Tools $tools,
        UserRepository $userRepository,
        UserValidationService $userValidationService,
        UserFactory $userFactory,
        ResponseFactory $responseFactory,
        ResponseErrorFactory $responseErrorFactory,
    ){
        $this->tools = $tools;
        $this->userRepository = $userRepository;
        $this->userValidationService = $userValidationService;
        $this->userFactory = $userFactory;
        $this->responseFactory = $responseFactory;
        $this->responseErrorFactory = $responseErrorFactory;
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
            $this->userValidationService->validateCreate(user: $user);
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

    public function createUser(User $user): user|ResponseError
    {
        try{
            $this->userValidationService->validateCreate(user: $user);
            $newUser = $this->userRepository->addUser(user: $user);
            return $newUser;
        } catch (\Exception $e) {
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