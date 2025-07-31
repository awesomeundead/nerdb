<?php

use FastRoute\RouteCollector;
use League\Plates\Engine;
use League\Plates\Extension\Asset;
use League\Plates\Extension\URI;

function redirect($path)
{
    header('location: ' . BASE_PATH . '/' . ltrim($path, '/'));

    exit;
}

return function(RouteCollector $route)
{
    require ROOT . '/session.php';

    $session = (object) [
        'logged_in'   => $_SESSION['logged_in'] ?? false,
        'steamid'     => $_SESSION['steamid'] ?? null,
        'personaname' => $_SESSION['personaname'] ?? null,
        'avatarhash'  => $_SESSION['avatarhash'] ?? null
    ];

    $check_login = function() use ($session)
    {
        if (!$session->logged_in)
        {
            redirect('login');
        }
    };

    $templates = function() use ($session)
    {
        $engine = new Engine(ROOT . '/templates');
        $engine->loadExtension(new Asset(ROOT . '/public'));
        $engine->loadExtension(new URI(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)));

        $engine->registerFunction('base', function($path = null)
        {
            if (isset($path))
            {
                $path = '/' . ltrim($path, '/');
            }
            
            return BASE_PATH . $path;
        });

        $engine->addData(['session' => $session]);

        return $engine;
    };

    $route->get('/', function() use ($templates)
    {
        echo $templates()->render('index');
    });

    $route->get('/auth', 'auth.php');

    $route->get('/friends', function() use ($check_login, $templates)
    {
        $check_login();

        $template = $templates()->make('friends');
        $template->layout('layouts/default', ['title' => 'Meus amigos']);

        echo $template->render();
    });

    $route->get('/friends/movielist/{id:\d+}', function($vars) use ($check_login, $templates)
    {
        $check_login();

        $template = $templates()->make('friends_movielist');
        $template->layout('layouts/default', ['title' => 'Amigos - Lista de filmes']);

        echo $template->render(['friend_id' => $vars['id']]);
    });

    $route->get('/login', function() use ($session, $templates)
    {
        if ($session->logged_in)
        {
            redirect('/');
        }

        $template = $templates()->make('login');
        $template->layout('layouts/default', ['title' => 'Login']);

        echo $template->render();
    });

    $route->get('/movielist', function() use ($check_login, $templates)
    {
        $check_login();

        $template = $templates()->make('movielist');
        $template->layout('layouts/default', ['title' => 'Minha lista de filmes']);

        echo $template->render();
    });

    $route->get('/movie/{id:\d+}', function($vars) use ($templates)
    {
        $template = $templates()->make('movie');
        $template->layout('layouts/default');

        echo $template->render(['movie_id' => $vars['id']]);
    });

    $route->get('/movie/add', function() use ($check_login, $templates)
    {
        $check_login();

        $template = $templates()->make('movie_add');
        $template->layout('layouts/default', ['title' => 'Adicionar filme']);

        echo $template->render();
    });

    $route->get('/movie/update/{id:\d+}', function($vars) use ($check_login, $templates)
    {
        $check_login();

        $template = $templates()->make('movie_update');
        $template->layout('layouts/default', ['title' => 'Atualizar filme']);

        echo $template->render(['movie_id' => $vars['id']]);
    });

    $route->get('/movies', function() use ($templates)
    {
        $template = $templates()->make('movies');
        $template->layout('layouts/default');

        echo $template->render();
    });

    $route->get('/movies/top', function() use ($templates)
    {
        $template = $templates()->make('movies_top');
        $template->layout('layouts/default', ['title' => 'Os 100 melhores filmes']);

        echo $template->render();
    });
};