<div class="item">
    <?php
    $o = new wpdreamsSelectTags("show_frontend_tags", __('Show the tag selectors?', 'ajax-search-pro'), $sd['show_frontend_tags']);
    ?>
</div>
<div class="item item-flex-nogrow wd_tag_mode_dropdown wd_tag_mode_radio">
    <?php
    $o = new wpdreamsYesNo("display_all_tags_option", __('Show all tags option?', 'ajax-search-pro'), $sd['display_all_tags_option']);
    $params[$o->getName()] = $o->getData();
    $o = new wpdreamsText("all_tags_opt_text", __('text ', 'ajax-search-pro'), $sd['all_tags_opt_text']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item item-flex-nogrow wd_tag_mode_checkboxes">
    <?php
    $o = new wpdreamsYesNo("display_all_tags_check_opt", __('Show select/deselect all option?', 'ajax-search-pro'), $sd['display_all_tags_check_opt']);
    $params[$o->getName()] = $o->getData();
    $o = new wpdreamsText("all_tags_check_opt_text", __('text ', 'ajax-search-pro'), $sd['all_tags_check_opt_text']);
    $params[$o->getName()] = $o->getData();
    $o = new wpdreamsCustomSelect("all_tags_check_opt_state", __('state ', 'ajax-search-pro'), array(
        "selects" => array(
            array("option" => __('Checked', 'ajax-search-pro'), "value" => "checked"),
            array("option" => __('Unchecked', 'ajax-search-pro'), "value" => "unchecked")
        ),
        "value" => $sd['all_tags_check_opt_state']
    ));
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item wd_tag_mode_multisearch">
    <?php
    $o = new wpdreamsText("frontend_tags_placeholder", __('Placeholder text', 'ajax-search-pro'), $sd['frontend_tags_placeholder']);
    ?>
    <p class="descMsg">
        <?php echo __('Placeholder text for the multiselect search layout, in case nothing is selected.', 'ajax-search-pro'); ?>
    </p>
</div>
<div class="item item-flex-nogrow">
    <?php
    $o = new wpdreamsYesNo("frontend_tags_required", __('Required field?', 'ajax-search-pro'), $sd['frontend_tags_required']);
    $params[$o->getName()] = $o->getData();

    $o = new wpdreamsText("frontend_tags_invalid_input_text", __('required popup text', 'ajax-search-pro'), $sd['frontend_tags_invalid_input_text']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsText("frontend_tags_header", __('Tags header text', 'ajax-search-pro'), $sd['frontend_tags_header']);
    ?>
    <p class="descMsg">
        <?php echo __('Leave empty if you don\'t want to display the header.', 'ajax-search-pro'); ?>
    </p>
</div>
<div class="item wd_tag_mode_checkboxes"><?php
    $o = new wpdreamsCustomSelect("frontend_tags_logic", __('Tags logic (only used for checkboxes!)', 'ajax-search-pro'),
        array(
            'selects' => array(
                array('option' => __('At least one selected tag should match', 'ajax-search-pro'), 'value' => 'or'),
                array('option' => __('All of the selected tags should match, and unchecked are exclusions', 'ajax-search-pro'), 'value' => 'and'),
                array('option' => __('All of the selected tags should match EXACTLY, but unchecked not excluded', 'ajax-search-pro'), 'value' => 'andex')
            ),
            'value' => $sd['frontend_tags_logic']
        ));
    $params[$o->getName()] = $o->getData();
    ?>
    <p class="descMsg">
        <?php echo __('This determines the rule how the selections should be treated. Only affects the <strong>checkbox</strong> layout!', 'ajax-search-pro'); ?>
    </p>
</div>
<div class="item">
    <?php
    $o = new wpdreamsYesNo("frontend_tags_empty", __('Display posts/pages/CPT as results, which have no tags?', 'ajax-search-pro'), $sd['frontend_tags_empty']);
    ?>
    <p class="descMsg">
        <?php echo __('When turned OFF, any custom post type (post, page etc..) without tags <strong>will not be displayed</strong> as results.', 'ajax-search-pro'); ?>
    </p>
</div>