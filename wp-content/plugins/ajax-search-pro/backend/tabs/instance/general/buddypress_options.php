<div class="item">
    <?php
    $o = new wpdreamsYesNo("search_in_bp_activities", __('Search in buddypress activities?', 'ajax-search-pro'),
        $sd['search_in_bp_activities']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsYesNo("search_in_bp_groups", __('Search in buddypress groups?', 'ajax-search-pro'),
        $sd['search_in_bp_groups']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsYesNo("search_in_bp_groups_public", __('Search in public groups?', 'ajax-search-pro'),
        $sd['search_in_bp_groups_public']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsYesNo("search_in_bp_groups_private", __('Search in private groups?', 'ajax-search-pro'),
        $sd['search_in_bp_groups_private']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsYesNo("search_in_bp_groups_hidden", __('Search in hidden groups?', 'ajax-search-pro'),
        $sd['search_in_bp_groups_hidden']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
