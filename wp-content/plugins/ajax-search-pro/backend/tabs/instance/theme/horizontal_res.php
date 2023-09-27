<div class="item item-rlayout item-rlayout-horizontal">
    <p><?php echo __('These options are hidden, because the <span>vertical</span> results layout is selected.', 'ajax-search-pro'); ?></p>
    <p><?php echo __('You can change that under the <a href="#402" data-asp-os-highlight="resultstype" tabid="402">Layout Options -> Results layout</a> panel,
        <br>..or choose a <a href="#601" tabid="601">different theme</a> with a different pre-defined layout.', 'ajax-search-pro'); ?></p>
</div>
<div class="item">
    <?php
    $o = new wpdreamsYesNo("hhidedesc", __('Hide description if images are available', 'ajax-search-pro'), $sd['hhidedesc']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsYesNo('h_res_show_scrollbar', __('Display the results scrollbar?', 'ajax-search-pro'), $sd['h_res_show_scrollbar']);
    $params[$o->getName()] = $o->getData();
    ?>
    <p class="descMsg">
        <?php echo __('When turned OFF, the results box will break space, instead of showing a horizontal scroll.', 'ajax-search-pro'); ?>
    </p>
</div>
<div class="item"><?php
    $o = new wpdreamsNumericUnit("hreswidth", __('Result width', 'ajax-search-pro'), array(
        'value' => $sd['hreswidth'],
        'units'=>array('px'=>'px')));
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item"><?php
    $o = new wpdreamsNumericUnit("hor_img_height", __('Result image height', 'ajax-search-pro'), array(
        'value' => $sd['hor_img_height'],
        'units'=>array('px'=>'px')));
    $params[$o->getName()] = $o->getData();
    ?>
    <p class="descMsg">
        <?php echo __('The image width is calcualted from the Result width option.', 'ajax-search-pro'); ?>
    </p>
</div>
<div class="item"><?php
    $o = new wpdreamsTextSmall("horizontal_res_height", __('Result height', 'ajax-search-pro'), $sd['horizontal_res_height']);
    $params[$o->getName()] = $o->getData();
    ?>
    <p class="descMsg">
        <?php echo sprintf(
            __('Use with <a href="%s" target="_blank">CSS units</a> (like %s or %s or %s ..) Default: <strong>%s</strong>', 'ajax-search-pro'),
            'https://www.w3schools.com/cssref/css_units.asp', '200px', '30vh', '30%', 'auto'
        ); ?>
    </p>
</div>
<div class="item"><?php
    $o = new wpdreamsNumericUnit("hressidemargin", __('Result side margin', 'ajax-search-pro'), array(
        'value' => $sd['hressidemargin'],
        'units'=>array('px'=>'px')));
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item"><?php
    $o = new wpdreamsNumericUnit("hrespadding", __('Result padding', 'ajax-search-pro'), array(
        'value' => $sd['hrespadding'],
        'units'=>array('px'=>'px')));
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item"><?php
    $o = new wpdreamsGradient("hboxbg", __('Result container background color', 'ajax-search-pro'), $sd['hboxbg']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsBorder("hboxborder", __('Results container border', 'ajax-search-pro'), $sd['hboxborder']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<fieldset>
    <legend><?php echo __('Custom scrollbar', 'ajax-search-pro') ?></legend>
    <div class="item item-flex-nogrow">
    <?php
    $o = new wpdreamsYesNo('h_res_overflow_autohide', __('Auto hide the scrollbar?', 'ajax-search-pro'), $sd['h_res_overflow_autohide']);
    $params[$o->getName()] = $o->getData();

    $o = new wpdreamsGradient("h_res_overflow_color", __('Scrollbar color', 'ajax-search-pro'), $sd['h_res_overflow_color']);
    $params[$o->getName()] = $o->getData();
    ?>
    </div>
</fieldset>
<div class="item">
    <?php
    $o = new wpdreamsBoxShadow("hboxshadow", __('Results container box shadow', 'ajax-search-pro'), $sd['hboxshadow']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item"><?php
    $o = new wpdreamsAnimations("hresultinanim", __('Result item incoming animation', 'ajax-search-pro'), $sd['hresultinanim']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item"><?php
    $o = new wpdreamsGradient("hresultbg", __('Result item background color', 'ajax-search-pro'), $sd['hresultbg']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item"><?php
    $o = new wpdreamsGradient("hresulthbg", __('Result item mouse hover background color', 'ajax-search-pro'), $sd['hresulthbg']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsBorder("hresultborder", __('Results item border', 'ajax-search-pro'), $sd['hresultborder']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsBoxShadow("hresultshadow", __('Results item box shadow', 'ajax-search-pro'), $sd['hresultshadow']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsBorder("hresultimageborder", __('Results image border', 'ajax-search-pro'), $sd['hresultimageborder']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsBoxShadow("hresultimageshadow", __('Results image box shadow', 'ajax-search-pro'), $sd['hresultimageshadow']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>