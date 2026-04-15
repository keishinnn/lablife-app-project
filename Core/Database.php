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
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            app_log_exception($e, 'Database connection failed');
            throw new \RuntimeException('Database connection failed.');
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

    public function getConnection(): PDO
    {
        return $this->connection;
    }
}
