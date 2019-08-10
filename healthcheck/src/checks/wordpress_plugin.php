<?php
namespace WPObsoflo\wordpress_plugin;

require_once dirname(__FILE__) . '/../DatabaseQueries.php';

function check()
{
    $plugins = \WPObsoflo\DatabaseQueries::plugins();
    return array_map(function ($plugin) {
        return [
            'wordpress_plugin',
            [
                'name' => $plugin['name'],
                'version' => $plugin['version']
            ]
        ];
    }, $plugins);
}
