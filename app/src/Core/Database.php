<?php

namespace App\Core;

class Database
{
    private static ?\PDO $connection = null;

    public static function getConnection(): \PDO
    {
        if (self::$connection === null) {
            self::$connection = new \PDO(
                'mysql:host=mysql;dbname=HaarlemFestivaldb;charset=utf8mb4',
                'developer',     
                'secret123',     
                [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
                ]
            );
        }

        return self::$connection;
    }
}
