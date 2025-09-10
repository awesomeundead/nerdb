<?php

header('Content-Type: application/json; charset=utf-8');

$pdo = Database::connect();

$params = [];
$query = 'SELECT * FROM games';

$developer = $_GET['developer'] ?? null;
$genre = $_GET['genre'] ?? null;
$release = $_GET['release'] ?? null;
$search = $_GET['search'] ?? null;

$conditions = [];

if (!empty($developer))
{
    $conditions[] = 'developer LIKE :developer';
    $params['developer'] = "%{$developer}%";
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
    $conditions[] = 'MATCH(title, developer) AGAINST(:search)';
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
$result['games'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);