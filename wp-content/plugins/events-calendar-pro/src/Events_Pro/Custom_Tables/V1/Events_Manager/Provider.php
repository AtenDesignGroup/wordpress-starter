<?php
/**
 * ${CARET}
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Events_Manager;
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Events_Manager;

use TEC\Events\Custom_Tables\V1\Updates\Events;
use TEC\Common\Contracts\Service_Provider;

/**
 * Class Provider.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Events_Manager;
 */
class Provider extends Service_Provider {

	/**
	 * Hooks on the Filters API to provide the Events Manager view support when working
	 * with the custom tables.
	 *
	 * @since 6.0.0
	 */
	public function register() {
		add_filter( 'tec_events_pro_manager_boundary_datetime_by_status', [
			$this,
			'get_boundary_datetime_by_status'
		], 10, 3 );
	}

	/**
	 * Returns the Events Manager boundary date for the required post statuses.
	 *
	 * @since 6.0.0
	 *
	 * @param int|null     $date        The date as provided to the filter, `null` by default.
	 * @param bool         $fetch_start Whether to fetch the earliest date (`true`) or the latest one (`false`).
	 * @param string|array $stati       A post status, or a list of post stati, to return the boundary date for.
	 *
	 * @return int The boundary date, fetched from the custom tables.
	 */
	public function get_boundary_datetime_by_status( $date, $fetch_start, $stati ) {
		if ( $fetch_start ) {
			return $this->container->make( Events::class )->get_earliest_date( $stati )->format( 'U' );
		}

		return $this->container->make( Events::class )->get_latest_date( $stati )->format( 'U' );
	}
}
