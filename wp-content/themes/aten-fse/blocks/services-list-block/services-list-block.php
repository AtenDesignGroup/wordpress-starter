<?php
/**
 * Services List Block Template.
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
	$class_name = 'services-list-block';
	if ( ! empty( $block['className'] ) ) {
		$class_name .= ' ' . $block['className'];
	}
	if ( ! empty( $block['align'] ) ) {
		$class_name .= ' align' . $block['align'];
	}

	// Load values and assign defaults.
	$title = get_field( 'title');
	$services = get_field( 'services');

	?>

	<div <?php echo $anchor; ?>class="<?php echo esc_attr( $class_name ); ?>">
		<div class="services-list-block-component l-full">
			<?php
			// Display the title field
			$title = get_field('title');
			echo '<div class="l-gutter"><div class="services-list-block-title"><h2>' . $title . '</h2></div>';

			// Loop over the services
			if (have_rows('services')) {
				echo '<ul>';
				while (have_rows('services')) {
					the_row();

					// Get the post object field and icon field
					$service = get_sub_field('service');
					$icon = get_sub_field('icon');

					// Check if the post object exists
					if ($service) {
						$service_title = $service->post_title;
						$service_url = get_permalink($service->ID);

						// Display the title as a link along with the icon
						echo '<li><a href="' . esc_url($service_url) . '">';

						// Check if the icon exists
						if (!empty($icon)) {
							echo '<span class="link-icon notranslate" aria-hidden="true">' . $icon . '</span><h3>';
						}

						echo $service_title . '</h3></a></li>';
					}
				}
				echo '</ul></div>';
			}
			?>
		</div>
	</div>
<?php endif; ?>