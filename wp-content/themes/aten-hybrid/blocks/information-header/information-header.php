<?php
/**
 * Information Header Block Template.
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
	$class_name = 'information-header-block';
	if ( ! empty( $block['className'] ) ) {
		$class_name .= ' ' . $block['className'];
	}
	if ( ! empty( $block['align'] ) ) {
		$class_name .= ' align' . $block['align'];
	}

	// Load values and assign defaults.
	$post_id = get_the_ID();
	$post_title = get_the_title( $post_id );

	// Setting all values to an empty string by default
	$address = $city = $state = $zip = $email = $hours_of_operation = $phone = $additional_information = $event_date = $event_time = $event_location = '';

	// Looping through the group to access subfields
	if( have_rows('contact_info') ): 
	while( have_rows('contact_info') ): the_row(); 
			$address = (get_sub_field('address') ?? '');
			$city = (get_sub_field('city') ?? '');
			$state = (get_sub_field('state') ?? '');
			$zip = (get_sub_field('zipcode') ?? '');
			$email = (get_sub_field('email') ?? '');
			$hours_of_operation = (get_sub_field('hours_of_operation') ?? '');
			$phone = (get_sub_field('phone_number') ?? '');
			$additional_information = (get_sub_field('additional_information') ?? '');
		endwhile; 
	endif;

	if(get_post_type() === 'tribe_events'):
		$event_date = $event_start = tribe_get_start_date(null, false, 'l, F j, Y');
		$event_end = tribe_get_end_date(null, false, 'l, F j, Y');
		if($event_start !== $event_end) {
			$event_date .= ' – ' . $event_end;
		}
		$event_time = tribe_get_start_time(null, 'g:i A') . ' – ' . tribe_get_end_time(null, 'g:i A');
		$event_location = get_field('event_location', $post_id);
		$additional_information = get_field('event_description', $post_id);
	endif; 

	if(get_post_type() === 'location'):
		if( have_rows('contact_info', $post_id) ): 
			while( have_rows('contact_info', $post_id) ): the_row(); 
				$address = (get_sub_field('address') ?? '');
				$city = (get_sub_field('city') ?? '');
				$state = (get_sub_field('state') ?? '');
				$zip = (get_sub_field('zipcode') ?? '');
				$email = (get_sub_field('email') ?? '');
				$hours_of_operation = (get_sub_field('hours_of_operation') ?? '');
				$phone = (get_sub_field('phone_number') ?? '');
				$additional_information = (get_sub_field('additional_information') ?? '');
			endwhile; 
		endif;
	endif; 

	// Building Google Maps link from the various address pieces
	if($address && $city && $state && $zip) {
		// Concat the entire address string
		$concat_address = $address . ' ' . $city . ' ' . $state . ' ' . $zip;
		// Replace whitespace for Google to handle it properly
		$concat_address = preg_replace('/\s+/', '+', $concat_address);
		$maps_link = 'https://www.google.com/maps/place/' . $concat_address;
	}

	// Check for featured image, and place it as the background
	$header_has_image = false;
	if (has_post_thumbnail()) {
		$header_has_image = true;
	}

	?>
	<div <?php echo $anchor; ?>class="<?php echo esc_attr( $class_name ); if( $header_has_image ) { echo " has-image"; } ?> information-header-block-component">
		<div class="information-header-swishies"><img src="<?php echo get_template_directory_uri(); ?>/assets/img/info-header_swishies.svg" /></div>
		<div class="information-header-block-wrapper l-gutter">
			<div class="information-header-block-title">
				<h1 class="header-text"><?php echo esc_html($post_title); ?></h1>
			</div>
			<hr class="gold" />
			<div class="information-header-block-content <?php if( $header_has_image ) { echo "has-image"; } ?>">
				<?php if( $header_has_image ) : ?>
					<div class="information-header-image-wrapper" style="background-image: url('<?php echo get_the_post_thumbnail_url(); ?>');">
					</div>
				<?php endif; ?>
				<?php
				$post_type = get_post_type($post_id);
				$heading_text = 'Contact Info';

				if ($post_type === 'tribe_events') { $heading_text = 'Event Info'; }
				?>
				<div class="info-wrapper">
					<h2 class="title"><?php echo $heading_text; ?></h2> 
					<?php if(
						$email 
						|| $phone
						|| $address 
						|| $city
						|| $state
						|| $zip
						|| $additional_information
						|| have_rows($hours_of_operation) 
						|| $event_date
						|| $event_time
						|| $event_location ): ?>
						<ul class="contact-info">
							<?php if ($event_date): ?>
								<li class="event-date">
									<span class="contact-icon calendar notranslate" aria-hidden="true">calendar_today</span><span class="a11y-visible">Event Date </span>
									<?php echo $event_date; ?>
								</li>
							<?php endif; // Event Dates ?>

							<?php if ($event_time): ?>
								<li class="event-time">
									<span class="contact-icon clock notranslate" aria-hidden="true">schedule</span><span class="a11y-visible">Event Time </span>
									<?php echo $event_time; ?>
								</li>
							<?php endif; // Event Time ?>

							<?php if ($event_location): ?>
								<li class="event-location">
									<span class="contact-icon location notranslate" aria-hidden="true">location_on</span><span class="a11y-visible">Event Location </span>
									<a href="<?php echo get_the_permalink($event_location->ID); ?>"><?php echo get_the_title($event_location->ID); ?></a>
								</li>
							<?php endif; // Event Location ?>

							<?php if ($phone): ?>
								<li class="phone">
									<span class="contact-icon call notranslate" aria-hidden="true">call</span><span class="a11y-visible">Phone Number </span>
									<a href="tel:<?php echo $phone; ?>" title="Call">
										<?php if(strlen($phone) === 10) { ?>
											(<?php echo substr($phone, 0, 3); ?>) <?php echo substr($phone, 2, 3); ?>-<?php echo substr($phone, 5, 4); ?>
										<?php } else { echo $phone; } ?>
									</a>
								</li>
							<?php endif; // Phone ?>

							<?php if ($address): ?>
								<li class="address">
									<span class="contact-icon address notranslate" aria-hidden="true">location_on</span><span class="a11y-visible">Street Address </span>
									<?php if($maps_link) { ?>
										<a href="<?php echo $maps_link; ?>" target="_blank" title="Get directions">
									<?php } ?>
											<?php echo ($address . ', ' . $city . ', ' . $state . ' ' . $zip); ?> 
									<?php if($maps_link) { ?>
										</a> 
									<?php } ?>
								</li>
							<?php endif; // Address ?>


							<?php if (is_array($hours_of_operation)): 
								$hours = '';
								// Loop through group for the nested repeater to function
								if( have_rows('contact_info', $post_id) ): while( have_rows('contact_info', $post_id) ): the_row();  
								?>
								<li class="information-header-block-hours-of-operation">
									<span class="contact-icon hours notranslate" aria-hidden="true">schedule</span><span class="a11y-visible">Operating Hours </span>
									<span class="hour-entries">Hours: 
										<?php
											// Looping repeater nested inside group
											if(have_rows('hours_of_operation')): while (have_rows('hours_of_operation')) : the_row(); 
											$days = get_sub_field('days');
											$start_time = get_sub_field('start_time');
											$end_time = get_sub_field('end_time');
											
											if ($days && $start_time && $end_time) { 
												$hours .= $days . ' ' . $start_time . '-' . $end_time . '; ';
											}
										endwhile; // While there are hours 

									// Removing trailing semicolon
									echo (substr($hours, 0, -2)); ?>
								</li>
							<?php endif; endwhile; endif; endif; // Hours ?>
						
							<?php if ($email): ?>
								<li class="email"><span class="contact-icon email notranslate" aria-hidden="true">mail</span><span class="a11y-visible">Email Address </span>
									<a href="mailto:<?php echo $email; ?>" title="Send an email"><?php echo $email; ?></a>
								</li>
							<?php endif; // Email ?>

							<?php if ($additional_information): ?>
								<li class="additional-info"><span class="contact-icon info notranslate" aria-hidden="true">info</span><span class="a11y-visible">Additional Information </span>
									<?php echo esc_html($additional_information); ?>
								</li>
							<?php endif; // Additional Information ?>
						</ul>
					<?php endif; // If any contact information is available ?>
				</div>
			</div>

		</div>
	</div>
<?php endif; ?>