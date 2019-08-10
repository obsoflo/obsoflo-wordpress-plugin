<?php
namespace WPObsoflo\wordpress_version;

function check()
{
    global $wp_version;
    return ['wordpress_version', ['version' => $wp_version]];
}
