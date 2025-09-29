<?php

class SteamAuth
{
    public function getAuthUrl(): string
    {
        $login_url_params = [
            'openid.ns'         => 'http://specs.openid.net/auth/2.0',
            'openid.mode'       => 'checkid_setup',
            'openid.return_to'  => HOST . BASE_PATH . '/auth?redirect=' . $_GET['redirect'],
            'openid.realm'      => HOST . BASE_PATH,
            'openid.identity'   => 'http://specs.openid.net/auth/2.0/identifier_select',
            'openid.claimed_id' => 'http://specs.openid.net/auth/2.0/identifier_select',
        ];

        return 'https://steamcommunity.com/openid/login' . '?' . http_build_query($login_url_params, '', '&');
    }

    public function getSteamUser(string $steamid)
    {
        $steam_api_key = (require ROOT_DIR . '/config.php')['steam_api_key'];

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

    public function isOpenIDAuthenticated()
    {
        return ($_GET['openid_mode'] ?? null) == 'id_res';
    }

    public function validate()
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
        }

        return $steamid ?? null;
    }
}