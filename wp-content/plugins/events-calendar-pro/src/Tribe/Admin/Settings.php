<?php
// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
class Tribe__Events__Pro__Admin__Settings {

	/**
	 * Get (and instantiate, if necessary) the instance of the class
	 *
	 * @static
	 * @return self
	 *
	 */
	public static function instance() {
		return tribe( 'events-pro.admin.settings' );
	}

	/**
	 * Hook the required Methods to the correct filters/actions
	 *
	 * @return void
	 */
	public function hook() {
		add_filter( 'tribe_settings_tab_fields', array( $this, 'inject_mobile_fields' ), 10, 2 );
	}

	/**
	 * Filters the Settings Fields to add the mobile fields
	 *
	 * @param  array  $settings An Array for The Events Calendar fields
	 * @param  string $id       Which tab you are dealing field
	 *
	 * @return array
	 */
	public function inject_mobile_fields( $settings, $id ) {
		// We don't care about other tabs
		if ( 'display' !== $id ) {
			return $settings;
		}

		// Include the fields and replace with the return from the include
		$settings = include Tribe__Events__Pro__Main::instance()->pluginPath . 'src/admin-views/tribe-options-mobile.php';

		return $settings;
	}
}
