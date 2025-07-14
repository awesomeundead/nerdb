<?php

require __DIR__ . '/../session.php';

if (isset($_SESSION['logged_in']))
{
    $session = [
        'logged_in'   => $_SESSION['logged_in'],
        'steamid'     => $_SESSION['steamid'],
        'personaname' => $_SESSION['personaname'],
        'avatarhash'  => $_SESSION['avatarhash']
    ];

    echo json_encode($session, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}
else
{
    echo 'false';
}