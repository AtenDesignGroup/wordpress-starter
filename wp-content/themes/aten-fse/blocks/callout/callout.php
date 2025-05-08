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
	echo '<img src="' . esc_url( get_site_url() . $block['data']['preview_image'] ) . '" style="width:100%; height:auto;">';
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
	$callout_style      = ( get_field( 'callout_style' ) ) ? get_field( 'callout_style' ) : 'vertical';
	$callout_title      = get_field( 'callout_title' );
	$callout_image      = ( get_field( 'callout_image' ) ) ? get_field( 'callout_image' ) : '';
	$description        = ( get_field( 'callout_description' ) ) ? get_field( 'callout_description' ) : '';
	$callout_link       = ( get_field( 'callout_link' ) ) ? get_field( 'callout_link' ) : '';
	$link_the_title     = ( get_field( 'callout_link_options_link_the_callout_title' ) ) ? get_field( 'callout_link_options_link_the_callout_title' ) : false;
	$display_the_button = ( get_field( 'callout_link_options_display_button' ) ) ? get_field( 'callout_link_options_display_button' ) : false;
	$button_text        = '';
	$callout_color      = '';
	$callout_size       = '';

	// Setting up button link.
	if ( $display_the_button ) {
		$button_text = get_field( 'callout_link_options_button_text' );
	}
	?>

	<?php if ( 'horizontal' === $callout_style ) : ?>
		<div <?php echo esc_attr( $anchor ); ?> class="l-gutter callout-link-component 
			<?php
			if ( ! $callout_image ) {
				echo 'callout-without-image ';
			} echo esc_attr( $class_name );
			?>
		">
			<div class="callout-link-text">
				<?php if ( $link_the_title ) : ?>
					<a href="<?php echo esc_url( $callout_link['url'] ); ?>" target="<?php echo esc_html( $callout_link['target'] ); ?>">
				<?php endif; ?>
						<h2><?php echo esc_html( $callout_title ); ?></h2>
				<?php if ( $link_the_title ) : ?>
					</a>
				<?php endif; ?>
				<?php
				if ( $description ) :
					?>
					<div class="callout-description"><?php echo esc_html( strip_tags( $description, '<p><a><strong><b><i><em>' ) ); ?></div>
					<?php
				endif;
				?>
				<?php
				if ( $display_the_button ) :
					?>
					<div class="callout-block-link">
						<a class="button" href="<?php echo esc_url( $callout_link['url'] ); ?>" target="<?php echo esc_attr( $callout_link['target'] ); ?>"><?php echo esc_html( $button_text ); ?></a>
					</div>
				<?php endif; ?>
			</div>

			<?php if ( $callout_image ) : ?>
				<div class="callout-link-image">
					<?php echo wp_get_attachment_image( $callout_image, 'callout-link' ); ?>
				</div>
			<?php endif; ?>
			
		</div>
	<?php else : ?>
		<?php
		$callout_color = ( get_field( 'callout_display_options_color_options' ) ) ? get_field( 'callout_display_options_color_options' ) : 'navy';
		$callout_size  = ( get_field( 'callout_display_options_size_options' ) ) ? get_field( 'callout_display_options_size_options' ) : 'medium';
		?>
		<div <?php echo esc_attr( $anchor ); ?> class="<?php echo esc_attr( $class_name ); ?> <?php echo esc_attr( $callout_size ); ?>-callout-block">
			<div class="callout-block-component <?php echo esc_attr( $callout_color ); ?> <?php echo esc_attr( $callout_size ); ?>">
				<?php if ( $callout_image ) : ?>
					<div class="callout-block-image">
						<?php echo wp_get_attachment_image( $callout_image, 'callout-link' ); ?>
					</div>
				<?php endif; ?>
				<div class="callout-block-title">
					<?php if ( $link_the_title ) : ?>
						<a href="<?php echo esc_url( $callout_link['url'] ); ?>" target="<?php echo esc_html( $callout_link['target'] ); ?>">
					<?php endif; ?>
							<h2><?php echo esc_html( $callout_title ); ?></h2>
					<?php if ( $link_the_title ) : ?>
						</a>
					<?php endif; ?>
				</div>
				<?php
				if ( $description ) :
					?>
					<div class="callout-block-body"><?php echo esc_html( strip_tags( $description, '<p><a><strong><b><i><em>' ) ); ?></div>
					<?php
				endif;
				?>
				<?php
				if ( $display_the_button ) :
					?>
					<div class="callout-block-link">
						<a class="button" href="<?php echo esc_url( $callout_link['url'] ); ?>" target="<?php echo esc_attr( $callout_link['target'] ); ?>"><?php echo esc_html( $button_text ); ?></a>
					</div>
				<?php endif; ?>
			</div>
		</div>
	<?php endif; ?>
<?php endif; ?>
