<?php
/**
 * Filter the array of views that are registered for the tribe bar
 *
 * @param array   $views          {
 *                                Array of views, where each view is itself represented by an associative array consisting of these keys:
 *
 * @type string   $displaying     slug for the view
 * @type string   $anchor         display text (i.e. "List" or "Month")
 * @type string   $event_bar_hook not used
 * @type string   $url            url to the view
 * }
 *
 * @param boolean $context
 */

use \Tribe\Events\Views\V2\Manager;

$enabled_views = tribe( Manager::class )->get_publicly_visible_views();

$default_view = [
	'default' => esc_html__( 'Use Default View', 'tribe-events-calendar-pro' ),
];

$enabled_views = array_map( static function ( $view ) {
	return tribe( Manager::class )->get_view_label_by_class( $view );
}, $enabled_views );

$enabled_views = array_merge( $default_view, $enabled_views );

$settings = Tribe__Main::array_insert_after_key(
	'viewOption',
	$settings,
	array(
		'mobile_default_view' => array(
			'type'            => 'dropdown',
			'label'           => esc_html__( 'Default mobile view', 'tribe-events-calendar-pro' ),
			'tooltip'         => esc_html__( 'Change the default view for Mobile users.', 'tribe-events-calendar-pro' ),
			'validation_type' => 'not_empty',
			'size'            => 'small',
			'default'         => 'default',
			'options'         => $enabled_views,
		),
	)
);

return $settings;
