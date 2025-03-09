<?php

namespace App\Services;

use App\Lib\Tools;

use App\Models\BlacklistUser;

use App\Factories\ResponseErrorFactory;

use App\Repositories\BlacklistUserRepository;

use App\Responses\ResponseError;

use App\Validators\BlacklistUserValidationService;

class BlacklistUserService
{
    private Tools $tools;
    private ResponseErrorFactory $responseErrorFactory;
    private BlacklistUserRepository $blacklistUserRepository;
    private BlacklistUserValidationService $blacklistUserValidationService;

    public function __construct(
        Tools $tools,
        ResponseErrorFactory $responseErrorFactory,
        BlacklistUserRepository $blacklistUserRepository,
        BlacklistUserValidationService $blacklistUserValidationService
    ){
        $this->tools = $tools;
        $this->responseErrorFactory = $responseErrorFactory;
        $this->blacklistUserRepository = $blacklistUserRepository;
        $this->blacklistUserValidationService = $blacklistUserValidationService;
    }

    public function getBlacklistUsers(): array|ResponseError
    {
        try{
            $blacklistUser = $this->blacklistUserRepository->getBlacklistUsers();
            return $blacklistUser;
        } catch (\Exception $e) {
            return $this->responseErrorFactory->createFromArray(data: ['code' => $e->getCode(), 'message' => $e->getMessage()]);
        }
    }

    public function addBlacklistUser(BlacklistUser $blacklistUser): BlacklistUser|ResponseError
    {
        try{
            $this->blacklistUserValidationService->validate( blacklistUser: $blacklistUser);
            $newBlacklistUser = $this->blacklistUserRepository->addBlacklistUser(blacklistUser: $blacklistUser);
            return $newBlacklistUser;
        } catch (\Exception $e) {
            return $this->responseErrorFactory->createFromArray(data: ['code' => $e->getCode(), 'message' => $e->getMessage()]);
        }
    }

    public function unBlacklistUser(BlacklistUser $blacklistUser): bool|ResponseError
    {
        try{
            $this->blacklistUserRepository->deleteBlacklistUser(blacklistUser: $blacklistUser);
            return true;
        } catch (\Exception $e) {
            return $this->responseErrorFactory->createFromArray(data: ['code' => $e->getCode(), 'message' => $e->getMessage()]);
        }
    }
}