<?php
namespace WPObsoflo\wordpress_mu_plugin;

require_once dirname(__FILE__) . '/../DatabaseQueries.php';

function check()
{
    $plugins = \WPObsoflo\DatabaseQueries::mu_plugin_versions();
    return array_map(function ($plugin) {
        return [
            'wordpress_mu_plugin',
            [
                'name' => $plugin['name'],
                'version' => $plugin['version'],
                'plugin' => $plugin['plugin']
            ]
        ];
    }, $plugins);
}
