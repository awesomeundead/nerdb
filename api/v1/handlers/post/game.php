<?php

header('Content-Type: application/json; charset=utf-8');



$logged_in = Session::get('logged_in');

if(!$logged_in)
{
    http_response_code(401);
    echo 'UNAUTHORIZED';
    exit;
}

require ROOT_DIR . '/pdo.php';

$content = trim(file_get_contents('php://input'));
$dados = json_decode($content, true);

$user_id = $_SESSION['user_id'];
$title = trim($dados['title']);
$developer = trim($dados['developer']);
$genres = trim($dados['genres']);
$release_year = trim($dados['release_year']);
$steam = trim($dados['steam']);

$params = [
    'title' => $title,
    'release_year' => $release_year
];

$query = 'SELECT id FROM games WHERE title = :title AND release_year = :release_year';

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
        'title' => $title,
        'developer' => $developer,
        'genres' => $genres,
        'release_year' => $release_year,
        'steam' => $steam,
        'first_user_id' => $user_id,
        'last_user_id' => $user_id
    ];

    $query = 'INSERT INTO games (title, developer, genres, release_year, steam, first_user_id, last_user_id)
              VALUES (:title, :developer, :genres, :release_year, :steam, :first_user_id, :last_user_id)';

    $stmt = $pdo->prepare($query);
    $result = $stmt->execute($params);

    $query = 'UPDATE score SET add_game = add_game + 1 WHERE id = :id';
    $stmt = $pdo->prepare($query);
    $stmt->execute(['id' => $user_id]);
}

$json['status'] = $result ? 'success' : 'failure';

echo json_encode($json);