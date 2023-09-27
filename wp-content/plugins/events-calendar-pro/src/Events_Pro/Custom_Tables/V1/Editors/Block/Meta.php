<?php
/**
 * Handles the registration and handling of meta fields in the context of the Blocks Editor.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Editors\Block
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Editors\Block;

use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use TEC\Events_Pro\Custom_Tables\V1\Editors\Event;
use TEC\Events_Pro\Custom_Tables\V1\Models\Provisional_Post;
use TEC\Events_Pro\Custom_Tables\V1\Traits\With_Event_Recurrence;
use Tribe__Editor__Meta;
use Tribe__Events__Main as TEC;
use WP_Post;
use Tribe__Utils__Array as Arr;
use Tribe__Events__Pro__Editor__Recurrence__Blocks_Meta as Blocks_Meta;

/**
 * Class Meta
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Editors\Block
 */
class Meta extends Tribe__Editor__Meta {
	use With_Event_Recurrence;

	const REQUIRES_FIRST_SAVE_META_KEY = '_tec_requires_first_save';

	/**
	 * A map from meta keys to the callbacks that will provide their values.
	 *
	 * @since 6.0.0
	 *
	 * @var array<string,callable>
	 */
	protected $meta_keys = [];

	/**
	 * Meta constructor.
	 *
	 * @since 6.0.0
	 */
	public function __construct() {
		$this->meta_keys = [
			self::REQUIRES_FIRST_SAVE_META_KEY => [ $this, 'requires_first_save' ],
		];
	}

	/**
	 * Registers the meta that will be exposed on the Blocks Editor.
	 *
	 * Notice the meta is not actually present in the database, but will be,
	 * instead, created on a per-request basis.
	 *
	 * @since 6.0.0
	 */
	public function register() {
		register_meta( 'post', self::REQUIRES_FIRST_SAVE_META_KEY, $this->boolean() );

		add_filter( 'get_post_metadata', [ $this, 'filter_post_metadata' ], 1, 4 );
		add_filter( 'update_post_metadata', [ $this, 'do_not_update_meta_values' ], 1, 3 );
		add_filter( 'delete_post_metadata', [ $this, 'do_not_update_meta_values' ], 1, 3 );
		add_filter( 'tec_events_custom_tables_v1_blocks_editor_event_meta', [
			$this,
			'filter_blocks_editor_meta'
		], 10, 2 );
		add_filter( 'tec_events_pro_custom_tables_v1_blocks_editor_event_meta', [
			$this,
			'filter_blocks_editor_meta'
		], 10, 2 );
	}

	/**
	 * Returns the current post ID.
	 *
	 * @since 6.0.0
	 *
	 * @return int|null The current post ID or `null` if it cannot be found in any global
	 *                  or super-global.
	 */
	private function get_post_id() {
		$post = get_post();

		if ( ! $post instanceof WP_Post ) {
			$request_post_id = Arr::get_first_set( $_REQUEST, [ 'id', 'ID', 'post_id', 'post', 'post_ID' ], null );
			$post            = get_post( filter_var( $request_post_id, FILTER_VALIDATE_INT ) );
		}

		if ( ! ( $post instanceof WP_Post && $post->post_type === TEC::POSTTYPE ) ) {
			return null;
		}

		return Occurrence::normalize_id( $post->ID );
	}

	/**
	 * Filters the value of a specific post metadata key to return a value if
	 *
	 * @since 6.0.0
	 *
	 *
	 * @param mixed|null $value     The original value of the meta, set to `null` by WordPress.
	 * @param int        $object_id The ID of the post whose metadata is being filtered.
	 * @param string     $meta_key  The metadata being filtered.
	 *
	 * @return mixed The original metadata value if filtering is not required, else the filtered
	 *               meta data value.
	 */
	public function filter_post_metadata( $value, $object_id, $meta_key ) {
		if ( ! isset( $this->meta_keys[ $meta_key ] ) ) {
			return $value;
		}

		$callback = $this->meta_keys[ $meta_key ];

		$value = $callback( $object_id, $meta_key );

		return $value;
	}

