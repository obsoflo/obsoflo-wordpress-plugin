<?php
namespace WPObsoflo\wordpress_file_editor_on;

function check()
{
    if (!(defined('DISALLOW_FILE_EDIT') && DISALLOW_FILE_EDIT)) {
        return ['wordpress_file_editor_on'];
    }

    // Otherwise the check returns null and the metric will not
    // report
}
