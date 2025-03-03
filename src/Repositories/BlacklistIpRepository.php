<?php

namespace App\Repositories;

use PDO;
use App\Services\DBConnection;
use App\Lib\Tools;

use App\Models\BlacklistIp;

use App\Factories\BlacklistIpFactory;

class BlacklistIpRepository
{
    private $db;
    private $tools;
    private $blacklistIpFactory;

    public function __construct(
        DBConnection $db,
        Tools $tools,
        BlacklistIpFactory $blacklistIpFactory
    ){
        $this->db = $db;
        $this->tools = $tools;
        $this->blacklistIpFactory = $blacklistIpFactory;
    }

    public function getBlacklistIps(): array|null
    {
        try{
            $query = "SELECT * FROM blacklist_ip";
            $stmt = $this->db->getConnection()->prepare(query: $query);
            $stmt->execute();
            $blacklistIps = [];
            while($row = $stmt->fetch(mode: PDO::FETCH_ASSOC)){
                $blacklistIp = $this->blacklistIpFactory->createFromArray(data: $row);
                $blacklistIps[] = $blacklistIp;
            }
            return $blacklistIps;
        } catch (\Exception $e) {
            $idError = uniqid();
            $this->tools->myErrorHandler(errno: $e->getCode(), errstr: $e->getMessage() . "Erreur SQL [" . $idError . "] : " . __METHOD__, errfile: $e->getFile(), errline: $e->getLine());
            throw new \Exception(message: "Erreur SQL : {$idError}", code: 1000);
        }
    }

    public function getBlacklistIpByIp(string $ip): BlacklistIp|null
    {
        try{
            $query = "SELECT * FROM blacklist_ip WHERE ip = :ip";
            $stmt = $this->db->getConnection()->prepare(query: $query);
            $stmt->bindParam(param: ':ip', var: $ip);
            $stmt->execute();
            $row = $stmt->fetch(mode: PDO::FETCH_ASSOC);
            if($row === false){
                return null;
            }else{
                $blacklistIp = $this->blacklistIpFactory->createFromArray(data: $row);
                return $blacklistIp;
            }
        }catch(\Exception $e){
            $idError = uniqid();
            $this->tools->myErrorHandler(errno: $e->getCode(), errstr: $e->getMessage() . "Erreur SQL [" . $idError . "] : " . __METHOD__ . " avec le paramètre ip = {$ip}", errfile: $e->getFile(), errline: $e->getLine());
            throw new \Exception(message: "Erreur SQL : {$idError}", code: 1000);
        }
    }


    public function addBlacklistIp(BlacklistIp $blacklistIp): BlacklistIp
    {
        try{
            $blacklistIp->id = uniqid();
            $query = "INSERT INTO blacklist_ip (id,ip,blacklistDate) VALUES (:id,:ip,:blacklistDate)";
            $stmt = $this->db->getConnection()->prepare(query: $query);
            $stmt->bindParam(param: ':id', var: $blacklistIp->id);
            $stmt->bindParam(param: ':ip', var: $blacklistIp->ip);
            $stmt->bindParam(param: ':blacklistDate', var: $blacklistIp->date);
            $stmt->execute();
            return $blacklistIp;
        } catch (\Exception $e) {
            $idError = uniqid();
            $this->tools->myErrorHandler(errno: $e->getCode(), errstr: $e->getMessage() . "Erreur SQL [" . $idError . "] : " . __METHOD__ . " avec le paramètre blacklistIp = " . json_encode(value: $blacklistIp), errfile: $e->getFile(), errline: $e->getLine());
            throw new \Exception(message: "Erreur SQL : {$idError}", code: 1000);
        }
    }

    public function deleteBlacklistIp(BlacklistIp $blacklistIp): bool
    {
        try{
            $query = "DELETE FROM blacklist_ip WHERE id = :id";
            $stmt = $this->db->getConnection()->prepare(query: $query);
            $stmt->bindParam(param: ':id', var: $blacklistIp->id);
            $stmt->execute();
            return true;
        }catch(\Exception $e){
            $idError = uniqid();
            $this->tools->myErrorHandler(errno: $e->getCode(), errstr: $e->getMessage() . "Erreur SQL [" . $idError . "] : " . __METHOD__ . " avec le paramètre blacklistIp = " . json_encode(value: $blacklistIp), errfile: $e->getFile(), errline: $e->getLine());
            throw new \Exception(message: "Erreur SQL : {$idError}", code: 1000);
        }
    }
}