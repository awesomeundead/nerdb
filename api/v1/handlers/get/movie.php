<?php

header('Content-Type: application/json; charset=utf-8');

require ROOT_DIR . '/pdo.php';
require ROOT_DIR . '/../../session.php';

$logged_in = $_SESSION['logged_in'] ?? false;
$id = $vars['id'];
$params = ['id' => $id];

if($logged_in)
{
    $user_id  = $_SESSION['user_id'];
    $params['user_id'] = $user_id;
    $query = 'SELECT movies.*, list.watchlist, list.watched, list.rating, list.liked FROM movies
              LEFT JOIN user_movie_list AS list ON movies.id = list.movie_id AND list.user_id = :user_id
              WHERE movies.id = :id';
}
else
{
    $query = 'SELECT * FROM movies WHERE id = :id';
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);