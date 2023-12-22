<?php
/* Prevent direct access */

use WPDRMS\ASP\Utils\Plugin;

defined('ABSPATH') or die("You can't access this file directly.");

foreach ( wd_asp()->front_filters->get('position', 'generic') as $filter ) {
    include( Plugin::templateFilePath('filters/generic/asp-generic-header.php') );

    switch ($filter->display_mode) {
        case 'checkboxes':
            include(Plugin::templateFilePath('filters/generic/asp-generic-checkboxes.php'));
            break;
        case 'radio':
            include(Plugin::templateFilePath('filters/generic/asp-generic-radio.php'));
            break;
        default:
            include(Plugin::templateFilePath('filters/generic/asp-generic-dropdown.php'));
            break;
    }

    include(Plugin::templateFilePath('filters/generic/asp-generic-footer.php'));
}