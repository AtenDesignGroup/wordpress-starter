<div class="item">
    <?php $o = new wpdreamsYesNo("js_prevent_body_scroll", __('Try preventing body touch scroll on mobile devices, when using the vertical results layout?', 'ajax-search-pro'),
        $com_options['js_prevent_body_scroll']
    ); ?>
    <p class='descMsg'>
        <?php echo __('When reaching the top or bottom of the results list via touch devices, the scrolling is automatically propagated to the parent element. This function will try to prevent that.', 'ajax-search-pro'); ?>
    </p>
</div>
<div class="item">
    <?php $o = new wpdreamsYesNo("detect_ajax", __('Try to re-initialize if the page was loaded via ajax?', 'ajax-search-pro'),
        $com_options['detect_ajax']
    ); ?>
    <p class='descMsg'>
        <?php echo __('Will try to re-initialize the plugin in case an AJAX page loader is used, like Polylang language switcher etc..', 'ajax-search-pro'); ?>
    </p>
</div>
<div class="item">
    <?php
    $o = new wpdreamsCustomSelect("css_compatibility_level", __('CSS compatibility level', 'ajax-search-pro'), array(
            'selects'=>array(
                array('option'=>'Optimal (recommended)', 'value'=>'low'),
                array('option'=>'Medium', 'value'=>'medium'),
                array('option'=>'Maximum', 'value'=>'maximum')
            ),
            'value'=>$com_options['css_compatibility_level']
        )
    );
    $params[$o->getName()] = $o->getData();
    ?>
    <p class="descMsg">
    <ul style="float:right;text-align:left;width:50%;">
        <li><?php echo __('<b>Optimal</b> - Good compabibility, smallest size', 'ajax-search-pro'); ?></li>
        <li><?php echo __('<b>Medium</b> - Better compatibility, bigger size', 'ajax-search-pro'); ?></li>
        <li><?php echo __('<b>Maximum</b> - High compatibility, very big size', 'ajax-search-pro'); ?></li>
    </ul>
    <div class="clear"></div>
    </p>
</div>
<div class="item">
    <?php $o = new wpdreamsYesNo("css_minify", __('Minify the generated CSS?', 'ajax-search-pro'),
        $com_options['css_minify']
    ); ?>
    <p class='descMsg'>
        <?php echo __('When enabled, the generated stylesheet files will be minified before saving. Can save ~10% CSS file size.', 'ajax-search-pro'); ?>
    </p>
</div>
<div class="item">
    <?php $o = new wpdreamsYesNo("load_google_fonts", __('Load the <strong>google fonts</strong> used in the search options?', 'ajax-search-pro'),
        $com_options['load_google_fonts']
    ); ?>
    <p class='descMsg'>
        <?php echo __('When <strong>turned off</strong>, the google fonts <strong>will not be loaded</strong> via this plugin at all.<br>Useful if you already have them loaded, to avoid mutliple loading times.', 'ajax-search-pro'); ?>
    </p>
</div>
<div class="item">
    <p class='infoMsg'>
        <?php echo __('This might speed up the search, but also can cause incompatibility issues with other plugins.', 'ajax-search-pro'); ?>
    </p>
    <?php $o = new wpdreamsYesNo("usecustomajaxhandler", __('Use the custom ajax handler?', 'ajax-search-pro'),
        $com_options['usecustomajaxhandler']
    ); ?>
</div>