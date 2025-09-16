<?php

function remove_accents(string $string)
{
    $string = iconv('UTF-8', 'ASCII//TRANSLIT', $string);
    $string = preg_replace('/[^\w\s]/', '', $string);
    return preg_replace('/\s+/', '_', $string);
}