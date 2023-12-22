<?php
/**
 * Marker for an Event related to Series in Single View.
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events-pro/custom-tables-v1/single/series-relationship-marker.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @since 6.0.0
 *
 * @version 6.0.0
 *
 * @var WP_Post $event                     The object associated with the current post.
 * @var string  $series_relationship_label The text that should be displayed in the icon.
 * @var string  $series_title              The filtered title of the Series post related to this Event.
 * @var string  $series_link               The URL to the Series post related to this Event.
 * @var string  $fg_accent_color_class     The class indicating the element should use the Customizer
 *                                         controlled accent color.
 */

use TEC\Events\Custom_Tables\V1\Models\Occurrence;

$classes = [
	'tribe-events-series-relationship-single-marker',
	'tribe-common',
];

if ( ! empty( $modifier ) ) {
	$classes[] = 'tribe-events-series-relationship-single-marker--' . $modifier;
}
$id = $event->ID;

// This is an occurrence the real post ID is hold as a reference on the occurrence table.
if ( isset( $event->_tec_occurrence ) && $event->_tec_occurrence instanceof Occurrence ) {
	$id = $event->_tec_occurrence->post_id;
}

$series = tec_event_series( $id );
$title_classes = tec_get_series_marker_label_classes( $series, $id );
?>
<div <?php tribe_classes( $classes ); ?>>
	<em
		class="tribe-events-series-relationship-single-marker__icon"
		aria-label="<?php echo esc_attr( $series_relationship_label ); ?>"
		title="<?php echo esc_attr( $series_relationship_label ); ?>"
	>
		<?php $this->template( 'components/icons/series', [ 'classes' => [ 'tribe-events-series-relationship-single-marker__icon-svg' ] ] ); ?>
	</em>

	<span class="tribe-events-series-relationship-single-marker__prefix">
		<?php echo esc_html( $series_relationship_label ); ?>
	</span>

	<a
		href="<?php echo esc_url( $series_link ); ?>"
		class="tribe-events-series-relationship-single-marker__title tribe-common-cta--alt"
	>
		<span class="tec_series_marker__title" >
			<?php echo esc_html( $series_title ); ?>
		</span>
	</a>
</div>
