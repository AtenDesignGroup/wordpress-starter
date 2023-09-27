<div class="item">
	<?php
	$o = new wpdreamsYesNo("display_search_text", __('Display the search text button?', 'ajax-search-pro'),
		$sd['display_search_text']);
	$params[$o->getName()] = $o->getData();
	?>
</div>
<div class="item">
	<?php
	$o = new wpdreamsYesNo("hide_magnifier", __('Hide the magnifier icon?', 'ajax-search-pro'),
		$sd['hide_magnifier']);
	$params[$o->getName()] = $o->getData();
	?>
</div>
<div class="item">
	<?php
	$o = new wpdreamsText("search_text", __('Button text', 'ajax-search-pro'),
		$sd['search_text']);
	$params[$o->getName()] = $o->getData();
	?>
</div>
<div class="item">
	<?php
	$o = new wpdreamsCustomSelect("search_text_position", __('Button position', 'ajax-search-pro'), array(
		'selects'=>array(
			array('option' => __('Left to the magnifier', 'ajax-search-pro'), 'value' => "left"),
			array('option' => __('Right to the magnifier', 'ajax-search-pro'), 'value' => "right")
		),
		'value'=>$sd['search_text_position']) );
	$params[$o->getName()] = $o->getData();
	?>
</div>
<div class="item"><?php
	$o = new wpdreamsFontComplete("search_text_font", __('Button font', 'ajax-search-pro'), $sd['search_text_font']);
	$params[$o->getName()] = $o->getData();
	?>
</div>