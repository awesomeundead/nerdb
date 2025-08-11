<?php

date_default_timezone_set('America/Sao_Paulo');

$protocol = !empty($_SERVER['HTTPS']) ? 'https' : 'http';

define('HOST', "{$protocol}://{$_SERVER['HTTP_HOST']}");
define('ROOT', dirname(__DIR__));
define('BASE_PATH', rtrim(dirname($_SERVER['PHP_SELF'], 2), '/\\'));

require ROOT . '/vendor/autoload.php';
require ROOT . '/index.php';