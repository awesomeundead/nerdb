<?php

header('Content-Type: application/json; charset=utf-8');

require ROOT_DIR . '/pdo.php';

$content = trim(file_get_contents('php://input'));
$dados = json_decode($content, true);

$id = $vars['id'];

$title_br = trim($dados['title_br']);
$title_us = trim($dados['title_us']);
$release_year = trim($dados['release_year']);
$imdb = trim($dados['imdb']);

$params = [
    'id' => $id,
    'title_br' => $title_br,
    'title_us' => $title_us,
    'release_year' => $release_year,
    'imdb' => $imdb
];

$query = 'UPDATE movies SET
          title_br = :title_br,
          title_us = :title_us,
          release_year = :release_year,
          imdb = :imdb
          WHERE id = :id';

$stmt = $pdo->prepare($query);
$result = $stmt->execute($params);

$json['status'] = $result ? 'success' : 'failure';

echo json_encode($json);