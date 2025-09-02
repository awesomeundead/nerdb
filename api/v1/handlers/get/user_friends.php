<?php

header('Content-Type: application/json; charset=utf-8');

$steam_api_key = (require ROOT_DIR . '/../../config.php')['steam_api_key'];



$logged_in = Session::get('logged_in');

if(!$logged_in)
{
    http_response_code(401);
    echo 'UNAUTHORIZED';
    exit;
}

$steamid  = $_SESSION['steamid'];

/*
 * Pode ocorrer um erro se o steamid estiver errado ou se a lista de amigos não estiver pública.
 */
$context = stream_context_create(['http' => ['ignore_errors' => true]]);
$response = file_get_contents("https://api.steampowered.com/ISteamUser/GetFriendList/v1/?key={$steam_api_key}&steamid={$steamid}&relationship=friend", false, $context);
$data = json_decode($response, true);
$list = $data['friendslist']['friends'] ?? false;

if (!$list)
{
    echo '{"friends": []}';
    exit;
}

foreach ($list as $index => $item)
{
    $key = ":placeholder_{$index}";
    $keys[] = $key;
    $params[$key] = $item['steamid'];
}

require ROOT_DIR . '/pdo.php';

$placeholders = implode(', ', $keys);

$query = "SELECT * FROM users WHERE steamid IN ({$placeholders})";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$result['friends'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);