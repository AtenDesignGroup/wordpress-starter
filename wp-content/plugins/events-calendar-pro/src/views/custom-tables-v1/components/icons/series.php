<?php
/**
 * View: Series Icon
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events-pro/custom-tables-v1/components/icons/series.php
 *
 * See more documentation about our views templating system.
 *
 * @link    http://evnt.is/1aiy
 *
 * @var array<string> $classes Additional classes to add to the svg icon.
 *
 * @version 6.0.0
 */

$svg_classes = [ 'tribe-common-c-svgicon', 'tribe-common-c-svgicon--series' ];

if ( ! empty( $classes ) ) {
	$svg_classes = array_merge( $svg_classes, $classes );
}
?>

<svg <?php tribe_classes( $svg_classes ); ?> width="14" height="12" viewBox="0 0 14 12" fill="none" xmlns="http://www.w3.org/2000/svg">
	<title><?php _e('Event Series', 'tribe-events-calendar-pro'); ?></title>
	<rect x="0.5" y="4.5" width="9" height="7" />
	<path d="M2 2.5H11.5V10" />
	<path d="M4 0.5H13.5V8" />
</svg>
