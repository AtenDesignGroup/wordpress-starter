<?php
/**
 * Controls the modifications to the WordPress and TEC Settings required by the feature.
 *
 * @since   6.0.12
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Admin;
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Admin;

use TEC\Common\Contracts\Provider\Controller;

/**
 * Class Settings_Controller.
 *
 * @since   6.0.12
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Admin;
 */
class Settings_Controller extends Controller {
	/**
	 * Hooks the Controller methods to the appropriate WordPress and TEC actions and filters.
	 *
	 * @since 6.0.12
	 *
	 * @return void The Controller methods are hooked to the appropriate WordPress and TEC actions and filters.
	 */
	public function do_register(): void {
		add_filter( 'tribe_settings_tab_fields', [ $this, 'update_tec_settings' ], 20 );
		add_filter( 'tribe_get_option_recurrenceMaxMonthsAfter', [ $this, 'filter_option_value' ], 0 );
	}

	/**
	 * Unregisters the filters hooked by this controller.
	 *
	 * @since 6.0.12
	 *
	 * @return void The filters hooked by this controller are unregistered.
	 */
	public function unregister(): void {
		remove_filter( 'tribe_settings_tab_fields', [ $this, 'update_tec_settings' ], 20 );
		remove_filter( 'tribe_get_option_recurrenceMaxMonthsAfter', [ $this, 'filter_option_value' ], 0 );
	}

	/**
	 * Filters the TEC Settings to remove the "recurrenceMaxMonthsAfter" setting.
	 *
	 * @since 6.0.12
	 *
	 * @param array<string,mixed> $args The TEC Settings.
	 *
	 * @return array<string,mixed> The TEC Settings, without the "recurrenceMaxMonthsAfter" setting.
	 */
	public function update_tec_settings( $args ) {
		if ( ! is_array( $args ) ) {
			return $args;
		}

		return array_diff_key( $args, [ 'recurrenceMaxMonthsAfter' => true ] );
	}

	/**
	 * Filters the "recurrenceMaxMonthsAfter" option value to always return 60.
	 *
	 * @since 6.0.12
	 *
	 * @return int The "recurrenceMaxMonthsAfter" option value, always 60.
	 */
	public function filter_option_value(): int {
		return 60;
	}
}
