<?php

namespace App\Repositories;

use PDO;
use App\Services\DBConnection;
use App\Lib\Tools;

use App\Models\BlacklistUser;

use App\Factories\BlacklistUserFactory;

class BlacklistUserRepository
{
    private $db;
    private $tools;
    private $blacklistUserFactory;

    public function __construct(
        DBConnection $db,
        Tools $tools,
        BlacklistUserFactory $blacklistUserFactory
    ){
        $this->db = $db;
        $this->tools = $tools;
        $this->blacklistUserFactory = $blacklistUserFactory;
    }

    public function getBlacklistUsers(): array
    {
        try{
            $query = "SELECT * FROM blacklist_users";
            $stmt = $this->db->getConnection()->prepare(query: $query);
            $stmt->execute();
            $blacklistUsers = [];
            while($row = $stmt->fetch(mode: PDO::FETCH_ASSOC)){
                $blacklistUser = $this->blacklistUserFactory->createFromArray(data: $row);
                $blacklistUsers[] = $blacklistUser;
            }
            return $blacklistUsers;
        } catch (\Exception $e) {
            $idError = uniqid();
            $this->tools->myErrorHandler(errno: $e->getCode(), errstr: $e->getMessage() . "Erreur SQL [" . $idError . "] : " . __METHOD__, errfile: $e->getFile(), errline: $e->getLine());
            throw new \Exception(message: "Erreur SQL : {$idError}", code: 1000);
        }
    }

    public function getBlacklistUsersByUserId(string $userId): BlacklistUser|null
    {
        try{
            $query = "SELECT * FROM blacklist_users WHERE userId = :userId";
            $stmt = $this->db->getConnection()->prepare(query: $query);
            $stmt->bindParam(param: ':userId', var: $userId);
            $stmt->execute();
            $row = $stmt->fetch(mode: PDO::FETCH_ASSOC);
            if($row === false){
                return null;
            }else{
                $blacklistUser = $this->blacklistUserFactory->createFromArray(data: $row);
                return $blacklistUser;
            }
        }catch(\Exception $e){
            $idError = uniqid();
            $this->tools->myErrorHandler(errno: $e->getCode(), errstr: $e->getMessage() . "Erreur SQL [" . $idError . "] : " . __METHOD__ . " avec le paramètre userId = {$userId}", errfile: $e->getFile(), errline: $e->getLine());
            throw new \Exception(message: "Erreur SQL : {$idError}", code: 1000);
        }
    }

    public function addBlacklistUser(BlacklistUser $blacklistUser): BlacklistUser
    {
        try{
            $query = "INSERT INTO blacklist_users (id,userId,date) VALUES (:id,:userId,:date)";
            $stmt = $this->db->getConnection()->prepare(query: $query);
            $stmt->bindParam(param: ':id', var: $blacklistUser->id);
            $stmt->bindParam(param: ':userId', var: $blacklistUser->userId);
            $stmt->bindParam(param: ':date', var: $blacklistUser->date);
            $stmt->execute();
            return $blacklistUser;
        } catch (\Exception $e) {
            $idError = uniqid();
            $this->tools->myErrorHandler(errno: $e->getCode(), errstr: $e->getMessage() . "Erreur SQL [" . $idError . "] : " . __METHOD__ . " avec le paramètre blacklistUser = " . json_encode(value: $blacklistUser), errfile: $e->getFile(), errline: $e->getLine());
            throw new \Exception(message: "Erreur SQL : {$idError}", code: 1000);
        }
    }

    public function deleteBlacklistUser(BlacklistUser $blacklistUser): bool
    {
        try{
            $query = "DELETE FROM blacklist_users WHERE id = :id";
            $stmt = $this->db->getConnection()->prepare(query: $query);
            $stmt->bindParam(param: ':id', var: $blacklistUser->id);
            $stmt->execute();
            return true;
        }catch(\Exception $e){
            $idError = uniqid();
            $this->tools->myErrorHandler(errno: $e->getCode(), errstr: $e->getMessage() . "Erreur SQL [" . $idError . "] : " . __METHOD__ . " avec le paramètre blacklistUser = " . json_encode(value: $blacklistUser), errfile: $e->getFile(), errline: $e->getLine());
            throw new \Exception(message: "Erreur SQL : {$idError}", code: 1000);
        }
    }
}