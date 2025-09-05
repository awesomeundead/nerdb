<?php

header('Content-Type: application/json; charset=utf-8');

require ROOT_DIR . '/pdo.php';

$query = 'SELECT games.*, SUM(rating) as rating FROM user_game_list
          INNER JOIN games ON games.id = user_game_list.game_id
          WHERE rating >= 1 GROUP BY game_id ORDER BY rating DESC LIMIT 42';

$stmt = $pdo->prepare($query);
$stmt->execute();
$result['games'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);