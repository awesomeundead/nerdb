<?php

header('Content-Type: application/json; charset=utf-8');

$pdo = Database::connect();
$loggedIn = Session::get('logged_in');
$userId = Session::get('user_id');
$movieId = $vars['id'];

$service = new MovieService($pdo, $loggedIn, $userId);
$result = $service->getMovieDetails($movieId);

echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);