<fieldset>
	<legend><?php echo __('Behavior on Mobile devices', 'ajax-search-pro'); ?></legend>
	<div class="item item-flex-nogrow" style="flex-wrap:wrap;">
		<?php
		$o = new wpdreamsYesNo("mob_display_search", __('Display the search bar on <strong>mobile</strong> devices?', 'ajax-search-pro'),
			$sd['mob_display_search']);
		$params[$o->getName()] = $o->getData();

		$o = new wpdreamsYesNo("desktop_display_search", __(' .. and on <strong>desktop</strong> devices?', 'ajax-search-pro'),
			$sd['desktop_display_search']);
		$params[$o->getName()] = $o->getData();
		?>
		<div class="descMsg item-flex-grow item-flex-100">
			<?php echo __('If you want to hide this search bar on mobile/desktop devices then turn OFF these option.', 'ajax-search-pro'); ?>
		</div>
	</div>
	<div class="item">
		<?php
		$o = new wpdreamsYesNo("mob_trigger_on_type", __('Trigger search when typing on mobile keyboard?', 'ajax-search-pro'),
			$sd['mob_trigger_on_type']);
		$params[$o->getName()] = $o->getData();
		?>
	</div>
	<div class="item item-flex-nogrow item-flex-wrap">
		<?php
		$_red_opts = array_merge(
			array(array('option'=> __('Same as on desktop', 'ajax-search-pro'), 'value'=>'same')),
			$_red_opts
		);
		$o = new wpdreamsCustomSelect("mob_click_action", __('Action when tapping <strong>the magnifier</strong> icon', 'ajax-search-pro'),
			array(
				'selects' => $_red_opts,
				'value' => $sd['mob_click_action']
			));
		$params[$o->getName()] = $o->getData();
		$o = new wpdreamsCustomSelect("mob_click_action_location", __(' location: ', 'ajax-search-pro'),
			array(
				'selects' => array(
					array('option' => __('Use same tab', 'ajax-search-pro'), 'value' => 'same'),
					array('option' => __('Open new tab', 'ajax-search-pro'), 'value' => 'new')
				),
				'value' => $sd['mob_click_action_location']
			));
		$params[$o->getName()] = $o->getData();
		?>
	</div>
	<div class="item item-flex-nogrow item-flex-wrap">
		<?php
		$o = new wpdreamsCustomSelect("mob_return_action", __('Action when tapping <strong>the return</strong> button (search icon on virtual keyboard)<br>', 'ajax-search-pro'),
			array(
				'selects' => $_red_opts,
				'value' => $sd['mob_return_action']
			));
		$params[$o->getName()] = $o->getData();
		$o = new wpdreamsCustomSelect("mob_return_action_location", __(' location: ', 'ajax-search-pro'),
			array(
				'selects' => array(
					array('option' => __('Use same tab', 'ajax-search-pro'), 'value' => 'same'),
					array('option' => __('Open new tab', 'ajax-search-pro'), 'value' => 'new')
				),
				'value' => $sd['mob_return_action_location']
			));
		$params[$o->getName()] = $o->getData();
		?>
	</div>
	<div class="item">
		<?php
		$o = new wd_CPTSearchCallBack('mob_redirect_elementor', __('Select a page with an Elementor Pro posts widget', 'ajax-search-pro'), array(
				'value'=>$sd['mob_redirect_elementor'],
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
		$o = new wpdreamsText("mob_redirect_url", __('Custom redirect URL', 'ajax-search-pro'),
			$sd['mob_redirect_url']);
		$params[$o->getName()] = $o->getData();
		?>
		<p class="descMsg">
			<?php echo __('You can use the <string>asp_redirect_url</string> filter to add more variables.', 'ajax-search-pro'); ?>
			<?php echo sprintf( __('See <a href="%s" target="_blank">this tutorial</a>.', 'ajax-search-pro'), 'http://wp-dreams.com/go/?to=kb-redirecturl' ); ?>
		</p>
	</div>
	<div class="item">
		<?php
		$o = new wpdreamsYesNo("mob_hide_keyboard", __('Hide the mobile keyboard when displaying the results?', 'ajax-search-pro'),
			$sd['mob_hide_keyboard']);
		$params[$o->getName()] = $o->getData();
		?>
	</div>
	<div class="item">
		<?php
		$o = new wpdreamsText("mob_auto_focus_menu_selector", __('Auto focus when opening the navigation menu (jQuery selector)', 'ajax-search-pro'),
			$sd['mob_auto_focus_menu_selector']);
		$params[$o->getName()] = $o->getData();
		?>
		<p class="descMsg">
			<?php echo __('When the search is placed within a mobile navigation menu, you can define a jQuery selector of the opening menu item here - which when clicking auto focuses the search', 'ajax-search-pro'); ?>
		</p>
	</div>
	<div class="item">
		<?php
		$o = new wpdreamsYesNo("mob_force_res_hover", __('Force \'hover\' results layout on mobile devices?', 'ajax-search-pro'),
			$sd['mob_force_res_hover']);
		$params[$o->getName()] = $o->getData();
		?>
		<div class="descMsg">
			<?php echo __('This will force to display the results below the search bar (floating above the content) on mobile devices, even if it\'s configured otherwise (or if the results shortcode is used).', 'ajax-search-pro'); ?>
		</div>
	</div>
	<div class="item item-flex-nogrow" style="flex-wrap:wrap;">
		<?php
		$o = new wpdreamsYesNo("mob_force_sett_hover", __('Force \'hover\' settings layout on mobile devices?', 'ajax-search-pro'),
			$sd['mob_force_sett_hover']);
		$params[$o->getName()] = $o->getData();
		?>
		<div class="descMsg item-flex-grow item-flex-100">
			<?php echo __('This will force to display the settings below the search bar (floating above the content) on mobile devices, even if the settings shortcode is used.', 'ajax-search-pro'); ?>
		</div>
	</div>
	<div class="item">
		<?php
		$o = new wpdreamsCustomSelect("mob_force_sett_state", __('Force search settings state', 'ajax-search-pro'), array(
			'selects' => array(
				array("option" => __("Same as desktop", 'ajax-search-pro'), "value" => "none"),
				array("option" => __("Hidden settings", 'ajax-search-pro'), "value" => "closed"),
				array("option" => __("Visible settings", 'ajax-search-pro'), "value" => "open")
			),
			"value" => $sd['mob_force_sett_state']
		));
		$params[$o->getName()] = $o->getData();
		?>
	</div>
</fieldset>