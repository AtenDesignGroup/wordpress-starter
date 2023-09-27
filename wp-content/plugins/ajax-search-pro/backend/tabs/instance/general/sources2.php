<div class="item">
    <?php
    $o = new wpdreamsYesNo("return_categories", __('Return post categories as results?', 'ajax-search-pro'),
        $sd['return_categories']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
	<?php
	$o = new wpdreamsYesNo("return_tags", __('Return post tags as results?', 'ajax-search-pro'),
		$sd['return_tags']);
	$params[$o->getName()] = $o->getData();
	?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsTaxonomySelect("return_terms", __('Return taxonomy terms as results', 'ajax-search-pro'), array(
        "value"=>$sd['return_terms'],
        "type"=>"include"));
    $params[$o->getName()] = $o->getData();
    $params['selected-'.$o->getName()] = $o->getSelected();
    ?>
</div>
<div wd-disable-on="return_categories:0;return_tags:0;return_terms:">
	<div class="item">
		<?php
		$o = new wpdreamsYesNo("search_term_titles", __('Search term titles?', 'ajax-search-pro'),
			$sd['search_term_titles']);
		$params[$o->getName()] = $o->getData();
		?>
	</div>
	<div class="item">
		<?php
		$o = new wpdreamsYesNo("search_term_descriptions", __('Search term descriptions?', 'ajax-search-pro'),
			$sd['search_term_descriptions']);
		$params[$o->getName()] = $o->getData();
		?>
	</div>
	<div class="item">
		<?php
		$o = new wpdreamsYesNo("search_term_meta", __('Search in term metadata?', 'ajax-search-pro'), $sd['search_term_meta']);
		$params[$o->getName()] = $o->getData();
		?>
		<p class="errorMsg">
			<?php echo __('<strong>NOTICE:</strong> This may slow down the search.', 'ajax-search-pro'); ?>
		</p>
	</div>
	<div class="item">
		<?php
		$o = new wpdreamsYesNo("display_number_posts_affected", __('Display the number of posts associated with the terms?', 'ajax-search-pro'),
			$sd['display_number_posts_affected']);
		$params[$o->getName()] = $o->getData();
		?>
		<p class="descMsg">
			<?php echo __('Will display the number of associated posts in a bracket after the term.', 'ajax-search-pro'); ?>
		</p>
	</div>
	<div class="item">
		<?php
		$o = new wpdreamsYesNo("return_terms_exclude_empty", __('Exclude empty taxonomy terms?', 'ajax-search-pro'),
			$sd['return_terms_exclude_empty']);
		$params[$o->getName()] = $o->getData();
		?>
		<p class="descMsg">
			<?php echo __('Ex. categories that does not contain posts', 'ajax-search-pro'); ?>
		</p>
	</div>
	<div class="item">
		<?php
		$o = new wd_TextareaExpandable("return_terms_exclude", __('Exclude categories/terms by ID', 'ajax-search-pro'),
			$sd['return_terms_exclude']);
		$params[$o->getName()] = $o->getData();
		?>
		<p class="descMsg">
			<?php echo __('Comma "," separated list of category/term IDs.', 'ajax-search-pro'); ?>
		</p>
	</div>
</div>