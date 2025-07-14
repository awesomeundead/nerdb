<?php

ini_set('session.use_strict_mode', 1);

session_set_cookie_params([
    'domain' => $_SERVER['SERVER_NAME'],
    'httponly' => true,
    'lifetime' => 3600,
    'path' => '/',
    'samesite' => 'Lax'
]);

session_start();

/*
if (session_id() === '' || !isset($_COOKIE[session_name()]))
{
    header('location: index.html?cookies=disabled');
    exit;
}
*/

/*
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN';
$address = $_SERVER['HTTP_CLIENT_IP']
        ?? $_SERVER['HTTP_X_FORWARDED_FOR']
        ?? $_SERVER['HTTP_X_FORWARDED']
        ?? $_SERVER['HTTP_FORWARDED_FOR']
        ?? $_SERVER['HTTP_FORWARDED']
        ?? $_SERVER['REMOTE_ADDR']
        ?? 'UNKNOWN';

if (isset($_SESSION['regeneration']))
{
    if (time() - $_SESSION['regeneration'] > 10)
    {
        session_regenerate_id(true);
        $_SESSION['regeneration'] = time();
    }

    if ($_SESSION['user_agent'] != $user_agent || $_SESSION['address'] != $address)
    {
        session_destroy();
        
        header('location: index.html?expired=1');
        exit;
    }
}
else
{
    session_regenerate_id(true);
    $_SESSION['regeneration'] = time();
    $_SESSION['user_agent'] = $user_agent;
    $_SESSION['address'] = $address;
}
*/