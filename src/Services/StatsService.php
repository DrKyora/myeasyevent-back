<?php

namespace App\Services;

use App\Lib\Tools;
use App\Repositories\StatsRepository;
use App\Factories\ResponseErrorFactory;
use App\Responses\ResponseError;

class StatsService
{
    private Tools $tools;
    private StatsRepository $statsRepository;
    private ResponseErrorFactory $responseErrorFactory;

    public function __construct(
        Tools $tools,
        StatsRepository $statsRepository,
        ResponseErrorFactory $responseErrorFactory
    ){
        $this->tools = $tools;
        $this->statsRepository = $statsRepository;
        $this->responseErrorFactory = $responseErrorFactory;
    }

    public function countNumberOfUsers(): int|ResponseError
    {
        try {
            return $this->statsRepository->countNumberOfUsers();
        } catch (\Exception $e) {
            return $this->responseErrorFactory->createFromArray(data: ['code' => $e->getCode(), 'message' => $e->getMessage()]);
        }
    }

    public function countReservations(): int|ResponseError
    {
        try {
            return $this->statsRepository->countReservations();
        } catch (\Exception $e) {
            return $this->responseErrorFactory->createFromArray(data: ['code' => $e->getCode(), 'message' => $e->getMessage()]);
        }
    }

    public function countEvents(): int|ResponseError
    {
        try {
            return $this->statsRepository->countEvents();
        } catch (\Exception $e) {
            return $this->responseErrorFactory->createFromArray(data: ['code' => $e->getCode(), 'message' => $e->getMessage()]);
        }
    }
}