<?php

header('Content-Type: application/json; charset=utf-8');



$logged_in = Session::get('logged_in');

if(!$logged_in)
{
    http_response_code(401);
    echo 'UNAUTHORIZED';
    //exit;
}

require ROOT_DIR . '/pdo.php';

$content = trim(file_get_contents('php://input'));
$data = json_decode($content, true);

$user_id = $_SESSION['user_id'];
$movie_id= $vars['id'];
$cast = $data['cast'];

foreach ($cast as $item)
{
    $id = $item['id'];
    $name = $item['name'];
    $character = $item['character'];
    $params = [
        'movie_id' => $movie_id,
        'character' => $character
    ];

    $query = 'SELECT id FROM people WHERE name = :name';
    $stmt = $pdo->prepare($query);
    $stmt->execute(['name' => $name]);
    $person_id = $stmt->fetchColumn();

    if ($person_id == false)
    {
        continue;
    }

    $params['person_id'] = $person_id;

    if ($id == "null")
    {
        $query = 'INSERT INTO movies_cast (person_id, movie_id, movie_character) VALUES (:person_id, :movie_id, :character)';
    }
    else
    {
        $params['id'] = $id;
        $query = 'UPDATE movies_cast SET person_id = :person_id, movie_character = :character WHERE id = :id AND movie_id = :movie_id';
    }

    $stmt = $pdo->prepare($query);
    $result = $stmt->execute($params);
}

$json['status'] = ($result ?? false) ? 'success' : 'failure';

echo json_encode($json);