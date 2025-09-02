<?php

header('Content-Type: application/json; charset=utf-8');



$logged_in = Session::get('logged_in');

if(!$logged_in)
{
    http_response_code(401);
    echo 'UNAUTHORIZED';
    exit;
}

$playlist = $_GET['playlist'] ?? null;
$played = $_GET['played'] ?? null;
$rating = $_GET['rating'] ?? null;
$liked = $_GET['liked'] ?? null;

require ROOT_DIR . '/pdo.php';

$params = ['user_id' => $_SESSION['user_id']];
$conditions = [];

if (!empty($playlist))
{
    $conditions[] = 'list.playlist = :playlist';
    $params['playlist'] = $playlist;
}

if (!empty($played))
{
    $conditions[] = 'list.played = :played';
    $params['played'] = $played;
}

if (!empty($rating))
{
    $conditions[] = 'list.rating >= :rating';
    $params['rating'] = $rating;
}

if (!empty($liked))
{
    $conditions[] = 'list.liked = :liked';
    $params['liked'] = $liked;
}

if (!empty($conditions))
{
    $query = 'SELECT  games.*, playlist, played, rating, liked FROM user_game_list AS list
              INNER JOIN games ON games.id = list.game_id
              WHERE list.user_id = :user_id';

    $query .= ' AND ' . implode(' AND ', $conditions);

    if ($rating == 1)
    {
        $query .= ' ORDER BY list.rating DESC';
    }
}
else
{    
    $params['playlist'] = 0;
    $params['played'] = 0;
    $params['rating'] = 0;
    $params['liked'] = 0;

    $query = 'SELECT  games.*, playlist, played, rating, liked FROM user_game_list AS list
              INNER JOIN games ON games.id = list.game_id
              WHERE list.user_id = :user_id AND (list.playlist != :playlist OR list.played != :played OR list.rating != :rating OR list.liked != :liked)';
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$result['games'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);