<fieldset>
    <legend>
        <?php echo __('General', 'ajax-search-pro'); ?>
        <span class="asp_legend_docs">
            <a target="_blank" href="https://documentation.ajaxsearchpro.com/frontend-search-settings/layout-and-position"><span class="fa fa-book"></span>
                <?php echo __('Layout & Position', 'ajax-search-pro'); ?>
            </a>
        </span>
    </legend>
    <div class="item">
        <?php
        $o = new wpdreamsYesNo("show_frontend_search_settings", __('Show search settings switch on the frontend?', 'ajax-search-pro'), $sd['show_frontend_search_settings']);
        $params[$o->getName()] = $o->getData();
        ?>
        <p class="descMsg"><?php echo __('This will hide the switch icon, so the user can\'t open/close the settings.', 'ajax-search-pro'); ?></p>
    </div>
    <div class="item">
        <?php
        $o = new wpdreamsYesNo("frontend_search_settings_visible", __('Set the search settings to visible by default?', 'ajax-search-pro'), $sd['frontend_search_settings_visible']);
        $params[$o->getName()] = $o->getData();
        ?>
        <p class="descMsg"><?php echo __('If set to Yes, then the settings will be visible/opened by default.', 'ajax-search-pro'); ?></p>
    </div>
    <div class="item item-flex-nogrow item-flex-wrap">
        <?php
        $o = new wpdreamsCustomSelect("frontend_search_settings_position", __('Search settings position', 'ajax-search-pro'), array(
            'selects'=>array(
                array('option' => __('Hovering (default)', 'ajax-search-pro'), 'value' => 'hover'),
                array('option' => __('Block or custom', 'ajax-search-pro'), 'value' => 'block')
            ),
            'value'=>$sd['frontend_search_settings_position']
        ));
        $params[$o->getName()] = $o->getData();

        $o = new wpdreamsCustomSelect("fss_hover_columns", __(' max. columns ', 'ajax-search-pro'), array(
            'selects'=>array(
                array("option"=>"1", "value" => 1),
                array("option"=>"2", "value" => 2),
                array("option"=>"3", "value" => 3),
                array("option"=>"4", "value" => 4),
                array("option"=>"5", "value" => 5),
                array("option"=>"6", "value" => 6)
            ),
            'value'=>$sd['fss_hover_columns']
        ));
        $params[$o->getName()] = $o->getData();

        $o = new wpdreamsCustomSelect("fss_block_columns", __(' max. columns ', 'ajax-search-pro'), array(
            'selects'=>array(
                array("option"=>"Auto", "value" => "auto"),
                array("option"=>"1", "value" => 1),
                array("option"=>"2", "value" => 2),
                array("option"=>"3", "value" => 3),
                array("option"=>"4", "value" => 4),
                array("option"=>"5", "value" => 5),
                array("option"=>"6", "value" => 6)
            ),
            'value'=>$sd['fss_block_columns']
        ));
        $params[$o->getName()] = $o->getData();
        ?>
        <div class="descMsg item-flex-grow item-flex-100">
            <?php echo __('The position is automatically set to Block if you use the settings shortcode.<br><strong>Columns WRAP</strong> if they reach the edge of the screen, or container element!', 'ajax-search-pro'); ?>
        </div>
    </div>
    <div class="item">
        <?php
        $o = new wpdreamsYesNo("fss_hide_on_results", __('Hide the settings when the results list show up?', 'ajax-search-pro'), $sd['fss_hide_on_results']);
        $params[$o->getName()] = $o->getData();
        ?>
        <p class="descMsg">
            <?php echo __('This will hide the settings (hover mode only), when the result list comes on screen', 'ajax-search-pro'); ?>
        </p>
    </div>
    <div class="item">
        <?php
        $o = new wd_imageRadio("fss_column_layout", __('Column layout', 'ajax-search-pro'), array(
            'images' => array(
                'flex' => "/ajax-search-pro/backend/settings/assets/img/fss_flex.jpg",
                'column' => "/ajax-search-pro/backend/settings/assets/img/fss_column.jpg",
                'masonry' => "/ajax-search-pro/backend/settings/assets/img/fss_masonry.jpg"
            ),
            'value' => $sd['fss_column_layout']
        ));
        $params[$o->getName()] = $o->getData();
        ?>
    </div>
    <div class="item">
        <?php
        $o = new wpdreamsTextSmall("fss_column_width", __('Column width (in pixels)', 'ajax-search-pro'), $sd['fss_column_width']);
        $params[$o->getName()] = $o->getData();
        ?>px
        <p class="descMsg">
            <?php echo __('Only numeric value please.', 'ajax-search-pro'); ?>
        </p>
    </div>
    <div class="item">
        <?php
        $o = new wpdreamsTextSmall("settings_boxes_height", __('Filter boxes max-height each', 'ajax-search-pro'), $sd['settings_boxes_height']);
        $params[$o->getName()] = $o->getData();
        ?>
        <p class="descMsg">
            <?php echo __('Height of each filter box within the search settings drop-down.', 'ajax-search-pro'); ?>
            <?php echo ' '. sprintf(
                __('Use with <a href="%s" target="_blank">CSS units</a> (like %s or %s or %s ..) Default: <strong>%s</strong>', 'ajax-search-pro'),
                'https://www.w3schools.com/cssref/css_units.asp', '220px', '30vw', 'auto', '220px'
            ); ?>
        </p>
    </div>
    <div class="item">
        <label class="shortcode"><?php echo __('Custom Settings position shortcode:', 'ajax-search-pro'); ?></label>
        <input type="text" class="quick_shortcode" value="[wpdreams_asp_settings id=<?php echo $search['id']; ?>]" readonly="readonly" />
    </div>
</fieldset>