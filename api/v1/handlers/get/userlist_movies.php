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

$my_user_id = $_SESSION['user_id'];
$user_id = $vars['id'];

if ($my_user_id == $user_id)
{
    http_response_code(400);
    echo 'BAD REQUEST';
    exit;
}

$watchlist = $_GET['watchlist'] ?? null;
$watched = $_GET['watched'] ?? null;
$rating = $_GET['rating'] ?? null;
$liked = $_GET['liked'] ?? null;

require ROOT_DIR . '/pdo.php';

$conditions = [];
$params = [
    'my_user_id' => $my_user_id,
    'user_id' => $user_id
];

if (!empty($watchlist))
{
    $conditions[] = 'list.watchlist = :watchlist';
    $params['watchlist'] = $watchlist;
}

if (!empty($watched))
{
    $conditions[] = 'list.watched = :watched';
    $params['watched'] = $watched;
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

$query = 'SELECT  m.id, m.title_br, m.media, list.watchlist, list.watched, list.rating, list.liked,
          ml.watchlist AS ml_watchlist,
          ml.watched AS ml_watched,
          ml.rating AS ml_rating,
          ml.liked AS ml_liked
          FROM user_movie_list AS list
          INNER JOIN movies AS m ON m.id = list.movie_id
          LEFT JOIN user_movie_list AS ml ON ml.movie_id = list.movie_id AND ml.user_id = :my_user_id
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
    $params['watchlist'] = 0;
    $params['watched'] = 0;
    $params['rating'] = 0;
    $params['liked'] = 0;

    $query .= ' AND (list.watchlist != :watchlist OR list.watched != :watched OR list.rating != :rating OR list.liked != :liked)';
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$result['movies'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);