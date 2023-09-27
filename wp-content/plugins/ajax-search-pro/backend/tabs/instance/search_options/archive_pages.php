<fieldset id="res_live_search">
	<legend>
		<?php echo __('Results and Archive page live loaders', 'ajax-search-pro'); ?>
		<span class="asp_legend_docs">
            <a target="_blank" href="https://documentation.ajaxsearchpro.com/behavior/results_page_live_loader"><span class="fa fa-book"></span>
                <?php echo __('Documentation', 'ajax-search-pro'); ?>
            </a>
        </span>
	</legend>
	<div class="errorMsg">
		<?php echo sprintf( __('<strong>Disclaimer:</strong> Live loading items to a page causes the script event handlers to detach on the affected elements - if there are
        interactive elements (pop-up buttons etc..) controlled by a script within the results, they will probably stop working after a live load.
        This cannot be prevented from this plugins perspective. <a href="%s" target="_blank">More information here.</a>', 'ajax-search-pro'), 'https://documentation.ajaxsearchpro.com/behavior/results_page_live_loader' ); ?>
	</div>
	<div class="item">
		<?php
		$o = new wpdreamsYesNo("res_live_search", __('Live load the results on the results page? <strong>(experimental)</strong>', 'ajax-search-pro'),
			$sd['res_live_search']);
		$params[$o->getName()] = $o->getData();
		?>
		<div class="descMsg">
			<?php echo __('If this is enabled, and the current page is the results page, the plugin will try to load the results there, without reloading the page.', 'ajax-search-pro'); ?>
		</div>
	</div>
	<div class="item">
		<?php
		$o = new wpdreamsText("res_live_selector", __('Results container DOM element selector', 'ajax-search-pro'), $sd['res_live_selector']);
		$params[$o->getName()] = $o->getData();
		?>
		<div class="descMsg">
			<?php echo __('In many themes this is <strong>#main</strong>, but it can be different. This is very important to get right, or this will surely not work. The plugin will try other values as well, if this fails.', 'ajax-search-pro'); ?>
		</div>
	</div>
	<div class="item">
		<?php
		$o = new wpdreamsYesNo("woo_shop_live_search", __('Live load the results on the WooCommerce Shop page? <strong>(experimental)</strong>', 'ajax-search-pro'),
			$sd['woo_shop_live_search']);
		$params[$o->getName()] = $o->getData();
		?>
		<div class="descMsg">
			<?php echo __('If this is enabled, and the current page is the results page, the plugin will try to load the results there, without reloading the page.', 'ajax-search-pro'); ?>
		</div>
	</div>
	<div class="item">
		<?php
		$o = new wpdreamsText("woo_shop_live_selector", __('Results container DOM element selector', 'ajax-search-pro'), $sd['woo_shop_live_selector']);
		$params[$o->getName()] = $o->getData();
		?>
		<div class="descMsg">
			<?php echo __('In many themes this is <strong>#main</strong>, but it can be different. This is very important to get right, or this will surely not work. The plugin will try other values as well, if this fails.', 'ajax-search-pro'); ?>
		</div>
	</div>
	<div class="item">
		<?php
		$o = new wpdreamsYesNo("taxonomy_archive_live_search", __('Live load/filter the Taxonomy Archive pages (category, tag etc..)? <strong>(experimental)</strong>', 'ajax-search-pro'),
			$sd['taxonomy_archive_live_search']);
		$params[$o->getName()] = $o->getData();
		?>
		<div class="descMsg">
			<?php echo __('If this is enabled, and the current page is the results page, the plugin will try to load the results there, without reloading the page.', 'ajax-search-pro'); ?>
		</div>
	</div>
	<div class="item">
		<?php
		$o = new wpdreamsText("taxonomy_archive_live_selector", __('Results container DOM element selector', 'ajax-search-pro'), $sd['taxonomy_archive_live_selector']);
		$params[$o->getName()] = $o->getData();
		?>
	</div>
	<div class="item">
		<?php
		$o = new wpdreamsYesNo("cpt_archive_live_search", __('Live load/filter the Post Type Archive pages (post, portfolio etc..)? <strong>(experimental)</strong>', 'ajax-search-pro'),
			$sd['cpt_archive_live_search']);
		$params[$o->getName()] = $o->getData();
		?>
		<div class="descMsg">
			<?php echo __('If this is enabled, and the current page is the results page, the plugin will try to load the results there, without reloading the page.', 'ajax-search-pro'); ?>
		</div>
	</div>
	<div class="item">
		<?php
		$o = new wpdreamsText("cpt_archive_live_selector", __('Results container DOM element selector', 'ajax-search-pro'), $sd['cpt_archive_live_selector']);
		$params[$o->getName()] = $o->getData();
		?>
	</div>
</fieldset>
<fieldset id="elementor_live_search_2">
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
<fieldset id="res_live_search_triggers">
	<legend><?php echo __('Results page live loader and Elementor post widget override triggers', 'ajax-search-pro'); ?></legend>
	<div class="item">
		<?php
		$o = new wpdreamsYesNo("res_live_trigger_type", __('Trigger live search when typing?', 'ajax-search-pro'),
			$sd['res_live_trigger_type']);
		$params[$o->getName()] = $o->getData();
		?>
		<div class="descMsg">
			<?php echo __('If enabled, on the results page (or custom Elementor posts widget page), overrides the default behavior.', 'ajax-search-pro'); ?>
		</div>
	</div>
	<div class="item">
		<?php
		$o = new wpdreamsYesNo("res_live_trigger_facet", __('Trigger live search when changing a facet on settings?', 'ajax-search-pro'),
			$sd['res_live_trigger_facet']);
		$params[$o->getName()] = $o->getData();
		?>
		<div class="descMsg">
			<?php echo __('If enabled, on the results page (or custom Elementor posts widget page), overrides the default behavior.', 'ajax-search-pro'); ?>
		</div>
	</div>
	<div class="item">
		<?php
		$o = new wpdreamsYesNo("res_live_trigger_click", __('Trigger live search when clicking the magnifier button?', 'ajax-search-pro'),
			$sd['res_live_trigger_click']);
		$params[$o->getName()] = $o->getData();
		?>
		<div class="descMsg">
			<?php echo __('If enabled, on the results page (or custom Elementor posts widget page), overrides the default behavior.', 'ajax-search-pro'); ?>
		</div>
	</div>
	<div class="item">
		<?php
		$o = new wpdreamsYesNo("res_live_trigger_return", __('Trigger live search when hitting the return key?', 'ajax-search-pro'),
			$sd['res_live_trigger_return']);
		$params[$o->getName()] = $o->getData();
		?>
		<div class="descMsg">
			<?php echo __('If enabled, on the results page (or custom Elementor posts widget page), overrides the default behavior.', 'ajax-search-pro'); ?>
		</div>
	</div>
</fieldset>