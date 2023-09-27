<div class="item">
	<?php
	$o = new wpdreamsYesNo("triggerontype", __('Trigger <strong>live</strong> search when typing?', 'ajax-search-pro'),
		$sd['triggerontype']);
	$params[$o->getName()] = $o->getData();
	?>
</div>
<div class="item">
	<?php
	$o = new wpdreamsYesNo("trigger_on_facet", __('Trigger <strong>live</strong> search when changing a facet on settings (like checkboxes, drop-downs etc..)?', 'ajax-search-pro'),
		$sd['trigger_on_facet']);
	$params[$o->getName()] = $o->getData();
	?>
	<p class="descMsg">
		<?php echo __('Will trigger the search if the user changes a checkbox, radio button, slider on the frontend
            search settings panel.', 'ajax-search-pro'); ?>
	</p>
</div>
<div class="item">
	<?php
	$o = new wpdreamsYesNo("trigger_update_href", __('Update the browser address bar with the last selected options?', 'ajax-search-pro'),
		$sd['trigger_update_href']);
	$params[$o->getName()] = $o->getData();
	?>
	<p class="descMsg">
		<?php echo __('The current state of the search and the filters is reflected in the address bar and remembered for the browser back/forward buttons.', 'ajax-search-pro'); ?>
	</p>
</div>
<div class="item">
	<?php
	$o = new wpdreamsTextSmall("charcount", __('Minimal character count to trigger search', 'ajax-search-pro'), $sd['charcount']);
	$params[$o->getName()] = $o->getData();
	?>
</div>