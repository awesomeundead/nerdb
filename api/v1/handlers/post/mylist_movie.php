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

$user_id = $_SESSION['user_id'];
$movie_id = $vars['id'];
$watchlist = $dados['watchlist'] ?? null;
$watched = $dados['watched'] ?? null;
$rating = $dados['rating'] ?? null;
$liked = $dados['liked'] ?? null;

require ROOT_DIR . '/pdo.php';

$params = [
    'user_id' => $user_id,
    'movie_id' => $movie_id
];
$query = 'SELECT id FROM user_movie_list WHERE user_id = :user_id AND movie_id = :movie_id';
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$user_movie_list_id = $stmt->fetchColumn();

if ($user_movie_list_id === false)
{
    $params['watchlist'] = 0;
    $params['watched'] = 0;
    $params['rating'] = 0;
    $params['liked'] = 0;

    if (!empty($watchlist))
    {
        $params['watchlist'] = $watchlist;
    }

    if (!empty($watched))
    {
        $params['watched'] = $watched; 
    }

    if (!empty($rating))
    {
        $params['rating'] = $rating;
    }

    if (!empty($liked))
    {
        $params['liked'] = $liked;
    }

    $query = 'INSERT INTO user_movie_list (user_id, movie_id, watchlist, watched, rating, liked)
              VALUES (:user_id, :movie_id, :watchlist, :watched, :rating, :liked)';
}
else
{
    $params = ['id' => $user_movie_list_id];
    $conditions = [];

    if ($watchlist === '0' || $watchlist === '1')
    {
        $conditions[] = 'watchlist = :watchlist';
        $params['watchlist'] = $watchlist;
    }

    if ($watched === '0' || $watched === '1')
    {
        $conditions[] = 'watched = :watched';
        $params['watched'] = $watched;
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

    $query = "UPDATE user_movie_list SET {$subquery} WHERE id = :id";
}

$stmt = $pdo->prepare($query);
$result = $stmt->execute($params);

if ($result && $user_movie_list_id === false)
{
    $query = 'UPDATE score SET rating_movie = rating_movie + 1 WHERE id = :id';
    $stmt = $pdo->prepare($query);
    $stmt->execute(['id' => $user_id]);
}

$json['status'] = $result ? 'success' : 'failure';

echo json_encode($json);