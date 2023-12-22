<fieldset class="rinfobox">
    <legend><?php echo __('Result Information Box', 'ajax-search-pro'); ?></legend>
    <div class="item item-rinfobox">
        <p><?php echo __('These options are hidden, because the <strong>Results Information Box</strong> option is disabled.', 'ajax-search-pro'); ?></p>
        <p><?php echo __('You can enable it under the <a href="#402" data-asp-os-highlight="results_top_box" tabid="402">Layout Options -> Results layout</a> panel.', 'ajax-search-pro'); ?></p>
    </div>
    <div class="item">
        <?php
        $o = new wpdreamsFontComplete("ritb_font", __('Information box Font', 'ajax-search-pro'), $sd['ritb_font']);
        $params[$o->getName()] = $o->getData();
        ?>
    </div>
    <div class="item">
        <?php $o = new wpdreamsColorPicker("ritb_bg", __('Background color', 'ajax-search-pro'), $sd['ritb_bg']); ?>
    </div>
    <div class="item">
        <?php $o = new wpdreamsBorder("ritb_border", __('Box border', 'ajax-search-pro'), $sd['ritb_border']); ?>
    </div>
    <div class="item item-flex-nogrow">
        <?php
        $o = new wd_ANInputs("ritb_padding", __('Padding', 'ajax-search-pro'),
            array(
                'args' => array(
                    'inputs' => array(
                        array( __('Top', 'ajax-search-pro'), '0px'),
                        array( __('Right', 'ajax-search-pro'), '0px'),
                        array( __('Bottom', 'ajax-search-pro'), '0px'),
                        array( __('Left', 'ajax-search-pro'), '0px')
                    )
                ),
                'value' => $sd['ritb_padding']
            ));
        $o = new wd_ANInputs("ritb_margin", __('Margin', 'ajax-search-pro'),
            array(
                'args' => array(
                    'inputs' => array(
                        array( __('Top', 'ajax-search-pro'), '0px'),
                        array( __('Right', 'ajax-search-pro'), '0px'),
                        array( __('Bottom', 'ajax-search-pro'), '0px'),
                        array( __('Left', 'ajax-search-pro'), '0px')
                    )
                ),
                'value' => $sd['ritb_margin']
            ));
        ?>
    </div>
</fieldset>