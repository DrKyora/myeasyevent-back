<?php

namespace App\Repositories;

use PDO;

use App\Services\DBConnection;
use App\Lib\Tools;

use App\Models\Reservation;

use App\Factories\ReservationFactory;

class ReservationRepository
{
    private $db;
    private $tools;
    private $factory;

    public function __construct(
        DBConnection $db,
        Tools $tools,
        ReservationFactory $factory
    ){
        $this->db = $db;
        $this->tools = $tools;
        $this->factory = $factory;
    }

    public function getReservationById(string $id): Reservation|null
    {
        try{
            $query = "SELECT * FROM reservations WHERE id = :id AND isDeleted = 0";
            $stmt = $this->db->getConnection()->prepare(query: $query);
            $stmt->bindParam(param: ':id', var: $id);
            $stmt->execute();
            $row = $stmt->fetch(mode: PDO::FETCH_ASSOC);
            if($row === false){
                return null;
            }else{
                $reservation = $this->factory->createFromArray(data: $row);
                return $reservation;
            }
        }catch(\Exception $e){
            $idError = uniqid();
            $this->tools->myErrorHandler(errno: $e->getCode(), errstr: $e->getMessage() . "Erreur SQL [" . $idError . "] : " . __METHOD__ . " avec le paramètre id = {$id}", errfile: $e->getFile(), errline: $e->getLine());
            throw new \Exception(message: "Erreur SQL : {$idError}", code: 1000);
        }
    }
    public function getResevationsOfEvent(string $eventId): array|null
    {
        try{
            $query = "SELECT * FROM reservations WHERE eventId = :eventId AND isDeleted = 0";
            $stmt = $this->db->getConnection()->prepare(query: $query);
            $stmt->bindParam(param: ':eventId', var: $eventId);
            $stmt->execute();
            $reservations = [];
            while($row = $stmt->fetch(mode: PDO::FETCH_ASSOC)){
                $reservation = $this->factory->createFromArray(data: $row);
                $reservations[] = $reservation;
            }
            return $reservations;
        }catch(\Exception $e){
            $idError = uniqid();
            $this->tools->myErrorHandler(errno: $e->getCode(), errstr: $e->getMessage() . "Erreur SQL [" . $idError . "] : " . __METHOD__ . " avec le paramètre eventId = {$eventId}", errfile: $e->getFile(), errline: $e->getLine());
            throw new \Exception(message: "Erreur SQL : {$idError}", code: 1000);
        }
    }

    public function reservationExist(string $emailToVerif, string $excludedId = null): bool
    {
        try{
            if($excludedId !== null){
                $query = "SELECT email FROM reservations WHERE email = :email AND id != :id AND isDeleted = :isDeleted";
            }else{
                $query = "SELECT email FROM reservations WHERE email = :email AND isDeleted = :isDeleted";
            }
            $stmt = $this->db->getConnection()->prepare(query: $query);
            $stmt->bindParam(param: ':email', var: $emailToVerif);
            if($excludedId !== null){
                $stmt->bindParam(param: ':id', var: $excludedId);
            }
            $stmt->bindValue(param: ':isDeleted', value: 0, type: PDO::PARAM_BOOL);
            $stmt->execute();
            $email = $stmt->fetchColumn();
            return $email !== false;
        } catch (\Exception $e) {
            $idError = uniqid();
            $this->tools->myErrorHandler(errno: $e->getCode(), errstr: $e->getMessage() . "Erreur SQL [" . $idError . "] : " . __METHOD__ . " avec les paramètres id = " . $excludedId . " et email = " . $emailToVerif, errfile: $e->getFile(), errline: $e->getLine());
            throw new \Exception(message: "Erreur SQL : {$idError}", code: 1000);
        }
    }

    public function addReservation(Reservation $reservation): Reservation
    {
        try{
            $reservation->id = uniqid();
            $query = "INSERT INTO reservations (id,eventId,lastName,firstName,email,birthDate,dateReservation)
            VALUES(:id,:eventId,:lastName,:firstName,:email,:birthDate,:dateReservation)";
            $stmt = $this->db->getConnection()->prepare(query: $query);
            $stmt->bindParam(param: ':id', var: $reservation->id);
            $stmt->bindParam(param: ':eventId', var: $reservation->eventId);
            $stmt->bindParam(param: ':lastName', var: $reservation->lastName);
            $stmt->bindParam(param: ':firstName', var: $reservation->firstName);
            $stmt->bindParam(param: ':email', var: $reservation->email);
            $stmt->bindParam(param: ':birthDate', var: $reservation->birthDate);
            $stmt->bindParam(param: ':dateReservation', var: $reservation->dateReservation);
            $stmt->execute();
            return $reservation;
        }catch(\Exception $e){
            $idError = uniqid();
            $this->tools->myErrorHandler(errno: $e->getCode(), errstr: $e->getMessage() . "Erreur SQL [" . $idError . "] : " . __METHOD__ . " avec le paramètre reservation = " . json_encode(value: $reservation), errfile: $e->getFile(), errline: $e->getLine());
            throw new \Exception(message: "Erreur SQL : {$idError}", code: 1000);
        }
    }

    public function updateReservation(Reservation $reservation): bool
    {
        try{
            $columnsToUpdate = [];
            $parameters = ['id' => $reservation->id];
            $parameters = ['eventId' => $reservation->eventId];
            if($reservation->lastName !== null){
                $columnsToUpdate[] = "lastName = :lastName";
                $parameters['lastName'] = $reservation->lastName;
            }
            if($reservation->firstName !== null){
                $columnsToUpdate[] = "firstName = :firstName";
                $parameters['firstName'] = $reservation->firstName;
            }
            if($reservation->email !== null){
                $columnsToUpdate[] = "email = :email";
                $parameters['email'] = $reservation->email;
            }
            if($reservation->birthDate !== null){
                $columnsToUpdate[] = "birthDate = :birthDate";
                $parameters['birthDate'] = $reservation->birthDate;
            }
            if($reservation->dateReservation !== null){
                $columnsToUpdate[] = "dateReservation = :dateReservation";
                $parameters['dateReservation'] = $reservation->dateReservation;
            }
            if($reservation->isDeleted !== null){
                $columnsToUpdate[] = 'isDeleted = :isDeleted';
                $parameters['isDeleted'] = $reservation->isDeleted;
            }
            $query = "UPDATE reservations SET " . implode(separator: ", ", array: $columnsToUpdate) . " WHERE id = :id";
            $stmt = $this->db->getConnection()->prepare(query: $query);
            $stmt->execute( params: $parameters);
            return true;
        }catch(\Exception $e){
            $idError = uniqid();
            $this->tools->myErrorHandler(errno: $e->getCode(), errstr: $e->getMessage() . "Erreur SQL [" . $idError . "] : " . __METHOD__ . " avec le paramètre reservation = " . json_encode(value: $reservation), errfile: $e->getFile(), errline: $e->getLine());
            throw new \Exception(message: "Erreur SQL : {$idError}", code: 1000);
        }
    }
}