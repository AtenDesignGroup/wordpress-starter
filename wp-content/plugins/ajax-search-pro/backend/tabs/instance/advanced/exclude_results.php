<div class="item" style="border-bottom: 0;">
    <?php
    $o = new wpdreamsYesNo("exclude_dates_on", __('Exclude Post/Page/CPT by date', 'ajax-search-pro'), $sd['exclude_dates_on']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wd_DateInterval("exclude_dates", 'posts', $sd['exclude_dates']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wd_UserSelect("exclude_content_by_users", __('Exclude or Include content by users', 'ajax-search-pro'), array(
        "value"=>$sd['exclude_content_by_users'],
        "args"=> array(
            "show_type" => 1
        )
    ));
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsSearchTags("exclude_post_tags", __('Exclude posts by tags', 'ajax-search-pro'), $sd['exclude_post_tags']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wd_TaxonomyTermSelect("exclude_by_terms", __("<span style='color: red; font-weight: bold'>Exclude</span> posts (or cpt, attachments, comments) by categories/taxonomy terms", 'ajax-search-pro'), array(
        "value"=>$sd['exclude_by_terms'],
        "args"  => array(
            "show_type" => 0,
            "op_type" => "exclude",
            "show_checkboxes" => 0,
            "show_display_mode" => 0,
            "show_more_options" => 0,
            'show_taxonomy_all' => 0,
            "built_in" => true,
            "exclude_taxonomies" => array("post_tag", "nav_menu", "link_category")
        )
    ));
    $params[$o->getName()] = $o->getData();
    ?>
    <p class="descMsg"><?php echo __('An object is excluded if matches <strong>any</strong> of the selected items.', 'ajax-search-pro'); ?></p>
</div>
<div class="item">
    <?php
    $o = new wd_TaxonomyTermSelect("include_by_terms", __("<span style='color: red; font-weight: bold;'>Include</span> posts (or cpt, attachments, comments) only from selected categories/taxonomy terms", 'ajax-search-pro'), array(
        "value"=>$sd['include_by_terms'],
        "args"  => array(
            "show_type" => 0,
            "op_type" => "include",
            "show_checkboxes" => 0,
            "show_display_mode" => 0,
            "show_more_options" => 0,
            'show_taxonomy_all' => 0,
            "built_in" => true,
            "exclude_taxonomies" => array("nav_menu", "link_category")
        )
    ));
    $params[$o->getName()] = $o->getData();
    ?>
    <p class="descMsg"><?php echo __('The exclusions from the above option <strong>still apply!</strong>', 'ajax-search-pro'); ?></p>
</div>
<div class="item">
    <?php
    $o = new wd_CPTSelect("exclude_cpt", __('Exclude posts/pages/cpt', 'ajax-search-pro'), array(
        "value"=>$sd['exclude_cpt'],
        "args"=> array(
            "show_parent_checkbox" => 1
        )
    ));
    $params[$o->getName()] = $o->getData();
    ?>
    <p class="descMsg"><?php echo __('The "Exclude direct children too?" option only works with <strong>DIRECT</strong> parent-child relationships. (1 level down)', 'ajax-search-pro'); ?></p>
</div>
<div class="item">
    <?php
    $o = new wd_TextareaExpandable("excludeposts", __('Exclude Posts/Pages/CPT by ID\'s (comma separated post ID-s)', 'ajax-search-pro'), $sd['excludeposts']);
    $params[$o->getName()] = $o->getData();
    ?>
    <p class="descMsg"><?php echo __('If you wish to exclude Posts, Pages and custom post types (like products etc..) by ID here. Comma separated list.', 'ajax-search-pro'); ?></p>
</div>
<div class="item">
    <p class="errorMsg"><?php echo __('<strong>WARNING:</strong> This option restricts Posts/Pages/CPT to the selected items only, no other inclusions will apply! <br>Exclusions still apply.', 'ajax-search-pro'); ?></p>
    <?php
    $o = new wd_CPTSelect("include_cpt", __('Include only posts/pages/cpt', 'ajax-search-pro'), array(
        "value"=>$sd['include_cpt'],
        "args"=> array(
            "show_parent_checkbox" => 0
        )
    ));
    $params[$o->getName()] = $o->getData();
    ?>
</div>