<?php

header('Content-Type: application/json; charset=utf-8');


$pdo = Database::connect();

$logged_in = Session::get('logged_in');

if(!$logged_in)
{
    http_response_code(401);
    echo 'UNAUTHORIZED';
    exit;
}

$my_user_id = $_SESSION['user_id'];
$user_id = $vars['id'];

if ($my_user_id == $user_id)
{
    http_response_code(400);
    echo 'BAD REQUEST';
    exit;
}

$params = [
    'id' => $user_id
];
$query = 'SELECT *,
          (add_movie * 10) AS add_movie_total, (add_game * 10) AS add_game_total, (update_movie * 2) AS update_movie_total, (update_game * 2) AS update_game_total,
          (add_movie * 10 + add_game * 10 + update_movie * 2 + update_game * 2 + rating_movie + rating_game) AS total
          FROM score WHERE id = :id';

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$result['user_score'] = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare($query);
$stmt->execute(['id' => $my_user_id]);
$result['my_score'] = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);