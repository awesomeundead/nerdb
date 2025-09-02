<?php

header('Content-Type: application/json; charset=utf-8');

require ROOT_DIR . '/pdo.php';


$logged_in = Session::get('logged_in');
$id = $vars['id'];
$params = ['id' => $id];

if($logged_in)
{
    $user_id  = $_SESSION['user_id'];
    $params['user_id'] = $user_id;
    $query = 'SELECT movies.*, list.watchlist, list.watched, list.rating, list.liked FROM movies
              LEFT JOIN user_movie_list AS list ON movies.id = list.movie_id AND list.user_id = :user_id
              WHERE movies.id = :id';
}
else
{
    $query = 'SELECT * FROM movies WHERE id = :id';
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if($result)
{
    $params = ['id' => $id];

    $query = 'SELECT movies_cast.id, name, movie_character, media FROM movies_cast
              INNER JOIN people ON people.id = movies_cast.person_id
              WHERE movie_id = :id';
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $result['cast'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $query = 'SELECT platform_name, platform_link FROM movie_platforms WHERE movie_id = :id';
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $result['platforms'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);