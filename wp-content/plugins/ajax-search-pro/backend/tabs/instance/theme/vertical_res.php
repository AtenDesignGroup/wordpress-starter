<div class="item item-rlayout item-rlayout-vertical">
    <p><?php echo __('These options are hidden, because the <span>vertical</span> results layout is selected.', 'ajax-search-pro'); ?></p>
    <p><?php echo __('You can change that under the <a href="#402" data-asp-os-highlight="resultstype" tabid="402">Layout Options -> Results layout</a> panel,
        <br>..or choose a <a href="#601" tabid="601">different theme</a> with a different pre-defined layout.', 'ajax-search-pro'); ?></p>
</div>
<div class="item"><?php
    $o = new wpdreamsTextSmall("resultitemheight", __('One result item height', 'ajax-search-pro'), $sd['resultitemheight']);
    $params[$o->getName()] = $o->getData();
    ?>
    <p class="descMsg">
    <?php echo sprintf(
        __('Use with <a href="%s" target="_blank">CSS units</a> (like %s or %s or %s ..) Default: <strong>%s</strong>', 'ajax-search-pro'),
        'https://www.w3schools.com/cssref/css_units.asp', '70px', '12vh', 'auto', 'auto'
    ); ?>
    </p>
</div>
<div class="item">
    <?php
    $o = new wpdreamsYesNo('v_res_show_scrollbar', __('Display the results scrollbar?', 'ajax-search-pro'), $sd['v_res_show_scrollbar']);
    $params[$o->getName()] = $o->getData();
    ?>
    <p class="descMsg">
        <?php echo __('When turned OFF, the results box height will be unlimited.', 'ajax-search-pro'); ?>
    </p>
</div>
<fieldset class="asp_v_res_scroll_dependent">
    <div class="item item-flex-nogrow item-flex-wrap">
        <?php
        $o = new wpdreamsTextSmall("v_res_max_height", __('Result box maximum height', 'ajax-search-pro'), array(
            'icon' => 'desktop',
            'value' => $sd['v_res_max_height']
        ));
        $params[$o->getName()] = $o->getData();
        $o = new wpdreamsTextSmall("v_res_max_height_tablet", '', array(
            'icon' => 'tablet',
            'value' => $sd['v_res_max_height_tablet']
        ));
        $params[$o->getName()] = $o->getData();
        $o = new wpdreamsTextSmall("v_res_max_height_phone", '', array(
            'icon' => 'phone',
            'value' => $sd['v_res_max_height_phone']
        ));
        $params[$o->getName()] = $o->getData();
        ?>
        <div class="descMsg item-flex-grow item-flex-100">
        <?php echo __('If this value is reached, the scrollbar will definitely trigger.', 'ajax-search-pro'); ?>
        <?php echo ' '. sprintf(
            __('Use with <a href="%s" target="_blank">CSS units</a> (like %s or %s or %s ..) Default: <strong>%s</strong>', 'ajax-search-pro'),
            'https://www.w3schools.com/cssref/css_units.asp', '240px', '30vh', 'auto', 'none'
        ); ?>
        </div>
    </div>
    <div class="item"><?php
        $o = new wpdreamsTextSmall("itemscount", __('Results box viewport size (in item numbers)', 'ajax-search-pro'), $sd['itemscount']);
        $params[$o->getName()] = $o->getData();
        ?>
        <p class="descMsg">
            <?php echo __('Used to calculate the results scroll box height. Result box height = (this option) x (average item height)', 'ajax-search-pro'); ?>
        </p>
    </div>
</fieldset>
<fieldset class="asp_v_res_scroll_dependent">
    <legend><?php echo __('Custom scrollbar', 'ajax-search-pro') ?></legend>
    <div class="item item-flex-nogrow">
    <?php
    $o = new wpdreamsYesNo('v_res_overflow_autohide', __('Auto hide the scrollbar?', 'ajax-search-pro'), $sd['v_res_overflow_autohide']);
    $params[$o->getName()] = $o->getData();

    $o = new wpdreamsGradient("v_res_overflow_color", __('Scrollbar color', 'ajax-search-pro'), $sd['v_res_overflow_color']);
    $params[$o->getName()] = $o->getData();
    ?>
    </div>
</fieldset>
<fieldset>
    <legend><?php echo __('Columns', 'ajax-search-pro') ?></legend>
    <div class="item item-flex-nogrow item-flex-wrap">
    <?php
    $o = new wpdreamsCustomSelect("v_res_column_count", __('Number of result columns', 'ajax-search-pro'), array(
        'selects'=>array(
            array('option' => '1', 'value' => 1),
            array('option' => '2', 'value' => 2),
            array('option' => '3', 'value' => 3),
            array('option' => '4', 'value' => 4),
            array('option' => '5', 'value' => 5),
            array('option' => '6', 'value' => 6),
            array('option' => '7', 'value' => 7),
            array('option' => '8', 'value' => 8)
        ),
        'value'=>$sd['v_res_column_count']
    ));
    $params[$o->getName()] = $o->getData();

    $o = new wpdreamsTextSmall("v_res_column_min_width", __('Column minimum width (px)', 'ajax-search-pro'), array(
        'icon' => 'desktop',
        'value' => $sd['v_res_column_min_width']
    ));
    $params[$o->getName()] = $o->getData();
    $o = new wpdreamsTextSmall("v_res_column_min_width_tablet", '', array(
        'icon' => 'tablet',
        'value' => $sd['v_res_column_min_width_tablet']
    ));
    $params[$o->getName()] = $o->getData();
    $o = new wpdreamsTextSmall("v_res_column_min_width_phone", '', array(
        'icon' => 'phone',
        'value' => $sd['v_res_column_min_width_phone']
    ));
    $params[$o->getName()] = $o->getData();
    ?>
    <div class="descMsg item-flex-grow item-flex-100">
    <?php echo ' '. sprintf(
        __('Use with <a href="%s" target="_blank">CSS units</a> (like %s or %s or %s ..) Default: <strong>%s</strong>', 'ajax-search-pro'),
        'https://www.w3schools.com/cssref/css_units.asp', '200px', '30vw', '30%', '200px'
    ); ?>
    </div>
    </div>
</fieldset>
<div class="item item-flex-nogrow">
    <?php
    $o = new wpdreamsTextSmall("image_width", __("Image width (px)", 'ajax-search-pro'), $sd["image_width"]);
    $params[$o->getName()] = $o->getData();

    $o = new wpdreamsTextSmall("image_height", __("Image height (px)", 'ajax-search-pro'), $sd["image_height"]);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsBorder("resultsborder", __('Results box border', 'ajax-search-pro'), $sd['resultsborder']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsBoxShadow("resultshadow", __('Results box Shadow', 'ajax-search-pro'), $sd['resultshadow']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item"><?php
    $o = new wpdreamsColorPicker("resultsbackground", __('Results box background color', 'ajax-search-pro'), $sd['resultsbackground']);
    $params[$o->getName()] = $o->getData();
    ?></div>
<div class="item"><?php
    $o = new wpdreamsColorPicker("resultscontainerbackground", __('Result items container box background color', 'ajax-search-pro'), $sd['resultscontainerbackground']);
    $params[$o->getName()] = $o->getData();
    ?></div>
<div class="item"><?php
    $o = new wpdreamsGradient("vresulthbg", __('Result item mouse hover box background color', 'ajax-search-pro'), $sd['vresulthbg']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item"><?php
    $o = new wpdreamsColorPicker("spacercolor", __('Spacer color between results', 'ajax-search-pro'), $sd['spacercolor']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>