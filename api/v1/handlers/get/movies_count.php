<?php

header('Content-Type: application/json; charset=utf-8');

require ROOT_DIR . '/pdo.php';

$query = 'SELECT COUNT(id) FROM movies';

$stmt = $pdo->prepare($query);
$stmt->execute();
$result['total'] = $stmt->fetchColumn();

echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);