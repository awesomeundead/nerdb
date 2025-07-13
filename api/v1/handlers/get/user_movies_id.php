<?php

header('Content-Type: application/json; charset=utf-8');

require ROOT_DIR . '/pdo.php';

$params = [
    'user_id' => $vars['id']
];
$query = 'SELECT watched, movies.* FROM user_movie_list
          INNER JOIN movies ON movies.id = user_movie_list.movie_id
          WHERE user_movie_list.user_id = :user_id';

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$result['movies'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);