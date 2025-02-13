<?php

namespace App\Repositories;

use PDO;

use App\Services\DBConnection;
use App\Lib\Tools;

use App\Models\User;

use App\Factories\UserFactory;

class UserRepository
{
    private DBConnection $db;
    private Tools $tools;

    public function __construct(
        DBConnection $db,
        Tools $tools
    ){
        $this->db = $db;
        $this->tools = $tools;
    }

    public function getUserById(string $id): User|null
    {
        try{
            $query = "SELECT * FROM users WHERE id = :id AND isDelete = 0";
            $stmt = $this->db->getConnection()->prepare(query: $query);
            $stmt->bindParam(param:':id',var: $id);
            $stmt->execute();
            $user = $stmt->fetch(mode: PDO::FETCH_ASSOC);
            if($user === false){
                return null;
            }else{
                return UserFactory::createFromArray(data: $user);
            }
        }catch(\Exception $e){
            $idError = uniqid();
            $this->tools->myErrorHandler(errno: $e->getCode(), errstr: $e->getMessage() . "Erreur SQL [" . $idError . "] : " . __METHOD__ . " avec le paramètre id = {$id}", errfile: $e->getFile(), errline: $e->getLine());
            throw new \Exception(message: "Erreur SQL : {$idError}", code: 1000);
        }
    }

    public function getUserByEmail(string $email): User|null
    {
        try{
            $query = "SELECT * FROM users WHERE email = :email AND isDeleted = 0";
            $stmt = $this->db->getConnection()->prepare(query: $query);
            $stmt->bindParam(param: ':email' , var: $email);
            $stmt->execute();
            $user = $stmt->fetch(mode: PDO::FETCH_ASSOC);
            if($user === null){
                return null;
            }else{
                return UserFactory::createFromArray(data: $user);
            }
        }catch(\Exception $e){
            $idError = uniqid();
            $this->tools->myErrorHandler(errno: $e->getCode(), errstr: $e->getMessage() . "Erreur SQL [" . $idError . "] : " . __METHOD__ . " avec le paramètre id = {$email}", errfile: $e->getFile(), errline: $e->getLine());
            throw new \Exception(message: "Erreur SQL : {$idError}", code: 1000);
        }
    }

    public function search(string $search): array|null
    {
        try {
            $searchCols = ["lastName", "firstName", "email"];
            $searchPatterns = explode(separator: ' ', string: $search);
            foreach ($searchPatterns as &$term) {
                $term = "%$term%";
            }
            $query = "SELECT * FROM users WHERE ";
            foreach ($searchPatterns as $term) {
                $query .= "(";
                foreach ($searchCols as $col) {
                    $query .= "{$col} LIKE '$term' OR ";
                }
                $query = rtrim(string: $query, characters: " OR ") . ") AND ";
            }
            $query .= "isDeleted = 0;";
            $stmt = $this->db->getConnection()->prepare(query: $query);
            $stmt->execute();
            $users = [];
            while ($row = $stmt->fetch(mode: PDO::FETCH_ASSOC)) {
                $user = UserFactory::createFromArray(data: $row);
                $users[] = $user;
            }
            return $users;
        } catch (\Exception $e) {
            $idError = uniqid();
            $this->tools->myErrorHandler(errno: $e->getCode(), errstr: $e->getMessage() . "Erreur SQL [" . $idError . "] : " . __METHOD__ . " avec le paramètre search = {$search}", errfile: $e->getFile(), errline: $e->getLine());
            throw new \Exception(message: "Erreur SQL : {$idError}", code: 1000);
        }
    }

    public function isAdmin(string $id):bool
    {
        try {
            $query = "SELECT isAdmin FROM users WHERE id = :id AND isDeleted = 0";
            $stmt = $this->db->getConnection()->prepare(query: $query);
            $stmt->bindParam(param: ':id', var: $id);
            $stmt->execute();
            $isAdmin = $stmt->fetchColumn();
            if ($isAdmin == 1) {
                return true;
            }
            return false;
        } catch (\Exception $e) {
            $idError = uniqid();
            $this->tools->myErrorHandler(errno: $e->getCode(), errstr: $e->getMessage() . "Erreur SQL [" . $idError . "] : " . __METHOD__ . " avec le paramètre id = {$id}", errfile: $e->getFile(), errline: $e->getLine());
            throw new \Exception(message: "Erreur SQL : {$idError}", code: 1000);
        }
    }

