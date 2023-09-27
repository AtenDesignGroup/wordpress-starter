<?php
/* Prevent direct access */

use WPDRMS\ASP\Utils\Plugin;

defined('ABSPATH') or die("You can't access this file directly.");

$hidden_types = array();
$displayed_custom_types = array();

foreach ( wd_asp()->front_filters->get('position', 'post_type') as $filter ) {
    include( Plugin::templateFilePath('filters/post_type/asp-post-type-header.php') );

    switch ( $filter->display_mode ) {
        case 'checkboxes':
            include(Plugin::templateFilePath('filters/post_type/asp-post-type-checkboxes.php'));
            break;
        case 'radio':
            include(Plugin::templateFilePath('filters/post_type/asp-post-type-radio.php'));
            break;
        default:
            include(Plugin::templateFilePath('filters/post_type/asp-post-type-dropdown.php'));
            break;
    }

    foreach ( $filter->get() as $item ) {
        $displayed_custom_types[] = $item->value;
    }

    include(Plugin::templateFilePath('filters/post_type/asp-post-type-footer.php'));
}