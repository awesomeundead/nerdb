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
    
    $query = 'SELECT platform_name, platform_id FROM movie_platforms WHERE movie_id = :id';
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $result['platforms'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $result['friends'] = [];

    if($logged_in)
    {
        $query = 'SELECT IF(user_id1 = :user_id, user_id2, user_id1) AS id
                  FROM friendship WHERE :user_id IN (user_id1, user_id2)';
        $stmt = $pdo->prepare($query);
        $stmt->execute(['user_id' => $user_id]);
        $list = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (!empty($list))
        {
            $params = ['movie_id' => $id];
        
            foreach ($list as $index => $item)
            {
                $key = ":placeholder_{$index}";
                $keys[] = $key;
                $params[$key] = $item;
            }

            $placeholders = implode(', ', $keys);

            $query = "SELECT personaname, avatarhash, user_movie_list.*
                    FROM user_movie_list
                    LEFT JOIN users ON users.id = user_movie_list.user_id
                    WHERE user_movie_list.movie_id = :movie_id AND users.id IN ({$placeholders})";
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            $result['friends'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
}

echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);