<?php
/**
 * Byline Block Template.
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
	$class_name = 'byline-block';
	if ( ! empty( $block['className'] ) ) {
		$class_name .= ' ' . $block['className'];
	}
	if ( ! empty( $block['align'] ) ) {
		$class_name .= ' align' . $block['align'];
	}

	// Load values and assign defaults.
	$current_post_id = get_the_ID();
	$current_post_type = get_post_type( $current_post_id );
	$display_options = get_field('display_options');
	$display_research_partner = (is_array($display_options) && !in_array('display_research_partner', $display_options)) ? false : true;
	$display_publication_date = (is_array($display_options) && !in_array('display_publication_date', $display_options)) ? false : true;
	$display_spotlight_authors = (is_array($display_options) && !in_array('display_spotlight_authors', $display_options)) ? false : true;

	if($display_research_partner) {
		if($current_post_type === 'research'){
			$research_partner = get_term_by('id', get_field( 'research_partner', $current_post_id), 'research-partner');
		} else {
			$research_partner = '';
		} 
	}

	if($display_publication_date) {
		if($current_post_type === 'research'){
			$publication_date = get_field( 'research_publication_date', $current_post_id);
		} else {
			$publication_date = get_the_date( 'n/j/y' );
		} 
		
	}

	if($display_spotlight_authors) {
		$spotlight_authors = get_field( 'spotlight_authors', $current_post_id);
	}
	?>

	<div class="byline-component">
		<div class="byline-content">
			<?php if($display_research_partner && $research_partner) : ?>
				<span class="byline-research-partner">
					<?php echo $research_partner->name; ?>
				</span>
			<?php endif; ?>
			<?php if($display_publication_date && $publication_date) : ?>
				<span class="byline-publication-date">
					<?php echo 'Published ' . $publication_date; ?>
				</span>
			<?php endif; ?>
			<?php if($display_spotlight_authors && is_array($spotlight_authors)) : ?>
							
				<?php foreach($spotlight_authors as $author) : 
					$author_id = $author->ID;
					$author_name = get_field('display_name', $author_id);
					$author_img = get_field('headshot', $author_id);
					$author_link = get_field('link', $author_id);
					?>
					<span class="byline-spotlight-author">
						<?php if($author_img) : ?>
							<img class="byline-author-image" src="<?php echo $author_img['url']; ?>" alt="<?php echo $author_name; ?>">
						<?php endif; ?>
						<?php if($author_link) : ?>
							<a href="<?php echo $author_link['url']; ?>" title="<?php echo $author_name; ?>" target="_blank">
						<?php endif; ?>
						<?php echo $author_name; ?>
						<?php if($author_link) : ?>
							</a>
						<?php endif; ?>
					</span>
				<?php endforeach; ?>
				
			<?php endif; ?>
		</div>
	</div>
<?php endif; ?>