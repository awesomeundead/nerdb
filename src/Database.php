<?php

class Database
{
    private static ?PDO $pdo = null;

    public static function connect()
    {
        if (!self::$pdo)
        {
            $config = (require ROOT_DIR . '/config.php')['pdo'];

            $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ];

            self::$pdo = new PDO($dsn, $config['username'], $config['password'], $options);
        }

        return self::$pdo;
    }
}