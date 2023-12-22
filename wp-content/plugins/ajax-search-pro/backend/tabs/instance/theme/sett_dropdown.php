<div class="item">
    <?php
    $o = new wpdreamsCustomSelect("settingsimagepos", __('Settings icon position', 'ajax-search-pro'), array(
        'selects'=>array(
            array('option' => __('Left', 'ajax-search-pro'), 'value' => 'left'),
            array('option' => __('Right', 'ajax-search-pro'), 'value' => 'right')
        ),
        'value'=>$sd['settingsimagepos']
    ));
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsImageRadio("settingsimage", __('Settings icon', 'ajax-search-pro'), array(
            'images'  => array(
                "/ajax-search-pro/img/svg/menu/menu1.svg",
                "/ajax-search-pro/img/svg/menu/menu2.svg",
                "/ajax-search-pro/img/svg/menu/menu3.svg",
                "/ajax-search-pro/img/svg/menu/menu4.svg",
                "/ajax-search-pro/img/svg/menu/menu5.svg",
                "/ajax-search-pro/img/svg/menu/menu6.svg",
                "/ajax-search-pro/img/svg/menu/menu7.svg",
                "/ajax-search-pro/img/svg/menu/menu8.svg",
                "/ajax-search-pro/img/svg/menu/menu9.svg",
                "/ajax-search-pro/img/svg/menu/menu10.svg",
                "/ajax-search-pro/img/svg/arrows-down/arrow1.svg",
                "/ajax-search-pro/img/svg/arrows-down/arrow2.svg",
                "/ajax-search-pro/img/svg/arrows-down/arrow3.svg",
                "/ajax-search-pro/img/svg/arrows-down/arrow4.svg",
                "/ajax-search-pro/img/svg/arrows-down/arrow5.svg",
                "/ajax-search-pro/img/svg/arrows-down/arrow6.svg",
                "/ajax-search-pro/img/svg/control-panel/cp1.svg",
                "/ajax-search-pro/img/svg/control-panel/cp2.svg",
                "/ajax-search-pro/img/svg/control-panel/cp3.svg",
                "/ajax-search-pro/img/svg/control-panel/cp4.svg",
                "/ajax-search-pro/img/svg/control-panel/cp5.svg",
                "/ajax-search-pro/img/svg/control-panel/cp6.svg",
                "/ajax-search-pro/img/svg/control-panel/cp7.svg",
                "/ajax-search-pro/img/svg/control-panel/cp8.svg",
                "/ajax-search-pro/img/svg/control-panel/cp9.svg",
                "/ajax-search-pro/img/svg/control-panel/cp10.svg",
                "/ajax-search-pro/img/svg/control-panel/cp11.svg",
                "/ajax-search-pro/img/svg/control-panel/cp12.svg",
                "/ajax-search-pro/img/svg/control-panel/cp13.svg",
                "/ajax-search-pro/img/svg/control-panel/cp14.svg",
                "/ajax-search-pro/img/svg/control-panel/cp15.svg",
                "/ajax-search-pro/img/svg/control-panel/cp16.svg"
            ),
            'value'=> $sd['settingsimage']
        )
    );
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item"><?php
    $o = new wpdreamsColorPicker("settingsimage_color", __('Settings icon color', 'ajax-search-pro'), $sd['settingsimage_color']);
    $params[$o->getName()] = $o->getData();
    ?>
    <p class="descMsg">
        <?php echo __('Only works with the built-in icons, or if the custom icon type is SVG (.svg file)', 'ajax-search-pro'); ?>
    </p>
</div>
<div class="item"><?php
    $o = new wpdreamsUpload("settingsimage_custom", __('Custom settings icon', 'ajax-search-pro'), $sd['settingsimage_custom']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<fieldset>
    <legend><?php echo __('Custom scrollbar', 'ajax-search-pro') ?></legend>
    <div class="item item-flex-nogrow">
    <?php
    $o = new wpdreamsYesNo('settings_overflow_autohide', __('Auto hide the scrollbar?', 'ajax-search-pro'), $sd['settings_overflow_autohide']);
    $params[$o->getName()] = $o->getData();

    $o = new wpdreamsGradient("settings_overflow_color", __('Scrollbar color', 'ajax-search-pro'), $sd['settings_overflow_color']);
    $params[$o->getName()] = $o->getData();
    ?>
    </div>
</fieldset>
<div class="item"><?php
    $o = new wpdreamsGradient("settingsbackground", __('Settings-icon background color', 'ajax-search-pro'), $sd['settingsbackground']);
    $params[$o->getName()] = $o->getData();
    ?></div>
<div class="item">
    <?php
    $o = new wpdreamsBorder("settingsbackgroundborder", __('Settings-icon border', 'ajax-search-pro'), $sd['settingsbackgroundborder']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsBoxShadow("settingsboxshadow", __('Settings-icon box-shadow', 'ajax-search-pro'), $sd['settingsboxshadow']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item"><?php
    $o = new wpdreamsGradient("settingsdropbackground", __('Settings drop-down background color', 'ajax-search-pro'), $sd['settingsdropbackground']);
    $params[$o->getName()] = $o->getData();
    ?></div>
<div class="item">
    <?php
    $o = new wpdreamsBoxShadow("settingsdropboxshadow", __('Settings drop-down box-shadow', 'ajax-search-pro'), $sd['settingsdropboxshadow']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item"><?php
    $o = new wpdreamsFontComplete("settingsdropfont", __('Settings drop down font', 'ajax-search-pro'), $sd['settingsdropfont']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item"><?php
    $o = new wpdreamsFontComplete("exsearchincategoriestextfont", __('Settings box header text font', 'ajax-search-pro'), $sd['exsearchincategoriestextfont']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item"><?php
    $o = new wpdreamsColorPicker("settingsdroptickcolor",__("Settings drop-down checkbox tick color", 'ajax-search-pro'), $sd['settingsdroptickcolor']);
    $params[$o->getName()] = $o->getData();
    ?></div>
<div class="item"><?php
    $o = new wpdreamsGradient("settingsdroptickbggradient", __('Settings drop-down checkbox background', 'ajax-search-pro'), $sd['settingsdroptickbggradient']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>