<?php

use FastRoute\RouteCollector;

return function(RouteCollector $route)
{
    $route->get('/', 'handlers/get/');
};