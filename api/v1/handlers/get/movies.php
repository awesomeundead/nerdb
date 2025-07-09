<?php

header('Content-Type: application/json; charset=utf-8');

require ROOT_DIR . '/pdo.php';

$params = [];

if (isset($_GET['release']) && preg_match('/^\d{4}$/', $_GET['release']))
{
    $params['release_year'] = $_GET['release'];

    $query = 'SELECT * FROM movies WHERE release_year = :release_year';
}
elseif (isset($_GET['search']))
{
    $search = trim($_GET['search']);
    $params['search'] = "%{$search}%";

    $query = 'SELECT * FROM movies WHERE CONCAT_WS(" ", title_br, title_us, release_year) LIKE :search';
}
else
{
    $query = 'SELECT * FROM movies';
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$result['movies'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);