<?php

use \FastRoute\Dispatcher;

class HttpException extends \Exception {}

$http_method = $_SERVER['REQUEST_METHOD'];
$uri = substr_replace(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '', 0, strlen(BASE_PATH));

try
{
    function runMiddlewares(array $middlewares, callable $finalHandler)
    {
        $pipeline = array_reduce(
            array_reverse($middlewares),
            fn($next, $middleware) => fn($request) => $middleware($request, $next),
            $finalHandler
        );

        return $pipeline;
    }

    $middlewares = require ROOT_DIR . '/middlewares.php';
    $routes = require ROOT_DIR . '/routes.php';

    $dispatcher = \FastRoute\simpleDispatcher($routes);
    $route_info = $dispatcher->dispatch($http_method, $uri);

    switch ($route_info[0])
    {
        case Dispatcher::NOT_FOUND:
            throw new HttpException('NOT FOUND', 404);
            break;
        case Dispatcher::METHOD_NOT_ALLOWED:
            throw new HttpException('METHOD NOT ALLOWED', 405);
            break;
        case Dispatcher::FOUND:
            [,$handler, $vars] = $route_info;            
            $finalHandler = fn($vars) => $handler($vars);
            $pipeline = runMiddlewares($middlewares, $finalHandler);
            $pipeline($vars);
            break;
    }
}
catch (HttpException $e)
{
    if (in_array($e->getCode(), [404, 405]))
    {
        http_response_code(404);
        $fallback = $dispatcher->dispatch('GET', '/404');
        call_user_func($fallback[1]);
    }
}
catch (Throwable $error)
{
    file_put_contents('error.log', $error->getMessage(), FILE_APPEND);

    echo $error->getMessage();
}