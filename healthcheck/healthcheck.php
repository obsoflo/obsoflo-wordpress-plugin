<?php

require_once 'src/Status.php';

add_action('rest_api_init', function () {
    register_rest_route('__healthcheck/v1', 'status', [
        'methods' => 'POST',
        'callback' => 'wp_obsoflo_status'
    ]);
});

function wp_obsoflo_status()
{
    if ($_POST['token'] !== WP_OBSOFLO_TOKEN) {
        return new WP_REST_Response(null, 403);
    }

    return WPObsoflo\Status::to_json();
}
