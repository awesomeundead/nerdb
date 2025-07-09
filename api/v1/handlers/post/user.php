<?php

header('Content-Type: application/json; charset=utf-8');

require ROOT_DIR . '/pdo.php';

$content = trim(file_get_contents('php://input'));
$dados = json_decode($content, true);

$steamid = trim($dados['steamid']);
$personaname = trim($dados['personaname']);
$avatarhash = trim($dados['avatarhash']);

$params = [
    'steamid' => $steamid,
    'personaname' => $personaname,
    'avatarhash' => $avatarhash,
    'created_date' => 'NOW()'
];

$query = 'INSERT INTO users (steamid, personaname, avatarhash, created_date) VALUES (:steamid, :personaname, :avatarhash, :created_date)';

$stmt = $pdo->prepare($query);
$result = $stmt->execute($params);

$json['status'] = $result ? 'success' : 'failure';

echo json_encode($json);