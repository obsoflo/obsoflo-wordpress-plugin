<?php
namespace WPObsoflo;

require dirname(__FILE__) . '/checks/wordpress_php.php';
require dirname(__FILE__) . '/checks/wordpress_theme.php';
require dirname(__FILE__) . '/checks/wordpress_mysql.php';
require dirname(__FILE__) . '/checks/wordpress_plugin.php';
require dirname(__FILE__) . '/checks/wordpress_version.php';
require dirname(__FILE__) . '/checks/wordpress_mu_plugin.php';
require dirname(__FILE__) . '/checks/wordpress_uri_scheme.php';
require dirname(__FILE__) . '/checks/wordpress_theme_updates.php';
require dirname(__FILE__) . '/checks/wordpress_file_editor_on.php';
require dirname(__FILE__) . '/checks/wordpress_plugin_updates.php';
require dirname(__FILE__) . '/checks/wordpress_weak_user_check.php';

// An array of healthcheck status items
class Status
{
    public static function to_json()
    {
        $results = [];
        $checks = [
            \WPObsoflo\wordpress_php\check(),
            \WPObsoflo\wordpress_theme\check(),
            \WPObsoflo\wordpress_mysql\check(),
            \WPObsoflo\wordpress_plugin\check(),
            \WPObsoflo\wordpress_version\check(),
            \WPObsoflo\wordpress_mu_plugin\check(),
            \WPObsoflo\wordpress_uri_scheme\check(),
            \WPObsoflo\wordpress_theme_updates\check(),
            \WPObsoflo\wordpress_plugin_updates\check(),
            \WPObsoflo\wordpress_file_editor_on\check(),
            \WPObsoflo\wordpress_weak_user_check\check()
        ];

        // flatten all the checks into a single array
        foreach ($checks as $check) {
            if (empty($check)) {
                continue;
            }
            if (is_array($check[0])) {
                $results = array_merge($results, $check);
            } else {
                $results[] = $check;
            }
        }

        // Format each item
        return array_map(function ($item) {
            $base = [
                'name' => $item[0]
            ];

            // Some checks contain tags, others don't
            if ($item[1]) {
                return [
                    'name' => $item[0],
                    'tags' => $item[1]
                ];
            } else {
                return ['name' => $item[0]];
            }
        }, $results);

        return $results;
    }
}
