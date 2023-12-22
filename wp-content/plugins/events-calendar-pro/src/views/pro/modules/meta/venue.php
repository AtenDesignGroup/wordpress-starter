<?php
/**
 * Single Event Meta (Venue) Template
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe-events/modules/meta/venue.php
 *
 * @package TribeEventsCalendar
 * @version 6.2.0
 */

use Tribe__Events__Editor__Blocks__Event_Venue as Event_Venue;

if ( ! tribe_get_venue_id() ) {
	return;
}

$event_id    = Tribe__Main::post_id_helper();
$venue_ids   = tec_get_venue_ids();
$venue_block = tribe( 'events.editor.blocks.event-venue' );

foreach ( $venue_ids as $venue_id ) {
	echo $venue_block->render( [
		'venue'       => $venue_id,
		'showMap'     => tribe_embed_google_map( $event_id ),
		'showMapLink' => tribe_show_google_map_link( $event_id ),
	] );
}
