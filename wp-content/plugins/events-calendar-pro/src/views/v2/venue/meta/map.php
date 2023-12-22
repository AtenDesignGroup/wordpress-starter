<?php
/**
 * View: Venue meta - Map
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events-pro/v2/venue/map/map.php
 *
 * See more documentation about our views templating system.
 *
 * @link https://evnt.is/1aiy
 *
 * @version 5.0.0
 *
 * @var WP_Post $venue The venue post object.
 * @var object $map_provider Object with data of map provider.
 *
 */

$url = '';
// Verifies if that event has a venue.
if ( ! empty( $venue->geolocation->address ) ) {
	$url = add_query_arg(
		[
			'key'  => $map_provider->api_key,
			'q'    => urlencode( $venue->geolocation->address ),
			'zoom' => (int) tribe_get_option( 'embedGoogleMapsZoom', 15 ),
		],
		$map_provider->iframe_url
	);
}

// Display the map based on the latitude and longitude if the values
// are available and the `Use latitude + longitude` setting is enabled.
if (
	get_post_meta( $venue->ID, '_VenueOverwriteCoords', true )
	&& ! empty( $venue->geolocation->latitude )
	&& ! empty( $venue->geolocation->longitude )
) {
	$url = add_query_arg(
		[
			'key'  => $map_provider->api_key,
			'q'    => urlencode( $venue->geolocation->latitude . ',' . $venue->geolocation->longitude ),
			'zoom' => (int) tribe_get_option( 'embedGoogleMapsZoom', 15 ),
		],
		$map_provider->iframe_url
	);
}

$venue = tribe_get_venue();

?>
<iframe
	title="<?php echo sprintf( __( "Google maps iframe displaying the address to %s", 'tribe-events-calendar-pro' ), $venue ); ?>"
	aria-label="<?php esc_attr_e( 'Venue location map', 'tribe-events-calendar-pro' ); ?>"
	class="tribe-events-pro-venue__meta-data-google-maps-default"
	src="<?php echo esc_url( $url ); ?>"
>
</iframe>
