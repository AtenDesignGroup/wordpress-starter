<?php
/**
 * Provides methods to interact with recurrences in the Blocks Editor format.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Traits;
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Traits;

use Tribe__Events__Pro__Editor__Meta as Pro_Editor_Meta;
use Tribe__Events__Pro__Editor__Recurrence__Blocks as Converter;
use Tribe__Events__Pro__Editor__Recurrence__Blocks_Meta as Blocks_Meta_Keys;

/**
 * Trait With_Blocks_Editor_Recurrence.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Traits;
 */
trait With_Blocks_Editor_Recurrence {
	/**
	 * If required, update the Blocks format recurrence meta.
	 *
	 * @since 6.0.0
	 *
	 * @param array<string,mixed> $recurrence The Event recurrence meta, in the format used by the
	 *                                        `_EventRecurrence` meta value.
	 */
	protected function update_blocks_format_recurrence_meta( int $post_id, array $recurrence ): void {
		$blocks_rules = $this->convert_event_recurrence_rules_to_block_format( $recurrence['rules'] );
		$blocks_exclusions = $this->convert_event_recurrence_rules_to_block_format( $recurrence['exclusions'] );

		$events_pro_editor_meta_bound = tribe()->isBound( 'events-pro.editor.meta' );
		if ( $events_pro_editor_meta_bound ) {
			// ECP editor meta will filter the meta fetches making it look like there is a meta, it should not.
			/** @var Pro_Editor_Meta $events_pro_editor_meta */
			$events_pro_editor_meta = tribe( 'events-pro.editor.meta' );
			$events_pro_editor_meta->unhook();
		}

		update_post_meta( $post_id, Blocks_Meta_Keys::$rules_key, json_encode( $blocks_rules, JSON_UNESCAPED_SLASHES ) );
		update_post_meta( $post_id, Blocks_Meta_Keys::$exclusions_key, json_encode( $blocks_exclusions, JSON_UNESCAPED_SLASHES ) );

		if ( $events_pro_editor_meta_bound ) {
			$events_pro_editor_meta->hook();
		}
	}

	/**
	 * Converts a single rule, or exclusion, from the format used in the `_EventRecurrence`
	 * meta to the format used in the Blocks Editor.
	 *
	 * @since 6.0.0
	 *
	 * @param array<string,mixed> $rule The recurrence rule, or exclusion, in the format
	 *                                  used in the `_EventRecurrence` meta value.
	 *
	 * @return array<string,mixed>|false Either the converted rule, or `false` if the conversion
	 *                                   failed.
	 */
	private function convert_recurrence_meta_rule_to_block_format( array $rule ) {
		$converter = new Converter( $rule );
		$converter->parse();

		return $converter->get_parsed();
	}

	/**
	 * Converts a set of recurrence rules, or exclusions, from the format used in the
	 * `_EventRecurrence` meta value to the format used by the Blocks Editor.
	 *
	 * @since 6.0.0
	 *
	 * @param array<array<string,mixed>> $rules The set of rules to convert.
	 *
	 * @return array<array<string,mixed>> The converted rules set.
	 */
	private function convert_event_recurrence_rules_to_block_format( array $rules ): array {
		return array_map( [ $this, 'convert_recurrence_meta_rule_to_block_format' ], $rules );
	}
}