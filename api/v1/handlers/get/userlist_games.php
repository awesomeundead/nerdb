<?php

header('Content-Type: application/json; charset=utf-8');



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

$playlist = $_GET['playlist'] ?? null;
$played = $_GET['played'] ?? null;
$rating = $_GET['rating'] ?? null;
$liked = $_GET['liked'] ?? null;

$pdo = Database::connect();

$conditions = [];
$params = [
    'my_user_id' => $my_user_id,
    'user_id' => $user_id
];

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

$query = 'SELECT  games.id, games.title, games.media, list.playlist, list.played, list.rating, list.liked,
          gl.playlist AS gl_playlist,
          gl.played AS gl_played,
          gl.rating AS gl_rating,
          gl.liked AS gl_liked
          FROM user_game_list AS list
          INNER JOIN games ON games.id = list.game_id
          LEFT JOIN user_game_list AS gl ON gl.game_id = list.game_id AND gl.user_id = :my_user_id
          WHERE list.user_id = :user_id';

if (!empty($conditions))
{
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

    $query .= ' AND (list.playlist != :playlist OR list.played != :played OR list.rating != :rating OR list.liked != :liked)';
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$result['games'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);