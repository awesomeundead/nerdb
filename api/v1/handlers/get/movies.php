<?php

header('Content-Type: application/json; charset=utf-8');

$pdo = Database::connect();

$params = [];
$query = 'SELECT * FROM movies';

$actor = $_GET['actor'] ?? null;
$director = $_GET['director'] ?? null;
$genre = $_GET['genre'] ?? null;
$release = $_GET['release'] ?? null;
$search = $_GET['search'] ?? null;

$conditions = [];

if (!empty($actor))
{
    $query = 'SELECT movies.* FROM movies
              INNER JOIN movies_cast ON movies_cast.movie_id = movies.id
              INNER JOIN people ON people.id = movies_cast.person_id';

    $conditions[] = 'people.name = :actor';
    $params['actor'] = $actor;
}

if (!empty($director))
{
    $conditions[] = 'director LIKE :director';
    $params['director'] = "%{$director}%";
}

if (!empty($genre))
{
    $conditions[] = 'genres LIKE :genre';
    $params['genre'] = "%{$genre}%";
}

if (!empty($release) && preg_match('/^\d{4}$/', $release))
{
    $conditions[] = 'release_year = :release';
    $params['release'] = $release;
}

if (!empty($search))
{
    $conditions[] = 'MATCH(title_br, title_us, director) AGAINST(:search)';
    $params['search'] = $search;
}

if (!empty($conditions))
{
    $query .= ' WHERE ' . implode(' AND ', $conditions);
}
else
{
    if (isset($_GET['order']) && $_GET['order'] == 'random')
    {
        $query .= ' WHERE media != "" ORDER BY rand() LIMIT 20';
    }
    elseif (isset($_GET['offset']) && is_numeric($_GET['offset']))
    {
        $offset = $_GET['offset'];
        $query .= " LIMIT {$offset}, 100";
    }
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$result['movies'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);