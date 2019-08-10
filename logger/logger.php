<?php

/*
* Obsoflo Logger
*/

define('LOG_LEVEL_DEBUG',  'DEBUG');
define('LOG_LEVEL_NOTICE', 'NOTICE');
define('LOG_LEVEL_INFO',   'INFO');
define('LOG_LEVEL_WARN',   'WARN');
define('LOG_LEVEL_ERROR',  'ERROR');
define('LOG_LEVEL_FATAL',  'FATAL');

define('OBSOFLO_DESTINATION_HOST', 'logs.obsoflo.com');
define('OBSOFLO_DESTINATION_PORT', 514);

class OLogger
{
    private static $socket = null;
    private static $level_debug  = LOG_LEVEL_DEBUG;
    private static $level_notice = LOG_LEVEL_NOTICE;
    private static $level_info   = LOG_LEVEL_INFO;
    private static $level_warn   = LOG_LEVEL_WARN;
    private static $level_error  = LOG_LEVEL_ERROR;
    private static $level_fatal  = LOG_LEVEL_FATAL;
    public static $levels = [
        LOG_LEVEL_DEBUG  => 0,
        LOG_LEVEL_NOTICE => 1,
        LOG_LEVEL_INFO   => 2,
        LOG_LEVEL_WARN   => 3,
        LOG_LEVEL_ERROR  => 4,
        LOG_LEVEL_FATAL  => 5
    ];

    public static function debug($message, $data = [])
    {
        self::log($message, self::$level_debug, $data);
    }

    public static function notice($message, $data = [])
    {
        self::log($message, self::$level_notice, $data);
    }

    public static function info($message, $data = [])
    {
        self::log($message, self::$level_info, $data);
    }

    public static function warn($message, $data = [])
    {
        self::log($message, self::$level_warn, $data);
    }

    public static function error($message, $data = [])
    {
        self::log($message, self::$level_error, $data);
    }

    public static function fatal($message, $data = [])
    {
        self::log($message, self::$level_fatal, $data);
    }

    // Handle shutdown callbacks
    public static function shutdown_handler()
    {
        $last_error = error_get_last();
        $id = $last_error['type'];
        switch ($id) {
            case E_ERROR:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_PARSE:
                self::error_handler($id, $last_error['message'], $last_error['file'], $last_error['line'], []);
        }
    }

    /**
     * Handle error logging to Obsoflo
     *
     * @param int    $id      Error number
     * @param string $message Error message
     * @param string $file    Error file
     * @param int    $line    Error line
     * @param array  $context Error context
     */
    public static function error_handler($id, $message, $file, $line, $context)
    {
        $data = self::build_message_tags([
            'file' => $file,
            'line' => $line
        ]);

        switch ($id) {
            case E_ERROR:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_PARSE:
                self::fatal($message, $data);
                break;
            case E_USER_ERROR:
            case E_RECOVERABLE_ERROR:
                self::error($message, $data);
                break;
            case E_WARNING:
            case E_CORE_WARNING:
            case E_COMPILE_WARNING:
            case E_USER_WARNING:
            case E_NOTICE:
            case E_USER_NOTICE:
                self::notice($message, $data);
                break;
            default:
                self::debug($message, $data);
        }
    }

    private static function log($message, $level, $data = [])
    {
        $data = self::build_message_tags($data);
        $defined_level = self::$levels[strtoupper(NOTICE)]

        if (defined('LOGGER_LEVEL')) {
            $defined_level = self::$levels[strtoupper(LOGGER_LEVEL)];
        }

        $current_level = self::$levels[strtoupper($level)];
        if ($current_level >= $defined_level) {
            self::send($message, $level, $data);
            return;
        }
    }

    public static function send($message, $level, $data = [])
    {

        $host = OBSOFLO_DESTINATION_HOST;
        $port = OBSOFLO_DESTINATION_PORT;
        $sender =
            'wp-' .
            parse_url(
                is_multisite() ? network_site_url() : site_url(),
                PHP_URL_HOST
            );

        $tags = [ 'version=' . APP_REF ];
        foreach ($data as $key => $value) {
            array_push(
                $tags,
                $key . '=' . (is_array($value) ? json_encode($value) : $value)
            );
        }
        $tags = implode(' ', $tags);

        $syslog_message = '<22>1 ' .  date_i18n('Y-m-d\TH:i:sP') .  ' ' .  $sender .  ' wordpress-' . APP_ENV . ' - - -';

        if ($level) {
            $syslog_message .= ' [' . trim($level) . ']';
        }

        $syslog_message .= ' ' . $message;

        if ($tags) {
            $syslog_message .= ' ' . $tags;
        }

        if (!self::$socket) {
            self::$socket = @socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);

            @socket_connect(self::$socket, $host, $port);
        }

        // Drop logs from local development
        if (
            strpos($sender, 'local.') !== false ||
            strpos($sender, 'localhost') !== false
        ) {
            return;
        }

        $result = socket_send(
            self::$socket,
            $syslog_message,
            strlen($syslog_message),
            0
        );

        $success = false;

        if (false !== $result) {
            $success = true;
        }

        return $success;
    }

    /**
     * Get page info
     *
     * @param array $page_info
     *
     * @return array
     */
    private static function build_message_tags($page_info = [])
    {
        // Setup URL
        $page_info['url'] = 'http://';

        if (is_ssl()) {
            $page_info['url'] = 'https://';
        }

        $page_info['url'] .= $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

        $page_info['url'] = explode('?', $page_info['url']);
        $page_info['url'] = $page_info['url'][0];
        $page_info['url'] = explode('#', $page_info['url']);
        $page_info['url'] = $page_info['url'][0];

        $page_info['method'] = $_SERVER['REQUEST_METHOD'];

        if (!empty($_GET)) {
            $page_info['params'] = $_GET;
        }

        if (!empty($_POST)) {
            $page_info['params'] = $_POST;
        }

        $page_info['ajax'] = defined('DOING_AJAX') && DOING_AJAX;
        $page_info['cron'] = defined('DOING_CRON') && DOING_CRON;

        // Remove potentially sensitive information from page info
        foreach(['password', 'pwd', 'token'] as $secret) {
            if (isset($page_info['params'][$secret])) {
                unset($page_info['params'][$secret]);
            }
        }

        return array_filter($page_info, function ($v) {
            return $v;
        });
    }
}

// Register error callbacks
register_shutdown_function(['Logger', 'shutdown_handler']);
set_error_handler(['Logger', 'error_handler'], E_ALL);
