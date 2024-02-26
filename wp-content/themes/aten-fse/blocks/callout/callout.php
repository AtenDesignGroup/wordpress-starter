<?php
/**
 * Callout Link Block Template.
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
	$class_name = 'callout-block';
	if ( ! empty( $block['className'] ) ) {
		$class_name .= ' ' . $block['className'];
	}
	if ( ! empty( $block['align'] ) ) {
		$class_name .= ' align' . $block['align'];
	}

	// Load values and assign defaults.
	$custom_title         = get_field( 'title' );
	$custom_image         = get_field( 'image' );
	$teaser               = get_field( 'teaser' );
	$referenced_post_link = get_field( 'referenced_post_link' );

	if ( $referenced_post_link ) {
		$permalink          = get_permalink( $referenced_post_link );
		$has_featured_image = has_post_thumbnail( $referenced_post_link );
	}

	$callout_title = ! empty( $custom_title ) ? $custom_title : get_the_title( $referenced_post_link );
	$image_id      = ! empty( $custom_image ) ? $custom_image : ( $has_featured_image ? get_post_thumbnail_id( $referenced_post_link ) : '' );

	?>

	<div <?php echo esc_attr( $anchor ); ?> class="l-gutter callout-link-component 
					<?php
					if ( ! $image_id ) {
						echo 'callout-without-image ';
					} echo esc_attr( $class_name );
					?>
	">
		<div class="callout-link-text">
			<?php if ( $referenced_post_link ) : ?>
				<a href="<?php echo esc_url( $permalink ); ?>">
					<h2><?php echo esc_html( $callout_title ); ?></h2>
				</a>
				<?php
				if ( $teaser ) {
					?>
					<div class="callout-teaser"><?php echo esc_html( $teaser ); ?></div><?php } ?>
			<?php endif; ?>
		</div>

		<?php if ( $image_id ) : ?>
			<div class="callout-link-image">
				<?php echo wp_get_attachment_image( $image_id, 'callout-link' ); ?>
			</div>
		<?php endif; ?>
		
	</div>
<?php endif; ?>
