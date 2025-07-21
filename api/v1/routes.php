<?php

use FastRoute\RouteCollector;

return function(RouteCollector $route)
{
    $route->get('/login', 'handlers/get/login.php'); // (Query String) selector={selector}

    $route->get('/movie/{id:\d+}', 'handlers/get/movie.php');

    $route->get('/movies', 'handlers/get/movies.php'); // (Query String) release={year} || search={title_br || title_us || release}

    $route->get('/user[/{id:\d+}]', 'handlers/get/user.php'); // (Query String) steamid={steamid}
    $route->get('/user/friends', 'handlers/get/user_friends.php');

    $route->get('/movie-list/my', 'handlers/get/movie_list_my.php'); // (Query String) watchlist={0 || 1}, watched={0 || 1} || rating={1-10} || liked={0 || 1}
    $route->get('/movie-list/user/{id:\d+}', 'handlers/get/movie_list_user.php'); // (Query String) watchlist={0 || 1}, watched={0 || 1} || rating={1-10} || liked={0 || 1}

    $route->get('/top-movies', 'handlers/get/top_movies.php');

    $route->post('/login', 'handlers/post/login.php');

    $route->post('/movie', 'handlers/post/movie.php');
    $route->post('/user', 'handlers/post/user.php');
    $route->post('/user/addmovie/{id:\d+}', 'handlers/post/user_addmovie.php');

    $route->put('/movie/{id:\d+}', 'handlers/put/movie.php');

};