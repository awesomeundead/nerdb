<?php

header('Content-Type: application/json; charset=utf-8');

require ROOT_DIR . '/pdo.php';

$content = trim(file_get_contents('php://input'));
$dados = json_decode($content, true);

$steamid = trim($dados['steamid']);
$personaname = trim($dados['personaname']);
$avatarhash = trim($dados['avatarhash']);
$realname = trim($dados['realname']);

$params = [
    'steamid' => $steamid,
    'personaname' => $personaname,
    'avatarhash' => $avatarhash,
    'name' => $realname
];

$query = 'INSERT INTO users (steamid, personaname, avatarhash, name, created_date) VALUES (:steamid, :personaname, :avatarhash, :name, NOW())';

$stmt = $pdo->prepare($query);
$result = $stmt->execute($params);

if ($result)
{
    $query = 'INSERT INTO score (add_movie, add_game, update_movie, update_game, rating_movie, rating_game) VALUES (0, 0, 0, 0, 0, 0)';

    $stmt = $pdo->prepare($query);
    $result = $stmt->execute();
}

$json['status'] = $result ? 'success' : 'failure';

echo json_encode($json);