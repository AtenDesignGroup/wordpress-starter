<?php
/**
 * Class that handles interfacing with TEC\Common\Telemetry.
 *
 * @since 6.1.0
 *
 * @package TEC\Events_Pro\Telemetry
 */

namespace TEC\Events_Pro\Telemetry;

/**
 * Class Telemetry
 *
 * @since 6.1.0

 * @package TEC\Events_Pro\Telemetry
 */
class Telemetry {

	/**
	 * The Telemetry plugin slug for The Events Calendar Pro.
	 *
	 * @since 6.1.0
	 *
	 * @var string
	 */
	protected static $plugin_slug = 'events-calendar-pro';

	/**
	 * The "plugin path" for The Events Calendar Pro main file.
	 *
	 * @since 6.1.0
	 *
	 * @var string
	 */
	protected static $plugin_path = 'events-calendar-pro.php';

	/**
	 * Adds The Events Calendar to the list of plugins
	 * to be opted in/out alongside tribe-common.
	 *
	 * @since 6.1.0
	 *
	 * @param array<string,string> $slugs The default array of slugs in the format  [ 'plugin_slug' => 'plugin_path' ]
	 *
	 * @see \TEC\Common\Telemetry\Telemetry::get_tec_telemetry_slugs()
	 *
	 * @return array<string,string> $slugs The same array with The Events Calendar added to it.
	 */
	public function filter_tec_telemetry_slugs( $slugs ) {
		$dir = trailingslashit( basename( EVENTS_CALENDAR_PRO_DIR ) );
		$slugs[self::$plugin_slug] =  $dir . self::$plugin_path;

		return array_unique( $slugs, SORT_STRING );
	}

}
