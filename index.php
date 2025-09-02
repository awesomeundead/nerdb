<?php

use \FastRoute\Dispatcher;

$http_method = $_SERVER['REQUEST_METHOD'];
$uri = substr_replace(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '', 0, strlen(BASE_PATH));

try
{
    $routes = require ROOT . '/routes.php';

    $dispatcher = \FastRoute\simpleDispatcher($routes);
    $route_info = $dispatcher->dispatch($http_method, $uri);

    switch ($route_info[0])
    {
        case Dispatcher::NOT_FOUND:
            http_response_code(404);
            echo 'NOT_FOUND';
            break;
        case Dispatcher::METHOD_NOT_ALLOWED:
            http_response_code(405);
            echo 'METHOD_NOT_ALLOWED';
            break;
        case Dispatcher::FOUND:
            [,$handler, $vars] = $route_info;
            
            if ($handler instanceof \Closure)
            {
                call_user_func($handler, $vars);
            }
            else
            {
                require $handler;
            }

            break;
    }
}
catch (Throwable $error)
{
    echo $error->getMessage();
}