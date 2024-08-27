<?php
/**
 * Jump Link Directory Block Template.
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
	$class_name = 'jump-link-card-directory includes-jump-links';
	if ( ! empty( $block['className'] ) ) {
		$class_name .= ' ' . $block['className'];
	}
	if ( ! empty( $block['align'] ) ) {
		$class_name .= ' align' . $block['align'];
	}

	// Get the cards field.
	$cards = get_field( 'cards' );

	// Loop through card sections if there are any.
	if ( have_rows( 'card_sections' ) ) : ?>

		<div <?php echo esc_attr( $anchor ); ?> class="<?php echo esc_attr( $class_name ); ?> l-gutter">
			<a id="jump-link-card-directory-top-link"></a>
			<h2>All Cards</h2>
			<h3><span class="jump-link-cards-icon jump-link-icon notranslate" aria-hidden="true">arrow_circle_down</span>Jump to a section of cards:</h3>
			<div class="cards-jump-link-wrapper"></div>

			<ul class="card-list">

					<?php
					// Counter to determine section card colors.
					$color_counter = 1;
					while ( have_rows( 'card_sections' ) ) :
						the_row();
						// Get the section title.
						$section_title = get_sub_field( 'section_title' );
						?>
					<li class="cards-section">
						<h4 class="jump-link-card-section-heading"><?php echo esc_html( $section_title ); ?></h4>
						<a href="#jump-link-card-directory-top-link" class="a11y-visible skip-link">Scroll back to Card List</a>

						<?php if ( have_rows( 'cards' ) ) : ?>
							<ul class="jump-link-cards">
								<?php
								while ( have_rows( 'cards' ) ) :
									the_row();
									// Individual card field vars.
									$jump_link_card_title       = get_sub_field( 'jump_link_card_title' );
									$jump_link_card_link        = get_sub_field( 'jump_link_card_link' );
									$jump_link_card_link_target = $jump_link_card_link['target'] ?? '_self';
									$icon                = get_sub_field( 'jump_link_card_icon' );
									// Optional.
									$description = get_sub_field( 'jump_link_card_description' );
									?>
									<li class="jump-link-card">
										<div class="icon-container color-<?php echo esc_attr( $color_counter ); ?>">
											<span class="jump-link-card-icon notranslate" aria-hidden="true"><?php echo esc_html( $icon ); ?></span>
										</div>
										<div class="jump-link-card-details">
											<p class="jump-link-card-title">
												<a href="<?php echo esc_url( $jump_link_card_link['url'] ); ?>" title="<?php echo esc_attr( $jump_link_card_link['title'] ); ?>" target="<?php echo esc_attr( $jump_link_card_link_target ); ?>" class="jump-link-card-link">
													<?php echo esc_html( $jump_link_card_title ); ?>
												</a>
											</p>
											<?php if ( $description ) : ?>
												<p class="jump-link-card-description">
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

			<a class="back-to-top-btn" href="#jump-link-card-directory-top-link">
				<span class="arrow-icon notranslate" aria-hidden="true">arrow_upward</span> Back to Top
			</a>
		</div>

												<?php
												endif;

						endif;
