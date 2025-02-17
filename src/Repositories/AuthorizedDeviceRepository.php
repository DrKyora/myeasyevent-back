<?php

namespace App\Repositories;

use PDO;
use App\Services\DBConnection;
use App\Lib\Tools;


use App\Models\AuthorizedDevice;
use App\Factories\AuthorizedDeviceFactory;

class AuthorizedDeviceRepository
{
    private DBConnection $db;
    private Tools $tools;
    private AuthorizedDeviceFactory $authorizedDeviceFactory;
    
    public function __construct(
        DBConnection $db,
        Tools $tools,
        AuthorizedDeviceFactory $authorizedDeviceFactory
    ){
        $this->db = $db;
        $this->tools = $tools;
        $this->authorizedDeviceFactory = $authorizedDeviceFactory;
    }

    public function getAuthorizedDeviceById(string $deviceId): AuthorizedDevice|null
    {
        try{
            $query = "SELECT * FROM authorized_devices WHERE id = :id AND isDeleted = 0";
            $stmt = $this->db->getConnection()->prepare(query: $query);
            $stmt->bindParam(param: ':id',var: $deviceId);
            $stmt->execute();
            $row = $stmt->fetch(mode: PDO::FETCH_ASSOC);
            if($row === false){
                return null;
            }else{
                $authorizedDevice = $this->authorizedDeviceFactory->createFromArray(array: $row);
                return $authorizedDevice;
            }
        }catch(\Exception $e){
            $idError = uniqid();
            $this->tools->myErrorHandler(errno: $e->getCode(), errstr: $e->getMessage() . "Erreur SQL [" . $idError . "] : " . __METHOD__ . " avec le paramètre deviceId = {$deviceId}", errfile: $e->getFile(), errline: $e->getLine());
            throw new \Exception(message: "Erreur SQL : {$idError}", code: 1000);
        }
    }

    public function getAuthorizedDeviceOfUser(string $userId): array
    {
        try{
            $query = "SELECT * FROM authorized_devices WHERE userid = :userId AND isDeleted = 0";
            $stmt = $this->db->getConnection()->prepare(query: $query);
            $stmt->bindParam(param: ':userId', var: $userId);
            $stmt->execute();
            $authorizedDevices = [];
            while($row = $stmt->fetch(mode: PDO::FETCH_ASSOC)){
                $authorizedDevice = $this->authorizedDeviceFactory->createFromArray(array: $row);
                $authorizedDevices[] = $authorizedDevice;
            }
            return $authorizedDevices;
        }catch(\Exception $e){
            $idError = uniqid();
            $this->tools->myErrorHandler(errno: $e->getCode(), errstr: $e->getMessage() . "Erreur SQL [" . $idError . "] : " . __METHOD__ . " avec le paramètre userId = {$userId}", errfile: $e->getFile(), errline: $e->getLine());
            throw new \Exception(message: "Erreur SQL : {$idError}", code: 1000);
        }
    }

    public function addAuthorizedDevice(AuthorizedDevice $authorizedDevice)
    {
        try{
            $authorizedDevice->id = uniqid();
            $query = "INSERT INTO authorized_devices (id,name,type,model,userId,validateDate,lastUsed) VALUES (:id,:name,:type,:mode,:userId,:validateDate,:lastUser)";
            $stmt = $this->db->getConnection()->prepare(query: $query);
            $stmt->bindParam(param: ':id',var: $authorizedDevice->id);
            $stmt->bindParam(param: ':name',var: $authorizedDevice->name);
            $stmt->bindParam(param: ':type',var: $authorizedDevice->type);
            $stmt->bindParam(param: ':model',var: $authorizedDevice->model);
            $stmt->bindParam(param: ':userId',var: $authorizedDevice->userId);
            $stmt->bindParam(param: ':validateDate',var: $authorizedDevice->validateDate);
            $stmt->bindParam(param: ':lastUsed',var: $authorizedDevice->lastUsed);
            $stmt->execute();
            return $authorizedDevice;
        }catch(\Exception $e){
            $idError = uniqid();
            $this->tools->myErrorHandler(errno: $e->getCode(), errstr: $e->getMessage() . "Erreur SQL [" . $idError . "] : " . __METHOD__ . " avec le paramètre id = " . json_encode(value: $authorizedDevice), errfile: $e->getFile(), errline: $e->getLine());
            throw new \Exception(message: "Erreur SQL : {$idError}", code: 1000);
        }
    }

    public function updateAuthorizedDevice(AuthorizedDevice $authorizedDevice): bool
    {
        try{
            $columnsToUpdate = [];
            $parameters =  [':id' => $authorizedDevice->id];
            if($authorizedDevice->name !== null){
                $columnsToUpdate[] = "name = :name";
                $parameters[':name'] = $authorizedDevice->name;
            }
            if($authorizedDevice->type !== null){
                $columnsToUpdate[] = "type = :type";
                $parameters[':type'] = $authorizedDevice->type;
            }
            if($authorizedDevice->model !== null){
                $columnsToUpdate[] = "model = :model";
                $parameters['model'] = $authorizedDevice->model;
            }
            if($authorizedDevice->userId !== null){
                $columnsToUpdate[] = "userId = :userId";
                $parameters['userId'] = $authorizedDevice->userId;
            }
            if($authorizedDevice->validateDate !== null){
                $columnsToUpdate[] = "validateDate = :validateDate";
                $parameters['validateDate'] = $authorizedDevice->validateDate;
            }
            if($authorizedDevice->lastUsed !== null){
                $columnsToUpdate[] = "lastUsed = :lastUsed";
                $parameters['lastUsed'] = $authorizedDevice->lastUsed;
            }
            if($authorizedDevice->isDeleted !== null){
                $columnsToUpdate[] = "isDeleted = :isDeleted";
                $parameters['isDeleted'] = $authorizedDevice->isDeleted;
            }
            $query = "UPDATE authorized_devices SET " . implode(separator: ', ', array: $columnsToUpdate) . " WHERE id = :id";
            $stmt = $this->db->getConnection()->prepare(query: $query);
            $stmt->execute(params: $parameters);
            return true;
        }catch(\Exception $e){
            $idError = uniqid();
            $this->tools->myErrorHandler(errno: $e->getCode(), errstr: $e->getMessage() . "Erreur SQL [" . $idError . "] : " . __METHOD__ . " avec le paramètre id = " . json_encode(value: $authorizedDevice), errfile: $e->getFile(), errline: $e->getLine());
            throw new \Exception(message: "Erreur SQL : {$idError}", code: 1000);
        }
    }

    public function deleteUnusedAuthorizedDevice($parameter): bool
    {
        try{
            $query = "DELETE * FROM authorized_devices WHERE lastUsed <= date_sub(now(), interval " . $parameter . " day)";
            $stmt = $this->db->getConnection()->prepare(query: $query);
            $stmt->execute();
            return true;
        }catch(\Exception $e){
            $idError = uniqid();
            $this->tools->myErrorHandler(errno: $e->getCode(), errstr: $e->getMessage() . "Erreur SQL [" . $idError . "] : " . __METHOD__ . " avec le paramètre parameter = {$parameter}", errfile: $e->getFile(), errline: $e->getLine());
            throw new \Exception(message: "Erreur SQL : {$idError}", code: 1000);
        }
    }
}