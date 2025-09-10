<?php

header('Content-Type: application/json; charset=utf-8');

$logged_in = Session::get('logged_in');
$user_id = Session::get('user_id');

if(!$logged_in)
{
    http_response_code(401);
    echo 'UNAUTHORIZED';
    exit;
}

$pdo = Database::connect();

$query = 'SELECT IF(user_id1 = :user_id, user_id2, user_id1) AS id
          FROM friendship WHERE :user_id IN (user_id1, user_id2)';
$stmt = $pdo->prepare($query);
$stmt->execute(['user_id' => $user_id]);
$list = $stmt->fetchAll(PDO::FETCH_COLUMN);

if (empty($list))
{
    echo '{"friends": []}';
    exit;
}

foreach ($list as $index => $item)
{
    $key = ":placeholder_{$index}";
    $keys[] = $key;
    $params[$key] = $item;
}

$placeholders = implode(', ', $keys);

$query = "SELECT * FROM users WHERE id IN ({$placeholders})";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$result['friends'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);