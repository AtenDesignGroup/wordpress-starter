<?php
/**
 * Single Event Meta Template
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe-events/pro/modules/meta.php
 *
 * @version 6.2.0
 *
 * @package TribeEventsCalendarPro
 */

/**
 * Fires before outputting single meta.
 *
 * This is a clone of an action from TEC.
 *
 * @since 6.2.0
 */
do_action( 'tribe_events_single_meta_before' );

// Check for skeleton mode (no outer wrappers per section)
/**
 * Filters whether or not the skeleton is being output.
 *
 * This is a clone of an action from TEC.
 *
 * @since 6.2.0
 *
 * @param bool $not_skeleton Whether or not the skeleton is being output.
 * @param int $post_id The post ID.
 */
$not_skeleton = ! apply_filters( 'tribe_events_single_event_the_meta_skeleton', false, get_the_ID() );
?>

<?php if ( $not_skeleton ) : ?>
	<div class="tribe-events-single-section tribe-events-event-meta primary tribe-clearfix">
<?php endif; ?>

<?php
/**
 * Fires before outputting the primary meta section.
 *
 * This is a clone of an action from TEC.
 *
 * @since 6.2.0
 */
do_action( 'tribe_events_single_event_meta_primary_section_start' );

// Always include the main event details in this first section
tribe_get_template_part( 'modules/meta/details' );

// Include organizer meta if appropriate
if ( tribe_has_organizer() ) {
	tribe_get_template_part( 'modules/meta/organizer' );
}

/**
 * Fires after outputting the primary meta section.
 *
 * This is a clone of an action from TEC.
 *
 * @since 6.2.0
 */
do_action( 'tribe_events_single_event_meta_primary_section_end' );
?>

<?php if ( $not_skeleton ) : ?>
	</div>
<?php endif; ?>
	<div class="tribe-events-single-section tribe-events-event-meta tec-single__venue-container secondary tribe-clearfix">
		<h2 class="tribe-events-single-section-title"> <?php esc_html_e( tribe_get_venue_label_plural(), 'the-events-calendar' ) ?> </h2>
		<div class="tec-single__venue-wrapper tec-single__venue-wrapper--classic">
			<?php tribe_get_template_part( 'modules/meta/venue' ); ?>
		</div>
	</div>
<?php
/**
 * Fires after outputting single meta.
 *
 * This is a clone of an action from TEC.
 *
 * @since 6.2.0
 */
do_action( 'tribe_events_single_meta_after' );
