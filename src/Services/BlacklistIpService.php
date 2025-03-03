<?php

namespace App\Services;

use App\Lib\Tools;

use App\Models\BlacklistIp;

use App\Factories\BlacklistIpFactory;
use App\Factories\ResponseErrorFactory;
use App\Factories\ResponseFactory;

use App\Repositories\BlacklistIpRepository;

use App\Responses\ResponseError;

use App\Validators\BlacklistIpValidationService;

class BlacklistIpService
{
    private Tools $tools;
    private BlacklistIpFactory $blacklistIpFactory;
    private ResponseErrorFactory $responseErrorFactory;
    private BlacklistIpRepository $blacklistIpRepository;
    private BlacklistIpValidationService $blacklistIpValidationService;

    public function __construct(
        Tools $tools,
        BlacklistIpFactory $blacklistIpFactory,
        ResponseErrorFactory $responseErrorFactory,
        BlacklistIpRepository $blacklistIpRepository,
        BlacklistIpValidationService $blacklistIpValidationService
    ){
        $this->tools = $tools;
        $this->blacklistIpFactory = $blacklistIpFactory;
        $this->responseErrorFactory = $responseErrorFactory;
        $this->blacklistIpRepository = $blacklistIpRepository;
        $this->blacklistIpValidationService = $blacklistIpValidationService;
    }

    public function getBlacklistIps(): array|ResponseError
    {
        try{
            $blacklistIps = $this->blacklistIpRepository->getBlacklistIps();
            return $blacklistIps;
        } catch (\Exception $e) {
            return $this->responseErrorFactory->createFromArray(data: ['code' => $e->getCode(), 'message' => $e->getMessage()]);
        }
    }
    
    public function addBlacklistIp(BlacklistIp $blacklistIp): BlacklistIp|ResponseError
    {
        try{
            $this->blacklistIpValidationService->validate(blacklistIp: $blacklistIp);
            $newBlacklistIp = $this->blacklistIpRepository->addBlacklistIp(blacklistIp: $blacklistIp);
            return $newBlacklistIp;
        } catch (\Exception $e) {
            return $this->responseErrorFactory->createFromArray(data: ['code' => $e->getCode(), 'message' => $e->getMessage()]);
        }
    }

    public function unBlacklistIp(BlacklistIp $blacklistIp): bool|ResponseError
    {
        try{
            $this->blacklistIpRepository->deleteBlacklistIp(blacklistIp: $blacklistIp);
            return true;
        } catch (\Exception $e) {
            return $this->responseErrorFactory->createFromArray(data: ['code' => $e->getCode(), 'message' => $e->getMessage()]);
        }
    }
}