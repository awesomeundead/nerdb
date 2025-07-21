<?php

require __DIR__ . '/../session.php';

if (isset($_SESSION['logged_in']))
{
    if (isset($_COOKIE['login']))
    {
        setcookie('login', '', -1, '/');
    }

    session_unset();
    session_destroy();
}

header('location: index.html?logout');