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

$movie_id = $vars['id'];

$params = [
    'user_id' => $_SESSION['user_id'],
    'movie_id' => $movie_id
];
$query = 'SELECT id FROM user_movie_list WHERE user_id = :user_id AND movie_id = :movie_id';
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$result = $stmt->fetchColumn();

if (!$result)
{
    $query = 'INSERT INTO user_movie_list (user_id, movie_id)
              VALUES (:user_id, :movie_id)';

    $stmt = $pdo->prepare($query);
    $result = $stmt->execute($params);

    $json['status'] = $result ? 'success' : 'failure';

    echo json_encode($json);
}
else
{
    echo 'false';
}