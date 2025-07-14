<?php

header('Content-Type: application/json; charset=utf-8');

require ROOT_DIR . '/../../session.php';

$logged_in = $_SESSION['logged_in'] ?? false;

if(!$logged_in)
{
    http_response_code(401);
    echo 'UNAUTHORIZED';
    exit;
}

$watched = $_GET['watched'] ?? null;
$rating = $_GET['rating'] ?? null;
$reaction = $_GET['reaction'] ?? null;

require ROOT_DIR . '/pdo.php';

$params = ['user_id' => $_SESSION['user_id']];
$conditions = [];

if ($watched !== null && $watched !== '')
{
    $conditions[] = 'list.watched = :watched';
    $params['watched'] = $watched;
}

if ($rating !== null && $rating !== '')
{
    $conditions[] = 'list.rating = :rating';
    $params['rating'] = $rating;
}

if ($reaction !== null && $reaction !== '')
{
    $conditions[] = 'list.reaction = :reaction';
    $params['reaction'] = $reaction;
}

$query = 'SELECT watched, movies.* FROM user_movie_list AS list
          INNER JOIN movies ON movies.id = list.movie_id
          WHERE list.user_id = :user_id';

if (!empty($conditions))
{
    $query .= ' AND ' . implode(' AND ', $conditions);
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$result['movies'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);