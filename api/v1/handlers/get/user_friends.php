<?php

header('Content-Type: application/json; charset=utf-8');

$steam_api_key = (require ROOT_DIR . '/../../config.php')['steam_api_key'];

/*
$steamid = $_GET['steamid'] ?? false;

if (!$steamid)
{
    http_response_code(400);
    echo 'BAD REQUEST';
    exit;
}
*/

require ROOT_DIR . '/../../session.php';

$logged_in = $_SESSION['logged_in'] ?? false;

if(!$logged_in)
{
    http_response_code(401);
    echo 'UNAUTHORIZED';
    exit;
}

$steamid  = $_SESSION['steamid'];

/*
 * Pode ocorrer um erro se o steamid estiver errado
 */
$response = file_get_contents("https://api.steampowered.com/ISteamUser/GetFriendList/v1/?key={$steam_api_key}&steamid={$steamid}&relationship=friend");
$data = json_decode($response, true);
$list = $data['friendslist']['friends'] ?? false;

if (!$list)
{
    echo '{friends: []}';
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