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
	echo '<img src="' . esc_attr( $block['data']['preview_image'] ) . '" style="width:100%; height:auto;">';
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

	// Check if currently viewing an archive page.
	$is_archive = is_tax();

	// Load values and assign defaults.
	$current_post_id = get_the_ID();
	$post_title      = get_the_title( $current_post_id );
	$current_term    = '';

	// Set defaults for no featured image.
	$header_has_image     = false;
	$header_wrapper_class = '';
	$header_background    = '';

	// Default publication date to empty string.
	$publication_date = '';

	// Check for featured image, and place it as the background.
	if ( has_post_thumbnail() && get_post_type( $current_post_id ) !== 'news' && ! is_search() ) {
		$header_has_image     = true;
		$header_wrapper_class = 'header-with-image ';
		$header_background    = 'background-image: linear-gradient(to top, rgba(28, 63, 148, 1), rgba(28, 63, 148, 0)), url("' . get_the_post_thumbnail_url() . '"); ';
	}

	// Check for publication date on News posts.
	if ( get_post_type( $current_post_id ) === 'news' && is_single() ) {
		$publication_date = get_field( 'publication_date', $current_post_id );
	}

	// get Subtitle field on post.
	$subtitle = get_field( 'page_header_subtitle', $current_post_id );

	if ( $is_archive ) {
		$current_term = get_queried_object();
		$post_title   = $current_term->name . ' News';
		if ( str_contains( $current_term->name, 'News' ) ) {
			$post_title = $current_term->name;
		}
		$current_post_id = $current_term->term_id;
		$subtitle        = get_field( 'category_description', $current_term );
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
	$department_link  = '';
	$division_link    = '';
	$parent_page_link = '';
	if ( get_field( 'departments', $current_post_id ) || get_field( 'divisions', $current_post_id ) ) {
		$has_breadcrumb = true;
		// Get Division ID of first Division set.
		$division_id = get_field( 'divisions', $current_post_id ) ? get_field( 'divisions', $current_post_id )[0]['wp_category_division'] : '';
		$division    = get_term( $division_id, 'category' ) ? get_term( $division_id, 'category' ) : '';
		// Get Department ID of first Department set.
		$department_id = get_field( 'departments', $current_post_id ) ? get_field( 'departments', $current_post_id )[0]['wp_category_department'] : '';
		if ( ! $department_id && $division_id ) {
			$department_id = wp_get_term_taxonomy_parent_id( $division_id, 'category' );
		}
		$department = get_term( $department_id, 'category' ) ? get_term( $department_id, 'category' ) : '';
		// Get Division and Department names.
		$division_name   = ( $division->name ) ? $division->name : '';
		$department_name = ( $department->name ) ? $department->name : '';
		// Get Division and Department links.
		$division_link   = ( get_field( 'overview_page', $division ) ) ? get_field( 'overview_page', $division ) : '';
		$department_link = ( get_field( 'overview_page', $department ) ) ? get_field( 'overview_page', $department ) : '';
		// Check for if current page is the overview page to avoid replicating breadcrumb links.
		$current_link = get_permalink( $current_post_id );
		if ( $division_link === $current_link ) {
			$division_link = '';
		}
		if ( $department_link === $current_link ) {
			$department_link = '';
		}
	} elseif ( has_post_parent( $current_post_id ) ) {
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
		<?php else : ?>
			<div class="header-swishies-wrapper" aria-hidden="true"></div>
		<?php endif; ?>

		<div <?php echo esc_attr( $anchor ); ?> class="
						<?php
						echo esc_attr( $header_wrapper_class );
						echo esc_attr( $class_name );
						?>
		" style="<?php echo esc_attr( $header_background ); ?>">
			<div class="header-content">
				<?php
				if ( $header_has_image && ! $has_breadcrumb ) {
					?>
					<hr /> <?php } ?>
				<?php if ( $publication_date ) { ?>
					<p class="header-publication-date"><?php echo esc_html( $publication_date ); ?></p>
				<?php } ?>
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
							<?php if ( $department_link ) { ?>
								<li>
									<a class="department-crumb" href="<?php echo esc_url( $department_link ); ?>"><?php echo esc_html( $department_name ); ?></a>
									<span class="breadcrumb-next a11y-hidden notranslate">
										chevron_right
									</span>
								</li>
							<?php } ?>
							<?php if ( $division_link ) { ?>
								<li>
									<a class="division-crumb" href="<?php echo esc_url( $division_link ); ?>"><?php echo esc_html( $division_name ); ?></a>
									<span class="breadcrumb-next a11y-hidden notranslate">
										chevron_right
									</span>
								</li>
							<?php } ?>
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
				<div class="header-subtitle"><p><?php echo esc_html( $subtitle ); ?></p></div>
				<?php
				if ( ! $header_has_image ) {
					?>
					<hr /> <?php } ?>
			</div>
		</div>

		<?php if ( $header_has_image ) : ?> 
			<img class="edge-wave" src="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/img/header-image-wave.svg" alt="" />
		<?php endif; ?>
	</div>
<?php endif; ?>
