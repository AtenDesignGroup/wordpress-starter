<?php
/**
 * News Meta Fields Block Template.
 *
 * @param   array $block The block settings and attributes.
 * @param   string $content The block inner HTML (empty).
 * @param   bool $is_preview True during backend preview render.
 * @param   int $post_id The post ID the block is rendering content against.
 *          This is either the post ID currently being displayed inside a query loop,
 *          or the post ID of the post hosting this block.
 * @param   array $context The context provided to the block by the post or it's parent block.
 */

if( isset( $block['data']['preview_image'] )  ) :    /* rendering in inserter preview  */
    echo '<img src="'. $block['data']['preview_image'] .'" style="width:100%; height:auto;">';
else :
		
	// Support custom "anchor" values.
	$anchor = '';
	if ( ! empty( $block['anchor'] ) ) {
		$anchor = 'id="' . esc_attr( $block['anchor'] ) . '" ';
	}

	// Create class attribute allowing for custom "className" and "align" values.
	$class_name = 'news-item-meta';
	if ( ! empty( $block['className'] ) ) {
		$class_name .= ' ' . $block['className'];
	}
	if ( ! empty( $block['align'] ) ) {
		$class_name .= ' align' . $block['align'];
	}

	// Load values and assign defaults.
	$publication_date = get_field( 'publication_date', $post_id );
	$news_category = $news_category_name = $news_category_icon = $news_category_link_title = '';

	// Get the news category for this news item
	$news_category_array = get_the_terms( $post_id, 'news_categories' );   
	foreach ( $news_category_array as $news_cat ){
		$news_category = $news_cat;
		$news_category_name = $news_category->slug;
	}

	// Determine which category it is
	if($news_category_name === 'alert') {
		$news_category_name = 'Alerts';
		$news_category_icon = 'warning';
	} else if($news_category_name === 'civic-participation') {
		$news_category_name = 'Civic Participation News';
		$news_category_icon = 'podium';
	} else if($news_category_name === 'community') {
		$news_category_name = 'Community News';
		$news_category_icon = 'groups';
	} else if($news_category_name === 'general-news-announcements') {
		$news_category_name = 'General News & Announcements';
		$news_category_icon = 'campaign';
	} else if($news_category_name === 'government-officials') {
		$news_category_name = 'Government Officials News';
		$news_category_icon = 'workspace_premium';
	}

	$news_category_help_text = "View All " . $news_category_name;
	?>

	<div class="news-category-icon-wrapper">
		<a class="news-category-link" href="<?php echo get_category_link($news_cat); ?>" target="_self" title="<?php echo $news_category_help_text; ?>">
			<span class="news-category-icon notranslate" aria-hidden="true"><?php echo $news_category_icon; ?></span>
			<span class="a11y-visible news-category-icon-description"><?php echo $news_category_help_text; ?></span>
		</a>
	</div>
	<p class="news-item-publication-date">
		<?php echo $publication_date; ?>
	</p>
<?php endif; ?>