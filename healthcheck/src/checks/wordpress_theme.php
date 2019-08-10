<?php
namespace WPObsoflo\wordpress_theme;

require_once dirname(__FILE__) . '/../DatabaseQueries.php';

function check()
{
    $themes = \WPObsoflo\DatabaseQueries::themes();
    return array_map(function ($theme) {
        return [
            'wordpress_theme',
            [
                'name' => $theme['name'],
                'version' => $theme['version']
            ]
        ];
    }, $themes);
}
