<?php

return [
    function ($vars, $next)
    {
        $loggedIn = Session::get('logged_in');

        if (!$loggedIn && isset($_COOKIE['login']))
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

                        $service->createSession($data);

                        $auth = new SteamAuth();
                        $player = $auth->getSteamUser($steamid);

                        /* atualiza usuário */
                        $service->updateUser($player);
                        $service->updateUserFriendship($data['id'], $steamid);
                    }
                }
            }
        }

        return $next($vars);
    }
];