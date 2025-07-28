<?php

header('Content-Type: application/json; charset=utf-8');

require ROOT_DIR . '/pdo.php';

$params = [];

/*
$logged_in = $_SESSION['logged_in'] ?? false;

if($logged_in)
{
    $user_id  = $_SESSION['user_id'];
    $params = ['user_id' => $user_id];
    $query = 'SELECT movies.*, list.watchlist, list.watched, list.rating, list.liked FROM movies
              LEFT JOIN user_movie_list AS list ON movies.id = list.movie_id AND list.user_id = :user_id
              ';

    if (isset($_GET['release']) && preg_match('/^\d{4}$/', $_GET['release']))
    {
        $params['release_year'] = $_GET['release'];
        $query .= 'WHERE release_year = :release_year';
    }
    elseif (isset($_GET['search']))
    {
        $search = trim($_GET['search']);
        $params['search'] = $search;
        $query .= 'WHERE MATCH(title_br, title_us, director) AGAINST(:search)';
    }
    else
    {
        $query .= 'ORDER BY movies.id ASC';
    }
}
*/

if (isset($_GET['release']) && preg_match('/^\d{4}$/', $_GET['release']))
{
    $params['release_year'] = $_GET['release'];

    $query = 'SELECT * FROM movies WHERE release_year = :release_year';
}
elseif (isset($_GET['director']))
{
    $director = trim($_GET['director']);
    $params['director'] = $director;

    $query = 'SELECT * FROM movies WHERE director = :director';
}
elseif (isset($_GET['search']))
{
    $search = trim($_GET['search']);
    $params['search'] = $search;

    $query = 'SELECT * FROM movies WHERE MATCH(title_br, title_us, director) AGAINST(:search)';
}
else
{
    $query = 'SELECT * FROM movies';
    
    if (isset($_GET['order']) && $_GET['order'] == 'random')
    {
        $query = 'SELECT * FROM movies WHERE media != "" ORDER BY rand() LIMIT 20';
    }
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$result['movies'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);