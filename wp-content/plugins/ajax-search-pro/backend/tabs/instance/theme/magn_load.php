<div class="item">
    <?php
    $o = new wpdreamsCustomSelect("magnifier_position", __('Magnifier position', 'ajax-search-pro'), array(
        'selects'=>array(
            array('option' => __('Left', 'ajax-search-pro'), 'value' => 'left'),
            array('option' => __('Right', 'ajax-search-pro'), 'value' => 'right')
        ),
        'value'=>$sd['magnifier_position']
    ));
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsImageRadio("magnifierimage", __('Magnifier image', 'ajax-search-pro'), array(
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
            'value'=> $sd['magnifierimage']
        )
    );
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item"><?php
    $o = new wpdreamsColorPicker("magnifierimage_color", __('Magnifier icon color', 'ajax-search-pro'), $sd['magnifierimage_color']);
    $params[$o->getName()] = $o->getData();
    ?>
    <p class="descMsg">
        <?php echo __('Only works with the built-in icons, or if the custom icon type is SVG (.svg file)', 'ajax-search-pro'); ?>
    </p>
</div>
<div class="item"><?php
    $o = new wpdreamsUpload("magnifierimage_custom", __('Custom magnifier icon', 'ajax-search-pro'), $sd['magnifierimage_custom']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item"><?php
    $o = new wpdreamsGradient("magnifierbackground", __('Magnifier background color', 'ajax-search-pro'), $sd['magnifierbackground']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsBorder("magnifierbackgroundborder", __('Magnifier-icon border', 'ajax-search-pro'), $sd['magnifierbackgroundborder']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsBoxShadow("magnifierboxshadow", __('Magnifier-icon box-shadow', 'ajax-search-pro'), $sd['magnifierboxshadow']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<fieldset>
    <legend>Close icon</legend>
    <div class="item">
        <?php
        $o = new wpdreamsYesNo("show_close_icon", __('Show the close icon?', 'ajax-search-pro'), $sd['show_close_icon']);
        $params[$o->getName()] = $o->getData();
        ?>
    </div>
    <div class="item item-flex-nogrow item-flex-wrap">
        <?php
        $o = new wpdreamsColorPicker("close_icon_background", __('Close icon background', 'ajax-search-pro'), $sd['close_icon_background']);
        $params[$o->getName()] = $o->getData();

        $o = new wpdreamsColorPicker("close_icon_fill", __('.. icon color', 'ajax-search-pro'), $sd['close_icon_fill']);
        $params[$o->getName()] = $o->getData();

        $o = new wpdreamsColorPicker("close_icon_outline", __('..icon outline', 'ajax-search-pro'), $sd['close_icon_outline']);
        $params[$o->getName()] = $o->getData();
        ?>
    </div>
</fieldset>

<div class="item">
    <?php
    $o = new wpdreamsCustomSelect("loader_display_location", __('Loading animation display location', 'ajax-search-pro'), array(
        'selects'=>array(
            array("option" => __('Auto', 'ajax-search-pro'), "value" => "auto"),
            array("option" => __('In search bar', 'ajax-search-pro'), "value" => "search"),
            array("option" => __('In results box', 'ajax-search-pro'), "value" => "results"),
            array("option" => __('Both', 'ajax-search-pro'), "value" => "both"),
            array("option" => __('None', 'ajax-search-pro'), "value" => "none")
        ),
        'value'=>$sd['loader_display_location']
    ));
    $params[$o->getName()] = $o->getData();
    ?>
    <p class="descMsg">
        <?php echo __('By default the loader displays in the search bar. If the search bar is hidden, id displays in the results box instead.', 'ajax-search-pro'); ?>
    </p>
</div>
<div class="item" id="magn_ajaxsearchpro_1">
    <div class="probox">
    <?php
    /*$o = new wpdreamsImageRadio("loadingimage", __('Loading image', 'ajax-search-pro'), array(
            'images'  => $sd['loadingimage_selects'],
            'value'=> $sd['loadingimage']
        )
    );
    $params[$o->getName()] = $o->getData();*/

    $o = new wpdreamsLoaderSelect( "loader_image", __('Loading image', 'ajax-search-pro'), $sd['loader_image'] );
    $params[$o->getName()] = $o->getData();
    ?>
    </div>
</div>
<div class="item"><?php
    $o = new wpdreamsColorPicker("loadingimage_color", __('Loader color', 'ajax-search-pro'), $sd['loadingimage_color']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item"><?php
    $o = new wpdreamsUpload("loadingimage_custom", __('Custom loading icon', 'ajax-search-pro'), $sd['loadingimage_custom']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>