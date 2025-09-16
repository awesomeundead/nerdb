<?php

header('Content-Type: application/json; charset=utf-8');

$logged_in = Session::get('logged_in');

if(!$logged_in)
{
    http_response_code(401);
    echo 'UNAUTHORIZED';
    exit;
}

$pdo = Database::connect();

$content = trim(file_get_contents('php://input'));
$dados = json_decode($content, true);

$user_id = $_SESSION['user_id'];
$movie_id= $vars['id'];
$title_br = trim($dados['title_br']);
$title_us = trim($dados['title_us']);
$director = trim($dados['director']);
$genres = trim($dados['genres']);
$release_year = trim($dados['release_year']);
$imdb = trim($dados['imdb']);
$title_url = remove_accents($dados['title_br']);

$params = [
    'id' => $movie_id,
    'title_br' => $title_br,
    'release_year' => $release_year
];

$query = 'SELECT id FROM movies WHERE id != :id AND title_br = :title_br AND release_year = :release_year';

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$result = !$stmt->fetchColumn();

if ($result)
{
    if (preg_match('#/title/(tt\d+)/#', $imdb, $matches))
    {
        $imdb = $matches[1];
    }

    $params = [
        'id' => $movie_id,
        'title_br' => $title_br,
        'title_us' => $title_us,
        'director' => $director,
        'genres' => $genres,
        'release_year' => $release_year,
        'imdb' => $imdb,
        'title_url' => $title_url,
        'last_user_id' => $user_id
    ];

    $query = 'UPDATE movies SET
            title_br = :title_br,
            title_us = :title_us,
            director = :director,
            genres = :genres,
            release_year = :release_year,
            imdb = :imdb,
            title_url = :title_url,
            last_user_id = :last_user_id
            WHERE id = :id';

    $stmt = $pdo->prepare($query);
    $result = $stmt->execute($params);

    $query = 'UPDATE score SET update_movie = update_movie + 1 WHERE id = :id';
    $stmt = $pdo->prepare($query);
    $stmt->execute(['id' => $user_id]);
}

$json['status'] = $result ? 'success' : 'failure';

echo json_encode($json);