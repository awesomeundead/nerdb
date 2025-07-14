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

$watchlist = $_GET['watchlist'] ?? null;
$watched = $_GET['watched'] ?? null;
$rating = $_GET['rating'] ?? null;
$reaction = $_GET['reaction'] ?? null;

require ROOT_DIR . '/pdo.php';

$params = ['user_id' => $_SESSION['user_id']];
$conditions = [];

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
    $conditions[] = 'list.rating = :rating';
    $params['rating'] = $rating;
}

if (!empty($reaction))
{
    $conditions[] = 'list.reaction = :reaction';
    $params['reaction'] = $reaction;
}

if (!empty($conditions))
{
    $query = 'SELECT  movies.*, watchlist, watched, rating, reaction FROM user_movie_list AS list
              INNER JOIN movies ON movies.id = list.movie_id
              WHERE list.user_id = :user_id';

    if (!empty($conditions))
    {
        $query .= ' AND ' . implode(' AND ', $conditions);
    }

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $result['movies'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
else
{
    $result['movies'] = [];
}

echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);