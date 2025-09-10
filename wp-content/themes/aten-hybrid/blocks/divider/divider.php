<?php
/**
 * Separator Block Template.
 *
 * @package aten-hybrid
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
	echo '<img src="' . esc_attr( get_site_url() . $block['data']['preview_image'] ) . '" style="width:100%; height:auto;">';
else :

	// Support custom "anchor" values.
	$anchor = '';
	if ( ! empty( $block['anchor'] ) ) {
		$anchor = 'id="' . esc_attr( $block['anchor'] ) . '" ';
	}

	// Create class attribute allowing for custom "className" and "align" values.
	$class_name = 'divider-component';
	if ( ! empty( $block['className'] ) ) {
		$class_name .= ' ' . $block['className'];
	}
	if ( ! empty( $block['align'] ) ) {
		$class_name .= ' align' . $block['align'];
	}

	// Load values and assign defaults.
	$style       = ( get_field( 'divider_style' ) ) ? get_field( 'divider_style' ) : 'black_line';
	$height      = ( get_field( 'divider_height' ) ) ? get_field( 'divider_height' ) : '32';
	$class_name .= ' divider-height-' . $height . ' ' . str_replace( '_', '-', $style );
	?>

	<div class="cleardiv"></div>
	<div class="is-style-wide <?php echo esc_attr( $class_name ); ?>">
		<?php if ( 'spacer' !== $style ) : ?>
			<hr />
		<?php endif; ?>
	</div>
<?php endif; ?>
