<?php
/**
 * Handles the update of the base Models from the TEC plugin to add ECP functions
 * and properties.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Models
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Models;

use Generator;
use TEC\Events\Custom_Tables\V1\Tables\Events;
use TEC\Events\Custom_Tables\V1\Tables\Occurrences;
use TEC\Events_Pro\Custom_Tables\V1\Events\Occurrences\Occurrences_Generator;
use TEC\Common\Contracts\Service_Provider;

/**
 * Class Provider
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Models
 */
class Provider extends Service_Provider {

	/**
	 * Registers the ECP specific versions of the Models.
	 *
	 * @since 6.0.0
	 */
	public function register() {
		$events = Events::table_name( false );
		if ( ! has_filter( "tec_custom_tables_{$events}_model_v1_extensions", [ $this, 'extend_event_model' ] ) ) {
			add_filter( "tec_custom_tables_{$events}_model_v1_extensions", [ $this, 'extend_event_model' ] );
		}

		$occurrences = Occurrences::table_name( false );
		if ( ! has_filter( "tec_custom_tables_{$occurrences}_model_v1_extensions", [ $this, 'extend_occurrence_model' ] )) {
			add_filter( "tec_custom_tables_{$occurrences}_model_v1_extensions", [ $this, 'extend_occurrence_model' ] );
		}

		// Yes: 10 arguments to play on the safe side.
		if ( ! has_filter( 'tec_events_custom_tables_v1_occurrences_generator', [ $this, 'get_occurrences_generator' ] ) ) {
			add_filter( 'tec_events_custom_tables_v1_occurrences_generator', [ $this, 'get_occurrences_generator' ], 10, 10 );
		}

		if ( ! add_filter( 'tec_events_custom_tables_v1_normalize_occurrence_id', [ $this, 'normalize_occurrence_id' ] ) ) {
			add_filter( 'tec_events_custom_tables_v1_normalize_occurrence_id', [ $this, 'normalize_occurrence_id' ] );
		}

		if ( ! has_filter( 'tec_events_custom_tables_v1_event_data_from_post', [ $this, 'add_event_post_data' ] ) ) {
			add_filter( 'tec_events_custom_tables_v1_event_data_from_post', [ $this, 'add_event_post_data' ], 10, 2 );
		}
	}

	/**
	 * Unregisters the filters managed by the Provider.
	 *
	 * @since 6.0.0
	 */
	public function unregister() {
		$events = Events::table_name( false );
		remove_filter( "tec_custom_tables_{$events}_model_v1_extensions", [ $this, 'extend_event_model' ] );
		$occurrences = Occurrences::table_name( false );
		remove_filter( "tec_custom_tables_{$occurrences}_model_v1_extensions", [ $this, 'extend_occurrence_model' ] );
		remove_filter( 'tec_custom_tables_v1_occurrences_generator', [ $this, 'get_occurrences_generator' ] );
		remove_filter( 'tec_events_custom_tables_v1_normalize_occurrence_id', [ $this, 'normalize_occurrence_id' ] );
		remove_filter( 'tec_events_custom_tables_v1_event_data_from_post', [ $this, 'add_event_post_data' ] );
	}

	/**
	 * Extends the Event base model to add fields required by ECP.
	 *
	 * @since 6.0.0
	 *
	 * @param array<string,array<string,mixed>> $extensions A map of the current Model
	 *                                                      extensions.
	 *
	 * @return array<string,array<string,mixed>> The filtered extensions map.
	 */
	public function extend_event_model( array $extensions = [] ): array {
		return $this->container->make( Event::class )->extend( $extensions );
	}

	/**
	 * Extends the Occurrence base model to add fields required by ECP.
	 *
	 * @since 6.0.0
	 *
	 * @param array<string,array<string,mixed>> $extensions A map of the current Model
	 *                                                      extensions.
	 *
	 * @return array<string,array<string,mixed>> The filtered extensions map.
	 */
	public function extend_occurrence_model( array $extensions = [] ): array {
		return $this->container->make( Occurrence::class )->extend( $extensions );
	}

	/**
	 * Filters TEC base Occurrences generation method to implement the ECP one.
	 *
	 * @since 6.0.0
	 *
	 * @param Generator|null $generator A reference to the filtered Generator instance, if not
	 *                                  null, then it will NOT be filtered.
	 * @param mixed          $args,...  The set of arguments that should be used to generate
	 *                                  the Occurrences.
	 */
	public function get_occurrences_generator( Generator $generator = null, ...$args ): ?Generator {
		if ( $generator !== null ) {
			return $generator;
		}

		$occurrences_generator = $this->container->make( Occurrences_Generator::class );

		return $occurrences_generator->get_occurrences_generator( ...$args );
	}

	/**
	 * Normalizes an Occurrence post ID taking Provisional Post IDs into
	 * account.
	 *
	 * @since 6.0.0
	 *
	 * @param int $id The Occurrence post ID to normalize.
	 *
	 * @return int The normalized Occurrence post ID.
	 */
	public function normalize_occurrence_id( int $id ): int {
		return $this->container->make( Occurrence::class )->normalize_occurrence_post_id( $id );
	}

	/**
	 * Filters the Event post data adding the ECP data to it.
	 *
	 * @since 6.0.0
	 *
	 * @param array<string,mixed> $data     The Event post data, as produced by The Events Calendar and
	 *                                      previous filtering functions.
	 * @param int                 $event_id The Event post ID.
	 *
	 * @return array<string,mixed> The filtered Event post data.
	 */
	public function add_event_post_data( array $data, int $event_id ): array {
		return $this->container->make( Event::class )->add_event_post_data( $data, $event_id );
	}
}
