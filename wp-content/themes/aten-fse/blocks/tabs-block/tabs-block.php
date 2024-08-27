<?php
/**
 * Tabs Block Template.
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
	$class_name = 'tabs-block';
	if ( ! empty( $block['className'] ) ) {
		$class_name .= ' ' . $block['className'];
	}
	if ( ! empty( $block['align'] ) ) {
		$class_name .= ' align' . $block['align'];
	}

	// Custom fields.
	$tab_group_label      = get_field( 'tab_group_label' );
	$hide_tab_group_label = ( get_field( 'hide_tab_group_label' ) ) ? get_field( 'hide_tab_group_label' ) : false;
	$tab_group_id_suffix  = wp_unique_id( substr( md5( serialize( $tab_group_label ) ), 0, 8 ) . '_' );

	// Loop through tab panels.
	if ( have_rows( 'tabs' ) ) :
		$tab_count = 0;
		?>
		<div <?php echo esc_attr( $anchor ); ?> class="<?php echo esc_attr( $class_name ); ?>">
			<h3 id="tab-group-<?php echo esc_attr( $tab_group_id_suffix ); ?>" class="
			tab-group-label 
			<?php
			if ( $hide_tab_group_label ) {
				echo esc_attr( ' a11y-visible' ); }
			?>
			">
				<?php echo esc_html( $tab_group_label ); ?>
			</h3>

			<div role="tablist" aria-labelledby="tab-group-<?php echo esc_attr( $tab_group_id_suffix ); ?>" class="manual tab-list">
				<?php
				while ( have_rows( 'tabs' ) ) :
					the_row();
					++$tab_count;
					// Get panel label.
					$tab_label = get_sub_field( 'tab_label' );
					// Generate a unique ID for each panel.
					$tab_id_suffix = substr( md5( serialize( $tab_label ) ), 0, 8 );
					?>
					<button id="tab-button-<?php echo esc_attr( $tab_id_suffix ); ?>" type="button" role="tab" 
						<?php
						if ( 1 === $tab_count ) {
							echo esc_html( 'aria-selected="true"' ); } else {
							echo esc_html( 'aria-selected="false" tabindex="-1"' );
							}
							?>
						aria-controls="tab-panel-<?php echo esc_attr( $tab_id_suffix ); ?>">
						<span class="focus"><?php echo esc_html( $tab_label ); ?></span>
					</button>
				<?php endwhile; ?>
			</div>

			<?php
			$tab_count = 0;
			while ( have_rows( 'tabs' ) ) :
				the_row();
				++$tab_count;
				// Get panel label.
				$tab_label = get_sub_field( 'tab_label' );
				// Generate a unique ID for each panel.
				$tab_id_suffix = substr( md5( serialize( $tab_label ) ), 0, 8 );
				?>
				<div id="tab-panel-<?php echo esc_attr( $tab_id_suffix ); ?>" role="tabpanel" aria-labelledby="tab-button-<?php echo esc_attr( $tab_id_suffix ); ?>" class="tab-panel 
					<?php
					if ( 1 !== $tab_count ) {
						echo esc_html( 'is-hidden' ); }
					?>
					">
					<?php the_sub_field( 'tab_content' ); ?>
				</div>
			<?php endwhile; ?>
		</div>
		<?php
	endif;

endif; ?>
