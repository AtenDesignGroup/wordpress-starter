<div class="item item-rlayout item-rlayout-isotopic">
    <p><?php echo __('These options are hidden, because the <span>vertical</span> results layout is selected.', 'ajax-search-pro'); ?></p>
    <p><?php echo __('You can change that under the <a href="#402" data-asp-os-highlight="resultstype" tabid="402">Layout Options -> Results layout</a> panel,
        <br>..or choose a <a href="#601" tabid="601">different theme</a> with a different pre-defined layout.', 'ajax-search-pro'); ?></p>
</div>
<div class="item item item-flex-nogrow item-flex-wrap">
    <?php
    $o = new wpdreamsYesNo("i_pagination", __('Display the pagination navigation?', 'ajax-search-pro'), $sd['i_pagination']);
    $params[$o->getName()] = $o->getData();

    $o = new wpdreamsTextSmall("i_rows", __('Rows count per page', 'ajax-search-pro'), $sd['i_rows']);
    $params[$o->getName()] = $o->getData();
    ?>
    <p class="descMsg item-flex-grow item-flex-100">
        <?php echo __('If the item would exceed the row limit, it gets placed to a new page.', 'ajax-search-pro'); ?>
    </p>
</div>
<div class="item item-iso-nav"><?php
    $o = new wpdreamsCustomSelect("i_pagination_position", __('Navigation position', 'ajax-search-pro'),  array(
        'selects'=>array(
            array('option' => __('Top', 'ajax-search-pro'), 'value' => 'top'),
            array('option' => __('Bottom', 'ajax-search-pro'), 'value' => 'bottom'),
            array('option' => __('Both Top and Bottom', 'ajax-search-pro'), 'value' => 'both')
        ),
        'value'=>$sd['i_pagination_position']
    ));
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item item-iso-nav"><?php
    $o = new wpdreamsColorPicker("i_pagination_background", __('Pagination background', 'ajax-search-pro'), $sd['i_pagination_background']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item item-iso-nav">
    <?php
    $o = new wpdreamsImageRadio("i_pagination_arrow", __('Arrow image', 'ajax-search-pro'), array(
            'images'  => array(
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
                "/ajax-search-pro/img/svg/arrows/arrow16.svg",
                "/ajax-search-pro/img/svg/arrows/arrow17.svg",
                "/ajax-search-pro/img/svg/arrows/arrow18.svg"
            ),
            'value'=> $sd['i_pagination_arrow']
        )
    );
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item item-iso-nav"><?php
    $o = new wpdreamsColorPicker("i_pagination_arrow_background", __('Arrow background color', 'ajax-search-pro'), $sd['i_pagination_arrow_background']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item item-iso-nav"><?php
    $o = new wpdreamsColorPicker("i_pagination_arrow_color", __('Arrow color', 'ajax-search-pro'), $sd['i_pagination_arrow_color']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item item-iso-nav"><?php
    $o = new wpdreamsColorPicker("i_pagination_page_background", __('Active page background color', 'ajax-search-pro'), $sd['i_pagination_page_background']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item item-iso-nav"><?php
    $o = new wpdreamsColorPicker("i_pagination_font_color", __('Font color', 'ajax-search-pro'), $sd['i_pagination_font_color']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>