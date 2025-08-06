<?php

header('Content-Type: application/json; charset=utf-8');

require ROOT_DIR . '/pdo.php';

$params = [];

if (isset($_GET['release']) && preg_match('/^\d{4}$/', $_GET['release']))
{
    $params['release_year'] = $_GET['release'];

    $query = 'SELECT * FROM movies WHERE release_year = :release_year';
}
elseif (isset($_GET['actor']))
{
    $actor = trim($_GET['actor']);
    $params['actor'] = "%{$actor}%";

    $query = 'SELECT * FROM movies WHERE cast LIKE :actor ORDER BY release_year DESC';
}
elseif (isset($_GET['director']))
{
    $director = trim($_GET['director']);
    $params['director'] = "%{$director}%";

    $query = 'SELECT * FROM movies WHERE director LIKE :director ORDER BY release_year DESC';
}
elseif (isset($_GET['genre']))
{
    $genre = trim($_GET['genre']);
    $params['genre'] = "%{$genre}%";

    $query = 'SELECT * FROM movies WHERE genres LIKE :genre ORDER BY release_year DESC';
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
    elseif (isset($_GET['offset']) && is_numeric($_GET['offset']))
    {
        $offset = $_GET['offset'];
        $query = "SELECT * FROM movies LIMIT {$offset}, 100";
    }
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$result['movies'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);