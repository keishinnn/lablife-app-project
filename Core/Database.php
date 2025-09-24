<?php

namespace Core;

use PDO;
use PDOException;

class Database
{
    protected $connection;

    public function __construct($config)
    {
        $dsn = "pgsql:host={$config['host']};port={$config['port']};dbname={$config['dbname']};sslmode=require";

        try {
            $this->connection = new PDO($dsn, $config['user'], $config['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);
        } catch (PDOException $e) {
            throw new \RuntimeException("Database connection failed: " . $e->getMessage());
        }
    }

    public function query($query, $params = [])
    {
        $statement = $this->connection->prepare($query);
        $statement->execute($params);
        return $statement;
    }

    public function prepare($sql)
    {
        return $this->connection->prepare($sql);
    }
}
