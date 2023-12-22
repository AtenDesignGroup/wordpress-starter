<?php
/**
 * The Virtual Event Integration with Zapier service provider.
 *
 * @since   6.0.11
 * @package TEC\Events_Pro\Compatibility\Event_Automator\Zapier\Maps
 */

namespace TEC\Events_Pro\Compatibility\Event_Automator\Zapier\Maps;

use TEC\Events_Pro\Custom_Tables\V1\Events\Converter\From_Event_Recurrence_Converter;
use TEC\Events_Pro\Custom_Tables\V1\Events\Converter\Event_Rule_Converter\From_Event_Rule_Converter;
use TEC\Events_Pro\Custom_Tables\V1\Events\Recurrence;
use WP_Post;

/**
 * Class Event
 *
 * @since   6.0.11
 *
 * @package TEC\Events_Pro\Compatibility\Event_Automator\Zapier\Maps
 */
class Event {

	/**
	 * Filters the event details with recurrence details.
	 *
	 * @since 6.0.11
	 *
	 * @param array<string|mixed> An array of event details.
	 * @param WP_Post An instance of the event WP_Post object.
	 *
	 * @return array<string|mixed> An array of event details.
	 */
	public function add_recurrence_fields( array $next_event, WP_Post $event ) {
		if ( ! $event->recurring ) {
			return $next_event;
		}

		$rules = Recurrence::from_event( $event );
		if ( ! $rules instanceof Recurrence ) {
			return $next_event;
		}

		if ( ! $rules->get_dtstart() ) {
			return $next_event;
		}

		$recurrence_meta = $rules->to_event_recurrence();
		if ( empty( $recurrence_meta['rules'] ) ) {
			return $next_event;
		}

		$dtstart                         = $event->dates->start;
		$dtend                           = $event->dates->end;
		$from_event_recurrence_converter = new From_Event_Recurrence_Converter( $dtstart, $dtend );
		$converted_rrules = array_map( static function ( array $rule ) use ( $from_event_recurrence_converter ) {
			return ( new From_Event_Rule_Converter( $from_event_recurrence_converter, $rule ) )->convert_to_rrule( false );
		}, $recurrence_meta['rules'] );

		$converted_exclusions = array_map( static function ( array $rule ) use ( $from_event_recurrence_converter ) {
			return ( new From_Event_Rule_Converter( $from_event_recurrence_converter, $rule ) )->convert_to_rrule( false );
		}, $recurrence_meta['exclusions'] );

		// Pro Recurring Fields.
		$pro_fields = [
			'recurring'      => true,
			'recurring_meta' => $recurrence_meta,
			'rrule'          => $converted_rrules,
			'exclusions'     => $converted_exclusions,
		];

		$next_event = array_merge( $next_event, $pro_fields );

		/**
		 * Filters the event details with recurrence details.
		 *
		 * @since 6.0.11
		 *
		 * @param array<string|mixed> $next_event An array of event details.
		 * @param WP_Post             $event      An instance of the event WP_Post object.
		 */
		$next_event = apply_filters( 'tec_pro_automator_map_event_recurrence_details', $next_event, $event );

		return $next_event;
	}

	/**
	 * Filters the event details with Pro additional fields.
	 *
	 * @since 6.0.11
	 *
	 * @param array<string|mixed> $next_event An array of event details.
	 * @param WP_Post             $event      An instance of the event WP_Post object.
	 *
	 * @return array<string|mixed> An array of event details.
	 */
	public function add_additional_fields( array $next_event, WP_Post $event ) : array {
		$additional_fields = $this->get_additional_fields($event);
		if ( empty( $additional_fields ) ) {
			return $next_event;
		}

		// Pro Fields.
		$pro_fields = [
			'additional_fields' => $additional_fields,
		];

		$next_event = array_merge_recursive( $next_event, $pro_fields );

		/**
		 * Filters the event details with Pro additional fields.
		 *
		 * @since 6.0.11
		 *
		 * @param array<string|mixed> $next_event An array of event details.
		 * @param WP_Post             $event      An instance of the event WP_Post object.
		 */
		$next_event = apply_filters( 'tec_pro_automator_map_event_additional_fields_details', $next_event, $event );

		return $next_event;
	}

	/**
	 * Get the additional fields with an id, label, and value for each.
	 *
	 * @since 6.0.11
	 *
	 * @param WP_Post $event An instance of the event WP_Post object.
	 *
	 * @return array<string|array>|false An array of additional field values or false if no values or additional fields saved.
	 */
	protected function get_additional_fields( WP_Post $event ) {
		$additional_field_pairs = tribe_get_custom_fields( $event->ID );
		if ( empty( $additional_field_pairs ) ) {
			return false;
		}

		$additional_field_settings = tribe_get_option( 'custom-fields', false );
		if ( ! is_array( $additional_field_settings ) ) {
			return false;
		}

		$additional_fields = [];
		foreach ( $additional_field_settings as $field ) {
			if ( ! isset ( $additional_field_pairs[ $field['label'] ] ) ) {
				continue;
			}

			$additional_fields[ $field['name'] ] = [
				'label' => $field['label'],
				'value' => $additional_field_pairs[ $field['label'] ],
			];
		}

		return $additional_fields;
	}
}
