<div class="item">
    <?php
    $o = new wpdreamsCustomSelect("autocomplete", __('Autocomplete status', 'ajax-search-pro'), array(
        'selects'=>array(
            array("option"=>__('Disabled', 'ajax-search-pro'), "value" => 0),
            array("option"=>__('Enabled for all devices', 'ajax-search-pro'), "value" => 1),
            array("option"=>__('Enabled for Desktop only', 'ajax-search-pro'), "value" => 2),
            array("option"=>__('Enabled for Mobile only', 'ajax-search-pro'), "value" => 3)
        ),
        'value'=>$sd['autocomplete']
    ));
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div wd-disable-on="autocomplete:0">
	<div class="item" style="display: none !important;">
		<?php
		// @TODO 4.10.5
		$o = new wpdreamsCustomSelect("autocomplete_mode", __('Autocomplete layout mode', 'ajax-search-pro'), array(
			'selects'=>array(
				array('option'=>__('Input autocomplete', 'ajax-search-pro'), 'value' => 'input'),
				array('option'=>__('Drop-down (like google)', 'ajax-search-pro'), 'value' => 'dropdown')
			),
			'value'=>$sd['autocomplete_mode']
		));
		$params[$o->getName()] = $o->getData();
		?>
	</div>
	<div class="item" style="display: none !important;">
		<?php
		// @TODO 4.10.5
		$o = new wpdreamsCustomSelect("autocomplete_instant", __('<strong>Instant</strong> autocomplete', 'ajax-search-pro'), array(
			'selects'=>array(
				array('option'=>__('Automatic (enabled)', 'ajax-search-pro'), 'value' => 'auto', 'disabled' => 1),
				array('option'=>__('Enabled', 'ajax-search-pro'), 'value' => 'enabled'),
				array('option'=>__('Disabled', 'ajax-search-pro'), 'value' => 'disabled')
			),
			'value'=>$sd['autocomplete_instant']
		));
		$params[$o->getName()] = $o->getData();
		?>
		<div class="descMsg">
			<a href="">What is <strong>instant autocomplete</strong> and how it works?</a>
		</div>
	</div>
	<div class="item" style="padding-right:20px;display:none !important;">
		<!-- @TODO 4.10.5 -->
		<label>Instant Autocomplete Database</label>
		<input type="button" id="asp_inst_generate" class="asp_inst_generate wd_button_green asp_submit" value="Generate">
		<input type="button" id="asp_inst_generate_save" class="asp_inst_generate wd_button_red asp_submit" value="Generate & Save options">
		<input type="button" id="asp_inst_generate_cancel" class="asp_inst_generate wd_button_red asp_submit hiddend" value="Cancel">
		<input type="button" id="asp_inst_generate_d" class="asp_inst_generate wd_button_green asp_submit hiddend" value="DB up to date for this configuration!" disabled>
		<div class="wd_progress wd_progress_75 hiddend"><span style="width:0%;"></span></div>
		<div class="descMsg">
			In order for the instant suggestions to work, the suggestions database must be generated.
		</div>
		<br>
		<?php
		$o = new wpdreamsTextSmall("autocomplete_instant_limit", __('<strong>Instant</strong> autocomplete item count per source', 'ajax-search-pro'), $sd['autocomplete_instant_limit']);
		$params[$o->getName()] = $o->getData();
		?>
		<div class="descMsg">
			<?php echo __('1500 is an optimal count. Changing this to higher numbers may reduce the initial page load time.', 'ajax-search-pro'); ?>
		</div>
		<?php
		$o = new wpdreamsHidden("autocomplete_instant_status", '', $sd['autocomplete_instant_status']);
		$params[$o->getName()] = $o->getData();
		$o = new wpdreamsHidden("autocomplete_instant_gen_config", '', $sd['autocomplete_instant_gen_config']);
		$params[$o->getName()] = $o->getData();
		?>
	</div>
	<div class="item">
		<?php
		$o = new wpdreamsDraggable("autocomplete_source", __('Autocomplete suggestion sources', 'ajax-search-pro'), array(
			'selects'=>$sugg_select_arr,
			'value'=>$sd['autocomplete_source'],
			'description'=>__('Select which sources you prefer for autocomplete. Order counts.', 'ajax-search-pro')
		));
		$params[$o->getName()] = $o->getData();
		?>
	</div>
	<div class="item"><?php
		$o = new wpdreamsTextSmall("autoc_trigger_charcount", __('Minimal character count to trigger autocomplete', 'ajax-search-pro'), $sd['autoc_trigger_charcount']);
		$params[$o->getName()] = $o->getData();
		?>
	</div>
	<div class="item hiddend"><?php
		$o = new wpdreamsText("autoc_google_places_api", __('Google places API key', 'ajax-search-pro'), $sd['autoc_google_places_api']);
		$params[$o->getName()] = $o->getData();
		?>
		<p class="errorMsg">
			<?php echo sprintf( __('This is required for the Google Places API to work. You can <a href="%s" target="_blank">get your API key here</a>.', 'ajax-search-pro'),
				'https://developers.google.com/places/web-service/autocomplete' ); ?>
		</p>
	</div>
	<div class="item"><?php
		$o = new wpdreamsTextSmall("autocomplete_length", __('Max. suggestion length', 'ajax-search-pro'),
			$sd['autocomplete_length']);
		$params[$o->getName()] = $o->getData();
		?>
		<p class="descMsg">
			<?php echo __('The length of each suggestion in characters. 30-60 is a good number to avoid too long suggestions.', 'ajax-search-pro'); ?>
		</p>
	</div>
	<div class="item"><?php
		$o = new wpdreamsLanguageSelect("autocomplete_google_lang", __('Google autocomplete suggestions language', 'ajax-search-pro'),
			$sd['autocomplete_google_lang']);
		$params[$o->getName()] = $o->getData();
		?>
	</div>
	<div class="item">
		<?php
		$o = new wd_TextareaExpandable("autocompleteexceptions", __('Keyword exceptions (comma separated)', 'ajax-search-pro'), $sd['autocompleteexceptions']);
		$params[$o->getName()] = $o->getData();
		?>
	</div>
</div>