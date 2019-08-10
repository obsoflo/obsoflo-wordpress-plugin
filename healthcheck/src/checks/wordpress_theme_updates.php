<?php
namespace WPObsoflo\wordpress_theme_updates;

require_once dirname(__FILE__) . '/../DatabaseQueries.php';

function check()
{
    $themes = \WPObsoflo\DatabaseQueries::theme_updates();
    return array_map(function ($theme) {
        return [
            'wordpress_theme_updates',
            [
                'name' => $theme['name'],
                'version' => $theme['version']
            ]
        ];
    }, $themes);
}
