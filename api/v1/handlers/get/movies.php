<?php

header('Content-Type: application/json; charset=utf-8');

require ROOT_DIR . '/pdo.php';

require ROOT_DIR . '/../../session.php';

$logged_in = $_SESSION['logged_in'] ?? false;

if($logged_in)
{
    $user_id  = $_SESSION['user_id'];
    $params = [
        'user_id' => $user_id
    ];

    if (isset($_GET['release']) && preg_match('/^\d{4}$/', $_GET['release']))
    {
        $params['release_year'] = $_GET['release'];

        $query = 'SELECT * FROM movies WHERE release_year = :release_year';
    }
    elseif (isset($_GET['search']))
    {
        $search = trim($_GET['search']);
        $params['search'] = "%{$search}%";

        $query = 'SELECT * FROM movies WHERE CONCAT_WS(" ", title_br, title_us, release_year) LIKE :search';
    }
    else
    {
        $query = 'SELECT movies.*, userlist.id AS added FROM movies
                  LEFT JOIN users_list_movies AS userlist ON movies.id = userlist.movie_id AND userlist.user_id = :user_id
                  ORDER BY movies.id ASC';
    }
}
else
{
    if (isset($_GET['release']) && preg_match('/^\d{4}$/', $_GET['release']))
    {
        $params['release_year'] = $_GET['release'];

        $query = 'SELECT * FROM movies WHERE release_year = :release_year';
    }
    elseif (isset($_GET['search']))
    {
        $search = trim($_GET['search']);
        $params['search'] = "%{$search}%";

        $query = 'SELECT * FROM movies WHERE CONCAT_WS(" ", title_br, title_us, release_year) LIKE :search';
    }
    else
    {
        $params = [];
        $query = 'SELECT * FROM movies';
    }
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$result['movies'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);