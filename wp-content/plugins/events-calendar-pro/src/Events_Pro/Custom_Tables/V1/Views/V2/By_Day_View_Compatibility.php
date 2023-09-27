<?php
/**
 * Handles the compatibility with the By Day Views (e.g. Month, Week) in the
 * context of the ECP plugin.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Views\V2
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Views\V2;

use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use TEC\Events\Custom_Tables\V1\Views\V2\By_Day_View_Compatibility as TEC_By_Day_View_Compatibility;
use TEC\Events_Pro\Custom_Tables\V1\Events\Provisional\ID_Generator as Provisional_ID_Generator;
use Tribe__Timezones as Timezones;

/**
 * Class By_Day_View_Compatibility
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Views\V2
 */
class By_Day_View_Compatibility extends TEC_By_Day_View_Compatibility {
	/**
	 * A reference to the current implementation of the Provisional Post ID Generator.
	 *
	 * @since 6.0.0
	 *
	 * @var Provisional_ID_Generator
	 */
	private $provisional_id_generator;

	/**
	 * By_Day_View_Compatibility constructor.
	 *
	 * @since 6.0.0
	 *
	 * @param Provisional_ID_Generator $provisional_id_generator A reference to the current implementation
	 *                                                           of the Provisional Post ID Generator.
	 */
	public function __construct( Provisional_ID_Generator $provisional_id_generator ) {
		$this->provisional_id_generator = $provisional_id_generator;
	}

	/**
	 * Returns the day results, prepared as the `By_Day_View` expects them.
	 *
	 * @since 6.0.0
	 *
	 * @param array<int> $result_ids      A list of the Event post IDs to prepare the day results
	 *                                    for.
	 *
	 * @return array<int,\stdClass> The prepared day results.
	 */
	public function prepare_day_results( array $result_ids = [] ) {
		if ( empty( $result_ids ) ) {
			return [];
		}

		$use_site_timezone = Timezones::is_mode( 'site' );
		$start_date_prop   = $use_site_timezone ? 'start_date_utc' : 'start_date';
		$end_date_prop     = $use_site_timezone ? 'end_date_utc' : 'end_date';

		$prepared       = [];
		$base           = $this->provisional_id_generator->current();
		$occurrence_ids = array_map( static function ( $provisional_id ) use ( $base ) {
			return $provisional_id > $base ? $provisional_id - $base : $provisional_id;
		}, $result_ids );

		/** @var Occurrence $occurrence */
		$column = 'occurrence_id';

		$occurrences = Occurrence::order_by( $start_date_prop, 'ASC' )
		                    ->find_all( $occurrence_ids, $column );

		foreach ( $occurrences as $occurrence ) {
			$prepared[ $base + $occurrence->occurrence_id ] = (object) [
				'ID'         => $base + $occurrence->occurrence_id,
				'start_date' => $occurrence->{$start_date_prop},
				'end_date'   => $occurrence->{$end_date_prop},
				'timezone'   => get_post_meta( $occurrence->post_id, '_EventTimezone', true ),
			];
		}

		return $prepared;
	}
}
