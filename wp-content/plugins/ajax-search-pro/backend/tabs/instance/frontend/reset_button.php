<div class="item">
    <?php
    $o = new wpdreamsYesNo("fe_reset_button", __('Display a Reset Filters button within the settings drop-down?', 'ajax-search-pro'), $sd['fe_reset_button']);
    ?>
</div>
<fieldset id="fe_rb_functionality">
    <legend>Functionality</legend>
    <div class="item">
        <?php
        $o = new wpdreamsCustomSelect("fe_rb_action", __('Action after pressing the button & reset', 'ajax-search-pro'),
            array(
                'selects' => array(
                    array('option' => __('Nothing', 'ajax-search-pro'), 'value' => 'nothing'),
                    array('option' => __('Nothing & Close the results', 'ajax-search-pro'), 'value' => 'close'),
                    array('option' => __('Trigger the live search', 'ajax-search-pro'), 'value' => 'live')
                ),
                'value' => $sd['fe_rb_action']
            ));
        $params[$o->getName()] = $o->getData();
        ?>
    </div>
    <div class="item">
        <?php
        $o = new wpdreamsCustomSelect("fe_rb_position", __('Display position relative to the search button (when enabled)', 'ajax-search-pro'),
            array(
                'selects' => array(
                    array('option' => __('Before the Search button', 'ajax-search-pro'), 'value' => 'before'),
                    array('option' => __('After the Search button', 'ajax-search-pro'), 'value' => 'after')
                ),
                'value' => $sd['fe_rb_position']
            ));
        $params[$o->getName()] = $o->getData();
        ?>
    </div>
</fieldset>
<fieldset id="fe_reset_button">
    <legend>Visual</legend>
    <div class="item">
        <?php
        $o = new wpdreamsText("fe_rb_text", __('Button text', 'ajax-search-pro'), $sd['fe_rb_text']);
        ?>
    </div>
    <div class="item">
        <?php
        $o = new wpdreamsCustomSelect("fe_rb_align", __('Button alignment', 'ajax-search-pro'),
            array(
                'selects' => array(
                    array('option' => __('Left', 'ajax-search-pro'), 'value' => 'left'),
                    array('option' => __('Right', 'ajax-search-pro'), 'value' => 'right'),
                    array('option' => __('Center', 'ajax-search-pro'), 'value' => 'center')
                ),
                'value' => $sd['fe_rb_align']
            ));
        ?>
    </div>
    <div class="item">
        <?php $o = new wpdreamsColorPicker("fe_rb_bg", __('Background color', 'ajax-search-pro'), $sd['fe_rb_bg']); ?>
    </div>
    <div class="item">
        <?php $o = new wpdreamsBorder("fe_rb_border", __('Button border', 'ajax-search-pro'), $sd['fe_rb_border']); ?>
    </div>
    <div class="item">
        <?php $o = new wpdreamsBoxShadow("fe_rb_boxshadow", __('Button box-shadow', 'ajax-search-pro'), $sd['fe_rb_boxshadow']); ?>
    </div>
    <div class="item item-flex-nogrow">
        <?php
        $o = new wd_ANInputs("fe_rb_padding", __('Padding', 'ajax-search-pro'),
            array(
                'args' => array(
                    'inputs' => array(
                        array( __('Top', 'ajax-search-pro'), '0px'),
                        array( __('Right', 'ajax-search-pro'), '0px'),
                        array( __('Bottom', 'ajax-search-pro'), '0px'),
                        array( __('Left', 'ajax-search-pro'), '0px')
                    )
                ),
                'value' => $sd['fe_rb_padding']
            ));
        $o = new wd_ANInputs("fe_rb_margin", __('Margin', 'ajax-search-pro'),
            array(
                'args' => array(
                    'inputs' => array(
                        array( __('Top', 'ajax-search-pro'), '0px'),
                        array( __('Right', 'ajax-search-pro'), '0px'),
                        array( __('Bottom', 'ajax-search-pro'), '0px'),
                        array( __('Left', 'ajax-search-pro'), '0px')
                    )
                ),
                'value' => $sd['fe_rb_margin']
            ));
        ?>
    </div>
    <div class="item">
        <?php
        $o = new wpdreamsFontComplete("fe_rb_font", __('Button font', 'ajax-search-pro'), $sd['fe_rb_font']);
        $params[$o->getName()] = $o->getData();
        ?>
    </div>
    <div style="display:none !important;">
        <input name="fe_rb_theme" type="hidden" value="default">
        <div class="triggerer"></div>
    </div>
    <div id="fe_rb_themes" style="display:none !important;"><?php echo json_encode($_sb_themes); ?></div>
    <div id="fe_rb_popup" class="hiddend"></div>
    <a href="#" id="fe_rb_trigger"><?php echo __('Select a button theme', 'ajax-search-pro'); ?></a>
    <div id="fe_rb_preview">
        <button class="asp_reset_btn asp_r_btn"><?php echo __('Search!', 'ajax-search-pro'); ?></button>
    </div>
    <style id="fe_rb_css"></style>
</fieldset>