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
$game_id = $vars['id'];
$title = trim($dados['title']);
$developer = trim($dados['developer']);
$genres = trim($dados['genres']);
$release_year = trim($dados['release_year']);
$steam = trim($dados['steam']);

$params = [
    'id' => $game_id,
    'title' => $title,
    'release_year' => $release_year
];

$query = 'SELECT id FROM games WHERE id != :id AND title = :title AND release_year = :release_year';

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$result = !$stmt->fetchColumn();

if ($result)
{
    if (preg_match('#/app/(\d+)/#', $steam, $matches))
    {
        $steam = $matches[1];
    }

    $params = [
        'id' => $game_id,
        'title' => $title,
        'developer' => $developer,
        'genres' => $genres,
        'release_year' => $release_year,
        'steam' => $steam,
        'last_user_id' => $user_id
    ];

    $query = 'UPDATE games SET
            title = :title,
            developer = :developer,
            genres = :genres,
            release_year = :release_year,
            steam = :steam,
            last_user_id = :last_user_id
            WHERE id = :id';

    $stmt = $pdo->prepare($query);
    $result = $stmt->execute($params);

    $query = 'UPDATE score SET update_game = update_game + 1 WHERE id = :id';
    $stmt = $pdo->prepare($query);
    $stmt->execute(['id' => $user_id]);
}

$json['status'] = $result ? 'success' : 'failure';

echo json_encode($json);