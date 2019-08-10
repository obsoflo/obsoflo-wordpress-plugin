<?php
namespace WPObsoflo\wordpress_weak_user_check;

require_once dirname(__FILE__) . '/../DatabaseQueries.php';

function check()
{
    $weak_users = \WPObsoflo\DatabaseQueries::weak_user_names();

    return array_map(function ($name) {
        return ['wordpress_weak_user_check', ['name' => $name]];
    }, $weak_users);
}
