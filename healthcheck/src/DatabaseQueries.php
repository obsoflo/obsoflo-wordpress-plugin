<?php
namespace WPObsoflo;

require dirname(__FILE__) . '/Database.php';

class DatabaseQueries
{
    const THEMES = '_site_transient_update_themes';
    const PLUGINS = '_site_transient_update_plugins';

    # >>> get_option('_site_transient_update_themes')
    # => {#457
    #      +"last_checked": 1492656781,
    #      +"checked": [
    #        "twentyfifteen" => "1.7",
    #        "twentyseventeen" => "1.0",
    #        "twentysixteen" => "1.3",
    #      ],
    #      +"response": [
    #        "twentyseventeen" => [
    #          "theme" => "twentyseventeen",
    #          "new_version" => "1.1",
    #          "url" => "https://wordpress.org/themes/twentyseventeen/",
    #          "package" => "https://downloads.wordpress.org/theme/twentyseventeen.1.1.zip",
    #        ],
    #      ],
    #      +"translations": [],
    #  }
    public static function themes()
    {
        $result = [];
        $update = self::theme_update_check();
        foreach ($update->checked as $theme => $version) {
            $result[] = [
                'name' => $theme,
                'version' => $version
            ];
        }
        return $result;
    }

    public static function theme_updates()
    {
        $result = [];
        $update = self::theme_update_check();
        foreach ($update->response as $name => $theme) {
            $result[] = [
                'name' => $name,
                'version' => $update->checked[$name],
                'latest_version' => $theme['new_version']
            ];
        }
        return $result;
    }

    # => {#471
    #      +"last_checked": 1492656785,
    #      +"checked": [
    #        "hello-dolly/hello.php" => "1.5",
    #        "wp-obsoflo/wp-obsoflo.php" => "0.1",
    #      ],
    #      +"response": [
    #        "hello-dolly/hello.php" => {#462
    #          +"id": "3564",
    #          +"slug": "hello-dolly",
    #          +"plugin": "hello-dolly/hello.php",
    #          +"new_version": "1.6",
    #          +"url": "https://wordpress.org/plugins/hello-dolly/",
    #          +"package": "https://downloads.wordpress.org/plugin/hello-dolly.1.6.zip",
    #          +"tested": "4.6.4",
    #          +"compatibility": {#458
    #            +"scalar": {#461
    #              +"scalar": false,
    #            },
    #          },
    #        },
    #      ],
    #      +"translations": [],
    #      +"no_update": [],
    #    }
    public static function plugins()
    {
        $result = [];
        $update = self::plugin_update_check();
        foreach ($update->checked as $plugin => $version) {
            $result[] = [
                'name' => $plugin,
                'version' => $version
            ];
        }
        return $result;
    }

    public static function mu_plugin_versions()
    {
        $result = self::scan_mu_plugins_dir(WPMU_PLUGIN_DIR);
        return array_filter($result, function($plugin) {
            return isset($plugin['name'], $plugin['version']);
        });
    }

    public static function scan_mu_plugins_dir($dir, &$results = [], $depth = 0)
    {
        if ($depth == 3) {
            return $results;
        }
        $files = scandir($dir);

        // Scan the PHP files first
        $php_files = [];
        foreach ($files as $file) {
            if (strpos($file, '.php')) {
                array_push($php_files, $file);
            }
        }

        foreach ($php_files as $file) {
            $meta = get_plugin_data($dir . "/$file");

            if ($meta['Version']) {
                $results[] = [
                    'plugin' => basename($dir) . "/$file",
                    'name' => $meta['TextDomain'],
                    'version' => $meta['Version']
                ];
            }
        }

        foreach ($files as $key => $value) {
            if ($value == '.') {
                continue;
            }
            if ($value == '..') {
                continue;
            }
            $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
            if (is_dir($path)) {
                self::scan_mu_plugins_dir($path, $results, $depth + 1);
            }
        }

        return $results;
    }

    public static function plugin_updates()
    {
        $result = [];
        $update = self::plugin_update_check();
        foreach ($update->response as $name => $plugin) {
            $meta = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin->plugin);
            $result[] = [
                'name' => $plugin->slug,
                'version' => $meta['Version'],
                'latest_version' => $plugin->new_version
            ];
        }
        return $result;
    }

    public static function plugin_update_check()
    {
        global $table_prefix;
        $query =
            'SELECT option_value from ' .
            $table_prefix .
            'options WHERE option_name = "' .
            self::PLUGINS .
            '";';
        return self::unserialize_query($query);
    }

    public static function theme_update_check()
    {
        global $table_prefix;
        $query =
            'SELECT option_value from ' .
            $table_prefix .
            'options WHERE option_name = "' .
            self::THEMES .
            '";';
        return self::unserialize_query($query);
    }

    // TODO add weak user passwords
    // https://wpsecurityninja.com/top-wordpress-passwords/

    public static function weak_user_names()
    {
        global $table_prefix;
        $query =
            'SELECT user_login from ' .
            $table_prefix .
            'users WHERE user_login in ("admin", "root", "master", "test", "administrator", "Administrator", "user1", "admin", "alex", "pos", "demo", "db2admin", "Admin", "sql");';

        return array_map(function ($row) {
            return $row[0];
        }, \WPObsoflo\Database::query_all($query));
    }

    private static function unserialize_query($query)
    {
        // The query returns an array of results
        $result = \WPObsoflo\Database::query($query)[0];
        if ($result) {
            return unserialize($result);
        }
    }
}
