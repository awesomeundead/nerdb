<?php

date_default_timezone_set('America/Sao_Paulo');
define('ROOT', dirname(__DIR__));
define('BASE_PATH', rtrim(dirname($_SERVER['PHP_SELF'], 2), '/\\'));

require ROOT . '/vendor/autoload.php';
require ROOT . '/index.php';