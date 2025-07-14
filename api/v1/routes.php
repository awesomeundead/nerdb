<?php

use FastRoute\RouteCollector;

return function(RouteCollector $route)
{
    $route->get('/login', 'handlers/get/login.php'); // (Query String) selector={selector}

    $route->get('/movie/{id:\d+}', 'handlers/get/movie.php');

    $route->get('/movies', 'handlers/get/movies.php'); // (Query String) release={year} || search={title_br || title_us || release}

    $route->get('/user[/{id:\d+}]', 'handlers/get/user.php'); // (Query String) steamid={steamid}
    $route->get('/user/friends', 'handlers/get/user_friends.php');

    $route->get('/user/movies', 'handlers/get/user_movies.php'); // (Query String) watched={0,1} || rating={1-10} || reaction={approval||disapproval}

    $route->get('/user/movies/{id:\d+}', 'handlers/get/user_movies_id.php');

    $route->post('/login', 'handlers/post/login.php');

    $route->post('/movie', 'handlers/post/movie.php');
    $route->post('/user', 'handlers/post/user.php');
    $route->post('/user/addmovie/{id:\d+}', 'handlers/post/user_addmovie.php');

    $route->put('/movie/{id:\d+}', 'handlers/put/movie.php');

};