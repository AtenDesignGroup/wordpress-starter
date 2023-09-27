<div class="item"><?php
    $o = new wpdreamsNumericUnit("boxmargin", __('Input field spacing', 'ajax-search-pro'), array(
        'value' => $sd['boxmargin'],
        'units'=>array('px'=>'px', '%'=>'%')
    ));
    $params[$o->getName()] = $o->getData();
    ?>
    <p class="descMsg">
        <?php echo __('Space between the input field, and the box container in pixels', 'ajax-search-pro'); ?>
    </p>
</div>
<div class="item"><?php
    $o = new wpdreamsGradient("inputbackground", __('Search input field background color', 'ajax-search-pro'), $sd['inputbackground']);
    $params[$o->getName()] = $o->getData();
    ?></div>
<div class="item">
    <?php
    $o = new wpdreamsBorder("inputborder", __('Search input field border', 'ajax-search-pro'), $sd['inputborder']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsBoxShadow("inputshadow", __('Search input field Shadow', 'ajax-search-pro'), $sd['inputshadow']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item"><?php
    $o = new wpdreamsFontComplete("inputfont", __('Search input font', 'ajax-search-pro'), $sd['inputfont']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>