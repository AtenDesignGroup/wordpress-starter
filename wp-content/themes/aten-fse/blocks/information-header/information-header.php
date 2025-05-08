<?php
/**
 * Information Header Block Template.
 *
 * @package aten-fse
 *
 * @param   array $block The block settings and attributes.
 * @param   string $content The block inner HTML (empty).
 * @param   bool $is_preview True during backend preview render.
 * @param   int $current_post_id The post ID the block is rendering content against.
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
	$class_name = 'information-header-block';
	if ( ! empty( $block['className'] ) ) {
		$class_name .= ' ' . $block['className'];
	}
	if ( ! empty( $block['align'] ) ) {
		$class_name .= ' align' . $block['align'];
	}

	// Load values and assign defaults.
	$current_post_id   = get_the_ID();
	$post_title        = get_the_title( $current_post_id );
	$current_post_type = get_post_type( $current_post_id );
	$subheading_text   = '';

	// Check for featured image, and place it as the background.
	$header_has_image = false;
	if ( has_post_thumbnail() ) {
		$header_has_image = true;
	}

	// Add any additional custom fields here as needed.

	?>
	<div <?php echo esc_attr( $anchor ); ?>class="
		<?php
		echo esc_attr( $class_name );
		if ( $header_has_image ) {
			echo ' has-image'; }
		?>
	information-header-block-component">
		<div class="information-header-block-wrapper l-gutter">
			<div class="information-header-block-title">
				<h1 class="header-text"><?php echo esc_html( $post_title ); ?></h1>
			</div>
			<hr />
			<div class="information-header-block-content 
			<?php
			if ( $header_has_image ) {
				echo 'has-image'; }
			?>
			">
			<?php if ( $header_has_image ) : ?>
				<div class="information-header-image-wrapper" style="background-image: url('<?php echo esc_attr( get_the_post_thumbnail_url() ); ?>');">
				</div>
			<?php endif; ?>
				<div class="info-wrapper">
					<?php if ( $subheading_text ) : ?>
						<h2 class="title"><?php echo esc_html( $subheading_text ); ?></h2> 
					<?php endif; ?>
				</div>
			</div>

		</div>
	</div>
<?php endif; ?>
