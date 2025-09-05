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

function authMiddleware($handler)
{
    return function($vars) use ($handler)
    {
        $logged_in = Session::get('logged_in');

        if (!$logged_in)
        {
            $uri = substr_replace(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '', 0, strlen(BASE_PATH));

            redirect("auth?redirect={$uri}");
        }

        $handler($vars);
    };
};

function templates(): Engine
{
    $engine = new Engine(ROOT . '/templates');
    $engine->setFileExtension(null);
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

    $session = (object) [
        'logged_in'   => Session::get('logged_in'),
        'steamid'     => Session::get('steamid'),
        'personaname' => Session::get('personaname'),
        'avatarhash'  => Session::get('avatarhash')
    ];

    $engine->addData(['session' => $session]);

    return $engine;
};

return function(RouteCollector $route)
{
    $route->get('/', function()
    {
        echo templates()->render('index.php');
    });
    
    $route->get('/achievements', authMiddleware(function()
    {
        $template = templates()->make('achievements.html');
        $template->layout('layouts/default.php', ['title' => 'Minhas conquistas']);

        echo $template->render();
    }));

    $route->get('/auth', 'auth.php');

    $route->get('/friends', authMiddleware(function()
    {
        $template = templates()->make('friends.html');
        $template->layout('layouts/default.php', ['title' => 'Meus amigos']);

        echo $template->render();
    }));

    $route->get('/friends/gamelist/{id:\d+}', authMiddleware(function()
    {
        $template = templates()->make('friends_gamelist.html');
        $template->layout('layouts/default.php', ['title' => 'Amigos - Lista de jogos']);

        echo $template->render();
    }));

    $route->get('/friends/achievements/{id:\d+}', authMiddleware(function()
    {
        $template = templates()->make('friends_achievements.html');
        $template->layout('layouts/default.php', ['title' => 'Amigos - Conquistas']);

        echo $template->render();
    }));

    $route->get('/friends/movielist/{id:\d+}', authMiddleware(function()
    {
        $template = templates()->make('friends_movielist.html');
        $template->layout('layouts/default.php', ['title' => 'Amigos - Lista de filmes']);

        echo $template->render();
    }));

    $route->get('/game/{id:\d+}', function()
    {
        $template = templates()->make('game.php');
        $template->layout('layouts/default.php');

        echo $template->render();
    });

    $route->get('/game/add', authMiddleware(function()
    {
        $template = templates()->make('game_add.html');
        $template->layout('layouts/default.php', ['title' => 'Adicionar jogo']);

        echo $template->render();
    }));

    $route->get('/game/update/{id:\d+}', authMiddleware(function()
    {
        $template = templates()->make('game_update.html');
        $template->layout('layouts/default.php', ['title' => 'Atualizar jogo']);

        echo $template->render();
    }));

    $route->get('/games', function()
    {
        $template = templates()->make('games.html');
        $template->layout('layouts/default.php', ['title' => 'Melhores jogos avaliados por usuários do site']);

        echo $template->render();
    });

    $route->get('/login', function()
    {
        $logged_in = Session::get('logged_in');

        if (!$logged_in)
        {
            redirect('/');
        }

        $template = templates()->make('login.html');
        $template->layout('layouts/default.php', ['title' => 'Login']);

        echo $template->render();
    });

    $route->get('/logout', authMiddleware(function()
    {
        if (isset($_COOKIE['login']))
        {
            setcookie('login', '', -1, '/');
        }

        session_unset();
        session_destroy();

        redirect('/?logout');
    }));

    $route->get('/movie/{id:\d+}', function()
    {
        $template = templates()->make('movie.php');
        $template->layout('layouts/default.php');

        echo $template->render();
    });

    $route->get('/movie/add', authMiddleware(function()
    {
        $template = templates()->make('movie_add.html');
        $template->layout('layouts/default.php', ['title' => 'Adicionar filme']);

        echo $template->render();
    }));

    $route->get('/movie/update/{id:\d+}', authMiddleware(function()
    {
        $template = templates()->make('movie_update.html');
        $template->layout('layouts/default.php', ['title' => 'Atualizar filme']);

        echo $template->render();
    }));

    $route->get('/movielist', function()
    {
        $template = templates()->make('movielist.html');
        $template->layout('layouts/default.php', ['title' => 'Lista de filmes']);

        echo $template->render();
    });

    $route->get('/movies', function()
    {
        $template = templates()->make('movies.html');
        $template->layout('layouts/default.php', ['title' => 'Melhores filmes avaliados por usuários do site']);

        echo $template->render();
    });

    $route->get('/movies/top', function()
    {
        $template = templates()->make('movies_top.html');
        $template->layout('layouts/default.php', ['title' => 'Os 100 melhores filmes']);

        echo $template->render();
    });

    $route->get('/mygamelist', authMiddleware(function()
    {
        $template = templates()->make('mygamelist.html');
        $template->layout('layouts/default.php', ['title' => 'Minha lista de filmes']);

        echo $template->render();
    }));

    $route->get('/mymovielist', authMiddleware(function()
    {
        $template = templates()->make('mymovielist.html');
        $template->layout('layouts/default.php', ['title' => 'Minha lista de filmes']);

        echo $template->render();
    }));    

    $route->get('/games/search', function()
    {
        $template = templates()->make('games_search.html');
        $template->layout('layouts/default.php', ['title' => 'Jogos']);

        echo $template->render();
    });

    $route->get('/movies/search', function()
    {
        $template = templates()->make('movies_search.html');
        $template->layout('layouts/default.php', ['title' => 'Filmes']);

        echo $template->render();
    });

    $route->get('/store', function()
    {
        $template = templates()->make('store.html');
        $template->layout('layouts/default.php', ['title' => 'Loja']);

        echo $template->render();
    });
};