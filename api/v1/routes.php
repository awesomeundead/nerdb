<?php

return function($route)
{
    $route->get('/game/{id:\d+}', jsonMiddleware(function($vars)
    {
        $pdo = Database::connect();
        $userId = Session::get('user_id');
        $gameId = $vars['id'];

        $service = new GameRepository($pdo);
        return $service->getGameDetails($gameId, $userId);
    }));

    $route->post('/game', jsonMiddleware(function()
    {
        $pdo = Database::connect();
        $loggedIn = Session::get('logged_in');
        $userId = Session::get('user_id');

        if(!$loggedIn)
        {
            http_response_code(401);
            echo 'UNAUTHORIZED';
            exit;
        }

        $content = trim(file_get_contents('php://input'));
        $data = json_decode($content, true);

        $data['user_id']      = $userId;
        $data['title']        = trim($data['title']);
        $data['developer']    = trim($data['developer']);
        $data['genres']       = trim($data['genres']);
        $data['release_year'] = trim($data['release_year']);
        $data['title_url']    = remove_accents($data['title']);

        if (preg_match('#/app/(\d+)/#', $data['steam'], $matches))
        {
            $data['steam'] = $matches[1];
        }

        $service = new GameRepository($pdo);
        $result = $service->getGameId($data['title'], $data['release_year']);

        if ($result)
        {
            return ['message' => 'Este jogo já foi adicionado.'];
        }

        $result = $service->addGame($data);
        $array['status'] = $result ? 'success' : 'failure';

        return $array;
    }));

    $route->put('/game/{id:\d+}', jsonMiddleware(function($vars)
    {
        $pdo = Database::connect();
        $loggedIn = Session::get('logged_in');
        $userId = Session::get('user_id');

        if(!$loggedIn)
        {
            http_response_code(401);
            echo 'UNAUTHORIZED';
            exit;
        }

        $content = trim(file_get_contents('php://input'));
        $data = json_decode($content, true);

        $data['id']           = $vars['id'];
        $data['user_id']      = $userId;
        $data['title']        = trim($data['title']);
        $data['developer']    = trim($data['developer']);
        $data['genres']       = trim($data['genres']);
        $data['release_year'] = trim($data['release_year']);
        $data['title_url']    = remove_accents($data['title']);
        
        if (preg_match('#/app/(\d+)/#', $data['steam'], $matches))
        {
            $data['steam'] = $matches[1];
        }

        $service = new GameRepository($pdo);
        $result = $service->getGameId($data['title'], $data['release_year']);

        if ($result && $result != $data['id'])
        {
            return ['message' => 'Este jogo já foi adicionado.'];
        }

        $result = $service->updateGame($data);
        $array['status'] = $result ? 'success' : 'failure';

        return $array;
    }));
    
    $route->get('/games', jsonMiddleware(function()
    {
        $pdo = Database::connect();

        $filters = [
            'developer' => $_GET['developer'] ?? null,
            'genre'     => $_GET['genre'] ?? null,
            'release'   => $_GET['release'] ?? null,
            'search'    => $_GET['search'] ?? null
        ];

        $service = new GameRepository($pdo);
        $array['games'] = $service->findGames($filters);
        return $array;
    }));

    $route->get('/movie/{id:\d+}', jsonMiddleware(function($vars)
    {
        $pdo = Database::connect();
        $userId = Session::get('user_id');
        $movieId = $vars['id'];

        $service = new MovieRepository($pdo);
        return $service->getMovieDetails($movieId, $userId);
    }));

    $route->post('/movie', jsonMiddleware(function()
    {
        $pdo = Database::connect();
        $loggedIn = Session::get('logged_in');
        $userId = Session::get('user_id');

        if(!$loggedIn)
        {
            http_response_code(401);
            echo 'UNAUTHORIZED';
            exit;
        }

        $content = trim(file_get_contents('php://input'));
        $data = json_decode($content, true);

        $data['user_id']      = $userId;
        $data['title_br']     = trim($data['title_br']);
        $data['title_us']     = trim($data['title_us']);
        $data['director']     = trim($data['director']);
        $data['genres']       = trim($data['genres']);
        $data['release_year'] = trim($data['release_year']);
        $data['title_url']    = remove_accents($data['title_br']);

        if (preg_match('#/title/(tt\d+)/#', $data['imdb'], $matches))
        {
            $data['imdb'] = $matches[1];
        }

        $service = new MovieRepository($pdo);
        $result = $service->getMovieId($data['title_br'], $data['release_year']);

        if ($result)
        {
            return ['message' => 'Este filme já foi adicionado.'];
        }

        $result = $service->addMovie($data);
        $array['status'] = $result ? 'success' : 'failure';

        return $array;
    }));

    $route->put('/movie/{id:\d+}', jsonMiddleware(function($vars)
    {
        $pdo = Database::connect();
        $loggedIn = Session::get('logged_in');
        $userId = Session::get('user_id');

        if(!$loggedIn)
        {
            http_response_code(401);
            echo 'UNAUTHORIZED';
            exit;
        }

        $content = trim(file_get_contents('php://input'));
        $data = json_decode($content, true);

        $data['id']           = $vars['id'];
        $data['user_id']      = $userId;
        $data['title_br']     = trim($data['title_br']);
        $data['title_us']     = trim($data['title_us']);
        $data['director']     = trim($data['director']);
        $data['genres']       = trim($data['genres']);
        $data['release_year'] = trim($data['release_year']);
        $data['title_url']    = remove_accents($data['title_br']);
        
        if (preg_match('#/title/(tt\d+)/#', $data['imdb'], $matches))
        {
            $data['imdb'] = $matches[1];
        }

        $service = new MovieRepository($pdo);
        $result = $service->getMovieId($data['title_br'], $data['release_year']);

        if ($result && $result != $data['id'])
        {
            return ['message' => 'Este filme já foi adicionado.'];
        }

        $result = $service->updateMovie($data);
        $array['status'] = $result ? 'success' : 'failure';

        return $array;
    }));

    $route->get('/movielist', jsonMiddleware(function()
    {
        $pdo = Database::connect();

        $offset = (int)$_GET['offset'] ?? 0;

        $service = new MovieRepository($pdo);
        $array['movies'] = $service->getMovies(100, $offset);
        return $array;
    }));

    $route->get('/movies', jsonMiddleware(function()
    {
        $pdo = Database::connect();

        $filters = [
            'actor'    => $_GET['actor'] ?? null,
            'director' => $_GET['director'] ?? null,
            'genre'    => $_GET['genre'] ?? null,
            'release'  => $_GET['release'] ?? null,
            'search'   => $_GET['search'] ?? null
        ];

        $service = new MovieRepository($pdo);
        $array['movies'] = $service->findMovies($filters);
        return $array;
    }));

    $route->get('/movies/count', jsonMiddleware(function()
    {
        $pdo = Database::connect();
        $service = new MovieRepository($pdo);
        $array['total'] = $service->getCountMovies();
        return $array;
    }));

    $route->get('/movies/random', jsonMiddleware(function()
    {
        $pdo = Database::connect();
        $service = new MovieRepository($pdo);
        $array['movies'] = $service->getRandomMovies(40);
        return $array;
    }));

    $route->post('/mylist/game/{id:\d+}', jsonMiddleware(function($vars)
    {
        $pdo = Database::connect();
        $loggedIn = Session::get('logged_in');
        $userId = Session::get('user_id');

        if(!$loggedIn)
        {
            http_response_code(401);
            echo 'UNAUTHORIZED';
            exit;
        }

        $content = trim(file_get_contents('php://input'));
        $data = json_decode($content, true);
        $data['game_id'] = $vars['id'];

        $service = new UserGamelist($pdo, $loggedIn, $userId);
        $result = $service->set($data);
        $array['status'] = $result ? 'success' : 'failure';

        return $array;
    }));

    $route->get('/mylist/games', jsonMiddleware(function()
    {
        $pdo = Database::connect();
        $loggedIn = Session::get('logged_in');
        $userId = Session::get('user_id');

        if(!$loggedIn)
        {
            http_response_code(401);
            echo 'UNAUTHORIZED';
            exit;
        }

        $filters = [
            'playlist' => $_GET['playlist'] ?? null,
            'played'   => $_GET['played'] ?? null,
            'rating'   => $_GET['rating'] ?? null,
            'liked'    => $_GET['liked'] ?? null
        ];

        $service = new UserGamelist($pdo, $loggedIn, $userId);
        $array['games'] = $service->getGames($filters);
        return $array;
    }));

    $route->get('/mylist/movies', jsonMiddleware(function()
    {
        $pdo = Database::connect();
        $loggedIn = Session::get('logged_in');
        $userId = Session::get('user_id');

        if(!$loggedIn)
        {
            http_response_code(401);
            echo 'UNAUTHORIZED';
            exit;
        }

        $filters = [
            'watchlist' => $_GET['watchlist'] ?? null,
            'watched'   => $_GET['watched'] ?? null,
            'rating'    => $_GET['rating'] ?? null,
            'liked'     => $_GET['liked'] ?? null
        ];

        $service = new UserMovielist($pdo, $loggedIn, $userId);
        $array['movies'] = $service->getMovies($filters);
        return $array;
    }));

    $route->post('/mylist/movie/{id:\d+}', jsonMiddleware(function($vars)
    {
        $pdo = Database::connect();
        $loggedIn = Session::get('logged_in');
        $userId = Session::get('user_id');

        if(!$loggedIn)
        {
            http_response_code(401);
            echo 'UNAUTHORIZED';
            exit;
        }

        $content = trim(file_get_contents('php://input'));
        $data = json_decode($content, true);
        $data['movie_id'] = $vars['id'];

        $service = new UserMovielist($pdo, $loggedIn, $userId);
        $result = $service->set($data);
        $array['status'] = $result ? 'success' : 'failure';

        return $array;
    }));

    /*
    $route->get('/people', jsonMiddleware(function()
    {
        $pdo = Database::connect();
        $loggedIn = Session::get('logged_in');
        $userId = Session::get('user_id');

        if(!$loggedIn)
        {
            http_response_code(401);
            echo 'UNAUTHORIZED';
            exit;
        }

        $service = new People($pdo);
        $array['people'] = $service->getPeople(1000);
        return $array;
    }));
    */

    $route->get('/score', jsonMiddleware(function()
    {
        $pdo = Database::connect();
        $loggedIn = Session::get('logged_in');
        $userId = Session::get('user_id');

        if(!$loggedIn)
        {
            http_response_code(401);
            echo 'UNAUTHORIZED';
            exit;
        }

        $service = new UserService($pdo);
        return $service->getScore($userId);
    }));

    $route->get('/user/friends', jsonMiddleware(function()
    {
        $pdo = Database::connect();
        $loggedIn = Session::get('logged_in');
        $userId = Session::get('user_id');

        if(!$loggedIn)
        {
            http_response_code(401);
            echo 'UNAUTHORIZED';
            exit;
        }

        $service = new UserService($pdo, $loggedIn, $userId);
        $array['friends'] = $service->getFriends();
        return $array;
    }));

    $route->get('/user/score/{id:\d+}', jsonMiddleware(function($vars)
    {
        $pdo = Database::connect();
        $loggedIn = Session::get('logged_in');
        $userId = Session::get('user_id');

        if(!$loggedIn)
        {
            http_response_code(401);
            echo 'UNAUTHORIZED';
            exit;
        }

        if ($userId == $vars['id'])
        {
            http_response_code(400);
            echo 'BAD REQUEST';
            exit;
        }

        $service = new UserService($pdo);
        $array['user_score'] = $service->getScore($vars['id']);
        $array['my_score'] = $service->getScore($userId);
        return $array;
    }));

    $route->get('/userlist/games/{id:\d+}', jsonMiddleware(function($vars)
    {
        $pdo = Database::connect();
        $loggedIn = Session::get('logged_in');
        $userId = Session::get('user_id');

        if(!$loggedIn)
        {
            http_response_code(401);
            echo 'UNAUTHORIZED';
            exit;
        }

        $filters = [
            'playlist' => $_GET['playlist'] ?? null,
            'played'   => $_GET['played'] ?? null,
            'rating'   => $_GET['rating'] ?? null,
            'liked'    => $_GET['liked'] ?? null
        ];

        $service = new UserGamelist($pdo, $loggedIn, $userId);
        $array['games'] = $service->getGamesFriends($vars['id'], $filters);
        return $array;
    }));

    $route->get('/userlist/movies/{id:\d+}', jsonMiddleware(function($vars)
    {
        $pdo = Database::connect();
        $loggedIn = Session::get('logged_in');
        $userId = Session::get('user_id');

        if(!$loggedIn)
        {
            http_response_code(401);
            echo 'UNAUTHORIZED';
            exit;
        }

        $filters = [
            'watchlist' => $_GET['watchlist'] ?? null,
            'watched'   => $_GET['watched'] ?? null,
            'rating'    => $_GET['rating'] ?? null,
            'liked'     => $_GET['liked'] ?? null
        ];

        $service = new UserMovielist($pdo, $loggedIn, $userId);
        $array['movies'] = $service->getMoviesFriends($vars['id'], $filters);
        return $array;
    }));
};