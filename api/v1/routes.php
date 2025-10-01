<?php

function jsonMiddleware($handler)
{
    return function($vars) use ($handler)
    {
        header('Content-Type: application/json; charset=utf-8');

        $result = $handler($vars);

        echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    };
}

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

    $route->get('/gamelist', jsonMiddleware(function()
    {
        $pdo = Database::connect();

        $limit = 80;
        $offset = $_GET['offset'] ?? 0;

        $service = new GameRepository($pdo);
        $games = $service->getGames($limit + 1, $offset);

        $hasNextPage = count($games) > $limit;

        if ($hasNextPage)
        {
            array_pop($games);
        }

        return [
            'games'          => $games,
            'previous_offset' => $offset > 0 ? max(0, $offset - $limit) : null,
            'next_offset'     => $hasNextPage ? $offset + $limit : null
        ];
    }));

    $route->get('/gamelist/added', jsonMiddleware(function()
    {
        $pdo = Database::connect();
        $userId = Session::get('user_id');

        if(!$userId)
        {
            http_response_code(401);
            echo 'UNAUTHORIZED';
            exit;
        }

        $limit = 80;
        $offset = $_GET['offset'] ?? 0;

        $service = new GameRepository($pdo);
        $games = $service->getUserGames($userId, $limit + 1, $offset);

        $hasNextPage = count($games) > $limit;

        if ($hasNextPage)
        {
            array_pop($games);
        }

        return [
            'games'          => $games,
            'previous_offset' => $offset > 0 ? max(0, $offset - $limit) : null,
            'next_offset'     => $hasNextPage ? $offset + $limit : null
        ];
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
        $limit = 40;
        $offset = $_GET['offset'] ?? 0;

        $service = new GameRepository($pdo);
        $games = $service->findGames($filters, $limit + 1, $offset);
        $hasNextPage = count($games) > $limit;

        if ($hasNextPage)
        {
            array_pop($games);
        }

        return [
            'games'           => $games,
            'previous_offset' => $offset > 0 ? max(0, $offset - $limit) : null,
            'next_offset'     => $hasNextPage ? $offset + $limit : null
        ];
    }));

    $route->get('/games/count', jsonMiddleware(function()
    {
        $pdo = Database::connect();
        $service = new GameRepository($pdo);
        $array['total'] = $service->getCountGames();
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

    $route->put('/movie/{id:\d+}/cast', jsonMiddleware(function($vars)
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
        //$data['user_id']      = $userId;
        $data['title_br']     = trim($data['cast']);
        
        $service = new MovieRepository($pdo);
        $result = $service->checkMovieExists($data['movie_id']);

        if ($result === false)
        {
            return ['message' => 'Este filme não foi encontrado.'];
        }

        $result = $service->updateMovieCast($data);
        $array['status'] = $result ? 'success' : 'failure';

        return $array;
    }));

    $route->get('/movielist', jsonMiddleware(function()
    {
        $pdo = Database::connect();

        $limit = 80;
        $offset = $_GET['offset'] ?? 0;

        $service = new MovieRepository($pdo);
        $movies = $service->getMovies($limit + 1, $offset);

        $hasNextPage = count($movies) > $limit;

        if ($hasNextPage)
        {
            array_pop($movies);
        }

        return [
            'movies'          => $movies,
            'previous_offset' => $offset > 0 ? max(0, $offset - $limit) : null,
            'next_offset'     => $hasNextPage ? $offset + $limit : null
        ];
    }));

    $route->get('/movielist/added', jsonMiddleware(function()
    {
        $pdo = Database::connect();
        $userId = Session::get('user_id');

        if(!$userId)
        {
            http_response_code(401);
            echo 'UNAUTHORIZED';
            exit;
        }

        $limit = 80;
        $offset = $_GET['offset'] ?? 0;

        $service = new MovieRepository($pdo);
        $movies = $service->getUserMovies($userId, $limit + 1, $offset);
        $hasNextPage = count($movies) > $limit;

        if ($hasNextPage)
        {
            array_pop($movies);
        }

        return [
            'movies'          => $movies,
            'previous_offset' => $offset > 0 ? max(0, $offset - $limit) : null,
            'next_offset'     => $hasNextPage ? $offset + $limit : null
        ];
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
        $limit = 40;
        $offset = $_GET['offset'] ?? 0;

        $service = new MovieRepository($pdo);
        $movies = $service->findMovies($filters, $limit + 1, $offset);
        $hasNextPage = count($movies) > $limit;

        if ($hasNextPage)
        {
            array_pop($movies);
        }

        return [
            'movies'          => $movies,
            'previous_offset' => $offset > 0 ? max(0, $offset - $limit) : null,
            'next_offset'     => $hasNextPage ? $offset + $limit : null
        ];
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

    $route->get('/movies/ratedbyfriends', jsonMiddleware(function()
    {
        $loggedIn = Session::get('logged_in');
        $userId = Session::get('user_id');

        if(!$loggedIn)
        {
            http_response_code(401);
            echo 'UNAUTHORIZED';
            exit;
        }

        $pdo = Database::connect();
        $service = new UserMovielist($pdo);
        $array['movies'] = $service->getMoviesRatedByFriends($userId);
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
            'listed' => $_GET['listed'] ?? null,
            'completed'   => $_GET['completed'] ?? null,
            'rating'   => $_GET['rating'] ?? null,
            'liked'    => $_GET['liked'] ?? null
        ];

        $limit = 80;
        $offset = $_GET['offset'] ?? 0;

        $service = new UserGamelist($pdo, $loggedIn, $userId);
        $games = $service->getGames($filters, $limit + 1, $offset);
        $hasNextPage = count($games) > $limit;

        if ($hasNextPage)
        {
            array_pop($games);
        }

        return [
            'games'          => $games,
            'previous_offset' => $offset > 0 ? max(0, $offset - $limit) : null,
            'next_offset'     => $hasNextPage ? $offset + $limit : null
        ];
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
            'listed' => $_GET['listed'] ?? null,
            'completed'   => $_GET['completed'] ?? null,
            'rating'    => $_GET['rating'] ?? null,
            'liked'     => $_GET['liked'] ?? null
        ];
        $limit = 80;
        $offset = $_GET['offset'] ?? 0;

        $service = new UserMovielist($pdo, $loggedIn, $userId);
        $movies = $service->getMovies($filters, $limit + 1, $offset);
        $hasNextPage = count($movies) > $limit;

        if ($hasNextPage)
        {
            array_pop($movies);
        }

        return [
            'movies'          => $movies,
            'previous_offset' => $offset > 0 ? max(0, $offset - $limit) : null,
            'next_offset'     => $hasNextPage ? $offset + $limit : null
        ];
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

        $service = new UserService($pdo);
        $array['friends'] = $service->getFriends($userId);
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
            'listed' => $_GET['listed'] ?? null,
            'completed'   => $_GET['completed'] ?? null,
            'rating'   => $_GET['rating'] ?? null,
            'liked'    => $_GET['liked'] ?? null
        ];
        $limit = 60;
        $offset = $_GET['offset'] ?? 0;

        $service = new UserGamelist($pdo, $loggedIn, $userId);
        $games = $service->getGamesFriends($vars['id'], $filters, $limit + 1, $offset);
        $hasNextPage = count($games) > $limit;

        if ($hasNextPage)
        {
            array_pop($games);
        }

        return [
            'games'          => $games,
            'previous_offset' => $offset > 0 ? max(0, $offset - $limit) : null,
            'next_offset'     => $hasNextPage ? $offset + $limit : null
        ];
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
            'listed' => $_GET['listed'] ?? null,
            'completed'   => $_GET['completed'] ?? null,
            'rating'    => $_GET['rating'] ?? null,
            'liked'     => $_GET['liked'] ?? null
        ];
        $limit = 60;
        $offset = $_GET['offset'] ?? 0;

        $service = new UserMovielist($pdo, $loggedIn, $userId);
        $movies = $service->getMoviesFriends($vars['id'], $filters, $limit + 1, $offset);
        $hasNextPage = count($movies) > $limit;

        if ($hasNextPage)
        {
            array_pop($movies);
        }

        return [
            'movies'          => $movies,
            'previous_offset' => $offset > 0 ? max(0, $offset - $limit) : null,
            'next_offset'     => $hasNextPage ? $offset + $limit : null
        ];
    }));
};