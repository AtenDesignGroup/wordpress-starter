<?php
/**
 * A repository for the Series post type.
 *
 * @since   6.0.1
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Repository;
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Repository;

use TEC\Events_Pro\Custom_Tables\V1\Events\Provisional\ID_Generator;
use TEC\Events_Pro\Custom_Tables\V1\Models\Provisional_Post;
use TEC\Events_Pro\Custom_Tables\V1\Models\Series_Relationship;
use Tribe__Repository as Repository;
use TEC\Events_Pro\Custom_Tables\V1\Series\Post_Type as Series;

/**
 * Class Series_Repository.
 *
 * @since   6.0.1
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Repository;
 */
class Series_Repository extends Repository {

	/**
	 * A map of the default arguments that should be applied to each query built by the repository.
	 *
	 * @since 6.0.1
	 *
	 * @var array<string,mixed>
	 */
	protected $default_args = [ 'post_type' => Series::POSTTYPE ];

	/**
	 * Series_Repository constructor.
	 *
	 * @since 6.0.1
	 */
	public function __construct() {
		parent::__construct();
		$this->schema['event_post_id'] = [ $this, 'filter_by_event_post_id' ];
	}

	/**
	 * Filters the Series by the Event Post ID.
	 *
	 * @since 6.0.1
	 * @param array|int $event_post_ids An array of Event Post IDs, or a single Event Post ID.
	 *
	 * @return array<string,int> The
	 */
	public function filter_by_event_post_id( $event_post_ids ): array {
		$provisional_post = tribe( Provisional_Post::class );
		$normalized_post_ids = array_map( static function ( $id ) use ( $provisional_post ) {
			return $provisional_post->is_provisional_post_id( $id ) ?
				$provisional_post->get_occurrence_post_id( $id )
				: (int) $id;
		}, (array) $event_post_ids );

		$series_post_ids = Series_Relationship::where_in( 'event_post_id', $normalized_post_ids )
			->pluck( 'series_post_id' );

		if ( count( array_filter( $series_post_ids ) ) === 0 ) {
			// If no Series was found matching, the query is void and will never return any value.
			$this->void_query( true );
			$series_post_ids = [ 0 ];
		}

		return [ 'post__in' => $series_post_ids ];
	}
}