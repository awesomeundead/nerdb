<?php

header('Content-Type: application/json; charset=utf-8');

require ROOT_DIR . '/../../session.php';

$logged_in = $_SESSION['logged_in'] ?? false;

if(!$logged_in)
{
    http_response_code(401);
    echo 'UNAUTHORIZED';
    exit;
}

require ROOT_DIR . '/pdo.php';

$content = trim(file_get_contents('php://input'));
$dados = json_decode($content, true);

$params = [
    'steamid' => $_SESSION['steamid']
];
$query = 'SELECT id FROM users WHERE steamid = :steamid';

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$user_id = $stmt->fetchColumn();

$title_br = trim($dados['title_br']);
$title_us = trim($dados['title_us']);
$release_year = trim($dados['release_year']);
$imdb = trim($dados['imdb']);

$params = [
    'title_br' => $title_br,
    'title_us' => $title_us,
    'release_year' => $release_year,
    'imdb' => $imdb,
    'first_user_id' => $user_id,
    'last_user_id' => $user_id
];

$query = 'INSERT INTO movies (title_br, title_us, release_year, imdb, first_user_id, last_user_id)
          VALUES (:title_br, :title_us, :release_year, :imdb, :first_user_id, :last_user_id)';

$stmt = $pdo->prepare($query);
$result = $stmt->execute($params);

$json['status'] = $result ? 'success' : 'failure';

echo json_encode($json);