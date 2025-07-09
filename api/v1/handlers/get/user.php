<?php

header('Content-Type: application/json; charset=utf-8');

require ROOT_DIR . '/pdo.php';

if (isset($_GET['steamid']))
{
    $params = [
        'steamid' => $_GET['steamid']
    ];
    $query = 'SELECT * FROM users WHERE steamid = :steamid';
}
else
{
    exit;
}


$stmt = $pdo->prepare($query);
$stmt->execute($params);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);