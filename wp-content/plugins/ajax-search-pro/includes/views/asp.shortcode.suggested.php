<?php
/***************** SUGGESTED PHRASES ******************/
if (w_isset_def($style['frontend_show_suggestions'], 0) == 1) {
	$sugg_keywords = trim(preg_replace('/[\s\t\n\r\s]+/', ' ', $style['frontend_suggestions_keywords']));
	$sugg_keywords = str_replace(array(" ,", ", ", " , "), ",", $sugg_keywords);
	$sugg_keywords = stripslashes(esc_html($sugg_keywords));

	$sugg_keywords_arr = apply_filters("asp_suggested_phrases", explode(",", $sugg_keywords), $real_id);
	foreach ( $sugg_keywords_arr as $skk => &$skv ) {
		$skv = asp_icl_t('Keyword suggestion ['.$skv.'] ' . '(' . $real_id . ')', $skv);
	}
	$s_phrases = implode('</a><a href="#">', $sugg_keywords_arr);

	?>
	<p id="asp-try-<?php echo $id; ?>"
	   class="asp-try asp-try-<?php echo $real_id; ?><?php echo $style['box_compact_layout'] == 1 ? ' asp_compact' : ' asp_non_compact'; ?>"
	>
	<?php echo asp_icl_t('Keyword suggestions text', $style['frontend_suggestions_text']).' <a href="#">'.$s_phrases.'</a>'; ?>
	</p><?php
}