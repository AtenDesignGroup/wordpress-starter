<div class="item">
    <?php
    $o = new wpdreamsYesNo("result_suggestions", __('Predictively suggest results when nothing matches the search keyword?', 'ajax-search-pro'),
        $sd['result_suggestions']);
    $params[$o->getName()] = $o->getData();
    ?>
    <p class="descMsg">
        <?php echo __('The first matching keyword is going to be used from the selected <strong>Keyword Suggestion Sources</strong> below to conduct an additional search for possible results.', 'ajax-search-pro'); ?>
    </p>
</div>
<div class="item">
    <?php
    $o = new wpdreamsYesNo("keywordsuggestions", __('Keyword suggestions on no results?', 'ajax-search-pro'),
        $sd['keywordsuggestions']);
    $params[$o->getName()] = $o->getData();
    ?>
	<p class="descMsg">
		<?php echo __('Keyword suggestions appear when no results match the keyword.', 'ajax-search-pro'); ?>
	</p>
</div>
<div wd-disable-on="result_suggestions:0;keywordsuggestions:0">
	<div class="item">
		<?php
		$o = new wpdreamsDraggable("keyword_suggestion_source", __('Keyword suggestion sources', 'ajax-search-pro'), array(
			'selects'=> $sugg_select_arr,
			'value'=>$sd['keyword_suggestion_source'],
			'description'=>'Select which sources you prefer for keyword suggestions. Order counts.'
		));
		$params[$o->getName()] = $o->getData();
		?>
	</div>
	<div class="item hiddend"><?php
		$o = new wpdreamsText("kws_google_places_api", __('Google places API key', 'ajax-search-pro'), $sd['kws_google_places_api']);
		$params[$o->getName()] = $o->getData();
		?>
		<p class="errorMsg">
			<?php echo sprintf( __('This is required for the Google Places API to work. You can <a href="%s" target="_blank">get your API key here</a>.', 'ajax-search-pro'),
				'https://developers.google.com/places/web-service/autocomplete' ); ?>
		</p>
	</div>
	<div class="item item-flex-nogrow item-flex-wrap"><?php
		$o = new wpdreamsTextSmall("keyword_suggestion_count", __('Max. suggestion count', 'ajax-search-pro'),
			$sd['keyword_suggestion_count']);
		$params[$o->getName()] = $o->getData();
		$o = new wpdreamsTextSmall("keyword_suggestion_length", __('word length', 'ajax-search-pro'),
			$sd['keyword_suggestion_length']);
		$params[$o->getName()] = $o->getData();

		$o = new wpdreamsLanguageSelect("keywordsuggestionslang", __('Google suggestions language', 'ajax-search-pro'),
			$sd['keywordsuggestionslang']);
		$params[$o->getName()] = $o->getData();
		?>
		<div class="descMsg item-flex-grow item-flex-100">
			<?php echo __('The length of each suggestion in characters. 30-50 is a good number to avoid too long suggestions.', 'ajax-search-pro'); ?>
		</div>
	</div>
	<div class="item">
		<?php
		$o = new wd_TextareaExpandable("noresultstext", __('No results text', 'ajax-search-pro'), $sd['noresultstext']);
		$params[$o->getName()] = $o->getData();
		?>
		<p class="descMsg">
			<?php echo __('Supports HTML and variable {phrase}', 'ajax-search-pro'); ?>
		</p>
	</div>
	<div class="item">
		<?php
		$o = new wd_TextareaExpandable("didyoumeantext", __('Did you mean text', 'ajax-search-pro'), $sd['didyoumeantext']);
		$params[$o->getName()] = $o->getData();
		?>
		<p class="descMsg">
			<?php echo __('Supports HTML', 'ajax-search-pro'); ?>
		</p>
	</div>
	<div class="item">
		<a class="asp_to_tab" href="#614" tabid="614">Go to Keyword Suggestions styling options >></a>
	</div>
</div>