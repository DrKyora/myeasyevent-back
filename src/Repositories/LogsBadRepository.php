<?php

namespace App\Repositories;

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/config.php';

use PDO;

use App\Services\DBConnection;
use App\Lib\Tools;

use App\Models\LogsBad;

use App\Factories\LogsBadFactory;

class LogsBadRepository
{
    private $db;
    private $tools;
    private $logsBadFactory;

    public function __construct(
        DBConnection $db,
        Tools $tools,
        LogsBadFactory $logsBadFactory
    ){
        $this->db = $db;
        $this->tools = $tools;
        $this->logsBadFactory = $logsBadFactory;
    }

    public function getLogsById(string $id): LogsBad|null
    {
        try{
            $query = "SELECT * FROM logs_bad WHERE id = :id";
            $stmt = $this->db->getConnection()->prepare(query: $query);
            $stmt->bindParam(param: ':id', var: $id);
            $stmt->execute();
            $row = $stmt->fetch(mode: PDO::FETCH_ASSOC);
            if($row === false){
                return null;
            }else{
                $logsBad = $this->logsBadFactory->createFromArray(data: $row);
                return $logsBad;
            }
        }catch(\Exception $e){
            $idError = uniqid();
            $this->tools->myErrorHandler(errno: $e->getCode(), errstr: $e->getMessage() . "Erreur SQL [" . $idError . "] : " . __METHOD__ . " avec le paramètre id = {$id}", errfile: $e->getFile(), errline: $e->getLine());
            throw new \Exception(message: "Erreur SQL : {$idError}", code: 1000);
        }
    }

    public function getLogsByIp(string $ip): array|null
    {
        try{
            $query = "SELECT * FROM logs_bad WHERE ip = :ip";
            $stmt = $this->db->getConnection()->prepare(query: $query);
            $stmt->bindParam(param: ':ip', var: $ip);
            $stmt->execute();
            $ips = [];
            while($row = $stmt->fetch(mode: PDO::FETCH_ASSOC)){
                $logsBad = $this->logsBadFactory->createFromArray(data: $row);
                $ips[] = $logsBad;
            }           
            return $ips; 
        }catch(\Exception $e){
            $idError = uniqid();
            $this->tools->myErrorHandler(errno: $e->getCode(), errstr: $e->getMessage() . "Erreur SQL [" . $idError . "] : " . __METHOD__ . " avec le paramètre ip = {$ip}", errfile: $e->getFile(), errline: $e->getLine());
            throw new \Exception(message: "Erreur SQL : {$idError}", code: 1000);
        }
    }

    public function getLogsByUserId(string $userId): array|null
    {
        try{
            $query = "SELECT * FROM logs_bad WHERE userId = :userId";
            $stmt = $this->db->getConnection()->prepare(query: $query);
            $stmt->bindParam(param: ':userId', var: $userId);
            $stmt->execute();
            $users = [];
            while($row = $stmt->fetch(mode: PDO::FETCH_ASSOC)){
                $logsBad = $this->logsBadFactory->createFromArray(data: $row);
                $users[] = $logsBad;
            }
            return $users;
        }catch(\Exception $e){
            $idError = uniqid();
            $this->tools->myErrorHandler(errno: $e->getCode(), errstr: $e->getMessage() . "Erreur SQL [" . $idError . "] : " . __METHOD__ . " avec le paramètre userId = {$userId}", errfile: $e->getFile(), errline: $e->getLine());
            throw new \Exception(message: "Erreur SQL : {$idError}", code: 1000);
        }
    }

    public function addLog(LogsBad $logsBad): LogsBad
    {
        try{
            $logsBad->id = uniqid();
            $query = "INSERT INTO logs_bad (id,ip,userId,logDate) VALUES (:id,:ip,:userId,:logDate)";
            $stmt = $this->db->getConnection()->prepare(query: $query);
            $stmt->bindParam(param: ':id', var: $logsBad->id);
            $stmt->bindParam(param: ':ip', var: $logsBad->ip);
            $stmt->bindParam(param: ':userId', var: $logsBad->userId);
            $stmt->bindParam(param: ':logDate', var: $logsBad->date);
            $stmt->execute();
            return $logsBad;
        }catch(\Exception $e){
            $idError = uniqid();
            $this->tools->myErrorHandler(errno: $e->getCode(), errstr: $e->getMessage() . "Erreur SQL [" . $idError . "] : " . __METHOD__ . " avec le paramètre logsBad = " . json_encode(value: $logsBad), errfile: $e->getFile(), errline: $e->getLine());
            throw new \Exception(message: "Erreur SQL : {$idError}", code: 1000);
        }
    }

    public function numberOflogs(string $ip, ?string $userId = null): int
    {
        try {
            $query = "SELECT COUNT(*) as count FROM logs_bad WHERE ip = :ip" . ($userId ? " AND userId = :userId" : "") . " AND logDate > DATE_SUB(NOW(), INTERVAL " . $_ENV["CONNECTION_WAIT_TIME"] . " MINUTE)";
            $stmt = $this->db->getConnection()->prepare(query: $query);
            $stmt->bindParam(param: ':ip', var: $ip);
            if ($userId) {
                $stmt->bindParam(param: ':userId', var: $userId);
            }
            $stmt->execute();
            $row = $stmt->fetch(mode: PDO::FETCH_ASSOC);
            return (int)$row['count'];
        }catch(\Exception $e){
            $idError = uniqid();
            $this->tools->myErrorHandler(errno: $e->getCode(), errstr: $e->getMessage() . "Erreur SQL [" . $idError . "] : " . __METHOD__ . " avec le paramètre ip = {$ip} et userId = {$userId}", errfile: $e->getFile(), errline: $e->getLine());
            throw new \Exception(message: "Erreur SQL : {$idError}", code: 1000);
        }
    }

    public function deleteLog($id): bool
    {
        try{
            $query = "DELETE FROM logsBad WHERE id = :id";
            $stmt = $this->db->getConnection()->prepare(query: $query);
            $stmt->bindParam(param: ':id', var: $id);
            $stmt->execute();
            return true;
        }catch(\Exception $e){
            $idError = uniqid();
            $this->tools->myErrorHandler(errno: $e->getCode(), errstr: $e->getMessage() . "Erreur SQL [" . $idError . "] : " . __METHOD__ . " avec le paramètre id = {$id}", errfile: $e->getFile(), errline: $e->getLine());
            throw new \Exception(message: "Erreur SQL : {$idError}", code: 1000);
        }
    }
}

