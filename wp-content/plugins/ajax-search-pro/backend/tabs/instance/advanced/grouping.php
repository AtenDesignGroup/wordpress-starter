<div class="item">
	<?php
	$o = new wpdreamsCustomSelect("group_by", __('Group results by', 'ajax-search-pro'), array(
			'selects'=> array(
				array("value" => "none", "option" => __('No grouping', 'ajax-search-pro')),
				array("value" => "post_type", "option" => __('Post Type', 'ajax-search-pro')),
				array("value" => "categories_terms", "option" => __('Categories/Terms', 'ajax-search-pro')),
				array("value" => "content_type", "option" => __('Content Type', 'ajax-search-pro'))
			),
			'value'=>$sd['group_by']) );
	$params[$o->getName()] = $o->getData();
	?>
	<p class="descMsg"><?php echo __('Only works with <b>Vertical</b> results layout.', 'ajax-search-pro'); ?></p>
</div>
<div class="item wd_groupby wd_groupby_categories_terms">
	<?php
	$o = new wd_TaxonomyTermSelect("groupby_terms", __('Category/Term grouping options', 'ajax-search-pro'), array(
			"value" => $sd['groupby_terms'],
			"args"  => array(
					"show_type" => 0,
					"show_checkboxes" => 0
			)
	));
	$params[$o->getName()] = $o->getData();
	?>
</div>
<div class="item wd_groupby wd_groupby_content_type">
	<?php
	$o = new wd_Sortable_Editable("groupby_content_type", __('Content type grouping options', 'ajax-search-pro'), $sd['groupby_content_type']);
	$params[$o->getName()] = $o->getData();
	?>
</div>
<div class="item wd_groupby wd_groupby_post_type">
	<?php
	$o = new wd_CPT_Editable("groupby_cpt", __('Custom Post Type grouping options', 'ajax-search-pro'), $sd['groupby_cpt']);
	$params[$o->getName()] = $o->getData();
	?>
</div>
<div class="item wd_groupby_op">
	<?php
	$o = new wpdreamsYesNo("group_reorder_by_pr", __('Reorder groups by highest priority and relevance in results?', 'ajax-search-pro'), $sd['group_reorder_by_pr']);
	$params[$o->getName()] = $o->getData();
	?>
	<p class="descMsg"><?php echo __('The groups are reordered according to the highest priority and relevance of each result within each individual group.', 'ajax-search-pro'); ?></p>
</div>
<div class="item wd_groupby_op">
	<?php
	$o = new wpdreamsText("group_header_prefix", __('Group header prefix text', 'ajax-search-pro'), $sd['group_header_prefix']);
	$params[$o->getName()] = $o->getData();
	?>
</div>
<div class="item wd_groupby_op">
	<?php
	$o = new wpdreamsText("group_header_suffix", __('Group header suffix text', 'ajax-search-pro'), $sd['group_header_suffix']);
	$params[$o->getName()] = $o->getData();
	?>
</div>
<div class="item wd_groupby_op">
	<?php
	$o = new wpdreamsCustomSelect("group_result_no_group", __('If result does not match any group?', 'ajax-search-pro'), array(
		'selects'=> array(
				array("value" => "remove", "option" => __('Remove it', 'ajax-search-pro')),
				array("value" => "display", "option" => __('Display in Other results group', 'ajax-search-pro'))
		),
		'value'=>$sd['group_result_no_group']) );
	$params[$o->getName()] = $o->getData();
	?>
</div>
<div class="item wd_groupby_op item-flex-nogrow item-flex-wrap">
	<?php
	$o = new wpdreamsText("group_other_results_head", __('Other results group header text', 'ajax-search-pro'), $sd['group_other_results_head']);
	$params[$o->getName()] = $o->getData();
	$o = new wpdreamsCustomSelect("group_other_location", ' ' . __('location' . ' ', 'ajax-search-pro'), array(
			'selects'=> array(
					array("value" => "top", "option" => __('Top of results', 'ajax-search-pro')),
					array("value" => "bottom", "option" => __('Bottom of results', 'ajax-search-pro'))
			),
			'value'=>$sd['group_other_location']) );
	$params[$o->getName()] = $o->getData();
	?>
</div>
<div class="item wd_groupby_op">
	<?php
	$o = new wpdreamsYesNo("group_exclude_duplicates", __('Display duplicates only in the first group match?', 'ajax-search-pro'), $sd['group_exclude_duplicates']);
	$params[$o->getName()] = $o->getData();
	?>
	<p class="descMsg"><?php echo __('For example posts in multiple categories will be displayed in the first matching group only.', 'ajax-search-pro'); ?></p>
</div>
<div class="item wd_groupby_op item-flex-nogrow item-flex-wrap">
	<?php
	$o = new wpdreamsYesNo("group_show_empty", __('Display empty groups with the \'No results!\' text?', 'ajax-search-pro'), $sd['group_show_empty']);
	$params[$o->getName()] = $o->getData();

	$o = new wpdreamsCustomSelect("group_show_empty_position", __(' ..emtpy group location ', 'ajax-search-pro'), array(
		'selects'=> array(
			array("value" => "default", "option" => __('Leave the default', 'ajax-search-pro')),
			array("value" => "bottom", "option" => __('Move to the bottom', 'ajax-search-pro')),
			array("value" => "top", "option" => __('Move to the top', 'ajax-search-pro'))
		),
		'value'=>$sd['group_show_empty_position']) );
	$params[$o->getName()] = $o->getData();
	?>
</div>
<div class="item wd_groupby_op">
	<?php
	$o = new wpdreamsYesNo("group_result_count", __('Show results count in group headers', 'ajax-search-pro'), $sd['group_result_count']);
	$params[$o->getName()] = $o->getData();
	?>
</div>