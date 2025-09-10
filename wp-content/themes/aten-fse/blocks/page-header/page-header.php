<?php
/**
 * Page Header Block Template.
 *
 * @package aten-fse.
 *
 * @param   array $block The block settings and attributes.
 * @param   string $content The block inner HTML (empty).
 * @param   bool $is_preview True during backend preview render.
 * @param   int $current_post_id The post ID the block is rendering content against.
 *          This is either the post ID currently being displayed inside a query loop,
 *          or the post ID of the post hosting this block.
 * @param   array $context The context provided to the block by the post or it's parent block.
 */

if ( isset( $block['data']['preview_image'] ) ) :    /* rendering in inserter preview. */
	echo '<img src="' . esc_url( get_site_url() . $block['data']['preview_image'] ) . '" style="width:100%; height:auto;">';
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

	// Load values and assign defaults.
	$current_post_id = get_the_ID();
	$post_title      = get_the_title( $current_post_id );
	$current_term    = '';
	$is_basic_post   = ( ! is_tax() && ! is_search() && ! is_404() );

	// Set defaults for no featured image.
	$header_has_image     = false;
	$header_wrapper_class = '';
	$header_background    = '';

	// get Subtitle field on post.
	$subtitle = get_field( 'page_header_subtitle', $current_post_id );

	// Check for featured image, and place it as the background.
	if ( has_post_thumbnail() && $is_basic_post ) {
		$header_has_image     = true;
		$header_wrapper_class = 'header-with-image ';
		$header_background    = 'background-image: url("' . get_the_post_thumbnail_url() . '"); ';
	}

	// Setting static title for Taxonomy Archives.
	if ( is_tax() ) {
		$current_term = get_queried_object();
		$post_title   = $current_term->name . ' Posts';
		if ( str_contains( $current_term->name, 'Posts' ) ) {
			$post_title = $current_term->name;
		}
		$current_post_id = $current_term->term_id;
		$subtitle        = ( get_field( 'category_description', $current_term ) ) ? get_field( 'category_description', $current_term ) : '';
	}

	// Setting static title for search results pages.
	if ( is_search() ) {
		$post_title = 'Search Results';
	}

	// Setting static title for 404 Errors.
	if ( is_404() ) {
		$post_title = 'Page not found';
	}

	// Building custom breadcrumbs.
	$has_breadcrumb   = false;
	$parent_page_link = '';

	if ( has_post_parent( $current_post_id ) ) {
		$has_breadcrumb = true;
		// Get parent post.
		$parent_post = get_post_parent( $current_post_id );
		// Get parent title and link.
		$parent_page_name = get_the_title( $parent_post );
		$parent_page_link = get_the_permalink( $parent_post );
	}

	?>

	<div class="page-header-component">
		<?php if ( $header_has_image ) : ?> 
			<div class="mobile-image">
				<?php echo wp_get_attachment_image( get_post_thumbnail_id(), 'large' ); ?>
				<div class="mobile-image-overlay"></div>
			</div>
		<?php endif; ?>

		<div <?php echo esc_attr( $anchor ); ?> class="
			<?php
			echo esc_attr( $header_wrapper_class );
			echo esc_attr( $class_name );
			?>
		" style="<?php echo esc_attr( $header_background ); ?>">
			<div class="header-content">
				<?php if ( $has_breadcrumb ) { ?>
					<nav aria-label="Breadcrumb" class="breadcrumb 
					<?php
					if ( $header_has_image ) {
						echo 'with-background-image'; }
					?>
					">
						<ul>
							<li>
								<a class="home-crumb" href="<?php echo esc_url( get_home_url() ); ?>">Home</a>
								<span class="breadcrumb-next a11y-hidden notranslate">
									chevron_right
								</span>
							</li>
							<?php if ( $parent_page_link ) { ?>
								<li>
									<a class="parent-crumb" href="<?php echo esc_url( $parent_page_link ); ?>"><?php echo esc_html( $parent_page_name ); ?></a>
									<span class="breadcrumb-next a11y-hidden notranslate">
										chevron_right
									</span>
								</li>
							<?php } ?>
							<li>
								<a class="current-crumb" href="javascript:void(0)" aria-disabled="true" aria-current="page">
									<?php echo esc_html( get_the_title( $current_post_id ) ); ?>
								</a>
							</li>
						</ul>
					</nav>
				<?php } ?>
				<h1 class="header-text"><?php echo esc_html( $post_title ); ?></h1>
				<?php if ( $subtitle ) : ?>
					<div class="header-subtitle"><p><?php echo esc_html( $subtitle ); ?></p></div>
				<?php endif; ?>
			</div>
		</div>
	</div>
<?php endif; ?>
