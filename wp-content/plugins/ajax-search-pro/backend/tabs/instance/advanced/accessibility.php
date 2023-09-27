<fieldset>
	<legend><?php echo __('Aria Labels', 'ajax-search-pro'); ?></legend>
	<div class="item">
		<?php
		$o = new wpdreamsText("aria_search_form_label",
			__('Search form aria-label', 'ajax-search-pro'),
			$sd['aria_search_form_label']
		);
		?>
	</div>
	<div class="item">
		<?php
		$o = new wpdreamsText("aria_settings_form_label",
			__('Search Settings form aria-label', 'ajax-search-pro'),
			$sd['aria_settings_form_label']
		);
		?>
	</div>
	<div class="item">
		<?php
		$o = new wpdreamsText("aria_search_input_label",
			__('Search input aria-label', 'ajax-search-pro'),
			$sd['aria_search_input_label']
		);
		?>
	</div>
	<div class="item">
		<?php
		$o = new wpdreamsText("aria_search_autocomplete_label",
			__('Search autocomplete input aria-label', 'ajax-search-pro'),
			$sd['aria_search_autocomplete_label']
		);
		?>
	</div>
	<div class="item">
		<?php
		$o = new wpdreamsText("aria_magnifier_label",
			__('Search magnifier button aria-label', 'ajax-search-pro'),
			$sd['aria_magnifier_label']
		);
		?>
	</div>
</fieldset>