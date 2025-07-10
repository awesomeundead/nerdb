<?php

header('Content-Type: application/json; charset=utf-8');

require ROOT_DIR . '/pdo.php';

$steamid = $_GET['steamid'] ?? false;

if (!$steamid)
{
    http_response_code(400);
    echo 'BAD REQUEST';
    exit;
}

$params = [
    'steamid' => $steamid
];
$query = 'SELECT * FROM users WHERE steamid = :steamid';

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);