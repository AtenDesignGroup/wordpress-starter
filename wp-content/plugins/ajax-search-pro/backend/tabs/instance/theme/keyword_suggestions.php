<div class="item">
	<?php
    new wpdreamsFontComplete("kw_suggest_font", __('Keyword suggestions base font', 'ajax-search-pro'), $sd['kw_suggest_font']);
    ?>
</div>
<div class="item item-flex-nogrow">
	<?php
    new wpdreamsColorPicker("kw_suggest_kw_font_color", __('Keyword color', 'ajax-search-pro'), $sd['kw_suggest_kw_font_color']);
    new wpdreamsColorPicker("kw_suggest_didyoumean_font_color", __('"Did you mean?" text color', 'ajax-search-pro'), $sd['kw_suggest_didyoumean_font_color']);
    ?>
</div>
<div class="item">
	<?php
	new wpdreamsColorPicker("kw_suggest_bg", __('Suggestions box background', 'ajax-search-pro'), $sd['kw_suggest_bg']);
	?>
</div>
<div class="item">
    <?php
    new wpdreamsBorder("kw_suggest_border", __('Suggestions box border', 'ajax-search-pro'), $sd['kw_suggest_border']);
    ?>
</div>
<div class="item">
    <?php
    new wpdreamsBoxShadow("kw_suggest_box_shadow", __('Results box Shadow', 'ajax-search-pro'), $sd['kw_suggest_box_shadow']);
    ?>
</div>
<div class="item item-flex-nogrow">
	<?php
	new wd_ANInputs("kw_suggest_padding", __('Padding', 'ajax-search-pro'),
		array(
			'args' => array(
				'inputs' => array(
					array( __('Top', 'ajax-search-pro'), '0px'),
					array( __('Right', 'ajax-search-pro'), '0px'),
					array( __('Bottom', 'ajax-search-pro'), '0px'),
					array( __('Left', 'ajax-search-pro'), '0px')
				)
			),
			'value' => $sd['kw_suggest_padding']
		));
	new wd_ANInputs("kw_suggest_margin", __('Margin', 'ajax-search-pro'),
		array(
			'args' => array(
				'inputs' => array(
					array( __('Top', 'ajax-search-pro'), '0px'),
					array( __('Right', 'ajax-search-pro'), '0px'),
					array( __('Bottom', 'ajax-search-pro'), '0px'),
					array( __('Left', 'ajax-search-pro'), '0px')
				)
			),
			'value' => $sd['kw_suggest_margin']
		));
	?>
</div>
<div class="item">
	<a class="asp_to_tab" href="#502" tabid="502">Go to Keyword Suggestions options >></a>
</div>