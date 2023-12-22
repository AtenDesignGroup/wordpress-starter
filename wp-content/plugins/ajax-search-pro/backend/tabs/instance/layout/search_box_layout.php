<div class="item">
    <?php
    $o = new wpdreamsText("defaultsearchtext", __('Search box placeholder text', 'ajax-search-pro'), $sd['defaultsearchtext']);
    $params[$o->getName()] = $o->getData();
    ?>
    <p class="descMsg">
        <?php echo __('Default search placeholder text appearing in the search bar.', 'ajax-search-pro'); ?>
    </p>
</div>
<div class="item">
	<?php
	$o = new wpdreamsYesNo("focus_on_pageload", __('Focus the search input when the page is loaded? (desktop devices only)', 'ajax-search-pro'), $sd['box_sett_hide_box']);
	$params[$o->getName()] = $o->getData();
	?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsCustomSelect("box_alignment", __('Search box alignment', 'ajax-search-pro'),
        array(
            'selects' => array(
                array('option' => __('Inherit', 'ajax-search-pro'), 'value' => 'inherit'),
                array('option' => __('Center', 'ajax-search-pro'), 'value' => 'center'),
                array('option' => __('Left', 'ajax-search-pro'), 'value' => 'left'),
                array('option' => __('Right', 'ajax-search-pro'), 'value' => 'right')
            ),
            'value' => $sd['box_alignment']
        ));
    $params[$o->getName()] = $o->getData();
    ?>
    <p class="descMsg">
        <?php echo __('By default the plugin follows the parent element alignment. This option might not have an effect if the parent element is displayed as "table" or "flex".', 'ajax-search-pro'); ?>
    </p>
</div>
<div class="item item-flex-nogrow item-flex-wrap">
    <?php
    $o = new wpdreamsTextSmall('box_margin_top', __('Search box Top Margin', 'ajax-search-pro'),
        $sd['box_margin_top']);

    $o = new wpdreamsTextSmall('box_margin_bottom', __('Bottom Margin', 'ajax-search-pro'),
        $sd['box_margin_bottom']);
    $params[$o->getName()] = $o->getData();
    ?>
    <div class="descMsg item-flex-grow item-flex-100">
        <?php echo sprintf(
            __('Use with <a href="%s" target="_blank">CSS units</a> (like %s or %s or %s ..) Default: <strong>%s</strong>', 'ajax-search-pro'),
            'https://www.w3schools.com/cssref/css_units.asp', '12px', '5vh', 'auto', '0'
        ); ?>
    </div>
</div>
<div class="item">
    <?php
    $o = new wpdreamsYesNo("box_sett_hide_box", __('Hide the search box completely, display settings only?', 'ajax-search-pro'), $sd['box_sett_hide_box']);
    $params[$o->getName()] = $o->getData();
    ?>
    <p class="descMsg">
        <?php echo __('If enabled, the search box will be hidden, and the Frontend settings will be displayed instead.', 'ajax-search-pro'); ?>
    </p>
</div>
<fieldset>
    <legend>
        <?php echo __('Auto Populate', 'ajax-search-pro'); ?>
        <span class="asp_legend_docs">
            <a target="_blank" href="https://documentation.ajaxsearchpro.com/frontend-search-settings/search-button"><span class="fa fa-book"></span>
                <?php echo __('Documentation', 'ajax-search-pro'); ?>
            </a>
        </span>
    </legend>
    <div class="item">
        <?php
        $o = new wpdreamsCustomSelect("auto_populate", __('Display results by default when the page loads (auto populate)?', 'ajax-search-pro'),
            array(
                'selects' => array(
                    array('option' => __('Disabled', 'ajax-search-pro'), 'value' => 'disabled'),
                    array('option' => __('Enabled - Results for a search phrase', 'ajax-search-pro'), 'value' => 'phrase'),
                    array('option' => __('Enabled - Latest results', 'ajax-search-pro'), 'value' => 'latest'),
                    array('option' => __('Enabled - Random results', 'ajax-search-pro'), 'value' => 'random')
                ),
                'value' => $sd['auto_populate']
            ));
        $params[$o->getName()] = $o->getData();
        ?>
        <p class="descMsg">
            <?php echo __('If enabled, the search will automatically populate on page load based on the selected configuration. The configuration and the
            frontend search options <strong>WILL be taken into account</strong> as if a normal search was made!', 'ajax-search-pro'); ?>
        </p>
    </div>
    <div class="item item-flex-nogrow item-flex-wrap">
        <?php
        $o = new wpdreamsText("auto_populate_phrase", __('Phrase', 'ajax-search-pro'), $sd['auto_populate_phrase']);
        $params[$o->getName()] = $o->getData();
        ?>&nbsp;&nbsp;&nbsp;&nbsp;
        <?php
        $o = new wpdreamsTextSmall("auto_populate_count", __(' Results count', 'ajax-search-pro'), $sd['auto_populate_count']);
        $params[$o->getName()] = $o->getData();
        ?>
        <div class="errorMsg hiddend autop-count-err">
            <?php echo __('The <strong>Results Count</strong> option is <strong>Disabled</strong> for compatibility reasons, because the <a class="asp_to_tab" tabid="405" href="#405">Load more feature</a> is enabled.
            The default results count will be used, that is set under the <a class="asp_to_tab" tabid="111" href="#111">General Options -> Limits</a> panel.', 'ajax-search-pro'); ?>
        </div>
    </div>
</fieldset>