<?php
/**
 * Accent Text Block Template.
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
	$class_name = 'accent-text-block';
	if ( ! empty( $block['className'] ) ) {
		$class_name .= ' ' . $block['className'];
	}
	if ( ! empty( $block['align'] ) ) {
		$class_name .= ' align' . $block['align'];
	}

	// Load values and assign defaults.
	$color = get_field('background_color');
	$content = get_field('colored_rich_text_box_fields');
	$section_title = get_field('accent_text_block_title');
	?>

	<div <?php echo $anchor; ?> class="<?php echo esc_attr($class_name); ?>">
		<div class="accent-text-block-component <?php echo esc_attr($color); ?>">
			<h2><?php echo $section_title; ?></h2>
			<div class="accent-text-block-content">
				<?php if (have_rows('colored_rich_text_box_fields')) : ?>
					<?php while (have_rows('colored_rich_text_box_fields')) : the_row(); 

						// Text & Media block
						if( get_row_layout() == 'text_and_media_block' ):
							$text_content = get_sub_field('content');
							echo $text_content;

						// Paragraph block
						elseif( get_row_layout() == 'paragraph_block' ):
							$paragraph_text_size = get_sub_field('paragraph_text_size');
							$paragraph_content = get_sub_field('paragraph_content');
							echo "<p class='paragraph-" . $paragraph_text_size . "'>" . $paragraph_content . "</p>";

						// Resources block
						elseif( get_row_layout() == 'resources_block' ): 
							if (have_rows('resources')) : ?>
							<div class="resources-block">
								<div class="resources-block-wrapper">
									<ul>
									<?php while (have_rows('resources')) : the_row();
										// Getting the subfield values
										$title = get_sub_field('title');
										$description = get_sub_field('description');
										$file = get_sub_field('file');
										$link = get_sub_field('link');
										$has_file_type = $resource_url = '';

										if(!$file && $link['url']) {
											$resource_url = $link['url'];
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
											echo '<span class="resource-icon notranslate">link</span> Web Link';
										}

										echo '</p></li>';

									endwhile; ?>
									</ul>
								</div>
							</div>
						<?php endif; // Resource Loop

						// Button block
						elseif( get_row_layout() == 'button_block' ): 
						$button_link = get_sub_field('button');
						$button_style = get_sub_field('style'); 
						$button_target = isset($button_link['target']) ? $button_link['target'] : '_self';
						?>

							<div class="wp-block-button is-style-<?php echo $button_style; ?>">
								<a class="wp-block-button__link wp-element-button" href="<?php echo $button_link['url']; ?>" title="<?php echo $button_link['title']; ?>" target="<?php echo $button_target; ?>">
									<?php echo $button_link['title']; ?>
								</a>
							</div>

						<?php 
						// Video block
						elseif( get_row_layout() == 'video_block' ): 
							$video_source = get_sub_field('video_source');
							$video_file = get_sub_field('video_file'); 
							$video_link = get_sub_field('video_link');

							if($video_source !== 'link' && $video_file): ?>
								<figure class="wp-block-video">
									<video controls="" preload="metadata" src="<?php echo $video_file['url']; ?>" type="<?php echo $video_file['mime_type']; ?>" alt="<?php echo $video_file['alt']; ?>"></video>
								</figure>
							<?php elseif($video_source === 'link' && $video_link): 
								echo $video_link;
							endif; // Video source type 
						endif; // Content type loop ?>
					<?php endwhile; // While content blocks ?>
				<?php endif; // If content blocks ?>
			</div>
		</div>
		<div class="wave-border">
			<img src="<?php echo get_template_directory_uri(); ?>/assets/img/wave-border_<?php echo esc_attr( $color ); ?>.svg" alt="" />
		</div>
	</div>
<?php endif; ?>