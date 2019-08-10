<?php
namespace WPObsoflo\wordpress_uri_scheme;

function check()
{
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
        $scheme = 'https';
    } else {
        $scheme = 'http';
    }

    return ['wordpress_uri_scheme', ['scheme' => $scheme]];
}
