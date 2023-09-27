<?php
/**
 * Page Header Block Template.
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
	$class_name = 'page-header-block';
	if ( ! empty( $block['className'] ) ) {
		$class_name .= ' ' . $block['className'];
	}
	if ( ! empty( $block['align'] ) ) {
		$class_name .= ' align' . $block['align'];
	}

	// Check if currently viewing an archive page
	$is_archive = is_tax();

	// Load values and assign defaults.
	$post_id = get_the_ID();
	$post_title = get_the_title( $post_id );
	$term = '';

	// Set defaults for no featured image
	$header_has_image = false;
	$header_wrapper_class = '';
	$header_background = '';

	// Default publication date to empty string 
	$publication_date = '';

	// Check for featured image, and place it as the background
	if (has_post_thumbnail() && get_post_type($post_id) !== 'news' && !is_search()) {
		$header_has_image = true;
		$header_wrapper_class = 'header-with-image ';
		$header_background = 'background-image: linear-gradient(to top, rgba(28, 63, 148, 1), rgba(28, 63, 148, 0)), url("' . get_the_post_thumbnail_url() . '"); ';
	}

	// Check for publication date on News posts
	if(get_post_type($post_id) === 'news' && is_single()) {
		$publication_date = get_field( 'publication_date', $post_id );
	}

	//get Subtitle field on post
	$subtitle = get_field( 'page_header_subtitle', $post_id );

	if($is_archive) {
		$term = get_queried_object();
		$post_title = $term->name . ' News';
		if( str_contains($term->name, 'News') ) {
			$post_title = $term->name;
		}
		$post_id = $term->term_id;
		$subtitle = get_field( 'category_description', $term );
	}

	// Setting static title for search results pages
	if(is_search()) {
		$post_title = 'Search Results';
	}

	?>

	<div class="page-header-component">
		<?php if( $header_has_image ) : ?> 
			<div class="mobile-image">
				<?php echo wp_get_attachment_image( get_post_thumbnail_id(), 'large' ); ?>
				<div class="mobile-image-overlay"></div>
			</div>
		<?php else : ?>
			<div class="header-swishies-wrapper" aria-hidden="true"></div>
		<?php endif; ?>

		<div <?php echo $anchor; ?> class="<?php echo esc_attr( $header_wrapper_class ); echo esc_attr( $class_name ); ?>" style="<?php echo esc_attr( $header_background ); ?>">
			<div class="header-content">
				<?php if( $header_has_image ) { ?> <hr /> <?php } ?>
				<?php if ( $publication_date ) { ?>
					<p class="header-publication-date"><?php echo $publication_date; ?></p>
				<?php } ?>
				<h1 class="header-text"><?php echo esc_html( $post_title ); ?></h1>
				<div class="header-subtitle"><p><?php echo esc_html( $subtitle ); ?></p></div>
				<?php if( !$header_has_image ) { ?> <hr /> <?php } ?>
			</div>
		</div>

		<?php if( $header_has_image ) : ?> 
			<img class="edge-wave" src="<?php echo get_template_directory_uri(); ?>/assets/img/header-image-wave.svg" alt="" />
		<?php endif; ?>
	</div>
<?php endif; ?>