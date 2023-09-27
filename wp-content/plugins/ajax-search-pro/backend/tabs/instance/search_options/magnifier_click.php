
<fieldset>
	<legend>
		<?php echo __('Trigger and redirection behavior', 'ajax-search-pro'); ?>
		<span class="asp_legend_docs">
            <a target="_blank" href="https://documentation.ajaxsearchpro.com/behavior/return-key-and-magnifier-icon-click-actions"><span class="fa fa-book"></span>
                <?php echo __('Documentation', 'ajax-search-pro'); ?>
            </a>
        </span>
	</legend>

	<div class="item item-flex-nogrow item-flex-wrap">
		<?php
		$o = new wpdreamsCustomSelect("click_action", __('Action when clicking <strong>the magnifier</strong> icon', 'ajax-search-pro'),
			array(
				'selects' => $_red_opts,
				'value' => $sd['click_action']
			));
		$params[$o->getName()] = $o->getData();
		?>
		<div wd-hide-on="click_action:ajax_search,nothing,same">
			<?php
			$o = new wpdreamsCustomSelect("click_action_location", __(' location: ', 'ajax-search-pro'),
				array(
					'selects' => array(
						array('option' => __('Use same tab', 'ajax-search-pro'), 'value' => 'same'),
						array('option' => __('Open new tab', 'ajax-search-pro'), 'value' => 'new')
					),
					'value' => $sd['click_action_location']
				));
			$params[$o->getName()] = $o->getData();
			?>
		</div>
	</div>
	<div class="item item-flex-nogrow item-flex-wrap">
		<?php
		$o = new wpdreamsCustomSelect("return_action", __('Action when pressing <strong>the return</strong> button', 'ajax-search-pro'),
			array(
				'selects' => $_red_opts,
				'value' => $sd['return_action']
			));
		$params[$o->getName()] = $o->getData();
		?>
		<div wd-hide-on="return_action:ajax_search,nothing,same">
			<?php
			$o = new wpdreamsCustomSelect("return_action_location", __(' location: ', 'ajax-search-pro'),
				array(
					'selects' => array(
						array('option' => __('Use same tab', 'ajax-search-pro'), 'value' => 'same'),
						array('option' => __('Open new tab', 'ajax-search-pro'), 'value' => 'new')
					),
					'value' => $sd['return_action_location']
				));
			$params[$o->getName()] = $o->getData();
			?>
		</div>
	</div>
	<div class="item" wd-hide-on="click_action:ajax_search,first_result,results_page,woo_results_page,custom_url,nothing;return_action:ajax_search,first_result,results_page,woo_results_page,custom_url,nothing">
		<?php
		$o = new wd_CPTSearchCallBack('redirect_elementor', __('Select a page with an Elementor Pro posts widget', 'ajax-search-pro'), array(
			'value'=>$sd['redirect_elementor'],
			'args'=> array(
				'controls_position' => 'left',
				'class'=>'wpd-text-right'
			)
		));
		$params[$o->getName()] = $o->getData();
		?>
	</div>
	<div class="item"
		 wd-hide-on="click_action:ajax_search,first_result,results_page,woo_results_page,elementor_page,nothing;return_action:ajax_search,first_result,results_page,woo_results_page,elementor_page,nothing">
		<?php
		$o = new wpdreamsText("redirect_url", __('Custom redirect URL', 'ajax-search-pro'),
			$sd['redirect_url']);
		$params[$o->getName()] = $o->getData();
		?>
		<p class="descMsg">
			<?php echo sprintf( __('You can use the <string>asp_redirect_url</string> filter to add more variables. See <a href="%s" target="_blank">this tutorial</a>.', 'ajax-search-pro'), 'http://wp-dreams.com/go/?to=kb-redirecturl' ); ?>
		</p>
	</div>
	<div class="item item-flex-nogrow item-flex-wrap">
		<?php
		$o = new wpdreamsYesNo("override_default_results", __('<b>Override</b> the default WordPress search results with results from this search instance?', 'ajax-search-pro'),
			$sd['override_default_results']);
		$params[$o->getName()] = $o->getData();
		?>
		<?php
		$o = new wpdreamsCustomSelect("override_method", __(' method ', 'ajax-search-pro'), array(
			"selects" =>array(
				array("option" => "Post", "value" => "post"),
				array("option" => "Get", "value" => "get")
			),
			"value" => $sd['override_method']
		));
		$params[$o->getName()] = $o->getData();
		?>
		<div class="descMsg item-flex-grow item-flex-100">
			<?php echo __('If this is enabled, the plugin will try to replace the default results with it\'s own. Might not work with themes which temper the search query themselves (very very rare).', 'ajax-search-pro'); ?>
		</div>
	</div>
	<div class="item">
		<?php
		$o = new wpdreamsTextSmall("results_per_page", __('Results count per page?', 'ajax-search-pro'),
			$sd['results_per_page']);
		$params[$o->getName()] = $o->getData();
		?>
		<p class="descMsg"><?php echo __('The number of results per page, on the results page. Default: auto', 'ajax-search-pro'); ?></p>
		<p class="errorMsg">
			<?php echo __('<strong>WARNING:</strong> This should be set to the same as the number of results originally displayed on the results page!<br>
            Most themes use the system option found on the <strong>General Options -> Reading</strong> submenu, which is 10 by default. <br>
            If you set it differently, or your theme has a different option for that, then <strong>set this option to the same value</strong> as well.', 'ajax-search-pro'); ?>
		</p>
	</div>
</fieldset>
<fieldset id="elementor_live_search">
	<legend>
		<?php echo __('Elementor Posts Widget Live Filter', 'ajax-search-pro'); ?>
		<span class="asp_legend_docs">
            <a target="_blank" href="https://documentation.ajaxsearchpro.com/behavior/elementor-pro-posts-widget-live-filter"><span class="fa fa-book"></span>
                <?php echo __('Documentation', 'ajax-search-pro'); ?>
            </a>
        </span>
	</legend>
	<div class="item">
		<label>
			<?php echo __('Add to Elementor Posts Widget class name to enable live filtering on that widget', 'ajax-search-pro'); ?>
			<input type="text" value="asp_es_<?php echo $search['id']; ?>" readonly="readonly">
		</label>
		<div class="descMsg">
			<?php echo sprintf(
				__('Please check the <a href="%s">Elementor Posts Live Loader documentation</a> for more details', 'ajax-search-pro'),
				'https://documentation.ajaxsearchpro.com/elementor-integration'); ?>
		</div>
	</div>
</fieldset>