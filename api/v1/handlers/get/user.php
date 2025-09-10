<?php

header('Content-Type: application/json; charset=utf-8');

$pdo = Database::connect();

$user_id = $vars['id'] ?? false;
$steamid = $_GET['steamid'] ?? false;

if (!$user_id && !$steamid)
{
    http_response_code(400);
    echo 'BAD REQUEST';
    exit;
}

$params = [
    'id' => $user_id,
    'steamid' => $steamid
];
$query = 'SELECT * FROM users WHERE id = :id || steamid = :steamid';

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);