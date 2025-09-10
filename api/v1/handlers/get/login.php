<?php

header('Content-Type: application/json; charset=utf-8');

$pdo = Database::connect();

$selector = $_GET['selector'] ?? false;

if (!$selector)
{
    http_response_code(400);
    echo 'BAD REQUEST';
    exit;
}

$params = ['selector' => $selector];
$query = 'SELECT * FROM login_log WHERE selector = :selector';

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);