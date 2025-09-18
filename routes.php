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

function jsonMiddleware($handler)
{
    return function($vars) use ($handler)
    {
        header('Content-Type: application/json; charset=utf-8');

        $result = $handler($vars);

        echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    };
}

function templates(array $data = []): Engine
{
    $engine = new Engine(ROOT_DIR . '/templates');
    $engine->setFileExtension(null);
    $engine->loadExtension(new Asset(ROOT_DIR . '/public'));
    $engine->loadExtension(new URI(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)));

    $engine->registerFunction('base', function($path = null)
    {
        if (isset($path))
        {
            $path = '/' . ltrim($path, '/');
        }
        
        return BASE_PATH . $path;
    });

    $data['open_graph']['image'] ??= HOST . BASE_PATH . '/nerdb_logo_social.png';
    $data['open_graph']['title'] ??= 'NERDB';
    $data['open_graph']['description'] ??= 'Plataforma em desenvolvimento com conteúdos sobre entretenimento, tecnologia e tendências. Acompanhe novidades e atualizações.';

    $engine->addData($data);

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
        $template = templates()->make('index.html');
        $template->layout('layouts/default.php');

        echo $template->render();
    });

    $route->get('/test', function()
    {
        $pdo = Database::connect();
        $loggedIn = Session::get('logged_in');
        $userId = Session::get('user_id');

        $service = new GameRepository($pdo);
        $result = $service->getGameId('Half-Life 2', '2004');

        var_dump($result);
    });
    
    $route->get('/achievements', authMiddleware(function()
    {
        $template = templates()->make('achievements.html');
        $template->layout('layouts/default.php', ['title' => 'Minhas conquistas']);

        echo $template->render();
    }));

    $route->get('/auth', function()
    {
        header('X-Robots-Tag: noindex');

        require 'auth.php';
    });

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

    $route->get('/game/{id:\d+}[/{title}]', function($vars)
    {
        $pdo = Database::connect();
        $userId = Session::get('user_id');
        $gameId = $vars['id'];

        $service = new GameRepository($pdo);
        $result = $service->getGameDetails($gameId, $userId);

        $result['developer'] = explode(';', $result['developer']);
        $result['genres'] = explode(';', $result['genres']);

        $data['open_graph']['image'] = HOST . BASE_PATH . "/images/games/512/{$result['media']}.webp";
        $data['open_graph']['title'] = $result['title'];

        $template = templates($data)->make('game.php', ['game' => $result]);
        $template->layout('layouts/default.php', ['title' => $result['title']]);

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
        $pdo = Database::connect();
        $service = new GameRepository($pdo);
        $result = $service->getTopRatedGames(90);

        $title = 'Melhores jogos avaliados por usuários do site';
        $data['open_graph']['title'] = $title;

        $template = templates($data)->make('games.php', ['games' => $result]);
        $template->layout('layouts/default.php', ['title' => $title]);

        echo $template->render();
    });

    $route->get('/games/search', function()
    {
        header('X-Robots-Tag: noindex');

        $template = templates()->make('games_search.html');
        $template->layout('layouts/default.php', ['title' => 'Jogos']);

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

    $route->get('/movie/{id:\d+}[/{title}]', function($vars)
    {
        $pdo = Database::connect();
        $userId = Session::get('user_id');
        $movieId = $vars['id'];

        $service = new MovieRepository($pdo);
        $result = $service->getMovieDetails($movieId, $userId);

        $result['director'] = explode(';', $result['director']);
        $result['genres'] = explode(';', $result['genres']);

        $data['open_graph']['image'] = HOST . BASE_PATH . "/images/512/{$result['media']}.webp";
        $data['open_graph']['title'] = $result['title_br'];

        $template = templates($data)->make('movie.php', ['movie' => $result]);
        $template->layout('layouts/default.php', ['title' => $result['title_br']]);

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
        $pdo = Database::connect();
        $service = new MovieRepository($pdo);
        $result = $service->getTopRatedMovies(90);

        $title = 'Melhores filmes avaliados por usuários do site';
        $data['open_graph']['title'] = $title;

        $template = templates($data)->make('movies.php', ['movies' => $result]);
        $template->layout('layouts/default.php', ['title' => $title]);

        echo $template->render();
    });

    $route->get('/movies/search', function()
    {
        header('X-Robots-Tag: noindex');

        $template = templates()->make('movies_search.html');
        $template->layout('layouts/default.php', ['title' => 'Filmes']);

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

    $route->get('/movies/added', authMiddleware(function()
    {
        $pdo = Database::connect();
        $userId = Session::get('user_id');

        $service = new MovieRepository($pdo);
        $result = $service->getUserMovies($userId, 10);

        print_r($result);

        exit;
        $template = templates()->make('mymovielist.html');
        $template->layout('layouts/default.php', ['title' => 'Minha lista de filmes']);

        echo $template->render();
    }));

    $route->addGroup('/api/v1', require ROOT_DIR . '/api/v1/routes.php');
};