<?php
/**
 * Accordion Block Template.
 *
 * @param   array $block The block settings and attributes.
 * @param   string $content The block inner HTML (empty).
 * @param   bool $is_preview True during backend preview render.
 * @param   int $post_id The post ID the block is rendering content against.
 *          This is either the post ID currently being displayed inside a query loop,
 *          or the post ID of the post hosting this block.
 * @param   array $context The context provided to the block by the post or it's parent block.
 */

if( isset( $block['data']['preview_image'] )  ) :    /* rendering in inserter preview  */
    echo '<img src="'. $block['data']['preview_image'] .'" style="width:100%; height:auto;">';
else :

	// Support custom "anchor" values.
	$anchor = '';
	if ( ! empty( $block['anchor'] ) ) {
		$anchor = 'id="' . esc_attr( $block['anchor'] ) . '" ';
	}

	// Create class attribute allowing for custom "className" and "align" values.
	$class_name = 'accordion-block';
	if ( ! empty( $block['className'] ) ) {
		$class_name .= ' ' . $block['className'];
	}
	if ( ! empty( $block['align'] ) ) {
		$class_name .= ' align' . $block['align'];
	} 

	// Loop through accordion panels
	if( have_rows('accordion_panels') ): ?>
		<div <?php echo $anchor; ?>class="<?php echo esc_attr( $class_name ); ?>">

		<?php while( have_rows('accordion_panels') ) : the_row();
			// Get panel label
			$panel_label = get_sub_field('panel_label'); 
			// Generate a unique ID for each panel 
			$id_suffix = substr( md5( serialize( $panel_label ) ), 0, 8 );
			// Get panel contents
			$panel_content = get_sub_field('panel_content');
			?>
			<div class="accordion-panel-wrapper">
				<div class="accordion-block-item">
					<h3>
						<button id="btn-<?php echo $id_suffix; ?>" aria-expanded="false" aria-controls="panel-<?php echo $id_suffix; ?>" class="accordion-block-button">
							<?php echo $panel_label; ?>
						</button>
					</h3>

					<div id="panel-<?php echo $id_suffix; ?>" aria-role="region" aria-labelledby="btn-<?php echo $id_suffix; ?>" class="accordion-block-panel collapsed js-delay">
						<?php echo $panel_content; ?>
					</div>
				</div>
			</div>
		<?php endwhile; // Accordion Panels loop ?>

		</div>
	<?php endif; // Accordion Panels loop 

endif;?>



