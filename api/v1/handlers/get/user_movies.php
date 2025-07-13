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

require ROOT_DIR . '/pdo.php';

$params = [
    'user_id' => $_SESSION['user_id']
];
$query = 'SELECT watched, movies.* FROM user_movie_list
          INNER JOIN movies ON movies.id = user_movie_list.movie_id
          WHERE user_movie_list.user_id = :user_id';

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$result['movies'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);