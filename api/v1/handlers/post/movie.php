<?php

header('Content-Type: application/json; charset=utf-8');

require ROOT_DIR . '/pdo.php';

$content = trim(file_get_contents('php://input'));
$dados = json_decode($content, true);

$title_br = trim($dados['title_br']);
$title_us = trim($dados['title_us']);
$release_year = trim($dados['release_year']);
$imdb = trim($dados['imdb']);

$params = [
    'title_br' => $title_br,
    'title_us' => $title_us,
    'release_year' => $release_year,
    'imdb' => $imdb
];

$query = 'INSERT INTO movies (title_br, title_us, release_year, imdb) VALUES (:title_br, :title_us, :release_year, :imdb)';

$stmt = $pdo->prepare($query);
$result = $stmt->execute($params);

$json['status'] = $result ? 'success' : 'failure';

echo json_encode($json);