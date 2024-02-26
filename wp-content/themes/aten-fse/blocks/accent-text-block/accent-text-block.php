<?php
/**
 * Accent Text Block Template.
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
	echo '<img src="' . esc_html( $block['data']['preview_image'] ) . '" style="width:100%; height:auto;">';
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
	$color         = get_field( 'background_color' );
	$content       = get_field( 'colored_rich_text_box_fields' );
	$section_title = get_field( 'accent_text_block_title' );
	?>

	<div <?php echo esc_html( $anchor ); ?> class="<?php echo esc_attr( $class_name ); ?>">
		<div class="accent-text-block-component <?php echo esc_attr( $color ); ?>">
			<h2><?php echo esc_html( $section_title ); ?></h2>
			<div class="accent-text-block-content">
				<?php
				if ( have_rows( 'colored_rich_text_box_fields' ) ) :
					?>
					<?php
					while ( have_rows( 'colored_rich_text_box_fields' ) ) :
						the_row();

						// Text & Media block.
						if ( get_row_layout() === 'text_and_media_block' ) :
							$text_content = get_sub_field( 'content' );
							echo esc_html( $text_content );

							// Paragraph block.
						elseif ( get_row_layout() === 'paragraph_block' ) :
							$paragraph_text_size = get_sub_field( 'paragraph_text_size' );
							$paragraph_content   = get_sub_field( 'paragraph_content' );
							echo "<p class='paragraph-" . esc_html( $paragraph_text_size ) . "'>" . esc_html( $paragraph_content ) . '</p>';

							// Resources block.
						elseif ( get_row_layout() === 'resources_block' ) :
							if ( have_rows( 'resources' ) ) :
								?>
							<div class="resources-block">
								<div class="resources-block-wrapper">
									<ul>
								<?php
								while ( have_rows( 'resources' ) ) :
									the_row();
									// Getting the subfield values.
									$resource_title = get_sub_field( 'title' );
									$description    = get_sub_field( 'description' );
									$file           = get_sub_field( 'file' );
									$resource_link  = get_sub_field( 'link' );
									$has_file_type  = '';
									$resource_url   = '';

									if ( ! $file && $resource_link['url'] ) {
										$resource_url = $resource_link['url'];
									} else {
										$resource_url = $file['url'];
										// Check if the resource has a file type.
										$has_file_type = ! empty( get_post_mime_type( $file['ID'] ) );
									}

									// Output the title as a link.
									echo '<li class="resource">';
									echo '<a href="' . esc_url( $resource_url ) . '"><h2>' . esc_html( $resource_title ) . '</h2></a>';

									// Output the description text.
									if ( $description ) {
										echo '<p>' . esc_html( $descriptio ) . '</p>';
									}
									echo '<p class="resource-details">';

									// Output file type icon, file size, and updated date.
									if ( $has_file_type ) {
										// Get the file size in bytes.
										$file_size = $file['filesize'];

										// Convert file size to kilobytes (KB).
										$file_size        = round( $file_size / 1024, 2 );
										$file_size_suffix = 'KB';

										if ( $file_size > 1000 ) {
											$file_size        = round( $file_size / 1000, 2 );
											$file_size_suffix = 'MB';
										}

										// Get the file type extension.
										$filetype = ( wp_check_filetype( $file['filename'] ) );

										// Get the updated date.
										$updated_date = get_the_modified_date( 'm/d/y', $file['ID'] );

										// Output file type icon, file size, and updated date.
										echo '<span class="resource-icon notranslate" aria-hidden="true">description</span> ' . esc_html( $file_size ) . ' ' . esc_html( $file_size_suffix ) . ' ' . esc_html( strtoupper( $filetype['ext'] ) ) . ' | Updated ' . esc_html( $updated_date );

									} else {
										echo '<span class="resource-icon notranslate">link</span> Web Link';
									}

									echo '</p></li>';

								endwhile;
								?>
									</ul>
								</div>
							</div>
								<?php
								// Resource Loop.
							endif;

							// Button block.
						elseif ( get_row_layout() == 'button_block' ) :
							$button_link   = get_sub_field( 'button' );
							$button_style  = get_sub_field( 'style' );
							$button_target = $button_link['target'] ?? '_self';
							?>

							<div class="wp-block-button is-style-<?php echo esc_html( $button_style ); ?>">
								<a class="wp-block-button__link wp-element-button" href="<?php echo esc_html( $button_link['url'] ); ?>" title="<?php echo esc_html( $button_link['title'] ); ?>" target="<?php echo esc_html( $button_target ); ?>">
									<?php echo esc_html( $button_link['title'] ); ?>
								</a>
							</div>

							<?php
							// Video block.
						elseif ( get_row_layout() === 'video_block' ) :
							$video_source = get_sub_field( 'video_source' );
							$video_file   = get_sub_field( 'video_file' );
							$video_link   = get_sub_field( 'video_link' );

							if ( 'link' !== $video_source && $video_file ) :
								?>
								<figure class="wp-block-video">
								<video controls="" preload="metadata" src="<?php echo esc_html( $video_file['url'] ); ?>" type="<?php echo esc_html( $video_file['mime_type'] ); ?>" alt="<?php echo esc_html( $video_file['alt'] ); ?>"></video>
								</figure>
								<?php
		elseif ( 'link' === $video_source && $video_link ) :
				echo esc_url( $video_link );
		endif;
			endif;
	endwhile;
	endif;
				?>
			</div>
		</div>
	</div>
							<?php
						endif; ?>
