<?php
namespace WPObsoflo\wordpress_plugin_updates;

require_once dirname(__FILE__) . '/../DatabaseQueries.php';

function check()
{
    $plugins = \WPObsoflo\DatabaseQueries::plugin_updates();
    return array_map(function ($plugin) {
        return [
            'wordpress_plugin_update',
            [
                'name' => $plugin['name'],
                'version' => $plugin['version'],
                'latest_version' => $plugin['latest_version']
            ]
        ];
    }, $plugins);
}
