<?php

use FastRoute\RouteCollector;

return function(RouteCollector $route)
{
    $route->get('/movies', 'handlers/get/movies.php');

    $route->post('/movie', 'handlers/post/movie.php');
};