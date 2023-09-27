<fieldset>
	<legend>
		<?php echo __('Logic and matching', 'ajax-search-pro'); ?>
		<span class="asp_legend_docs">
            <a target="_blank" href="https://documentation.ajaxsearchpro.com/search-logic"><span class="fa fa-book"></span>
                <?php echo __('Documentation', 'ajax-search-pro'); ?>
            </a>
        </span>
	</legend>
	<div class="item item-flex-nogrow item-flex-wrap">
		<div wd-disable-on="exactonly:1">
			<?php
			$o = new wpdreamsCustomSelect("keyword_logic", __('Primary keyword logic', 'ajax-search-pro'),
				array(
					'selects' => array(
						array('option' => __('OR', 'ajax-search-pro'), 'value' => 'or'),
						array('option' => __('OR with exact word matches', 'ajax-search-pro'), 'value' => 'orex'),
						array('option' => __('AND', 'ajax-search-pro'), 'value' => 'and'),
						array('option' => __('AND with exact word matches', 'ajax-search-pro'), 'value' => 'andex')
					),
					'value' => $sd['keyword_logic']
				));
			$params[$o->getName()] = $o->getData();
			?>
		</div>
		<div>
			<?php
			$o = new wpdreamsCustomSelect('secondary_kw_logic', __('Secondary logic', 'ajax-search-pro'),
				array(
					'selects' => array(
						array('option' => __('Disabled', 'ajax-search-pro'), 'value' => 'none'),
						array('option' => __('OR', 'ajax-search-pro'), 'value' => 'or'),
						array('option' => __('OR with exact word matches', 'ajax-search-pro'), 'value' => 'orex'),
						array('option' => __('AND', 'ajax-search-pro'), 'value' => 'and'),
						array('option' => __('AND with exact word matches', 'ajax-search-pro'), 'value' => 'andex')
					),
					'value' => $sd['secondary_kw_logic']
				));
			$params[$o->getName()] = $o->getData();
			?>
		</div>
		<div class="descMsg item-flex-grow item-flex-100">
			<?php echo sprintf( __('<strong>Secodary logic</strong> is used when the results count does not reach the limit. More <a href="%s" target="_blank">information about logics here</a>.', 'ajax-search-pro'), 'https://documentation.ajaxsearchpro.com/search-logic/search-logics-explained' ); ?>
		</div>
	</div>
	<div class="item item-flex-nogrow item-conditional" style="flex-wrap: wrap;">
		<?php
		$o = new wpdreamsYesNo("exactonly", __('Show exact matches only?', 'ajax-search-pro'),
			$sd['exactonly']);
		$params[$o->getName()] = $o->getData();
		?>
		<div wd-disable-on="exactonly:0">
			<?php
			$o = new wpdreamsCustomSelect('exact_match_location', __('..and match fields against the search phrase', 'ajax-search-pro'),
				array(
					'selects' => array(
						array('option' => __('Anywhere', 'ajax-search-pro'), 'value' => 'anywhere'),
						array('option' => __('Starting with phrase', 'ajax-search-pro'), 'value' => 'start'),
						array('option' => __('Ending with phrase', 'ajax-search-pro'), 'value' => 'end'),
						array('option' => __('Complete match', 'ajax-search-pro'), 'value' => 'full')
					),
					'value' => $sd['exact_match_location']
				));
			$params[$o->getName()] = $o->getData();
			?>
		</div>
		<div class="descMsg" wd-enable-on="exactonly:1;secondary_kw_logic:or,orex,and,andex" style="margin-top:4px;min-width: 100%;flex-wrap: wrap;flex-basis: auto;flex-grow: 1;box-sizing: border-box;">
			<?php
			$o = new wpdreamsYesNo("exact_m_secondary", __(' ..allow Secondary logic when exact matching?', 'ajax-search-pro'),
				$sd['exact_m_secondary']);
			$params[$o->getName()] = $o->getData();
			?></div>
		<div class="descMsg item-flex-grow item-flex-100">
			<?php echo __('If this is enabled, the Regular search engine is used. Index table engine doesn\'t support exact matches.', 'ajax-search-pro'); ?>
		</div>
	</div>
	<div class="item">
		<?php
		$o = new wpdreamsTextSmall("min_word_length", __('Minimum word length', 'ajax-search-pro'), $sd['min_word_length']);
		$params[$o->getName()] = $o->getData();
		?>
		<p class="descMsg">
			<?php echo __('Words shorter than this will not be treated as separate keywords. Higher value increases performance, lower increase accuracy. Recommended values: 2-5', 'ajax-search-pro'); ?>
		</p>
	</div>
</fieldset>