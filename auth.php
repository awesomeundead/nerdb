<?php

$params = [
    'openid.assoc_handle' => $_GET['openid_assoc_handle'],
    'openid.signed'       => $_GET['openid_signed'],
    'openid.sig'          => $_GET['openid_sig'],
    'openid.ns'           => 'http://specs.openid.net/auth/2.0',
    'openid.mode'         => 'check_authentication'
];

$signed_fields = explode(',', $_GET['openid_signed']);

foreach ($signed_fields as $field)
{
    $key = 'openid_' . str_replace('.', '_', $field);
    $params['openid.' . $field] = $_GET[$key];
}

$context = stream_context_create([
    'http' => [
        'method'  => 'POST',
        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
        'content' => http_build_query($params)
    ]
]);

$response = file_get_contents('https://steamcommunity.com/openid/login', false, $context);

if (preg_match('#is_valid\s*:\s*true#i', $response))
{
    preg_match('#^https://steamcommunity.com/openid/id/([0-9]{17,25})#', $_GET['openid_claimed_id'], $matches);
    $steamid64 = $matches[1] ?? null;

    //$response = file_get_contents("https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key={$steam_api_key}&steamids={$steamID64}");
    //return json_decode($response, true);

    require __DIR__ . '/session.php';

    $_SESSION['logged_in'] = true;
    $_SESSION['steamid'] = $steamid64;

    $protocol = !empty($_SERVER['HTTPS']) ? 'https' : 'http';
    $url = "{$protocol}://{$_SERVER['HTTP_HOST']}";
    $base_path = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');

    $response = file_get_contents("{$url}{$base_path}/api/v1/user?steamid={$steamid64}");

    if (!$response)
    {

    }

    header('movies.html?login=success');
}
else
{
    header('index.html?login=failure');
}
