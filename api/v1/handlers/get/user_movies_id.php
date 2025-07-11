<?php

header('Content-Type: application/json; charset=utf-8');

require ROOT_DIR . '/pdo.php';

$params = [
    'user_id' => $vars['id']
];
$query = 'SELECT watched, movies.* FROM users_list_movies
          INNER JOIN movies ON movies.id = users_list_movies.movie_id
          WHERE users_list_movies.user_id = :user_id';

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$result['movies'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);