	/**
	 * Returns whether an Even post requires a first save or not and the Udpate Types
	 * should, thus, default to All.
	 *
	 * @since 6.0.0
	 *
	 * @return array<bool> Whether an Event post requires a first save or not, in array
	 *                     format to stick to the required format.
	 */
	private function requires_first_save() {
		$post_id = $this->get_post_id();

		if ( $post_id ) {
			return [ ! Occurrence::find( $post_id, 'post_id' ) instanceof Occurrence ];
		}

		return [ true ];
	}

	/**
	 * Filters the update and delete operations on the tracked meta keys to short-circuit them.
	 *
	 * @since 6.0.0
	 *
	 * @param mixed|null $update    A value, initially `null` to indicate whether the value
	 *                              should be updated or not.
	 * @param mixed      $object_id Unused.
	 * @param string     $meta_key  The meta key being updated or deleted.
	 *
	 * @return mixed|true Either the input value if the meta key is not one the
	 *                    class shoudl handle, or `true` to short-circuit the operation.
	 */
	public function do_not_update_meta_values( $update, $object_id, $meta_key ) {
		if ( ! isset( $this->meta_keys[ $meta_key ] ) ) {
			return $update;
		}

		return true;
	}

	/**
	 * Fetches the value of a meta field for an Occurrence provisional post ID redirecting
	 * the request to the original Event post.
	 *
	 * @since 6.0.0
	 *
	 * @param mixed|null $value    The original value, as initialized by the filer, initially `null`.
	 * @param int        $post_id  The post ID to fetch the meta value for.
	 * @param string     $meta_key The meta key to fetch the value for.
	 *
	 * @return mixed|null Either the value fetched for the meta key, or `null` if the method should
	 *                    not be redirected.
	 */
	public function get_value( $value, $post_id, $meta_key ) {
		if ( ! tribe( Provisional_Post::class )->is_provisional_post_id( $post_id ) ) {
			return $value;
		}

		$real_id = Occurrence::normalize_id( $post_id );

		return get_post_meta( $real_id, $meta_key, true );
	}

	/**
	 * Adds a flag to each rule data to indicate whether the rule DTSTART is
	 * off-pattern in respect to the rule.
	 *
	 * @since 6.0.0
	 *
	 * @param array<array<string,mixed>> $data             The Event recurrence rules data in the format
	 *                                                     used by the Blocks Editor.
	 * @param array<string,mixed>        $recurrence_rules The Event recurrence meta rules or exclusions, in the format
	 *                                                     used in the `_EventRecurrence` meta value.
	 * @param int|null                   $post_id          The ID of the post whose meta is being filtered.
	 *
	 * @return array<array<string,mixed>> The Event recurrence meta in the format used by the Blocks
	 *                                    Editor, updated to include the off-pattern DTSTART flag.
	 */
	public function add_off_pattern_dtstart_flag( $data, $recurrence_rules, int $post_id = null ) {
		if ( ! (
			is_array( $data ) && is_array( $recurrence_rules )
			&& count( $data ) === count( $recurrence_rules )
		) ) {
			return $data;
		}

		$post_id = $post_id ?? $this->get_post_id();
		$flag = Event::OFF_PATTERN_DTSTART_FLAG;

		return array_map( function ( array $blocks_rule, array $rule ) use ( $flag, $post_id ) {
			if ( ! isset( $rule[ $flag ] ) ) {
				$rule = $this->add_off_pattern_flag_to_rule( $rule, $post_id );
			}

			// If the flag is not set, then assume the DTSTART is not off-pattern.
			$blocks_rule[ $flag ] = (bool) $rule[ $flag ];

			return $blocks_rule;
		}, $data, $recurrence_rules );
	}

