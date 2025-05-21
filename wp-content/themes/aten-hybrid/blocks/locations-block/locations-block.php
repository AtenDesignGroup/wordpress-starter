<?php
/**
 * Locations Block Template.
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
    echo '<img src="' . esc_url( get_site_url() . $block['data']['preview_image'] ) . '" style="width:100%; height:auto;">';
else :
		
	// Support custom "anchor" values.
	$anchor = '';
	if ( ! empty( $block['anchor'] ) ) {
		$anchor = 'id="' . esc_attr( $block['anchor'] ) . '" ';
	}

	// Create class attribute allowing for custom "className" and "align" values.
	$class_name = 'locations-block';
	if ( ! empty( $block['className'] ) ) {
		$class_name .= ' ' . $block['className'];
	}
	if ( ! empty( $block['align'] ) ) {
		$class_name .= ' align' . $block['align'];
	}

	$locations = get_field('locations');
	?>

	<div <?php echo $anchor; ?> class="<?php echo esc_attr($class_name); ?>">
		<div class="locations-block-component">
			<?php
			// Output the locations from the repeater field
			if (have_rows('locations')) :
				echo '<ul class="location-blocks">';

				// Set up post data
				setup_postdata(get_post());

				while (have_rows('locations')) : the_row();
					// Determine whether Location is an external link or an existing Location post
					$is_location_external = get_sub_field('external_link');

					// Get fields from the block
					$phone = get_sub_field('phone');
					$address = get_sub_field('address');
					$city = get_sub_field('city');
					$state = get_sub_field('state');
					$zip = get_sub_field('zipcode');
					$hours_of_operation = get_sub_field('hours_of_operation');
					$info = get_sub_field('info');
					$image = wp_get_attachment_image( get_sub_field('image'), 'location-image' );
					$location_title = $location_link = $maps_link = '';

					if($is_location_external && get_sub_field('external_location_link')) {
						$location_link = (get_sub_field('external_location_link')['url']) ? get_sub_field('external_location_link')['url'] : '';
						$location_title = (get_sub_field('external_location_link')['title']) ? get_sub_field('external_location_link')['title'] : '';
					} else {
						// Get post info for the existing Location post
						$location_post = get_sub_field('existing_location');
						if($location_post) {
							$location_post_id = $location_post->ID;

							$location_link = get_permalink($location_post_id);
							$location_title = get_the_title($location_post_id);
		
							// Getting default information for the location from the post's ACF fields
							$default_phone = get_field('contact_info_phone_number', $location_post_id);
							$default_address = get_field('contact_info_address', $location_post_id);
							$default_city = get_field('contact_info_city', $location_post_id);
							$default_state = get_field('contact_info_state', $location_post_id);
							$default_zip = get_field('contact_info_zipcode', $location_post_id);
							$default_hours = get_field('contact_info_hours_of_operation', $location_post_id);
							$default_info = get_field('contact_info_additional_information', $location_post_id);
		
							// Defaulting to Location post values unless overridden by ACF content entered in block
							$phone = empty($phone) ? esc_html($default_phone) : esc_html($phone);
							$address = empty($address) ? esc_html($default_address) : esc_html($address);
							$city = empty($city) ? esc_html($default_city) : esc_html($city);
							$state = empty($state) ? esc_html($default_state) : esc_html($state);
							$zip = empty($zip) ? esc_html($default_zip) : esc_html($zip);
							$info = empty($info) ? $default_info : $info;
							$image = empty($image) ? get_the_post_thumbnail($location_post_id, 'location-image') : $image;
							if(empty($hours_of_operation)) {
								$hours_parent_repeater = get_field('contact_info', $location_post_id);
								$hours_of_operation = $default_hours;
							}
						} 
					} 

					// Generating Google Maps link if all pieces of address are present
					if($address && $city && $state && $zip) {
						// Concat the entire address string
						$concat_address = $address . ' ' . $city . ' ' . $state . ' ' . $zip;
						// Replace whitespace for Google to handle it properly
						$concat_address = preg_replace('/\s+/', '+', $concat_address);
						$maps_link = 'https://www.google.com/maps/place/' . $concat_address;
					}

					// Locations need to have some type of information in order to display
					if($location_title || $location_link || $phone || $address || $hours_of_operation || $info) : ?>
					
						<li class="location-item">
							<!-- Info Block -->
							<div class="location-information">
								<h2 class="location-block-title"><a href="<?php echo $location_link; ?>"><?php echo $location_title; ?></a></h2>
								<ul class="location-details">
									<?php 
									
									// Phone
									if($phone) : ?>
										<li class="location-phone">
											<span class="contact-icon call notranslate" aria-hidden="true">call</span><span class="a11y-visible">Phone Number </span>
											<a href="tel:<?php echo $phone; ?>" title="Call">
												<?php if(strlen($phone) === 10) { ?>
													(<?php echo substr($phone, 0, 3); ?>) <?php echo substr($phone, 2, 3); ?>-<?php echo substr($phone, 5, 4); ?>
												<?php } else { echo $phone; } ?>
											</a>
										</li>
									<?php endif;
									
									// Address
									if($address || $city || $zip) : ?>
										<li class="location-address">
											<span class="contact-icon address notranslate" aria-hidden="true">location_on</span><span class="a11y-visible">Street Address </span>
											<?php if ($maps_link) { ?>
												<a href="<?php echo $maps_link; ?>" target="_blank" title="Get directions">
											<?php } ?>
													<?php echo ($address . ', ' . $city . ', ' . $state . ' ' . $zip); ?>
											<?php if ($maps_link) { ?>
												</a>
											<?php } ?>
										</li>
									<?php endif;
									
									// Hours
									if(is_array($hours_of_operation)) : 
									$hours = ''; ?>
										<li class="location-hours">
											<span class="contact-icon hours notranslate" aria-hidden="true">schedule</span><span class="a11y-visible">Operating Hours </span>
											<span class="hour-entries">Hours:
											<?php foreach($hours_of_operation as $hours_entry) {
												$days = $hours_entry['days'];
												$start_time = $hours_entry['start_time'];
												$end_time = $hours_entry['end_time'];

												if ($days && $start_time && $end_time) {
													$hours .= $days . ' ' . $start_time . '-' . $end_time . '; ';
												}
											}

											// Removing trailing semicolon
											echo (substr($hours, 0, -2)); ?>
										</li>
									<?php endif; // Hours 

									// Additional Information
									if($info) : ?>
										<li class="location-info">
											<span class="contact-icon info notranslate" aria-hidden="true">info</span><span class="a11y-visible">Additional Information </span>
											<?php echo esc_html($info); ?>
										</li>
									<?php endif; ?>
								</ul>
							</div>

							<!-- Image Block -->
							<div class="location-image <?php if(!$image) { echo 'default-image'; } else { echo 'custom-image'; } ?>">
								<?php if($image) :
									echo $image;
								elseif(!$image) : ?>
									<img src="<?php echo get_template_directory_uri(); ?>/assets/icons/location_default.svg" alt="<?php echo $location_title; ?>" />
								<?php endif; ?>
							</div>
						</li>

					<?php endif;
				endwhile;
				wp_reset_postdata();
			echo '</ul>';
			endif;
			?>
		</div>
	</div>
<?php endif; ?>