<?php

namespace App\Services;

use App\Lib\Tools;

use App\Models\LogsBad;

use App\Factories\LogsBadFactory;
use App\Factories\ResponseErrorFactory;

use App\Repositories\LogsBadRepository;
use App\Repositories\UserRepository;

use App\Responses\ResponseError;

use App\Validators\LogsBadValidationService;

class LogsBadService
{
    private Tools $tools;
    private LogsBadFactory $logsBadFactory;
    private ResponseErrorFactory $responseErrorFactory;
    private LogsBadRepository $logsBadRepository;
    private UserRepository $userRepository;
    private LogsBadValidationService $logsBadValidationService;

    public function __construct(
        Tools $tools,
        LogsBadFactory $logsBadFactory,
        ResponseErrorFactory $responseErrorFactory,
        LogsBadRepository $logsBadRepository,
        UserRepository $userRepository,
        LogsBadValidationService $logsBadValidationService
    ){
        $this->tools = $tools;
        $this->logsBadFactory = $logsBadFactory;
        $this->responseErrorFactory = $responseErrorFactory;
        $this->logsBadRepository = $logsBadRepository;
        $this->userRepository = $userRepository;
        $this->logsBadValidationService = $logsBadValidationService;
    }

    public function getLogsByIp(string $ip): array|ResponseError|null
    {
        try{
            $logsBad = $this->logsBadRepository->getLogsByIp(ip: $ip);
            return $logsBad;
        } catch (\Exception $e) {
            return $this->responseErrorFactory->createFromArray(data: ['code' => $e->getCode(), 'message' => $e->getMessage()]);
        }
    }

    public function getLogsByUserId(string $userId): array|ResponseError|null
    {
        try{
            $logsBad = $this->logsBadRepository->getLogsByUserId(userId: $userId);
            return $logsBad;
        } catch (\Exception $e) {
            return $this->responseErrorFactory->createFromArray(data: ['code' => $e->getCode(), 'message' => $e->getMessage()]);
        }
    }

    public function createLogs(LogsBad $logsBad): LogsBad|ResponseError
    {
        try{
            $this->logsBadValidationService->validate(logsBad: $logsBad);
            $newLogs = $this->logsBadRepository->addLog(logsBad: $logsBad);
            return $newLogs;
        } catch (\Exception $e) {
            return $this->responseErrorFactory->createFromArray(data: ['code' => $e->getCode(), 'message' => $e->getMessage()]);
        }
    }

    public function countLogs(string $ip,string $email): int|ResponseError
    {
        try{
            $user = $this->userRepository->getUserByEmail(email: $email);
            if($user){
                $numberLog = $this->logsBadRepository->numberOflogs(ip: $ip,userId: $user->id);
            }else{
                $numberLog = $this->logsBadRepository->numberOflogs(ip: $ip);
            }
            return $numberLog;
        } catch (\Exception $e) {
            return $this->responseErrorFactory->createFromArray(data: ['code' => $e->getCode(), 'message' => $e->getMessage()]);
        }
    }

    public function deleteLog($id): bool|ResponseError
    {
        try{
            $this->logsBadRepository->deleteLog(id: $id);
            return true;
        } catch (\Exception $e) {
            return $this->responseErrorFactory->createFromArray(data: ['code' => $e->getCode(), 'message' => $e->getMessage()]);
        }
    }
}