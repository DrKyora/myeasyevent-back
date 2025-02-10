<?php

namespace App\Services;

use PDO;

class DBConnection
{
    private $pdo;

    public function __construct()
    {
        $host = $_ENV['DB_HOST'];
        $db = $_ENV['DB_NAME'];
        $user = $_ENV['DB_USER'];
        $password = $_ENV['DB_PASS'];
        $connectionString = "mysql:host=$host;dbname=$db;charset=utf8mb4;port=3306";
        $this->pdo = new PDO(dsn: $connectionString, username: $user, password: $password);
    }

    public function getConnection(): PDO
    {
        return $this->pdo;
    }
}