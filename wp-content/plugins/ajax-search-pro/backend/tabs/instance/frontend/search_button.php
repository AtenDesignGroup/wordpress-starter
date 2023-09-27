<span class="asp_legend_docs">
    <a target="_blank" href="https://documentation.ajaxsearchpro.com/frontend-search-settings/search-button"><span class="fa fa-book"></span>
        <?php echo __('Documentation', 'ajax-search-pro'); ?>
    </a>
</span>
<div class="item">
    <?php
    $o = new wpdreamsYesNo("fe_search_button", __('Display a search button within the settings drop-down?', 'ajax-search-pro'), $sd['fe_search_button']);
    ?>
</div>
<fieldset id="fe_sb_functionality">
    <legend><?php _e('Functionality', 'ajax-search-pro'); ?></legend>
    <div class="item item-flex-nogrow item-flex-wrap">
        <?php
        foreach ($_red_opts as $rok => $rov)
            if ( $rov['value'] == 'same' || $rov['value'] == 'nothing' )
                unset($_red_opts[$rok]);
        $o = new wpdreamsCustomSelect("fe_sb_action", __('Action when pressing the button', 'ajax-search-pro'),
            array(
                'selects' => $_red_opts,
                'value' => $sd['fe_sb_action']
            ));
        $params[$o->getName()] = $o->getData();
        $o = new wpdreamsCustomSelect("fe_sb_action_location", __(' location: ', 'ajax-search-pro'),
            array(
                'selects' => array(
                    array('option' => __('Use same tab', 'ajax-search-pro'), 'value' => 'same'),
                    array('option' => __('Open new tab', 'ajax-search-pro'), 'value' => 'new')
                ),
                'value' => $sd['fe_sb_action_location']
            ));
        $params[$o->getName()] = $o->getData();
        ?>
    </div>
    <div class="item">
        <?php
        $o = new wd_CPTSearchCallBack('fe_sb_redirect_elementor', __('Select a page with an Elementor Pro posts widget', 'ajax-search-pro'), array(
                'value'=>$sd['fe_sb_redirect_elementor'],
                'args'=> array(
                        'controls_position' => 'left',
                        'class'=>'wpd-text-right'
                )
        ));
        $params[$o->getName()] = $o->getData();
        ?>
    </div>
    <div class="item">
        <?php
        $o = new wpdreamsText("fe_sb_redirect_url", __('Custom redirect URL', 'ajax-search-pro'),
            $sd['fe_sb_redirect_url']);
        $params[$o->getName()] = $o->getData();
        ?>
        <p class="descMsg">
            <?php echo sprintf( __('You can use the <string>asp_redirect_url</string> filter to add more variables. See <a href="%s" target="_blank">this tutorial</a>.', 'ajax-search-pro'), 'http://wp-dreams.com/go/?to=kb-redirecturl' ); ?>
        </p>
    </div>
</fieldset>
<fieldset id="fe_search_button">
    <legend><?php _e('Visual', 'ajax-search-pro'); ?></legend>
    <div class="item">
        <?php
        $o = new wpdreamsText("fe_sb_text", __('Button text', 'ajax-search-pro'), $sd['fe_sb_text']);
        ?>
    </div>
    <div class="item">
        <?php
        $o = new wpdreamsCustomSelect("fe_sb_align", __('Button alignment', 'ajax-search-pro'),
            array(
                'selects' => array(
                    array('option' => __('Left', 'ajax-search-pro'), 'value' => 'left'),
                    array('option' => __('Right', 'ajax-search-pro'), 'value' => 'right'),
                    array('option' => __('Center', 'ajax-search-pro'), 'value' => 'center')
                ),
                'value' => $sd['fe_sb_align']
            ));
        ?>
    </div>
    <div class="item">
        <?php $o = new wpdreamsColorPicker("fe_sb_bg", __('Background color', 'ajax-search-pro'), $sd['fe_sb_bg']); ?>
    </div>
    <div class="item">
        <?php $o = new wpdreamsBorder("fe_sb_border", __('Button border', 'ajax-search-pro'), $sd['fe_sb_border']); ?>
    </div>
    <div class="item">
        <?php $o = new wpdreamsBoxShadow("fe_sb_boxshadow", __('Button box-shadow', 'ajax-search-pro'), $sd['fe_sb_boxshadow']); ?>
    </div>
    <div class="item item-flex-nogrow">
        <?php
        $o = new wd_ANInputs("fe_sb_padding", __('Padding', 'ajax-search-pro'),
            array(
                'args' => array(
                    'inputs' => array(
                        array( __('Top', 'ajax-search-pro'), '0px'),
                        array( __('Right', 'ajax-search-pro'), '0px'),
                        array( __('Bottom', 'ajax-search-pro'), '0px'),
                        array( __('Left', 'ajax-search-pro'), '0px')
                    )
                ),
                'value' => $sd['fe_sb_padding']
            ));
        $o = new wd_ANInputs("fe_sb_margin", __('Margin', 'ajax-search-pro'),
            array(
                'args' => array(
                    'inputs' => array(
                        array( __('Top', 'ajax-search-pro'), '0px'),
                        array( __('Right', 'ajax-search-pro'), '0px'),
                        array( __('Bottom', 'ajax-search-pro'), '0px'),
                        array( __('Left', 'ajax-search-pro'), '0px')
                    )
                ),
                'value' => $sd['fe_sb_margin']
            ));
        ?>
    </div>
    <div class="item">
        <?php
        $o = new wpdreamsFontComplete("fe_sb_font", __('Button font', 'ajax-search-pro'), $sd['fe_sb_font']);
        $params[$o->getName()] = $o->getData();
        ?>
    </div>
    <div style="display:none !important;">
        <input name="fe_sb_theme" type="hidden" value="default">
        <div class="triggerer"></div>
    </div>
    <div id="fe_sb_themes" style="display:none !important;"><?php echo json_encode($_sb_themes); ?></div>
    <div id="fe_sb_popup" class="hiddend"></div>
    <a href="#" id="fe_sb_trigger"><?php echo __('Select a button theme', 'ajax-search-pro'); ?></a>
    <div id="fe_sb_preview">
        <button class="asp_search_btn asp_s_btn"><?php echo __('Search!', 'ajax-search-pro'); ?></button>
    </div>
    <style id="fe_sb_css"></style>
</fieldset>
<?php if (ASP_DEBUG == 1): ?>
    <textarea id="sb_previewtext"></textarea>
    <script>
    jQuery(function($){
        $("#sb_previewtext").click(function(){
            var skip = ['fe_sb_text', 'fe_sb_align'];
            var parent = $('#fe_search_button');
            var content = "";
            var v = "";
            parent.find("input[isparam=1], select[isparam=1]").each(function(){
                var name = $(this).attr("name");
                if ( skip.indexOf(name) > -1 )
                    return true;
                var val = $(this).val().replace(/(\r\n|\n|\r)/gm,"");
                content += '"'+name+'":"'+val+'",\n';
            });

            content = content.trim();
            content = content.slice(0, - 1);
            $(this).val('"theme": {\n' + content + "\n}");
        });
    });
    </script>
<?php endif; ?>