<?php
/**
 * Inline Contact Block Template.
 *
 * @package aten-fse
 *
 * @param   array $block The block settings and attributes.
 * @param   string $content The block inner HTML (empty).
 * @param   bool $is_preview True during backend preview render.
 * @param   int $current_post_id The post ID the block is rendering content against.
 *          This is either the post ID currently being displayed inside a query loop,
 *          or the post ID of the post hosting this block.
 * @param   array $context The context provided to the block by the post or it's parent block.
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
	$class_name = 'inline-contact-block';
	if ( ! empty( $block['className'] ) ) {
		$class_name .= ' ' . $block['className'];
	}
	if ( ! empty( $block['align'] ) ) {
		$class_name .= ' align' . $block['align'];
	}

	// Load values and assign defaults.
	$current_post_id    = get_the_ID();
	$current_post_title = get_the_title( $current_post_id );
	// Setting all values to an empty string by default.
	$director               = '';
	$address                = '';
	$city                   = '';
	$state                  = '';
	$zip                    = '';
	$maps_link              = '';
	$email                  = '';
	$hours_of_operation     = '';
	$phone                  = '';
	$additional_information = '';
	$contact                = get_field( 'inline_contact_info', $current_post_id );
	// Looping through the group to access subfields.
	if ( have_rows( 'inline_contact_info', $current_post_id ) ) :
		while ( have_rows( 'inline_contact_info', $current_post_id ) ) :
			the_row();
			$director               = ( get_sub_field( 'director' ) ?? '' );
			$address                = ( get_sub_field( 'address' ) ?? '' );
			$city                   = ( get_sub_field( 'city' ) ?? '' );
			$state                  = ( get_sub_field( 'state' ) ?? '' );
			$zip                    = ( get_sub_field( 'zipcode' ) ?? '' );
			$email                  = ( get_sub_field( 'email' ) ?? '' );
			$hours_of_operation     = ( get_sub_field( 'hours_of_operation' ) ?? '' );
			$phone                  = ( get_sub_field( 'phone_number' ) ?? '' );
			$additional_information = ( get_sub_field( 'additional_information' ) ?? '' );
		endwhile;
endif;

	// Building Google Maps link from the various address pieces.
	if ( $address && $city && $state && $zip ) {
		// Concat the entire address string.
		$concat_address = $address . ' ' . $city . ' ' . $state . ' ' . $zip;
		// Replace whitespace for Google to handle it properly.
		$concat_address = preg_replace( '/\s+/', '+', $concat_address );
		$maps_link      = 'https://www.google.com/maps/place/' . $concat_address;
	}

	if ( $director
		|| $email
		|| $phone
		|| $address
		|| $city
		|| $zip
		|| $additional_information
		|| have_rows( $hours_of_operation ) ) : ?>
		
		<div <?php echo esc_attr( $anchor ); ?>class="<?php echo esc_attr( $class_name ); ?>">
			<div class="inline-contact-info-block-wrapper contact-info l-gutter">
				<div class="inline-contact-info-block">
					<div class="info-content">
						<h2 class="title">Contact Information</h2>

								<?php if ( $director ) : ?>
									<p>
										<strong>Director</strong><br/>
										<?php echo esc_html( $director ); ?>
									</p>
								<?php endif; // Director. ?>

								<?php if ( $address ) : ?>
									<p>
										<strong>Location </strong><br/>
										<?php
										if ( $maps_link ) {
											?>
											<a href="<?php echo esc_url( $maps_link ); ?>" target="_blank" title="Get directions"> <?php } ?>
											<?php echo esc_html( $address . ', ' . $city . ', ' . $state . ' ' . $zip ); ?>
										<?php
										if ( $maps_link ) {
											?>
											</a> <?php } ?>
									</p>
								<?php endif; // Address. ?>

								<?php if ( $phone ) : ?>
									<p><strong>Phone</strong> 
										<a href="tel:<?php echo esc_url( $phone ); ?>" title="Call">
											<?php if ( strlen( $phone ) === 10 ) { ?>
												(<?php echo esc_html( substr( $phone, 0, 3 ) ); ?>) <?php echo esc_html( substr( $phone, 2, 3 ) ); ?>-<?php echo esc_html( substr( $phone, 5, 4 ) ); ?>
												<?php
											} else {
												echo esc_html( $phone ); }
											?>
										</a>
									</p>
								<?php endif; // Phone. ?>

								<?php if ( $email ) : ?>
									<p><strong>Email</strong> 
										<a href="mailto:<?php echo esc_url( $email ); ?>" title="Send an email"><?php echo esc_html( $email ); ?></a>
									</p>
								<?php endif; // Email. ?>

								<?php
								if ( is_array( $hours_of_operation ) ) :
									$hours = '';
									// Loop through group for the nested repeater to function.
									if ( have_rows( 'inline_contact_info', $current_post_id ) ) :
										while ( have_rows( 'inline_contact_info', $current_post_id ) ) :
											the_row();
											?>
										<p class="information-header-block-hours-of-operation">
											<strong>Hours</strong><br/>
											<?php
											// Looping repeater nested inside group.
											if ( have_rows( 'hours_of_operation' ) ) :
												while ( have_rows( 'hours_of_operation' ) ) :
													the_row();
													$days       = get_sub_field( 'days' );
													$start_time = get_sub_field( 'start_time' );
													$end_time   = get_sub_field( 'end_time' );

													if ( $days && $start_time && $end_time ) {
														$hours .= $days . ' ' . $start_time . '-' . $end_time . '; ';
													}
											endwhile; // While there are hours.

												// Removing trailing semicolon.
												echo esc_html( substr( $hours, 0, -2 ) );
												?>
										</p>
												<?php
																		endif;
endwhile;
endif;
endif; // Hours.
								?>

								<?php if ( $additional_information ) : ?>
									<p><strong>For further information:</strong><br/>
										<?php echo esc_html( $additional_information ); ?>
									</p>
								<?php endif; // Additional Information. ?>
							</ul>
					</div>
				</div>

			</div>
		</div>
		<hr class="wp-block-separator is-style-wide separator-component grey-line">
	<?php endif;
endif; ?>
