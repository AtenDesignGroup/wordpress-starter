<fieldset>
    <legend>
        <?php echo __('More results text and behavior', 'ajax-search-pro'); ?>
        <span class="asp_legend_docs">
            <a target="_blank" href="https://documentation.ajaxsearchpro.com/layout-settings/more-results-link"><span class="fa fa-book"></span>
                <?php echo __('Documentation', 'ajax-search-pro'); ?>
            </a>
        </span>
    </legend>
    <div class="item item-flex-nogrow item-flex-wrap">
        <?php
        $o = new wpdreamsYesNo("showmoreresults", __('Show \'More results..\' text in the bottom of the search box?', 'ajax-search-pro'), $sd['showmoreresults']);
        $params[$o->getName()] = $o->getData();
        $o = new wpdreamsCustomSelect("more_results_action", __(' action', 'ajax-search-pro'), array(
            'selects'=>array(
                array('option' => __('Load more ajax results', 'ajax-search-pro'), 'value' => 'ajax'),
                array('option' => __('Redirect to Results Page', 'ajax-search-pro'), 'value' => 'results_page'),
                array('option' => __('Redirect to WooCommerce Results Page', 'ajax-search-pro'), 'value' => 'woo_results_page'),
                array('option' => __('Redirect to Elementor post widget page', 'ajax-search-pro'), 'value' => 'elementor_page'),
                array('option' => __('Redirect to custom URL', 'ajax-search-pro'), 'value' => 'redirect')
            ),
            'value'=>$sd['more_results_action']
        ));
        $params[$o->getName()] = $o->getData();
        ?>
        <div class="descMsg item-flex-grow item-flex-100">
            <?php echo __('"Load more ajax results" option will not work if Polaroid layout or Grouping is activated, or if results are removed when no images are present.', 'ajax-search-pro'); ?>
        </div>
    </div>
    <div class="item">
        <?php
        $o = new wd_CPTSearchCallBack('more_redirect_elementor', __('Select a page with an Elementor Pro posts widget', 'ajax-search-pro'), array(
                'value'=>$sd['more_redirect_elementor'],
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
        $o = new wpdreamsText("more_redirect_url", __('\' Show more results..\' url', 'ajax-search-pro'), $sd['more_redirect_url']);
        $params[$o->getName()] = $o->getData();
        ?>
    </div>
    <div class="item">
        <?php
        $o = new wpdreamsYesNo("more_results_infinite", __('<strong>Infinite scroll</strong> - Trigger loading more results on scrolling near the end of results list', 'ajax-search-pro'), $sd['more_results_infinite']);
        $params[$o->getName()] = $o->getData();
        ?>
    </div>
    <div class="item item-flex-nogrow item-flex-wrap">
        <?php
        $o = new wpdreamsText("showmoreresultstext", __('\' Show more results..\' text', 'ajax-search-pro'), $sd['showmoreresultstext']);
        $params[$o->getName()] = $o->getData();
        $o = new wpdreamsCustomSelect("more_redirect_location", __(' location: ', 'ajax-search-pro'),
            array(
                'selects' => array(
                    array('option' => 'Use same tab', 'value' => 'same'),
                    array('option' => 'Open new tab', 'value' => 'new')
                ),
                'value' => $sd['more_redirect_location']
            ));
        $params[$o->getName()] = $o->getData();
        ?>
    </div>
</fieldset>
<fieldset>
    <legend>
        <?php echo __('Results text keyword highlighter - Live results list', 'ajax-search-pro'); ?>
        <span class="asp_legend_docs">
            <a target="_blank" href="https://documentation.ajaxsearchpro.com/layout-settings/search-phrase-highlighter"><span class="fa fa-book"></span>
                <?php echo __('Documentation', 'ajax-search-pro'); ?>
            </a>
        </span>
    </legend>
    <div class="item"><?php
        $o = new wpdreamsYesNo("highlight", __('Highlight search text in live results?', 'ajax-search-pro'), $sd['highlight']);
        $params[$o->getName()] = $o->getData();
        ?></div>
    <div class="item"><?php
        $o = new wpdreamsYesNo("highlightwholewords", __('Highlight only whole words?', 'ajax-search-pro'), $sd['highlightwholewords']);
        $params[$o->getName()] = $o->getData();
        ?></div>
    <div class="item"><?php
        $o = new wpdreamsColorPicker("highlightcolor", __('Highlight text color', 'ajax-search-pro'), $sd['highlightcolor']);
        $params[$o->getName()] = $o->getData();
        ?></div>
    <div class="item"><?php
        $o = new wpdreamsColorPicker("highlightbgcolor", __('Highlight-text background color', 'ajax-search-pro'), $sd['highlightbgcolor']);
        $params[$o->getName()] = $o->getData();
        ?>
    </div>
</fieldset>
<fieldset>
    <legend>
        <?php echo __('Results text keyword highlighter - Single Result & Search Results page', 'ajax-search-pro'); ?>
        <span class="asp_legend_docs">
            <a target="_blank" href="https://documentation.ajaxsearchpro.com/layout-settings/search-phrase-highlighter"><span class="fa fa-book"></span>
                <?php echo __('Documentation', 'ajax-search-pro'); ?>
            </a>
        </span>
    </legend>
    <div class="errorMsg">
            <?php echo __('<strong>Disclaimer: </strong> This feature is highly experimental, and may not work correctly in all cases.', 'ajax-search-pro'); ?>
    </div>
    <div class="item">
		<?php
        $o = new wpdreamsYesNo("single_highlight", __('Highlight search text on single result pages?', 'ajax-search-pro'), $sd['single_highlight']);
        ?>
	</div>
	<div class="item">
		<?php
		$o = new wpdreamsYesNo("result_page_highlight", __('Highlight search text on the Search Results page?', 'ajax-search-pro'), $sd['result_page_highlight']);
		?>
	</div>
	<div wd-disable-on="single_highlight:0;result_page_highlight:0">
		<div class="item"><?php
			$o = new wpdreamsYesNo("single_highlightwholewords", __('Highlight only whole words?', 'ajax-search-pro'), $sd['single_highlightwholewords']);
			$params[$o->getName()] = $o->getData();
			?></div>
		<div class="item"><?php
			$o = new wpdreamsColorPicker("single_highlightcolor", __('Highlight text color', 'ajax-search-pro'), $sd['single_highlightcolor']);
			$params[$o->getName()] = $o->getData();
			?></div>
		<div class="item"><?php
			$o = new wpdreamsColorPicker("single_highlightbgcolor", __('Highlight-text background color', 'ajax-search-pro'), $sd['single_highlightbgcolor']);
			$params[$o->getName()] = $o->getData();
			?>
		</div>
		<div class="item item-flex-nogrow item-flex-wrap">
			<?php
			$o = new wpdreamsYesNo("single_highlight_scroll", __('Scroll to the first keyword match if possible?', 'ajax-search-pro'), $sd['single_highlight_scroll']);

			$o = new wpdreamsTextSmall("single_highlight_offset", __('scroll offset (px)', 'ajax-search-pro'), $sd['single_highlight_offset']);
			?>
			<div class="descMsg item-flex-grow item-flex-100">
				<?php echo __('A negative offset will move the window upwards, a positive downwards. Default: 0', 'ajax-search-pro'); ?>
			</div>
		</div>
		<div class="item"><?php
			$o = new wpdreamsText("single_highlight_selector", __('Result page content jQuery element selector', 'ajax-search-pro'), $sd['single_highlight_selector']);
			$params[$o->getName()] = $o->getData();
			?>
			<div class="descMsg item-flex-grow item-flex-100">
				<?php echo __('Optional, but very useful - it tells which element contains exactly the result content, so words are highlighted only on the given section of the page.', 'ajax-search-pro'); ?>
			</div>
		</div>
	</div>
</fieldset>