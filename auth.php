<?php

function auto_login()
{
    $token = htmlspecialchars($_COOKIE['login']);
    $parts = explode(':', $token);

    if ($parts && count($parts) == 2)
    {
        [$selector, $validator] = $parts;
    }

    $pdo = Database::connect();
    $service = new LoginService($pdo);
    $login = $service->getLog($selector);

    if (password_verify($validator, $login['hashed_validator']))
    {
        if ($login['expire_date'] > date('Y-m-d H:i:s'))
        {
            $service = new UserService($pdo);

            /* verifica se o usuário existe */
            $data = $service->getUserById($login['user_id']);

            if (!empty($data))
            {
                $steamid = $data['steamid'];

                create_session($data);

                $player = get_steam_user($steamid);

                /* atualiza usuário */
                $service->updateUser($player);
                $service->updateUserFriendship($data['id'], $steamid);

                if (isset($_GET['redirect']))
                {
                    redirect($_GET['redirect']);
                }

                redirect('/');
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

    $pdo = Database::connect();
    $service = new LoginService($pdo);
    $data = $service->addLog($user_id, $address);

    if ($data)
    {
        setcookie('login', $data['token'], ['expires' => $data['expire_date'], 'path' => '/', 'httponly' => true]);
    }
}

function create_session($data)
{
    Session::set('logged_in', true);
    Session::set('user_id', $data['id']);
    Session::set('steamid', $data['steamid']);
    Session::set('personaname', $data['personaname']);
    Session::set('avatarhash', $data['avatarhash']);
}

function get_steam_user($steamid)
{
    $steam_api_key = (require __DIR__ . '/config.php')['steam_api_key'];

    /*
     *  busca os dados do usuário steam
     */
    $context = stream_context_create(['http' => ['ignore_errors' => true]]);
    $response = file_get_contents("https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key={$steam_api_key}&steamids={$steamid}", false, $context);
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

if (isset($_COOKIE['login']))
{
    auto_login();
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
        $steamid = $matches[1] ?? null;

        $player = get_steam_user($steamid);

        /* verifica se o usuário existe */
        
        $pdo = Database::connect();
        $service = new UserService($pdo);
        $data = $service->getUserBySteam($steamid);

        if (empty($data))
        {
            /* insere novo usuário */
            $user_id = $service->addNewUser($player);

            if (!$user_id)
            {
                redirect('/?error');
            }

            $data = [
                'id'          => $user_id,
                'steamid'     => $player['steamid'],
                'personaname' => $player['personaname'],
                'avatarhash'  => $player['avatarhash']
            ];
        }
        else
        {
            /* atualiza usuário */
            $service->updateUser($player);
        }

        create_session($data);
        create_auto_login($data['id']);
        $service->updateUserFriendship($data['id'], $steamid);

        if (isset($_GET['redirect']))
        {
            redirect($_GET['redirect']);
        }

        redirect('/');
    }
}
else
{
    $login_url_params = [
        'openid.ns'         => 'http://specs.openid.net/auth/2.0',
        'openid.mode'       => 'checkid_setup',
        'openid.return_to'  => HOST . BASE_PATH . '/auth?redirect=' . $_GET['redirect'],
        'openid.realm'      => HOST . BASE_PATH,
        'openid.identity'   => 'http://specs.openid.net/auth/2.0/identifier_select',
        'openid.claimed_id' => 'http://specs.openid.net/auth/2.0/identifier_select',
    ];

    $steam_login_url = 'https://steamcommunity.com/openid/login' . '?' . http_build_query($login_url_params, '', '&');

    header("location: {$steam_login_url}");
    exit;
}

redirect('/login?login=failure');