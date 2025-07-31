<?php

$protocol = !empty($_SERVER['HTTPS']) ? 'https' : 'http';

define('HOST', "{$protocol}://{$_SERVER['HTTP_HOST']}");

function auto_login()
{
    $token = htmlspecialchars($_COOKIE['login']);
    $parts = explode(':', $token);

    if ($parts && count($parts) == 2)
    {
        [$selector, $validator] = $parts;
    }

    $response = file_get_contents(HOST . BASE_PATH . "/api/v1/login?selector={$selector}");
    $login = json_decode($response, true);

    if (password_verify($validator, $login['hashed_validator']))
    {
        if ($login['expire_date'] > date('Y-m-d H:i:s'))
        {
            /*
             *  verifica se o usuário existe
             */
            $response = file_get_contents(HOST . BASE_PATH . "/api/v1/user/{$login['user_id']}");

            if ($response)
            {
                $data = json_decode($response, true);

                $steamid64 = $data['steamid'];

                create_session($data);

                $player = get_steam_user($steamid64);                       

                /*
                * atualiza usuário
                */
                $response = update_user($player);

                redirect('/movielist');
            }
        }
    }
}

function create_auto_login($user_id)
{
    $address = $_SERVER['HTTP_CLIENT_IP']
            ?? $_SERVER['HTTP_X_FORWARDED_FOR']
            ?? $_SERVER['HTTP_X_FORWARDED']
            ?? $_SERVER['HTTP_FORWARDED_FOR']
            ?? $_SERVER['HTTP_FORWARDED']
            ?? $_SERVER['REMOTE_ADDR']
            ?? 'UNKNOWN';

    $params = [
        'user_id' => $user_id,
        'address' => $address
    ];

    $jsonData = json_encode($params, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $context = stream_context_create([
        'http' => [
            'method'  => 'POST',
            'header'  => "Content-type: application/json\r\n" .
                          "Content-Length: " . strlen($jsonData) . "\r\n",
            'content' => $jsonData
        ]
    ]);
    $response = file_get_contents(HOST . BASE_PATH . '/api/v1/login', false, $context);

    if ($response)
    {
        $data = json_decode($response, true);

        setcookie('login', $data['token'], ['expires' => $data['expire_date'], 'path' => '/', 'httponly' => true]);
    }
}

function create_session($data)
{
    require __DIR__ . '/session.php';

    $_SESSION['logged_in'] = true;
    $_SESSION['user_id'] = $data['id'];
    $_SESSION['steamid'] = $data['steamid'];
    $_SESSION['personaname'] = $data['personaname'];
    $_SESSION['avatarhash'] = $data['avatarhash'];
}

function get_steam_user($steamid64)
{
    $steam_api_key = (require __DIR__ . '/config.php')['steam_api_key'];

    /*
     *  busca os dados do usuário steam
     */
    $context = stream_context_create(['http' => ['ignore_errors' => true]]);
    $response = file_get_contents("https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key={$steam_api_key}&steamids={$steamid64}", false, $context);
    $data = json_decode($response, true);
    $player = $data['response']['players'][0] ?? false;

    if (!$player)
    {
        http_response_code(500);
        echo 'INTERNAL SERVER ERROR';
        exit;
    }

    return $player;
}

function insert_user($player)
{
    $params = [
        'steamid'     => $player['steamid'],
        'personaname' => $player['personaname'],
        'avatarhash'  => $player['avatarhash'],
        'realname'    => $player['realname'] ?? ''
    ];

    $jsonData = json_encode($params, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $context = stream_context_create([
        'http' => [
            'method'  => 'POST',
            'header'  => "Content-type: application/json\r\n" .
                         "Content-Length: " . strlen($jsonData) . "\r\n",
            'content' => $jsonData
        ]
    ]);

    /*
     * insere novo usuário
     */
    return file_get_contents(HOST . BASE_PATH . '/api/v1/user', false, $context);
}

function update_user($player)
{
    $params = [
        'personaname' => $player['personaname'],
        'avatarhash'  => $player['avatarhash'],
        'realname'    => $player['realname'] ?? ''
    ];

    $jsonData = json_encode($params, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $context = stream_context_create([
        'http' => [
            'method'  => 'PUT',
            'header'  => "Content-type: application/json\r\n" .
                          "Content-Length: " . strlen($jsonData) . "\r\n",
            'content' => $jsonData
        ]
    ]);

    /*
     * atualiza usuário
     */
    return file_get_contents(HOST . BASE_PATH . '/api/v1/user', false, $context);
}

if (isset($_COOKIE['login']))
{
    auto_login();
}
elseif (isset($_GET['login']))
{
    $login_url_params = [
        'openid.ns'         => 'http://specs.openid.net/auth/2.0',
        'openid.mode'       => 'checkid_setup',
        'openid.return_to'  => HOST . BASE_PATH . '/auth.php',
        'openid.realm'      => HOST . BASE_PATH,
        'openid.identity'   => 'http://specs.openid.net/auth/2.0/identifier_select',
        'openid.claimed_id' => 'http://specs.openid.net/auth/2.0/identifier_select',
    ];

    $steam_login_url = 'https://steamcommunity.com/openid/login' . '?' . http_build_query($login_url_params, '', '&');

    header("location: {$steam_login_url}");
    exit;
}
elseif (isset($_GET['openid_signed']))
{
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

    $body = http_build_query($params);
    $context = stream_context_create([
        'http' => [
            'method'  => 'POST',
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n" .
                        'Content-Length: ' . strlen($body) . "\r\n",
            'content' => $body
        ]
    ]);

    $response = file_get_contents('https://steamcommunity.com/openid/login', false, $context);

    if (preg_match('#is_valid\s*:\s*true#i', $response))
    {
        preg_match('#^https://steamcommunity.com/openid/id/([0-9]{17,25})#', $_GET['openid_claimed_id'], $matches);
        $steamid64 = $matches[1] ?? null;

        $player = get_steam_user($steamid64);

        /*
        *  verifica se o usuário existe
        */
        $response = file_get_contents(HOST . BASE_PATH . "/api/v1/user?steamid={$steamid64}");

        if ($response == 'false')
        {
            /*
            * insere novo usuário
            */
            $response = insert_user($player);

            $data = json_decode($response, true);
            $status = $data['status'] ?? false;

            if ($status)
            {
                $response = file_get_contents(HOST . BASE_PATH . "/api/v1/user?steamid={$steamid64}");
            }
        }

        $data = json_decode($response, true);

        create_session($data);
        create_auto_login($data['id']);

        redirect('/movielist?login=success');
    }
}

redirect('/login?login=failure');