	/**
	 * Updates the Editor configuration array produced  by The Events Calendar to
	 * add the off-pattern DTSTART flag to each recurrence rules.
	 *
	 * @since 6.0.0
	 *
	 * @param array<string,mixed> $editor_config The editor configuration as produced by
	 *                                           The Events Calendar and previous filtering functions.
	 *
	 * @return array<string,mixed> The Editor configuration, modified to include teh off-pattern
	 *                             DTSTART flag into each rule, if required.
	 */
	public function update_editor_config( array $editor_config ) {
		$post_type            = TEC::POSTTYPE;
		$blocks_rules_key     = Blocks_Meta::$rules_key;
		$exclusions_rules_key = Blocks_Meta::$exclusions_key;
		$type_to_blocks_type = [
			'rules'      => Blocks_Meta::$rules_key,
			'exclusions' => Blocks_Meta::$exclusions_key,
		];

		if ( ! isset(
			$editor_config['post_objects'][ $post_type ]['meta'][ $blocks_rules_key ],
			$editor_config['post_objects'][ $post_type ]['meta'][ $exclusions_rules_key ],
			$editor_config['post_objects'][ $post_type ]['meta']['_EventRecurrence'] )
		) {
			return $editor_config;
		}

		$flag            = Event::OFF_PATTERN_DTSTART_FLAG;
		$rules_meta_json = $editor_config['post_objects'][ $post_type ]['meta'][ $blocks_rules_key ];
		$exclusions_meta_json = $editor_config['post_objects'][ $post_type ]['meta'][ $exclusions_rules_key ];
		$classic_meta = null;

		foreach (
			[ 'rules' => $rules_meta_json, 'exclusions' => $exclusions_meta_json ]
			as $type => $json_meta
		) {
			if ( false !== strpos( $json_meta, $flag ) ) {
				// Avoid applying any logic if the flag has already been set.
				continue;
			}

			$blocks_key = $type_to_blocks_type[ $type ];

			// Unserialize classic meta once, just in time.
			$classic_meta = $classic_meta ?:
				unserialize( $editor_config['post_objects'][ $post_type ]['meta']['_EventRecurrence'] );

			if ( ! isset( $classic_meta[ $type ] ) ) {
				continue;
			}

			$decoded_meta = json_decode( $json_meta, true );

			if ( empty( $decoded_meta ) || empty( $classic_meta ) ) {
				return $editor_config;
			}

			$updated_blocks_meta = $this->add_off_pattern_dtstart_flag( $decoded_meta, $classic_meta[ $type ] );

			if ( ! empty( $updated_blocks_meta ) ) {
				$editor_config['post_objects'][ $post_type ]['meta'][ $blocks_key ] = json_encode( $updated_blocks_meta, JSON_UNESCAPED_SLASHES );
			}
		}

		return $editor_config;
	}

	/**
	 * Filters and returns the Blocks Editor format recurrences or exclusions data for a post.
	 *
	 * @since 6.0.0
	 *
	 * @param int                      $post_id The ID of the post to filter the data for.
	 * @param string                   $key     Either `rules` or `exclusions`.
	 * @param array<string,mixed>|null $data    The data to filter.
	 *
	 * @return array|null Either the filtered data, or `null` if th data was empty.
	 */
	public function filter_recurrence_meta_for_post( int $post_id, string $key, array $data = null ): ?array {
		if ( empty( $data ) || ! is_array( $data ) ) {
			return $data;
		}

		$event_recurrence = get_post_meta( $post_id, '_EventRecurrence', true );
		$recurrence = $event_recurrence[ $key ] ?? null;

		return $this->add_off_pattern_dtstart_flag( $data, $recurrence, $post_id );
	}

	/**
	 * Filters the meta keys and values that will be set for an Occurrence in the Blocks Editor context.
	 *
	 * @since 6.0.0
	 *
	 * @param array<string,array> $meta           A map from meta keys to arrays of meta values, in the
	 *                                            same format returned by a `get_post_meta($id)` call.
	 * @param int                 $provisional_id The provisional ID assigned to the Occurrence.
	 *
	 * @return array<string,array> The filtered meta map.
	 */
	public function filter_blocks_editor_meta( array $meta, int $provisional_id ): array {
		if ( ! isset( $meta[ Blocks_Meta::$rules_key ] ) ) {
			$meta[ Blocks_Meta::$rules_key ] = get_post_meta( $provisional_id, Blocks_Meta::$rules_key, true );
		}

		if ( ! isset( $meta[ Blocks_Meta::$exclusions_key ] ) ) {
			$meta[ Blocks_Meta::$exclusions_key ] = get_post_meta( $provisional_id, Blocks_Meta::$exclusions_key, true );
		}

		return $meta;
	}
}
