<p class='infoMsg'>
    <?php echo __('This css will be added into the site header as embedded CSS', 'ajax-search-pro'); ?>
</p>
<div class="item">
    <?php
    $option_name = "custom_css";
    $option_desc = __('Custom CSS', 'ajax-search-pro');
    $o = new wd_Textarea_B64($option_name, $option_desc, $sd[$option_name]);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item" style="display:none !important;">
    <?php
    $option_name = "custom_css_h";
    $option_desc = "";
    $o = new wd_Textarea_B64($option_name, $option_desc, $sd[$option_name]);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsText("res_z_index", __('Results box z-index', 'ajax-search-pro'), $sd['res_z_index']);
    $params[$o->getName()] = $o->getData();
    ?>
    <p class="descMsg">
        <?php echo __('In case you have some other elements floating above/below the results, you can adjust it\'s position with the z-index.', 'ajax-search-pro'); ?>
    </p>
</div>
<div class="item">
    <?php
    $o = new wpdreamsText("sett_z_index", __('Settings drop-down box z-index', 'ajax-search-pro'), $sd['sett_z_index']);
    $params[$o->getName()] = $o->getData();
    ?>
    <p class="descMsg">
        <?php echo __('In case you have some other elements floating above/below the settings drop-down, you can adjust it\'s position with the z-index.', 'ajax-search-pro'); ?>
    </p>
</div>