    public function emailUserExist(string $emailToVerif, string $excludedId = null): bool
    {
        try {
            if ($excludedId !== null) {
                $query = "SELECT email FROM users WHERE email = :email AND id != :id AND isDeleted = :isDeleted";
            } else {
                $query = "SELECT email FROM users WHERE email = :email AND isDeleted = :isDeleted";
            }
            $stmt = $this->db->getConnection()->prepare(query: $query);
            $stmt->bindParam(param: ':email', var: $emailToVerif);
            if ($excludedId !== null) {
                $stmt->bindParam(param: ':id', var: $excludedId);
            }
            $stmt->bindValue(param: ':isDeleted', value: 0, type: PDO::PARAM_BOOL);
            $stmt->execute();
            $email = $stmt->fetchColumn();
            return $email !== false;
            /* Fonctionnement de la ligne return $email !== false;
            if($email !== false){
                return true;
            }else{
                return false;
            }
            */
        } catch (\Exception $e) {
            $idError = uniqid();
            $this->tools->myErrorHandler(errno: $e->getCode(), errstr: $e->getMessage() . "Erreur SQL [" . $idError . "] : " . __METHOD__ . " avec les paramètres id = " . $excludedId . " et email = " . $emailToVerif, errfile: $e->getFile(), errline: $e->getLine());
            throw new \Exception(message: "Erreur SQL : {$idError}", code: 1000);
        }
    }

    public function addUser(User $user): ?User
    {
        try{
            $user->id = uniqid();
            $user->password = password_hash(password: $user->password, algo: PASSWORD_DEFAULT, options: ['cost'=>10]);
            $query = "INSERT INTO users(id,lastName,firstName,email,password,isAdmin)VALUES(:id,:lastName,:firstName,:email,:password,:isAdmin)";
            $stmt = $this->db->getConnection()->prepare(query: $query);
            $stmt->bindParam(param: ':id',var: $user->id);
            $stmt->bindParam(param: ':lastName',var: $user->lastName);
            $stmt->bindParam(param: ':firstName',var: $user->firstName);
            $stmt->bindParam(param: ':email',var: $user->email);
            $stmt->bindParam(param: ':password',var: $user->password);
            $stmt->bindParam(param: ':isAdmin',var: $user->isAdmin);
            $stmt->execute();
            return $user;
        } catch (\Exception $e) {
            $idError = uniqid();
            $this->tools->myErrorHandler(errno: $e->getCode(), errstr: $e->getMessage() . "Erreur SQL [" . $idError . "] : " . __METHOD__ . " avec le paramètre user = " . json_encode(value: $user), errfile: $e->getFile(), errline: $e->getLine());
            throw new \Exception(message: "Erreur SQL : {$idError}", code: 1000);
        }
    }

    public function updateUser(User $user): bool
    {
        try{
            $columnsToUpdate = [];
            $parameters = [':id' => $user->id];
            if($user->lastName !== null){
                $columnsToUpdate[] = "lastName = :lastName";
                $parameters[':lastName'] = $user->lastName;
            }
            if ($user->firstName !== null) {
                $columnsToUpdate[] = "firstName = :firstName";
                $parameters[":firstName"] = $user->firstName;
            }
            if ($user->email !== null) {
                $columnsToUpdate[] = "email = :email";
                $parameters[":email"] = $user->email;
            }
            if ($user->password !== null) {
                $user->password = password_hash(password: $user->password, algo: PASSWORD_DEFAULT, options: ['cost' => 10]);
                $columnsToUpdate[] = "password = :password";
                $parameters[":password"] = $user->password;
            }
            if ($user->isAdmin !== null) {
                $columnsToUpdate[] = "isAdmin = :isAdmin";
                $parameters[":isAdmin"] = $user->isAdmin;
            }
            if ($user->isDeleted !== null) {
                $columnsToUpdate[] = "isDeleted = :isDeleted";
                $parameters[":isDeleted"] = $user->isDeleted;
            }
            $query = "UPDATE users set" . implode(separator: ", ",array: $columnsToUpdate) . "WHERE id = :id";
            $parameters[":id"] = $user->id;
            $stmt = $this->db->getConnection()->prepare(query: $query);
            $stmt->execute(params: $parameters);
            return true;
        } catch (\Exception $e) {
            $idError = uniqid();
            $this->tools->myErrorHandler(errno: $e->getCode(), errstr: $e->getMessage() . "Erreur SQL [" . $idError . "] : " . __METHOD__ . " avec le paramètre user = " . json_encode(value: $user), errfile: $e->getFile(), errline: $e->getLine());
            throw new \Exception(message: "Erreur SQL : {$idError}", code: 1000);
        }
    }
}