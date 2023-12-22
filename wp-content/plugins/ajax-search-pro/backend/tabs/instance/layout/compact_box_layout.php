<div class="item">
    <?php
    $o = new wpdreamsYesNo("box_compact_layout", __('Compact layout mode', 'ajax-search-pro'), $sd['box_compact_layout']);
    $params[$o->getName()] = $o->getData();
    ?>
    <p class="descMsg">
        <?php echo __('In compact layout only the search magnifier is visible, and the user has to click on the magnifier first to show the search bar.', 'ajax-search-pro'); ?>
    </p>
</div>
<div wd-disable-on="box_compact_layout:0">
	<div class="item item-flex-nogrow item-flex-wrap">
		<?php
		$o = new wpdreamsCustomSelect("box_compact_layout_desktop", __('Compact mode on Devices: ', 'ajax-search-pro'), array(
				'selects'=>array(
					array('option'=>'Enabled', 'value'=>'1'),
					array('option'=>'Disabled', 'value'=>'0')
				),
				'icon'=>'desktop',
				'value'=>$sd['box_compact_layout_desktop']
			)
		);
		$o = new wpdreamsCustomSelect("box_compact_layout_tablet", __('', 'ajax-search-pro'), array(
				'selects'=>array(
					array('option'=>'Enabled', 'value'=>'1'),
					array('option'=>'Disabled', 'value'=>'0')
				),
				'icon'=>'tablet',
				'value'=>$sd['box_compact_layout_tablet']
			)
		);
		$o = new wpdreamsCustomSelect("box_compact_layout_mobile", __('', 'ajax-search-pro'), array(
				'selects'=>array(
					array('option'=>'Enabled', 'value'=>'1'),
					array('option'=>'Disabled', 'value'=>'0')
				),
				'icon'=>'phone',
				'value'=>$sd['box_compact_layout_mobile']
			)
		);
		?>
	</div>
	<div class="item">
		<?php
		$o = new wpdreamsYesNo("box_compact_layout_focus_on_open", __('Auto focus the input field, after the compact box opens', 'ajax-search-pro'), $sd['box_compact_layout_focus_on_open']);
		$params[$o->getName()] = $o->getData();
		?>
	</div>
	<div class="item">
		<?php
		$o = new wpdreamsYesNo("box_compact_close_on_magn", __('Close on magnifier click', 'ajax-search-pro'), $sd['box_compact_close_on_magn']);
		$params[$o->getName()] = $o->getData();
		?>
		<p class="descMsg">
			<?php echo __('Closes the box when the magnifier is clicked.', 'ajax-search-pro'); ?>
		</p>
	</div>
	<div class="item">
		<?php
		$o = new wpdreamsYesNo("box_compact_close_on_document", __('Close on document click', 'ajax-search-pro'), $sd['box_compact_close_on_document']);
		$params[$o->getName()] = $o->getData();
		?>
		<p class="descMsg">
			<?php echo __('Closes the box when the document is clicked.', 'ajax-search-pro'); ?>
		</p>
	</div>
	<div class="item item-flex-nogrow item-flex-wrap wpd-isotopic-width" style="flex-wrap: wrap;">
		<?php
		$o = new wpdreamsTextSmall("box_compact_width", __('Compact box final width', 'ajax-search-pro'), array(
			'icon' => 'desktop',
			'value' => $sd['box_compact_width']
		));
		$params[$o->getName()] = $o->getData();
		$o = new wpdreamsTextSmall("box_compact_width_tablet", '', array(
			'icon' => 'tablet',
			'value' => $sd['box_compact_width_tablet']
		));
		$params[$o->getName()] = $o->getData();
		$o = new wpdreamsTextSmall("box_compact_width_phone", '', array(
			'icon' => 'phone',
			'value' => $sd['box_compact_width_phone']
		));
		$params[$o->getName()] = $o->getData();
		?>
		<div class="descMsg item-flex-grow item-flex-100">
			<?php echo sprintf(
				__('Use with <a href="%s" target="_blank">CSS units</a> (like %s or %s or %s ..) Default: <strong>%s</strong>', 'ajax-search-pro'),
				'https://www.w3schools.com/cssref/css_units.asp', '200px', '50%', 'auto', '100%'
			); ?>
			<br>
			<?php echo __('You might need to adjust this to a static value like 200px, as 100% is not always working in compact mode.', 'ajax-search-pro'); ?>
		</div>
	</div>
	<div class="item item-flex-nogrow item-flex-wrap">
		<?php
		$o = new wpdreamsYesNo("box_compact_overlay", __('Display background overlay?', 'ajax-search-pro'), $sd['box_compact_overlay']);
		$params[$o->getName()] = $o->getData();
		?>
		<?php
		$o = new wpdreamsColorPicker("box_compact_overlay_color", __(' color ', 'ajax-search-pro'), $sd['box_compact_overlay_color']);
		$params[$o->getName()] = $o->getData();
		?>
		<div class="descMsg item-flex-grow item-flex-100">
			<?php echo __('Overlay only works with if the <strong>box position is set to Fixed</strong> below.', 'ajax-search-pro'); ?>
		</div>
	</div>
	<div class="item"><?php
		$o = new wpdreamsCustomSelect("box_compact_float", __('Compact layout alignment', 'ajax-search-pro'),
			array(
				'selects' => array(
					array('option' => __('No floating', 'ajax-search-pro'), 'value' => 'none'),
					array('option' => __('Left', 'ajax-search-pro'), 'value' => 'left'),
					array('option' => __('Right', 'ajax-search-pro'), 'value' => 'right')
				),
				'value' => $sd['box_compact_float']
			));
		$params[$o->getName()] = $o->getData();
		?>
		<p class="descMsg">
			<?php echo __('By default the search box floats with the theme default (none). You can change that here.', 'ajax-search-pro'); ?>
		</p>
	</div>
	<div class="item"><?php
		$o = new wpdreamsCustomSelect("box_compact_position", __('Compact search box position', 'ajax-search-pro'),
			array(
				'selects' => array(
					array('option' => __('Static (default)', 'ajax-search-pro'), 'value' => 'static'),
					array('option' => __('Fixed', 'ajax-search-pro'), 'value' => 'fixed'),
					array('option' => __('Absolute', 'ajax-search-pro'), 'value' => 'absolute')
				),
				'value' => $sd['box_compact_position']
			));
		$params[$o->getName()] = $o->getData();
		?>
		<p class="descMsg">
			<?php echo __('In absolute position the search can not affect it\'s parent element height as absolutely positioned elements are removed from the flow, thus ignored by other elements.', 'ajax-search-pro'); ?>
		</p>
	</div>
	<div class="item">
		<?php
		$option_name = "box_compact_screen_position";
		$option_desc = __('Position values', 'ajax-search-pro');
		$option_expl = __('You can use auto or include the unit as well, example: 10px or 1em or 90%', 'ajax-search-pro');
		$o = new wpdreamsFour($option_name, $option_desc,
			array(
				"desc" => $option_expl,
				"value" => $sd[$option_name]
			)
		);
		$params[$o->getName()] = $o->getData();
		?>
	</div>
	<div class="item">
		<?php
		$o = new wpdreamsTextSmall("box_compact_position_z", __('z-index', 'ajax-search-pro'), $sd['box_compact_position_z']);
		$params[$o->getName()] = $o->getData();
		?>
		<p class="descMsg">
			<?php echo __('In case you have some other elements floating above/below the search icon, you can adjust it\'s position with the z-index.', 'ajax-search-pro'); ?>
		</p>
	</div>
</div>

