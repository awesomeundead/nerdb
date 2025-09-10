<?php

header('Content-Type: application/json; charset=utf-8');


$pdo = Database::connect();

$logged_in = Session::get('logged_in');

if(!$logged_in)
{
    http_response_code(401);
    echo 'UNAUTHORIZED';
    exit;
}

$query = 'SELECT * FROM people';

$stmt = $pdo->prepare($query);
$stmt->execute();
$result['people'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);