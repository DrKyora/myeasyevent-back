<?php

namespace App\Services;

use App\Lib\Tools;
use App\Responses\ResponseError;
use App\Models\Session;

use App\Factories\SessionFactory;
use App\Factories\ResponseErrorFactory;

use App\Repositories\SessionRepository;

use App\Validators\SessionValidationService;

class SessionService
{
    private $tools;
    private $sessionFactory;
    private $responseErrorFactory;
    private $sessionRepository;
    private $sessionValidationService;

    public function __construct(
        Tools $tools,
        SessionFactory $sessionFactory,
        ResponseErrorFactory $responseErrorFactory,
        SessionRepository $sessionRepository,
        SessionValidationService $sessionValidationService
    ){
        $this->tools = $tools;
        $this->sessionFactory = $sessionFactory;
        $this->responseErrorFactory = $responseErrorFactory;
        $this->sessionRepository = $sessionRepository;
        $this->sessionValidationService = $sessionValidationService;
    }

    public function checkSessionValidity(Session $session): bool|ResponseError
    {
        try{
            $now = new \DateTime(datetime: 'now', timezone: new \DateTimeZone(timezone: 'Europe/brussels'));
            $expireSession = new \DateTime(datetime: $session->lastAction);
            $expireSession->add(interval: new \DateInterval(duration: 'PT' . $_ENV['SESSION_LIFETIME'] . 'M'));
            if($expireSession >= $now){
                $session->lastAction = $now->format(format: 'Y-m-d H:i:s');
                $this->sessionRepository->update(session: $session);
                return true;
            }else{
                return false;
            }
        } catch (\Exception $e) {
            return $this->responseErrorFactory->createFromArray(data: ['code' => $e->getCode(), 'message' => $e->getMessage()]);
        }
    }

    public function gc_unusedSession(): bool|ResponseError
    {
        try{
            $now = new \DateTime(datetime: 'now', timezone: new \DateTimeZone(timezone: 'Europe/brussels'));
            $this->sessionRepository->deleteUnusedSession(parameter: $_ENV['SESSION_LIFETIME']);
            return true;
        } catch (\Exception $e) {
            return $this->responseErrorFactory->createFromArray(data: ['code' => $e->getCode(), 'message' => $e->getMessage()]);
        }
    }

    public function tokenSessionIsValide(string $tokenSession): bool|ResponseError
    {
        try{
            $stringToken = $this->tools->encrypt_decrypt(action: 'decrypt', stringToTreat: $tokenSession);
            $session = $this->sessionFactory->createFromJson(json: $stringToken);
            $this->checkSessionValidity(session: $session);
            return true;
        } catch (\Exception $e) {
            return $this->responseErrorFactory->createFromArray(data: ['code' => $e->getCode(), 'message' => $e->getMessage()]);
        }
    }

    public function getSession(string $key,string $value): Session|ResponseError
    {
        try{
            switch($key){
                case'id':
                    $session = $this->sessionRepository->getSessionById(id: $value);
                    break;
                case 'userId':
                    $session = $this->sessionRepository->getSessionByUserId(userId: $value);
                    break;
                default:
                    return $this->responseErrorFactory->createFromArray(data: ['code' => 2000, 'message' => "Le service demandé: " . $key . " n'existe pas"]);
            }
            $this->sessionValidationService->validate(session: $session);
            return $session;
        } catch (\Exception $e) {
            return $this->responseErrorFactory->createFromArray(data: ['code' => $e->getCode(), 'message' => $e->getMessage()]);
        }
    }

    public function createSession(string $userId, string $lastAction): Session|ResponseError
    {
        try{
            $sessionId = uniqid();
            $session = $this->sessionFactory->createFromArray(data: [
                'id' => $sessionId,
                'userId' => $userId,
                'lastAction' => $lastAction
            ]);
            $this->sessionValidationService->validate(session: $session);
            $this->sessionRepository->addSession(session: $session);
            return $session;
        } catch (\Exception $e) {
            return $this->responseErrorFactory->createFromArray(data: ['code' => $e->getCode(), 'message' => $e->getMessage()]);
        }
    }
}
