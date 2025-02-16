<?php

namespace App\Repositories;

use PDO;

use App\Services\DBConnection;
use App\Lib\Tools;

use App\Models\Session;

use App\Factories\SessionFactory;

class SessionRepository
{
    private DBConnection $db;
    private Tools $tools;
    private SessionFactory $sessionFactory;

    public function __construct(
        DBConnection $db,
        Tools $tools,
        SessionFactory $sessionFactory
    ){
        $this->db = $db;
        $this->tools = $tools;
        $this->sessionFactory = $sessionFactory;
    }

    public function getSessionById(string $id): Session|null
    {
        try{
            $query = "SELECT * FROM sessions WHERE id = :id AND isDeleted = 0";
            $stmt = $this->db->getConnection()->prepare(query: $query);
            $stmt->bindParam(param: ":id", var: $id);
            $stmt->execute();
            $row = $stmt->fetch(mode: PDO::FETCH_ASSOC);
            if(!$row){
                return null;
            }
            $session = $this->sessionFactory->createFromArray(data: $row);
            return $session;
        } catch (\Exception $e) {
            $idError = uniqid();
            $this->tools->myErrorHandler(errno: $e->getCode(), errstr: $e->getMessage() . "Erreur SQL [" . $idError . "] : " . __METHOD__ . " avec le paramètre Id = {$id}", errfile: $e->getFile(), errline: $e->getLine());
            throw new \Exception(message: "Erreur SQL : {$idError}", code: 1000);
        }
    }

    public function getSessionByUserId(string $userId): Session|null
    {
        try{
            $query = "SELECT * FROM sessions WHERE userId = :userId AND isDeleted = 0";
            $stmt = $this->db->getConnection()->prepare(query: $query);
            $stmt->bindParam(param: ":userId", var: $userId);
            $stmt->execute();
            $row = $stmt->fetch(mode: PDO::FETCH_ASSOC);
            if(!$row){
                return null;
            }
            $session = $this->sessionFactory->createFromArray(data: $row);
            return $session;
        } catch (\Exception $e) {
            $idError = uniqid();
            $this->tools->myErrorHandler(errno: $e->getCode(), errstr: $e->getMessage() . "Erreur SQL [" . $idError . "] : " . __METHOD__ . " avec le paramètre userId = {$userId}", errfile: $e->getFile(), errline: $e->getLine());
            throw new \Exception(message: "Erreur SQL : {$idError}", code: 1000);
        }
    }

    public function addSession(Session $session): Session
    {
        try{
            $query = "INSERT INTO sessions (id,userId,lastAction,isDeleted) VALUES(:id,:userId,:lastAction,:isDeleted)";
            $stmt = $this->db->getConnection()->prepare(query: $query);
            $stmt->bindParam(param: ":id", var: $session->id);
            $stmt->bindParam(param: ":userId", var: $session->userId);
            $stmt->bindParam(param: ":lastAction", var: $session->lastAction);
            $stmt->bindParam(param: ":isDeleted", var: $session->isDeleted);
            $stmt->execute();
            return $session;
        } catch (\Exception $e) {
            $idError = uniqid();
            $this->tools->myErrorHandler(errno: $e->getCode(), errstr: $e->getMessage() . "Erreur SQL [" . $idError . "] : " . __METHOD__ . " avec le paramètre session =" . json_encode(value: $session), errfile: $e->getFile(), errline: $e->getLine());
            throw new \Exception(message: "Erreur SQL : {$idError}", code: 1000);
        }
    }

    public function update(Session $session): bool
    {
        try{
            $columnsToUpdate = [];
            $parameters = [':id' => $session->id];
            if($session->userId !== null){
                $columnsToUpdate[] = "userId = :userId";
                $parameters[':userId'] = $session->userId;
            }
            if($session->lastAction !== null){
                $columnsToUpdate[] = "lastAction = :lastAction";
                $parameters[':lastAction'] = $session->lastAction;
            }
            if($session->isDeleted !== null){
                $columnsToUpdate[] = "isDeleted = :isDeleted";
                $parameters[':isDeleted'] = $session->isDeleted;
            }
            $query = "UPDATE sessions SET " . implode(separator: ",", array: $columnsToUpdate) . " WHERE id = :id";
            $stmt = $this->db->getConnection()->prepare(query: $query);
            $stmt->execute(params: $parameters);
            return true;
        } catch (\Exception $e) {
            $idError = uniqid();
            $this->tools->myErrorHandler(errno: $e->getCode(), errstr: $e->getMessage() . "Erreur SQL [" . $idError . "] : " . __METHOD__ . " avec le paramètre session =" . json_encode(value: $session), errfile: $e->getFile(), errline: $e->getLine());
            throw new \Exception(message: "Erreur SQL : {$idError}", code: 1000);
        }
    }

    public function deleteUnusedSession($parameter): bool
    {
        try{
            $query = "DELETE * FROM sessions WHERE lastAction < date_sub(now(), interval" . $parameter . "MINUTE)";
            $stmt = $this->db->getConnection()->prepare(query: $query);
            $stmt->execute();
            return true;
        } catch (\Exception $e) {
            $idError = uniqid();
            $this->tools->myErrorHandler(errno: $e->getCode(), errstr: $e->getMessage() . "Erreur SQL [" . $idError . "] : " . __METHOD__ . " avec le paramètre parameter = {$parameter}", errfile: $e->getFile(), errline: $e->getLine());
            throw new \Exception(message: "Erreur SQL : {$idError}", code: 1000);
        }
    }
}