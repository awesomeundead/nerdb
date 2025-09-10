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

    $engine->addData(['open_graph' => []]);

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
        $template = templates()->make('index.php');
        $template->layout('layouts/default.php', ['title' => '']);

        echo $template->render();
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

    $route->get('/game/{id:\d+}[/{title}]', function($vars)
    {
        $pdo = Database::connect();
        $loggedIn = Session::get('logged_in');
        $userId = Session::get('user_id');
        $gameId = $vars['id'];

        $service = new GameService($pdo, $loggedIn, $userId);
        $result = $service->getGameDetails($gameId);

        $result['developer'] = explode(';', $result['developer']);
        $result['genres'] = explode(';', $result['genres']);

        $template = templates()->make('game.php', ['game' => $result]);
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

    $route->get('/movie/{id:\d+}[/{title}]', function($vars)
    {
        $pdo = Database::connect();
        $loggedIn = Session::get('logged_in');
        $userId = Session::get('user_id');
        $movieId = $vars['id'];

        $service = new MovieService($pdo, $loggedIn, $userId);
        $result = $service->getMovieDetails($movieId);

        $result['director'] = explode(';', $result['director']);
        $result['genres'] = explode(';', $result['genres']);

        $template = templates()->make('movie.php', ['movie' => $result]);
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
        $template = templates()->make('movies.html');
        $template->layout('layouts/default.php', ['title' => 'Melhores filmes avaliados por usuários do site']);

        echo $template->render();
    });

    $route->get('/movies/search', function()
    {
        $template = templates()->make('movies_search.html');
        $template->layout('layouts/default.php', ['title' => 'Filmes']);

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

    $route->get('/store', function()
    {
        $template = templates()->make('store.html');
        $template->layout('layouts/default.php', ['title' => 'Loja']);

        echo $template->render();
    });

    $route->addGroup('/api/v1', function($route)
    {
        $root = 'api/v1/handlers';

        $route->get('/friendship/{id:\d+}', $root . '/get/friendship.php'); // (Query String) steamid={selector}

        $route->get('/game/{id:\d+}', $root . '/get/game.php');
        $route->post('/game', $root . '/post/game.php');
        $route->put('/game/{id:\d+}', $root . '/put/game.php');
        
        $route->get('/games', $root . '/get/games.php');
        $route->get('/games/best-rated', $root . '/get/games_best_rated.php');

        $route->get('/login', $root . '/get/login.php'); // (Query String) selector={selector}
        $route->post('/login', $root . '/post/login.php');    

        $route->post('/mylist/game/{id:\d+}', $root . '/post/mylist_game.php');
        $route->post('/mylist/movie/{id:\d+}', $root . '/post/mylist_movie.php');
        $route->get('/mylist/games', $root . '/get/mylist_games.php'); // Query String: playlist={0-1}|played={0-1}|rating={1-10}|liked={0-1}
        $route->get('/mylist/movies', $root . '/get/mylist_movies.php'); // Query String: watchlist={0-1}|watched={0-1}|rating={1-10}|liked={0-1}

        $route->get('/movie/{id:\d+}', $root . '/get/movie.php');
        $route->post('/movie', $root . '/post/movie.php');
        $route->put('/movie/{id:\d+}', $root . '/put/movie.php');
        $route->post('/movie/cast/{id:\d+}', $root . '/post/movie_cast.php');

        /*
        * url: /movies?actor=Bale&director=Nolan&genre=Ação&release=2005&search=Batman
        * url: /movies?limit=20&offset=100&order=random
        */
        $route->get('/movies', $root . '/get/movies.php');
        $route->get('/movies/best-rated', $root . '/get/movies_best_rated.php');
        $route->get('/movies/count', $root . '/get/movies_count.php');

        $route->get('/people', $root . '/get/people.php');

        $route->get('/score', $root . '/get/score.php');

        $route->get('/user[/{id:\d+}]', $root . '/get/user.php'); // (Query String) steamid={steamid}
        $route->post('/user', $root . '/post/user.php');
        $route->get('/user/friends', $root . '/get/user_friends.php');
        $route->get('/user/score/{id:\d+}', $root . '/get/user_score.php');

        $route->get('/userlist/games/{id:\d+}', $root . '/get/userlist_games.php');
        $route->get('/userlist/movies/{id:\d+}', $root . '/get/userlist_movies.php');

        $route->get('/top-movies', $root . '/get/top_movies.php');
    });
};