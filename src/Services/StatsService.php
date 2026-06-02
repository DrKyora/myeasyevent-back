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

    public function getAllStats(): array|ResponseError
    {
        try{
            $allSats = [];
            $numberOfUsers = $this->statsRepository->countNumberOfUsers();
            $numberOfReservations = $this->statsRepository->countReservations();
            $numberOfEvents = $this->statsRepository->countEvents();
            $allSats['numberOfUsers'] = $numberOfUsers;
            $allSats['numberOfReservations'] = $numberOfReservations;
            $allSats['numberOfEvents'] = $numberOfEvents;
            return $allSats;
        } catch (\Exception $e) {
            return $this->responseErrorFactory->createFromArray(data: ['code' => $e->getCode(), 'message' => $e->getMessage()]);
        }
    }
}