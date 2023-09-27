<?php
/**
 * Manipulates and syncs the meta values relevant for updates in the context of the Blocks Editor.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Updates
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Updates;

use Exception;
use Tribe__Events__Pro__Editor__Meta as Editor_Meta;
use Tribe__Events__Pro__Editor__Recurrence__Blocks_Meta as Meta_Definitions;

/**
 * Class Blocks_Meta
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Updates
 */
class Blocks_Meta {

	/**
	 * A reference to the ECP Editor meta handler.
	 *
	 * @since 6.0.0
	 *
	 * @var Editor_Meta
	 */
	private $editor_meta;

	/**
	 * Blocks_Meta constructor.
	 *
	 * @since 6.0.0
	 *
	 * @param Editor_Meta $editor_meta A reference to the ECP Editor meta handler.
	 */
	public function __construct( Editor_Meta $editor_meta ) {
		$this->editor_meta = $editor_meta;
	}

	/**
	 * Converts the `_EventRecurrence` to the format used by the Block Editor.
	 *
	 * @since 6.0.0
	 *
	 * @param array<string,mixed> $classic_meta The value of the meta to convert, in the format used
	 *                                          in the `_EventRecurrence` meta value.
	 *
	 * @return array<string,string> A map from `rules` and `exclusions` to their JSON string
	 *                              representation.
	 */
	public function from_classic_format( $classic_meta ) {
		$converted_pairs = [];

		if ( empty( $classic_meta ) || ! is_array( $classic_meta ) ) {
			return $converted_pairs;
		}

		try {
			$classic_to_blocks = array(
				'rules'      => Meta_Definitions::$rules_key,
				'exclusions' => Meta_Definitions::$exclusions_key,
			);
			foreach ( $classic_meta as $classic_key => $classic_rules ) {
				// Only care about exclusions and rules.
				if ( ! isset( $classic_to_blocks[ $classic_key ] ) ) {
					continue;
				}
				// Convert rules for the... converter?
				foreach ( $classic_rules as $i => $rule ) {
					if ( isset( $rule['custom']['year']['month'] ) ) {
						$classic_rules[ $i ]['custom']['year']['month'] = implode( ',', (array) $rule['custom']['year']['month'] );
					}
				}
				$new_values                                            = $this->editor_meta->parse_for_rules( $classic_rules );
				$converted_pairs[ $classic_to_blocks[ $classic_key ] ] = json_encode( $new_values, JSON_UNESCAPED_SLASHES );
			}
		} catch ( Exception $e ) {
			/*
			 * The original code is meant to be used in the context of a specific flow,
			 * outside that flow there might be exceptions we're ignoring here.
			 */
			return $converted_pairs;
		}

		return $converted_pairs;
	}

	/**
	 * Deletes the Blocks Editor recurrence and exclusion rules meta values for a post.
	 *
	 * @since 6.0.0
	 *
	 * @param int $post_id The Event post ID to delete the Blocks Editor format meta for.
	 */
	public function delete_blocks_meta( $post_id ) {
		$meta_keys = [
			Meta_Definitions::$rules_key,
			Meta_Definitions::$exclusions_key,
		];

		foreach ( $meta_keys as $meta_key ) {
			delete_post_meta( $post_id, $meta_key );
		}
	}
}