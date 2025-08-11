<?php

use FastRoute\RouteCollector;

return function(RouteCollector $route)
{
    $route->get('/game/{id:\d+}', 'handlers/get/game.php');
    $route->post('/game', 'handlers/post/game.php');
    $route->put('/game/{id:\d+}', 'handlers/put/game.php');
    
    $route->get('/games', 'handlers/get/games.php');

    $route->get('/login', 'handlers/get/login.php'); // (Query String) selector={selector}
    $route->post('/login', 'handlers/post/login.php');    

    $route->post('/mylist/game/{id:\d+}', 'handlers/post/mylist_game.php');
    $route->post('/mylist/movie/{id:\d+}', 'handlers/post/mylist_movie.php');
    $route->get('/mylist/games', 'handlers/get/mylist_games.php'); // Query String: playlist={0-1}|played={0-1}|rating={1-10}|liked={0-1}
    $route->get('/mylist/movies', 'handlers/get/mylist_movies.php'); // Query String: watchlist={0-1}|watched={0-1}|rating={1-10}|liked={0-1}

    $route->get('/movie/{id:\d+}', 'handlers/get/movie.php');
    $route->post('/movie', 'handlers/post/movie.php');
    $route->put('/movie/{id:\d+}', 'handlers/put/movie.php');

    /*
     * url: /movies?actor=Bale&director=Nolan&genre=Ação&release=2005&search=Batman
     * url: /movies?limit=20&offset=100&order=random
     */
    $route->get('/movies', 'handlers/get/movies.php');
    $route->get('/movies/count', 'handlers/get/movies_count.php');

    $route->get('/score', 'handlers/get/score.php');

    $route->get('/user[/{id:\d+}]', 'handlers/get/user.php'); // (Query String) steamid={steamid}
    $route->post('/user', 'handlers/post/user.php');
    $route->get('/user/friends', 'handlers/get/user_friends.php');
    $route->get('/user/score/{id:\d+}', 'handlers/get/user_score.php');

    $route->get('/userlist/games/{id:\d+}', 'handlers/get/userlist_games.php');
    $route->get('/userlist/movies/{id:\d+}', 'handlers/get/userlist_movies.php');

    $route->get('/top-movies', 'handlers/get/top_movies.php');
};