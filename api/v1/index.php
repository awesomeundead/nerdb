<?php

require __DIR__ . '/../../vendor/autoload.php';

date_default_timezone_set('America/Sao_Paulo');
define('ROOT_DIR', __DIR__);

$base_path = '/projeto_abigo/api/v1';

$routes = require 'routes.php';
$dispatcher = \FastRoute\simpleDispatcher($routes);
$http_method = $_SERVER['REQUEST_METHOD'];
$path = substr_replace(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '', 0, strlen($base_path ?? ''));
$route_info = $dispatcher->dispatch($http_method, $path);

switch ($route_info[0])
{
    case \FastRoute\Dispatcher::NOT_FOUND:
        http_response_code(404);
        echo 'NOT_FOUND';
        break;
    case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        http_response_code(405);
        echo 'METHOD_NOT_ALLOWED';
        break;
    case \FastRoute\Dispatcher::FOUND:
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