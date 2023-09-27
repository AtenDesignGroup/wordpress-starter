<?php
/**
 * Provides the code required to extend the base Event Model using the extensions API.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Models
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Models;

use TEC\Events\Custom_Tables\V1\Models\Event as Event;
use TEC\Events\Custom_Tables\V1\Models\Formatters\Boolean_Formatter;
use TEC\Events\Custom_Tables\V1\Models\Formatters\Integer_Key_Formatter;
use TEC\Events\Custom_Tables\V1\Models\Occurrence as Occurrence_Model;
use TEC\Events\Custom_Tables\V1\Models\Validators\Ignore_Validator;
use TEC\Events\Custom_Tables\V1\Tables\Occurrences;
use TEC\Events_Pro\Custom_Tables\V1\Events\Provisional\ID_Generator;
use TEC\Events_Pro\Custom_Tables\V1\RRule\RSet_Wrapper;
use TEC\Events_Pro\Custom_Tables\V1\Updates\Post_Links\Single_Edit_Post_Link;
use Tribe__Cache as Cache;
use Tribe__Date_Utils as Dates;
use Tribe__Utils__Array as Arr;
use TEC\Events_Pro\Custom_Tables\V1\RRule\Occurrence as Occurrence_Date;

/**
 * Class Occurrence
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Models
 */
class Occurrence {

	/**
	 * ${CARET}
	 *
	 * @since 6.0.0
	 *
	 * @var Provisional_Post
	 */
	private $provisional_post;

	/**
	 * ${CARET}
	 *
	 * @since 6.0.0
	 *
	 * @var Cache
	 */
	private $cache;

	/**
	 * ${CARET}
	 *
	 * @since 6.0.0
	 *
	 * @var ID_Generator
	 */
	private $id_generator;

	public function __construct( Provisional_Post $provisional_post, ID_Generator $id_generator, Cache $cache ) {
		$this->provisional_post = $provisional_post;
		$this->cache = $cache;
		$this->id_generator = $id_generator;
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
	public function extend( array $extensions = [] ): array {
		return wp_parse_args( [
			'validators' => [
				'has_recurrence' => Ignore_Validator::class,
				'sequence'       => Ignore_Validator::class,
				'is_rdate'       => Ignore_Validator::class,
			],
			'formatters' => [
				'has_recurrence' => Boolean_Formatter::class,
				'sequence'       => Integer_Key_Formatter::class,
				'is_rdate'       => Boolean_Formatter::class,
			],
			'properties' => [
				'provisional_id' => [ $this, 'get_provisional_id' ],
				'is_rdate'       => [ $this, 'get_is_rdate' ],

			],
			'methods'    => [
				'get_single_edit_post_link' => function () {
					/** @var Occurrence_Model $this Bound at run time to the Closure. */
					return ( new Single_Edit_Post_Link( $this ) )->__toString();
				}
			],
		], $extensions );
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
	public function normalize_occurrence_post_id( int $id ): int {
		if ( ! $this->provisional_post->is_provisional_post_id( $id ) ) {
			return $id;
		}

		$occurrence = Occurrence_Model::find(
			$this->provisional_post->normalize_provisional_post_id( $id ),
			'occurrence_id'
		);

		return $occurrence instanceof Occurrence_Model ? $occurrence->post_id : $id;
	}

	/**
	 * Fetches the sequence value for an Event Occurrences.
	 *
	 * Note: the first, valid, sequence value is `1`. A value of `0` indicates
	 * no sequence was found.
	 *
	 * @since 6.0.0
	 *
	 * @param int $post_id The Event post ID to fetch the sequence for.
	 *
	 * @return int The sequence value, or `0` if no sequence could be found.
	 */
	public static function get_sequence( int $post_id ): int {
		global $wpdb;
		$occurrences = Occurrences::table_name( true );
		$sequence    = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT MAX(sequence) FROM $occurrences WHERE post_id = %d",
				$post_id
			)
		);

		return empty( $sequence ) ? 0 : (int) $sequence;
	}

	/**
	 * Returns an Occurrence provisional post ID.
	 *
	 * @since 6.0.0
	 *
	 * @param array<string,mixed> $model_data The Occurrence model data, as provided by
	 *                                        the base Model.
	 *
	 * @return int|null The Occurrence provisional post ID, or `null` if not found.
	 */
	public function get_provisional_id( array $model_data ): ?int {
		return isset( $model_data['occurrence_id'] ) ?
			$this->id_generator->provide_id( $model_data['occurrence_id'] )
			: null;
	}

	/**
	 * Returns whether an Occurrence is an RDATE or not.
	 *
	 * @since 6.0.0
	 *
	 * @param array<string,mixed> $model_data The Occurrence model data, as provided by
	 *                                        the base Model.
	 *
	 * @return bool Whether the Occurrence is an RDATE or not.
	 */
	public function get_is_rdate( array $model_data ): bool {
		return ! empty( $model_data['is_rdate'] );
	}
}
