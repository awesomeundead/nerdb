<?php

$config = (require '../../config.php')['pdo'];

$dsn = $config['dsn'];
$username = $config['username'];
$password = $config['password'];

try
{
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch (PDOException $e)
{
    echo $e->getMessage();

    exit;
}