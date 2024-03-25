<?php
/**
 * Callout Block Template.
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
	$custom_title = get_field( 'title' );
	$custom_link  = get_field( 'link' );
	$body         = get_field( 'body_text' );
	$color        = get_field( 'color_options' );
	$size         = get_field( 'size_options' );
	?>

	<div <?php echo esc_attr( $anchor ); ?> class="<?php echo esc_attr( $class_name ); ?> <?php echo esc_attr( $size ); ?>-callout-block">
		<div class="callout-block-component <?php echo esc_attr( $color ); ?> <?php echo esc_attr( $size ); ?>">
			<div class="callout-block-title">
				<h2><?php echo esc_html( $custom_title ); ?></h2>
			</div>
			<div class="callout-block-body">
				<p><?php echo esc_html( $body ); ?></p>
			</div>
			<?php
			if ( $custom_link ) :
				$custom_link_url    = $custom_link['url'];
				$custom_link_title  = $custom_link['title'];
				$custom_link_target = isset( $custom_link['target'] ) ? $custom_link['target'] : '_self';
				?>
				<div class="callout-block-link">
					<a class="button" href="<?php echo esc_url( $custom_link_url ); ?>" target="<?php echo esc_attr( $custom_link_target ); ?>"><?php echo esc_html( $custom_link_title ); ?></a>
				</div>
			<?php endif; ?>
		</div>
	</div>
<?php endif; ?>
