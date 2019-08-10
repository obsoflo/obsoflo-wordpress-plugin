<?php
namespace WPObsoflo\wordpress_mysql;

require_once dirname(__FILE__) . '/../Database.php';

function check()
{
    $conn = \WPObsoflo\Database::connect();
    $version = $conn->client_info;
    $conn->close();
    $parts = explode(' ', $version);
    return [
        'wordpress_mysql',
        [
            'version' => $parts[1],
            'release_date' => $parts[3]
        ]
    ];
}
