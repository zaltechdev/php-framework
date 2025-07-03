<?php

namespace App\Core;

class Database {

    private static ?\PDO $pdo = null;

    public const UPLOAD_DIR = __DIR__ . "/../../storages/uploads/";

    private static function connect(): \PDO {
        if (self::$pdo === null) {
            $driver   = env("database_driver_prefix");
            $host     = env("database_hostname");
            $dbname   = env("database_dbname");
            $username = env("database_username");
            $password = env("database_password");

            $dsn = "$driver:host=$host;dbname=$dbname";

            self::$pdo = new \PDO($dsn, $username, $password);
            self::$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            self::$pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        }

        return self::$pdo;
    }

    public static function query(string $sql, array $params = []) {
        try {
            $stmt = self::connect()->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } 
        catch (\PDOException $error) {
            Logging::record("error", $error, self::class);
            return false;
        }
    }

    protected static function result(null|bool|array $data, string $message){
        return (object) [
            "data" => $data,
            "message" => $message
        ];
    }
}
