<?php

header('Content-Type: application/json; charset=utf-8');

$pdo = Database::connect();
$loggedIn = Session::get('logged_in');
$userId = Session::get('user_id');
$gameId = $vars['id'];

$service = new GameService($pdo, $loggedIn, $userId);
$result = $service->getGameDetails($gameId);

echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);