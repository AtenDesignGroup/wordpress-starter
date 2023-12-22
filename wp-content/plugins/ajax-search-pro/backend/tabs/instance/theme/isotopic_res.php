<style>
    .wpdreamsTextSmall {
        display: inline-block;
    }
</style>
<div class="item item-rlayout item-rlayout-isotopic">
    <p><?php echo __('These options are hidden, because the <span>vertical</span> results layout is selected.', 'ajax-search-pro'); ?></p>
    <p><?php echo __('You can change that under the <a href="#402" data-asp-os-highlight="resultstype" tabid="402">Layout Options -> Results layout</a> panel,
        <br>..or choose a <a href="#601" tabid="601">different theme</a> with a different pre-defined layout.', 'ajax-search-pro'); ?></p>
</div>
<div class="item"><?php
    $o = new wpdreamsCustomSelect("i_ifnoimage", __('If no image found', 'ajax-search-pro'),  array(
        'selects'=>array(
            array('option' => __('Show the default image', 'ajax-search-pro'), 'value' => 'defaultimage'),
            array('option' => __('Show the description', 'ajax-search-pro'), 'value' => 'description'),
            array('option' => __('Show the background', 'ajax-search-pro'), 'value' => 'background'),
            array('option' => __('Dont show that result', 'ajax-search-pro'), 'value' => 'removeres')
        ),
        'value'=>$sd['i_ifnoimage']
    ));
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item"><?php
    $o = new wpdreamsColorPicker("i_res_item_background", __('Result content background', 'ajax-search-pro'), $sd['i_res_item_background']);
    $params[$o->getName()] = $o->getData();
    ?>
    <p class="descMsg">
        <?php echo __('Background color under the image. Not visible by default, unless the image is opaque.', 'ajax-search-pro'); ?>
    </p>
</div>
<div class="item item-flex-nogrow item-flex-wrap wpd-isotopic-width">
    <?php
    $o = new wpdreamsTextSmall("i_item_width", __('Result width', 'ajax-search-pro'), array(
        'icon' => 'desktop',
        'value' => $sd['i_item_width']
    ));
    $params[$o->getName()] = $o->getData();
    $o = new wpdreamsTextSmall("i_item_width_tablet", '', array(
        'icon' => 'tablet',
        'value' => $sd['i_item_width_tablet']
    ));
    $params[$o->getName()] = $o->getData();
    $o = new wpdreamsTextSmall("i_item_width_phone", '', array(
        'icon' => 'phone',
        'value' => $sd['i_item_width_phone']
    ));
    $params[$o->getName()] = $o->getData();
    ?>
    <div class="descMsg item-flex-grow item-flex-100">
        <?php echo sprintf(
            __('Use with <a href="%s" target="_blank">CSS units</a> (like %s or %s or %s ..) Default: <strong>%s</strong>', 'ajax-search-pro'),
            'https://www.w3schools.com/cssref/css_units.asp', '200px', '32%', 'auto', '200px'
        ); ?><br>
        <?php echo __('The search will try to stick close to this value when filling the width of the results list.', 'ajax-search-pro'); ?>
    </div>
</div>
<div class="item item-flex-nogrow item-flex-wrap wpd-isotopic-width">
    <?php
    $o = new wpdreamsTextSmall("i_item_height", __('Result height', 'ajax-search-pro'), array(
        'icon' => 'desktop',
        'value' => $sd['i_item_height']
    ));
    $params[$o->getName()] = $o->getData();
    $o = new wpdreamsTextSmall("i_item_height_tablet", '', array(
        'icon' => 'tablet',
        'value' => $sd['i_item_height_tablet']
    ));
    $params[$o->getName()] = $o->getData();
    $o = new wpdreamsTextSmall("i_item_height_phone", '', array(
        'icon' => 'phone',
        'value' => $sd['i_item_height_phone']
    ));
    $params[$o->getName()] = $o->getData();
    ?>
    <div class="descMsg item-flex-grow item-flex-100">
        <?php echo sprintf(
            __('Use with <a href="%s" target="_blank">CSS units</a> (like %s or %s or %s ..) Default: <strong>%s</strong>', 'ajax-search-pro'),
            'https://www.w3schools.com/cssref/css_units.asp', '200px', '32%', 'auto', '200px'
        ); ?><br>
        <?php echo __('For % values, it will be relative to the results container width, not container height - as the container height is dynamic.', 'ajax-search-pro'); ?>
    </div>
</div>
<div class="item"><?php
    $o = new wpdreamsTextSmall("i_item_margin", __('Result margin space', 'ajax-search-pro'), $sd['i_item_margin']);
    $params[$o->getName()] = $o->getData();
    ?>px
    <p class="descMsg">
        <?php echo __('Margin (gutter) between the items on the isotope grid.', 'ajax-search-pro'); ?>
    </p>
</div>
<div class="item"><?php
    $o = new wpdreamsColorPicker("i_res_item_content_background", __('Result content/title background', 'ajax-search-pro'), $sd['i_res_item_content_background']);
    $params[$o->getName()] = $o->getData();
    ?>
    <p class="descMsg">
        <?php echo __('The background color of the title/content overlay.', 'ajax-search-pro'); ?>
    </p>
</div>
<div class="item">
    <?php
    $o = new wpdreamsImageRadio("i_res_magnifierimage", __('Hover background icon', 'ajax-search-pro'), array(
            'images'  => array(
                "/ajax-search-pro/img/svg/magnifiers/magn1.svg",
                "/ajax-search-pro/img/svg/magnifiers/magn2.svg",
                "/ajax-search-pro/img/svg/magnifiers/magn3.svg",
                "/ajax-search-pro/img/svg/magnifiers/magn4.svg",
                "/ajax-search-pro/img/svg/magnifiers/magn5.svg",
                "/ajax-search-pro/img/svg/magnifiers/magn6.svg",
                "/ajax-search-pro/img/svg/magnifiers/magn7.svg",
                "/ajax-search-pro/img/svg/arrows/arrow1.svg",
                "/ajax-search-pro/img/svg/arrows/arrow2.svg",
                "/ajax-search-pro/img/svg/arrows/arrow3.svg",
                "/ajax-search-pro/img/svg/arrows/arrow4.svg",
                "/ajax-search-pro/img/svg/arrows/arrow5.svg",
                "/ajax-search-pro/img/svg/arrows/arrow6.svg",
                "/ajax-search-pro/img/svg/arrows/arrow7.svg",
                "/ajax-search-pro/img/svg/arrows/arrow8.svg",
                "/ajax-search-pro/img/svg/arrows/arrow9.svg",
                "/ajax-search-pro/img/svg/arrows/arrow10.svg",
                "/ajax-search-pro/img/svg/arrows/arrow11.svg",
                "/ajax-search-pro/img/svg/arrows/arrow12.svg",
                "/ajax-search-pro/img/svg/arrows/arrow13.svg",
                "/ajax-search-pro/img/svg/arrows/arrow14.svg",
                "/ajax-search-pro/img/svg/arrows/arrow15.svg",
                "/ajax-search-pro/img/svg/arrows/arrow16.svg"
            ),
            'value'=> $sd['i_res_magnifierimage']
        )
    );
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item"><?php
    $o = new wpdreamsUpload("i_res_custom_magnifierimage", __('Custom hover background icon', 'ajax-search-pro'), $sd['i_res_custom_magnifierimage']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsYesNo("i_overlay", __('Show overlay on mouseover?', 'ajax-search-pro'), $sd['i_overlay']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsYesNo("i_overlay_blur", __('Blur overlay image on mouseover?', 'ajax-search-pro'), $sd['i_overlay_blur']);
    $params[$o->getName()] = $o->getData();
    ?>
    <p class="descMsg">
        <?php echo __('This might not work on some browsers.', 'ajax-search-pro'); ?>
    </p>
</div>
<div class="item">
    <?php
    $o = new wpdreamsYesNo("i_hide_content", __('Hide the content when overlay is active?', 'ajax-search-pro'), $sd['i_hide_content']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsAnimations("i_animation", __('Display animation', 'ajax-search-pro'), $sd['i_animation']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item"><?php
    $o = new wpdreamsColorPicker("i_res_container_bg", __('Result box background', 'ajax-search-pro'), $sd['i_res_container_bg']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>