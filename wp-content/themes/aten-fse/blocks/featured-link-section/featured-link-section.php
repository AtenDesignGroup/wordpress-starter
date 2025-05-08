<?php
/**
 * Featured Link Section Template.
 *
 * @package aten-fse
 *
 * @param   array $block The block settings and attributes.
 * @param   string $content The block inner HTML (empty).
 * @param   bool $is_preview True during backend preview render.
 * @param   int $post_id The post ID the block is rendering content against.
 *          This is either the post ID currently being displayed inside a query loop,
 *          or the post ID of the post hosting this block.
 * @param   array $context The context provided to the block by the post or it's parent block.
 */

if ( isset( $block['data']['preview_image'] ) ) :    /* rendering in inserter preview  */
	echo '<img src="' . esc_url( get_site_url() . $block['data']['preview_image'] ) . '" style="width:100%; height:auto;">';
else :

	// Support custom "anchor" values.
	$anchor = '';
	if ( ! empty( $block['anchor'] ) ) {
		$anchor = 'id="' . esc_attr( $block['anchor'] ) . '" ';
	}

	// Create class attribute allowing for custom "className" and "align" values.
	$class_name = 'featured-link-section-block';
	if ( ! empty( $block['className'] ) ) {
		$class_name .= ' ' . $block['className'];
	}
	if ( ! empty( $block['align'] ) ) {
		$class_name .= ' align' . $block['align'];
	}


	// Looping through repeater for Featured Link Sections.
	if ( have_rows( 'featured_links' ) ) :
		// Counting sections for color and layout purposes.
		$section_count = 0; ?> 
		<div <?php echo esc_attr( $anchor ); ?> class="<?php echo esc_attr( $class_name ); ?> featured-link-section-wrapper">
			<ul class="sections">

				<?php
				while ( have_rows( 'featured_links' ) ) :
					the_row();
					++$section_count;
					// Getting the subfield values.
					$section_title     = get_sub_field( 'title' );
					$section_icon      = get_sub_field( 'featured_icon' );
					$section_image     = get_sub_field( 'display_image' );
					$section_image_url = '';
					if ( is_array( $section_image ) ) {
						$section_image_url = $section_image['url'];
					}
					?>
				<li class="featured-link-section featured-link-section-<?php echo esc_attr( $section_count ); ?>">
					<!-- Title Pane containing title, icon, background image, and button links -->
					<div class="title-pane">
						<div class="title-bg" style="background-image: linear-gradient(rgba(28, 63, 148, 0.9) 6.15%, rgba(28, 63, 148, 0.15)), url('<?php echo esc_attr( $section_image_url ); ?>');"></div>
						<div class="title-wrapper">
							<img role="icon" src="<?php echo esc_attr( get_stylesheet_directory_uri() ); ?>/assets/icons/acf-icons/handshake.svg" alt="" />
							<div class="desktop-title">
								<h2><?php echo esc_html( $section_title ); ?></h2>
								<?php
								// Looping through button links, up to 2 allowed.
								if ( have_rows( 'button_links' ) ) :
									?>
									<div class="button-link-wrapper">		
									<?php
									while ( have_rows( 'button_links' ) ) :
										the_row();
										$button_link = get_sub_field( 'button_link' );
										$button_text = get_sub_field( 'button_text' );
										?>
										<a href="<?php echo esc_attr( $button_link['url'] ); ?>" title="<?php echo esc_attr( $button_link['title'] ); ?>" class="btn-large--white featured-button"><?php echo esc_html( $button_text ); ?></a>
									<?php endwhile; ?>
									</div>
								<?php endif; ?>
							</div>
						</div>
					</div>

					<!-- Link Pane, contains ul of links. Also contains title, icon, and button links when mobile -->
					<div class="link-pane">
						<div class="mobile-title">
							<h2><?php echo esc_html( $section_title ); ?></h2>
							<?php if ( have_rows( 'button_links' ) ) : ?>
								<div class="button-link-wrapper">		
								<?php
								while ( have_rows( 'button_links' ) ) :
									the_row();
									$button_link = get_sub_field( 'button_link' );
									$button_text = get_sub_field( 'button_text' );
									?>
									<a href="<?php echo esc_attr( $button_link['url'] ); ?>" title="<?php echo esc_attr( $button_link['title'] ); ?>" class="btn--black"><?php echo esc_html( $button_text ); ?></a>
								<?php endwhile; ?>
								</div>
							<?php endif; ?>
							<hr class="black" />
						</div>

						<?php
						// Looping through list of links.
						if ( have_rows( 'listed_links' ) ) :
							?>
							<ul class="listed-links">
							<?php
							while ( have_rows( 'listed_links' ) ) :
								the_row();
								$link_icon   = get_sub_field( 'link_icon' );
								$link_title  = get_sub_field( 'link_title' );
								$link_url    = get_sub_field( 'link_url' );
								$link_target = isset( $link_url['target'] ) ? $link_url['target'] : '_self';
								?>
								<li>
									<span class="list-link-icon notranslate" aria-hidden="true"><?php echo esc_html( $link_icon ); ?></span>
									<a href="<?php echo esc_attr( $link_url['url'] ); ?>" title="<?php echo esc_html( $link_url['title'] ); ?>" target="<?php echo esc_html( $link_target ); ?>">
										<?php
										echo esc_html( $link_title );
										if ( '_blank' === $link_target ) {
											?>
										<span class="link-icon external-link notranslate" aria-hidden="true">open_in_new</span> <?php } ?>
									</a>
								</li>
							<?php endwhile; // Endwhile Listed Links. ?>
							</ul>
						<?php endif; // Endif Listed Links. ?>
					</div>

				</li>
				<?php endwhile; // Endwhile Sections. ?>
			</ul>

		</div>

		<?php
	endif; // Endif Sections.
endif;
?>
