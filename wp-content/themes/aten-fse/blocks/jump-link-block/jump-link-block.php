<?php
/**
 * Jump Link Block Template.
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
	$class_name = 'jump-link-block';
	if ( ! empty( $block['className'] ) ) {
		$class_name .= ' ' . $block['className'];
	}
	if ( ! empty( $block['align'] ) ) {
		$class_name .= ' align' . $block['align'];
	}

	// Loop through jump link sections.
	if ( have_rows( 'jump_link_sections' ) ) :
		$jump_link_section_title = get_field( 'jump_link_section_title' );
		?>
		<div <?php echo esc_attr( $anchor ); ?> class="<?php echo esc_attr( $class_name ); ?>">
			<a id="jump-link-block-top-link"></a>
			<h2 class="jump-link-block-title"><?php echo esc_html( $jump_link_section_title ); ?></h2>
			<div class="jump-link-block-list-container"></div>
			<ul class="jump-link-sections">

				<?php
				while ( have_rows( 'jump_link_sections' ) ) :
					the_row();
					// Get section title.
					$section_title = get_sub_field( 'section_title' );
					?>
					<li class="jump-link-section">
						<h2 class="jump-link-section-title"><?php echo esc_html( $section_title ); ?></h2>
						<a href="#jump-link-block-top-link" class="a11y-visible skip-link">Scroll back to Jump Links</a>
						<ul class="jump-link-section-content">
							<?php
							// Loop through flexible content field.
							if ( have_rows( 'section_content' ) ) :
								while ( have_rows( 'section_content' ) ) :
									the_row();
									?>
									<li class="jump-link-section-content-item">
										<?php
										// Text & Media block.
										if ( get_row_layout() == 'text_and_media_block' ) :
											$text_content = get_sub_field( 'content' );
											echo "<div class='text-media-block'>" . esc_html( $text_content ) . '</div>';

											// Resources block.
										elseif ( get_row_layout() == 'resources_block' ) :
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

																if ( ! $file ) {
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
																	echo '<p>' . esc_html( $description ) . '</p>';
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
																	echo '<span class="resource-icon notranslate" aria-hidden="true">link</span> Web Link';
																}

																echo '</p></li>';

															endwhile;
															?>
														</ul>
													</div>
												</div>

												<?php
											endif; // Resource Loop.

											// Button block.
										elseif ( get_row_layout() === 'button_block' ) :
											$button_link   = get_sub_field( 'button' );
											$button_style  = get_sub_field( 'style' );
											$button_target = isset( $button_link['target'] ) ? $button_link['target'] : '_self';
											?>

											<div class="wp-block-button is-style-<?php echo esc_attr( $button_style ); ?>">
												<a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( $button_link['url'] ); ?>" title="<?php echo esc_attr( $button_link['title'] ); ?>" target="<?php echo esc_attr( $button_target ); ?>">
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
													<video controls="" preload="metadata" src="<?php echo esc_url( $video_file['url'] ); ?>" type="<?php echo esc_attr( $video_file['mime_type'] ); ?>" alt="<?php echo esc_attr( $video_file['alt'] ); ?>"></video>
												</figure>
												<?php
											elseif ( 'link' === $video_source && $video_link ) :
												echo esc_url( $video_link );
											endif; // Video source type.

										elseif ( get_row_layout() === 'accordion_block' ) :
											// Loop through accordion panels.
											if ( have_rows( 'accordion_panels' ) ) :
												?>
												<div class="accordion-block">

													<?php
													while ( have_rows( 'accordion_panels' ) ) :
														the_row();
														// Get panel label.
														$panel_label = get_sub_field( 'panel_label' );
														// Generate a unique ID for each panel.
														$id_suffix = substr( md5( serialize( $panel_label ) ), 0, 8 );
														?>
														<div class="accordion-panel-wrapper">
															<div class="accordion-block-item">
																<h3>
																	<button id="btn-<?php echo esc_attr( $id_suffix ); ?>" aria-expanded="false" aria-controls="panel-<?php echo esc_attr( $id_suffix ); ?>" class="accordion-block-button">
																		<span class="accordion-icon closed notranslate" aria-hidden="true">add_circle</span>
																		<?php echo esc_html( $panel_label ); ?>
																	</button>
																</h3>

																<div id="panel-<?php echo esc_attr( $id_suffix ); ?>" aria-role="region" aria-labelledby="btn-<?php echo esc_attr( $id_suffix ); ?>" class="accordion-block-panel collapsed">

																	<?php
																	// Loop through flexible content field.
																	if ( have_rows( 'panel_content' ) ) :
																		while ( have_rows( 'panel_content' ) ) :
																			the_row();
																			// Text & Media block.
																			if ( get_row_layout() == 'text_and_media_block' ) :
																				$text_content = get_sub_field( 'content' );
																				echo esc_html( $text_content );

																				// Resources block.
																			elseif ( get_row_layout() == 'resources_block' ) :
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

																									if ( ! $file ) {
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
																										echo '<p>' . esc_html( $description ) . '</p>';
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
																										echo '<span class="resource-icon notranslate" aria-hidden="true">link</span> Web Link';
																									}

																									echo '</p></li>';

																								endwhile;
																								?>
																							</ul>
																						</div>
																					</div>

																					<?php
																				endif; // Resource Loop.

																				// Button block.
																			elseif ( get_row_layout() == 'button_block' ) :
																				$button_link   = get_sub_field( 'button' );
																				$button_style  = get_sub_field( 'style' );
																				$button_target = $button_link['target'] ? $button_link['target'] : '_self';
																				?>

																				<div class="wp-block-button is-style-<?php echo esc_attr( $button_style ); ?>">
																					<a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( $button_link['url'] ); ?>" title="<?php echo esc_attr( $button_link['title'] ); ?>" target="<?php echo esc_attr( $button_target ); ?>">
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
																						<video controls="" preload="metadata" src="<?php echo esc_url( $video_file['url'] ); ?>" type="<?php echo esc_attr( $video_file['mime_type'] ); ?>" alt="<?php echo esc_attr( $video_file['alt'] ); ?>"></video>
																					</figure>
																					<?php
																				elseif ( 'link' === $video_source && $video_link ) :
																					echo esc_url( $video_link );
																				endif; // Video source type.
																			endif; // Accordion Flexible Content type.
																		endwhile; // Panel Content Loop.
																	endif; // Panel Content Loop.
																	?>
																</div>
															</div>
														</div>
													<?php endwhile; // Accordion Panels loop. ?>
												</div>
												<?php
											endif; // Accordion Panels loop.

										endif; // Section Flexible Content type.
										?>
									</li>
									<?php
								endwhile; // Section Content Loop.
							endif; // Section Content Loop.
							?>
						</ul>
					</li>
				<?php endwhile; // Jump Link sections loop. ?>
			</ul>
		</div>
		<?php
	endif; // Jump Link sections loop.
endif;
?>
