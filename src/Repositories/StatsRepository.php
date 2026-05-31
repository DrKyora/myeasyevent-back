<?php

namespace App\Repositories;

use PDO;
use App\Services\DBConnection;
use App\Lib\Tools;

class StatsRepository {
    private DBConnection $db;
    private Tools $tools;

    public function __construct(
        DBConnection $db,
        Tools $tools
    ){
        $this->db = $db;
        $this->tools = $tools;
    }

    public function countNumberOfUsers(): int
    {
        try{
            $query = "SELECT COUNT(*) as total FROM users WHERE isDeleted = 0";
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$row['total'];
        } catch (\Exception $e) {
            $idError = uniqid();
            $this->tools->myErrorHandler(errno: $e->getCode(), errstr: $e->getMessage() . "Erreur SQL [" . $idError . "] : " . __METHOD__, errfile: $e->getFile(), errline: $e->getLine());
            throw new \Exception(message: "Erreur SQL : {$idError}", code: 1000);
        }
    }

        public function countReservations(): int
    {
        try{
            $query = "SELECT COUNT(*) as total FROM reservations WHERE isDeleted = 0";
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$row['total'];
        } catch (\Exception $e) {
            $idError = uniqid();
            $this->tools->myErrorHandler(errno: $e->getCode(), errstr: $e->getMessage() . "Erreur SQL [" . $idError . "] : " . __METHOD__, errfile: $e->getFile(), errline: $e->getLine());
            throw new \Exception(message: "Erreur SQL : {$idError}", code: 1000);
        }
    }

    public function countEvents(): int
    {
        try{
            $query = "SELECT COUNT(*) as total FROM events WHERE isDeleted = 0";
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$row['total'];
        } catch (\Exception $e) {
            $idError = uniqid();
            $this->tools->myErrorHandler(errno: $e->getCode(), errstr: $e->getMessage() . "Erreur SQL [" . $idError . "] : " . __METHOD__, errfile: $e->getFile(), errline: $e->getLine());
            throw new \Exception(message: "Erreur SQL : {$idError}", code: 1000);
        }
    }
}