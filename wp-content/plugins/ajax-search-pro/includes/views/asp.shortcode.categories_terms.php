<?php
/* Prevent direct access */

use WPDRMS\ASP\Utils\Plugin;

defined('ABSPATH') or die("You can't access this file directly.");

//$tax_term_filters = asp_parse_tax_term_filters($style);

foreach ( wd_asp()->front_filters->get('position', 'taxonomy') as $k => $filter ) {
    // $filter variable is an instance of aspTaxFilter object
    // $filter->get() will return the array of filter objects (of stdClass)

    // Some local variables for ease of use within the theme
    $taxonomy = $filter->data['taxonomy'];
    $ch_class = $filter->isMixed() ? 'terms' : preg_replace("/[^a-zA-Z0-9]+/", "", $taxonomy);

    include(Plugin::templateFilePath('filters/taxonomy/asp-tax-header.php'));
    switch ($filter->display_mode) {
        case 'checkbox':
        case 'checkboxes':
            include(Plugin::templateFilePath('filters/taxonomy/asp-tax-checkboxes.php'));
            break;
        case 'dropdown':
            include(Plugin::templateFilePath('filters/taxonomy/asp-tax-dropdown.php'));
            break;
        case 'dropdownsearch':
            include(Plugin::templateFilePath('filters/taxonomy/asp-tax-dropdownsearch.php'));
            break;
        case 'multisearch':
            include(Plugin::templateFilePath('filters/taxonomy/asp-tax-multisearch.php'));
            break;
        case 'radio':
            include(Plugin::templateFilePath('filters/taxonomy/asp-tax-radio.php'));
            break;
        default:
            include(Plugin::templateFilePath('filters/taxonomy/asp-tax-checkboxes.php'));
            break;
    }

    include(Plugin::templateFilePath('filters/taxonomy/asp-tax-footer.php'));
}