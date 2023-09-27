<?php
/* Prevent direct access */

use WPDRMS\ASP\Utils\Plugin;

defined('ABSPATH') or die("You can't access this file directly.");

foreach ( wd_asp()->front_filters->get('position', 'date') as $filter ) {
    include( Plugin::templateFilePath('filters/date/asp-date-header.php') );

    include( Plugin::templateFilePath('filters/date/asp-date-filter.php') );

    include( Plugin::templateFilePath('filters/date/asp-date-footer.php') );
}