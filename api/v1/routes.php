<?php

use FastRoute\RouteCollector;

return function(RouteCollector $route)
{
    $route->get('/movies', 'handlers/get/movies.php');

    $route->get('/user', 'handlers/get/user.php');
    $route->get('/user/movies/{id:\d+}', 'handlers/get/user_movies.php');

    $route->post('/movie', 'handlers/post/movie.php');
    $route->post('/user', 'handlers/get/user.php');
};