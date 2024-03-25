<?php
/**
 * Progress Block Template.
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
	echo '<img src="' . esc_attr( $block['data']['preview_image'] ) . '" style="width:100%; height:auto;">';
else :

	// Support custom "anchor" values.
	$anchor = '';
	if ( ! empty( $block['anchor'] ) ) {
		$anchor = 'id="' . esc_attr( $block['anchor'] ) . '" ';
	}

	// Create class attribute allowing for custom "className" and "align" values.
	$class_name = 'progress-block';
	if ( ! empty( $block['className'] ) ) {
		$class_name .= ' ' . $block['className'];
	}
	if ( ! empty( $block['align'] ) ) {
		$class_name .= ' align' . $block['align'];
	}
	$title        = get_field( 'title' );
	$subtitle     = get_field( 'subtitle' );
	$column_count = count( get_field( 'column' ) );

	// Loop through Column
	if ( have_rows( 'column' ) ) : ?>
		<div <?php echo $anchor; ?>class="<?php echo esc_attr( $class_name ); ?> l-full">
			<div class="progress-block-component">
				<h2><?php echo esc_html( $title ); ?></h2>
				<?php if ( $subtitle ) : ?>
					<p class="subtitle"><?php echo esc_html( $subtitle ); ?></p>
				<?php endif; // Subtitle ?>
				<div class="progress-block-columns">
					<ul class="progress-block-list-wrapper col-count-<?php echo $column_count; ?>">
						<?php
						while ( have_rows( 'column' ) ) :
							the_row();
							$section_title = get_sub_field( 'section_title' );
							?>
							<li class="progress-block-column">
								<hr />

								<!-- Column loop content -->
								<h3><?php echo $section_title; ?></h3>

								<ul class="progress-links">
									<?php
									while ( have_rows( 'link' ) ) :
										the_row();
										$link        = get_sub_field( 'link_url' );
										$link_title  = get_sub_field( 'link_title' );
										$link_icon   = get_sub_field( 'link_icon' );
										$link_target = isset( $link['target'] ) ? $link['target'] : '_self';
										?>
										<li class="progress-link">
											<!-- Link loop content -->
											<a href="<?php echo $link['url']; ?>" title="<?php echo $link['title']; ?>" target="<?php echo $link_target; ?>">
												<span class="link-icon notranslate" aria-hidden="true"><?php echo $link_icon; ?></span>
												<?php echo $link_title; ?>
											</a>
										</li>
									<?php endwhile; // Link loop ?>
								</ul>
							</li>
						<?php endwhile; // Column loop ?>
					</ul>

				</div>
			</div>
		</div>
		<div class="progress-block-border" aria-hidden="true">
			<img src="<?php echo get_template_directory_uri(); ?>/assets/img/progress-border.svg" alt="" />
		</div>
	<?php endif; // If Columns
endif; ?>
