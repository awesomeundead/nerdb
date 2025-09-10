<?php

header('Content-Type: application/json; charset=utf-8');

$steam_api_key = (require ROOT_DIR . '/config.php')['steam_api_key'];

$user_id = $vars['id'] ?? false;
$steamid = $_GET['steamid'] ?? false;

if (!$user_id && !$steamid)
{
    http_response_code(400);
    echo 'BAD REQUEST';
    exit;
}

/*
 * Pode ocorrer um erro se o steamid estiver errado ou se a lista de amigos não estiver pública.
 */
$context = stream_context_create(['http' => ['ignore_errors' => true]]);
$response = file_get_contents("https://api.steampowered.com/ISteamUser/GetFriendList/v1/?key={$steam_api_key}&steamid={$steamid}&relationship=friend", false, $context);
$data = json_decode($response, true);
$list = $data['friendslist']['friends'] ?? false;

if (!$list)
{
    //echo '{"friends": []}';
    exit;
}

foreach ($list as $index => $item)
{
    $key = ":placeholder_{$index}";
    $keys[] = $key;
    $params[$key] = $item['steamid'];
}

$placeholders = implode(', ', $keys);

$pdo = Database::connect();

$query = "SELECT id FROM users WHERE steamid IN ({$placeholders})";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$list = $stmt->fetchAll(PDO::FETCH_COLUMN);

if (empty($list))
{
    //echo '{"friends": []}';
    exit;
}

foreach ($list as  $friend)
{
    $params = [
        'me' => $user_id,
        'friend' => $friend
    ];
    $query = "SELECT 1 FROM friendship
              WHERE (user_id1 = :me AND user_id2 = :friend) OR (user_id1 = :friend AND user_id2 = :me) LIMIT 1";
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);

    if ($stmt->fetchColumn() === false)
    {
        $query = 'INSERT INTO friendship (user_id1, user_id2) VALUES (LEAST(:me, :friend), GREATEST(:me, :friend))';
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
    }
}