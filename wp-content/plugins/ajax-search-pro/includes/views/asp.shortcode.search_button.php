<?php

use WPDRMS\ASP\Utils\Plugin;

foreach (wd_asp()->front_filters->get('position', 'button') as $k => $filter ) {
    include(Plugin::templateFilePath('filters/button/asp-button-header.php'));

    include(Plugin::templateFilePath('filters/button/asp-button-filter.php'));

    include(Plugin::templateFilePath('filters/button/asp-button-footer.php'));
}

return;