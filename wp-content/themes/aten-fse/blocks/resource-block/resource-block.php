<?php
/**
 * Resources Block Template.
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
	$class_name = 'resources-block';
	if ( ! empty( $block['className'] ) ) {
		$class_name .= ' ' . $block['className'];
	}
	if ( ! empty( $block['align'] ) ) {
		$class_name .= ' align' . $block['align'];
	}

	// Looping through repeater for Column Blocks
	if (have_rows('resources')) : ?>

		<div <?php echo $anchor; ?>class="<?php echo esc_attr($class_name); ?>">
			<div class="resources-block-wrapper">
				<ul>
					<?php while (have_rows('resources')) : the_row();
						// Getting the subfield values
						$title = get_sub_field('title');
						$description = get_sub_field('description');
						$file = get_sub_field('file');
						$link = get_sub_field('link');
						$has_file_type = $resource_url = '';

						if(!$file) {
							if(is_array($link)) {
								$resource_url = $link['url'];
							} else {
								$resource_url = $link;
							}
						} else {
							$resource_url = $file['url'];
							// Check if the resource has a file type
							$has_file_type = !empty(get_post_mime_type($file['ID']));
						}

						// Output the title as a link
						echo '<li class="resource">';
						echo '<a href="' . esc_url($resource_url) . '"><h2>' . $title . '</h2></a>';

						// Output the description text
						if ($description) {
							echo '<p>' . $description . '</p>';
						}
						echo '<p class="resource-details">';

						// Output file type icon, file size, and updated date
						if ($has_file_type) {
							// Get the file size in bytes
							$file_size = $file['filesize'];

							// Convert file size to kilobytes (KB)
							$file_size = round($file_size / 1024, 2);
							$file_size_suffix = 'KB';
							
							if($file_size > 1000) {
								$file_size = round($file_size / 1000, 2);
								$file_size_suffix = 'MB';
							}

							// Get the file type extension
							$filetype = (wp_check_filetype($file['filename']));

							// Get the updated date
							$updated_date = get_the_modified_date('m/d/y', $file['ID']);

							// Output file type icon, file size, and updated date
							echo '<span class="resource-icon notranslate" aria-hidden="true">description</span> ' . $file_size . ' ' . $file_size_suffix . ' ' . strtoupper($filetype['ext']) . ' | Updated ' . $updated_date;

							
						} else {
							echo '<span class="resource-icon notranslate" aria-hidden="true">link</span> Web Link';
						}

						echo '</p></li>';

					endwhile; ?>
				</ul>
			</div>
		</div>

	<?php endif; 
endif; ?>
