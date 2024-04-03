<?php
/**
 * Featured Link Cards Template.
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
	$class_name = 'featured-link-cards-block';
	if ( ! empty( $block['className'] ) ) {
		$class_name .= ' ' . $block['className'];
	}
	if ( ! empty( $block['align'] ) ) {
		$class_name .= ' align' . $block['align'];
	}

	$block_title = get_field( 'title' );
	$subtitle    = get_field( 'subtitle' );

	// Looping through repeater for Featured Link Cards.
	if ( have_rows( 'featured_link_cards' ) ) : ?>

		<div <?php echo esc_attr( $anchor ); ?> class="<?php echo esc_attr( $class_name ); ?> featured-link-cards-block">
			<img role="icon" alt="" src="<?php echo esc_attr( get_stylesheet_directory_uri() ); ?>/assets/icons/acf-icons/account_balance.svg" />
			<h2 class="h2-stylized"><?php echo esc_html( $block_title ); ?></h2>
		<?php
		if ( $subtitle ) {
			?>
		<p class="subtitle"><?php echo esc_html( $subtitle ); ?></p>
			<?php
		}
		?>

			<ul class="featured-link-cards-wrapper">
				<?php
				while ( have_rows( 'featured_link_cards' ) ) :
					the_row();
					// Getting the subfield values.
					$link_icon   = get_sub_field( 'link_icon' );
					$link_title  = get_sub_field( 'link_title' );
					$link_url    = get_sub_field( 'link_url' );
					$link_target = $link_url['target'] ?? '_self';
					$description = get_sub_field( 'description' );
					// Checking for all required fields, otherwise this throws an error.
					if ( $link_icon && $link_title && $link_url && $description ) :
						?>
					<li class="featured-link-card">
						<span class="link-icon notranslate" aria-hidden="true"><?php echo esc_html( $link_icon ); ?></span>
						<div class="text-wrapper">
							<h3>
								<a href="<?php echo esc_attr( $link_url['url'] ); ?>" title="<?php echo esc_attr( $link_url['title'] ); ?>" target="<?php echo esc_attr( $link_target ); ?>" class="card-link">
									<?php echo esc_html( $link_title ); ?>
								</a>
							</h3>
							<p><?php echo esc_html( $description ); ?></p>
						</div>
					</li>
						<?php
					endif;

					endwhile;
				?>
			</ul>

		</div>

		<?php
	endif;
endif;
