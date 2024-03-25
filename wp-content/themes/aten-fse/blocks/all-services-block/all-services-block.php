<?php
/**
 * All Services Block Template.
 *
 * @package aten-fse
 *
 * @param array $block The block settings and attributes.
 * @param string $content The block inner HTML (empty).
 * @param bool $is_preview True during backend preview render.
 * @param int $post_id The post ID the block is rendering content against.
 *          This is either the post ID currently being displayed inside a query loop,
 *          or the post ID of the post hosting this block.
 * @param array $context The context provided to the block by the post or it's parent block.
 */

if ( isset( $block['data']['preview_image'] ) ) :    /* rendering in inserter preview  */
	echo '<img src="' . esc_attr( $block['data']['preview_image'] ) . '" style="width:100%; height:auto;">';
else :

	// Support custom "anchor" values.
	$anchor = '';
	if ( ! empty( $block['anchor'] ) ) {
		$anchor = 'id="' . esc_attr( $block['anchor'] ) . '" ';
	}

	// Create class attribute allowing for custom "className" and "align" values.
	$class_name = 'all-services-block includes-jump-links';
	if ( ! empty( $block['className'] ) ) {
		$class_name .= ' ' . $block['className'];
	}
	if ( ! empty( $block['align'] ) ) {
		$class_name .= ' align' . $block['align'];
	}

	// Get the services field.
	$services = get_field( 'services' );

	// Loop through services sections if there are any.
	if ( have_rows( 'services_sections' ) ) : ?>

		<div <?php echo esc_attr( $anchor ); ?> class="<?php echo esc_attr( $class_name ); ?> l-gutter">
			<a id="all-services-top-link"></a>
			<h2>All Services</h2>
			<h3><span class="services-icon jump-link-icon notranslate" aria-hidden="true">arrow_circle_down</span>Jump to a section of services:</h3>
			<div class="services-jump-link-wrapper"></div>

			<ul class="section-list">

					<?php
					// Counter to determine section card colors.
					$color_counter = 1;
					while ( have_rows( 'services_sections' ) ) :
						the_row();
						// Get the section title.
						$section_title = get_sub_field( 'section_title' );
						?>
					<li class="services-section">
						<h4 class="service-section-heading"><?php echo esc_html( $section_title ); ?></h4>
						<a href="#all-services-top-link" class="a11y-visible skip-link">Scroll back to Services List</a>

						<?php if ( have_rows( 'services' ) ) : ?>
							<ul class="service-cards">
								<?php
								while ( have_rows( 'services' ) ) :
									the_row();
									// Individual service field vars.
									$service_title       = get_sub_field( 'service_title' );
									$service_link        = get_sub_field( 'service_link' );
									$service_link_target = $service_link['target'] ?? '_self';
									$icon                = get_sub_field( 'service_icon' );
									// Optional.
									$description = get_sub_field( 'service_description' );
									?>
									<li class="service-card">
										<div class="icon-container color-<?php echo esc_attr( $color_counter ); ?>">
											<span class="service-icon notranslate" aria-hidden="true"><?php echo esc_html( $icon ); ?></span>
										</div>
										<div class="service-details">
											<p class="service-title">
												<a href="<?php echo esc_url( $service_link['url'] ); ?>" title="<?php echo esc_attr( $service_link['title'] ); ?>" target="<?php echo esc_attr( $service_link_target ); ?>" class="service-card-link">
													<?php echo esc_html( $service_title ); ?>
												</a>
											</p>
											<?php if ( $description ) : ?>
												<p class="service-description">
												<?php echo esc_html( $description ); ?>
												</p>
												<?php
												endif;
											?>
										</div>
									</li>
												<?php
												endwhile;
								?>
							</ul>
												<?php
												endif;
						?>

					</li>
												<?php
												// Reset the color pattern after 4 sections.
												if ( $color_counter < 4 ) {
													++$color_counter;
												} else {
													$color_counter = 1;
												}
							endwhile;
					?>

			</ul>

			<a class="back-to-top-btn" href="#all-services-top-link">
				<span class="arrow-icon notranslate" aria-hidden="true">arrow_upward</span> Back to Top
			</a>
		</div>

												<?php
												endif;

						endif;
