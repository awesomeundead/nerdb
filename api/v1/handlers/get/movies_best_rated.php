<?php

header('Content-Type: application/json; charset=utf-8');

$pdo = Database::connect();

$query = 'SELECT movies.*, SUM(rating) as rating FROM user_movie_list
          INNER JOIN movies ON movies.id = user_movie_list.movie_id
          WHERE rating >= 1 GROUP BY movie_id ORDER BY rating DESC LIMIT 60';

$stmt = $pdo->prepare($query);
$stmt->execute();
$result['movies'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);