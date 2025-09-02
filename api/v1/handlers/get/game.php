<?php

header('Content-Type: application/json; charset=utf-8');

require ROOT_DIR . '/pdo.php';


$logged_in = Session::get('logged_in');
$game_id = $vars['id'];
$params = ['id' => $game_id];
$query = 'SELECT * FROM games WHERE id = :id';

if($logged_in)
{
    $user_id  = $_SESSION['user_id'];
    $params['user_id'] = $user_id;
    $query = 'SELECT games.*, list.playlist, list.played, list.rating, list.liked FROM games
              LEFT JOIN user_game_list AS list ON games.id = list.game_id AND list.user_id = :user_id
              WHERE games.id = :id';
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);