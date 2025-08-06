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

$content = trim(file_get_contents('php://input'));
$dados = json_decode($content, true);

$game_id = $vars['id'];
$playlist = $dados['playlist'] ?? null;
$played = $dados['played'] ?? null;
$rating = $dados['rating'] ?? null;
$liked = $dados['liked'] ?? null;

require ROOT_DIR . '/pdo.php';

$params = [
    'user_id' => $_SESSION['user_id'],
    'game_id' => $game_id
];
$query = 'SELECT id FROM user_game_list WHERE user_id = :user_id AND game_id = :game_id';
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$user_game_list_id = $stmt->fetchColumn();

if (!$user_game_list_id)
{
    $params['playlist'] = 0;
    $params['played'] = 0;
    $params['rating'] = 0;
    $params['liked'] = 0;

    if (!empty($playlist))
    {
        $params['playlist'] = $playlist;
    }

    if (!empty($played))
    {
        $params['played'] = $played; 
    }

    if (!empty($rating))
    {
        $params['rating'] = $rating;
    }

    if (!empty($liked))
    {
        $params['liked'] = $liked;
    }

    $query = 'INSERT INTO user_game_list (user_id, game_id, playlist, played, rating, liked)
              VALUES (:user_id, :game_id, :playlist, :played, :rating, :liked)';
}
else
{
    $params = ['id' => $user_game_list_id];
    $conditions = [];

    if ($playlist === '0' || $playlist === '1')
    {
        $conditions[] = 'playlist = :playlist';
        $params['playlist'] = $playlist;
    }

    if ($played === '0' || $played === '1')
    {
        $conditions[] = 'played = :played';
        $params['played'] = $played;
    }

    if (is_numeric($rating))
    {
        $conditions[] = 'rating = :rating';
        $params['rating'] = $rating;
    }

    if ($liked === '0' || $liked === '1')
    {
        $conditions[] = 'liked = :liked';
        $params['liked'] = $liked;
    }

    if (empty($conditions))
    {
        http_response_code(400);
        echo 'BAD REQUEST';
        exit;
    }
    else
    {
        $subquery = implode(', ', $conditions);
    }

    $query = "UPDATE user_game_list SET {$subquery} WHERE id = :id";
}

$stmt = $pdo->prepare($query);
$result = $stmt->execute($params);

$json['status'] = $result ? 'success' : 'failure';

echo json_encode($json);