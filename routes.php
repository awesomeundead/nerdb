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
        $loggedIn = Session::get('logged_in');

        if (!$loggedIn)
        {
            $uri = substr_replace(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '', 0, strlen(BASE_PATH));

            redirect("login?redirect={$uri}");
        }

        $handler($vars);
    };
};

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
        $loggedIn = Session::get('logged_in');
        $userId = Session::get('user_id');

        if ($loggedIn)
        {
            $pdo = Database::connect();
            $service = new UserService($pdo);
            $count = $service->countFriends($userId);

            $service = new UserMovielist($pdo);

            if ($count)
            {
                $data['movies_label'] = 'Sugestões de filmes que você ainda não viu e que seus amigos avaliaram bem.';
                $data['movies'] = $service->getMoviesRatedByFriends($userId);
            }
            else
            {
                $data['movies_label'] = 'Sugestões de filmes que você ainda não viu e que outras pessoas avaliaram bem.';
                $data['movies'] = $service->getMoviesRatedByUsers($userId);
            }

            $service = new UserGamelist($pdo);

            if ($count)
            {
                $data['games_label'] = 'Sugestões de jogos que você ainda não jogou e que seus amigos avaliaram bem.';
                $data['games'] = $service->getGamesRatedByFriends($userId);
            }
            else
            {
                $data['games_label'] = 'Sugestões de jogos que você ainda não jogou e que outras pessoas avaliaram bem.';
                $data['games'] = $service->getGamesRatedByUsers($userId);
            }

            if ($data['movies'] || $data['games'])
            {
                $template = templates()->make('index.php', $data);
            }
        }

        if (!isset($template))
        {
            $template = templates()->make('index.html');
        }

        $template->layout('layouts/default.php');

        echo $template->render();
    });

    $route->get('/404', function()
    {
        $template = templates()->make('404.html');
        $template->layout('layouts/default.php');

        echo $template->render();
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

        $title = "{$result['title']} ({$result['release_year']})";

        $result['developer'] = explode(';', $result['developer']);
        $result['genres'] = explode(';', $result['genres']);

        $data['open_graph']['image'] = HOST . BASE_PATH . "/images/games/512/{$result['media']}.webp";
        $data['open_graph']['title'] = $title;

        $template = templates($data)->make('game.php', ['game' => $result]);
        $template->layout('layouts/default.php', ['title' => $title]);

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

    $route->get('/gamelist', function()
    {
        $template = templates()->make('gamelist.html');
        $template->layout('layouts/default.php', ['title' => 'Lista de jogos']);

        echo $template->render();
    });

    $route->get('/gamelist/added', authMiddleware(function()
    {
        $template = templates()->make('gamelist_added.html');
        $template->layout('layouts/default.php', ['title' => 'Jogos que eu adicionei']);

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
        $loggedIn = Session::get('logged_in');

        if ($loggedIn)
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

        $title = "{$result['title_br']} ({$result['release_year']})";

        $result['director'] = explode(';', $result['director']);
        $result['genres'] = explode(';', $result['genres']);

        $data['open_graph']['image'] = HOST . BASE_PATH . "/images/512/{$result['media']}.webp";
        $data['open_graph']['title'] = $title;

        $template = templates($data)->make('movie.php', ['movie' => $result]);
        $template->layout('layouts/default.php', ['title' => $title]);

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

    $route->get('/movielist/added', authMiddleware(function()
    {
        $template = templates()->make('movielist_added.html');
        $template->layout('layouts/default.php', ['title' => 'Filmes que eu adicionei']);

        echo $template->render();
    }));

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

    $route->addGroup('/api/v1', require ROOT_DIR . '/api/v1/routes.php');
};