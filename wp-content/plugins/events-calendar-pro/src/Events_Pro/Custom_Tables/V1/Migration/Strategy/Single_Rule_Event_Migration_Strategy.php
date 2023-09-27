<?php

namespace TEC\Events_Pro\Custom_Tables\V1\Migration\Strategy;

use TEC\Events\Custom_Tables\V1\Migration\Expected_Migration_Exception;
use TEC\Events\Custom_Tables\V1\Migration\Migration_Exception;
use TEC\Events\Custom_Tables\V1\Migration\Reports\Event_Report;
use TEC\Events\Custom_Tables\V1\Migration\Strategies\Strategy_Interface;
use TEC\Events\Custom_Tables\V1\Migration\String_Dictionary;
use TEC\Events\Custom_Tables\V1\Models\Event;
use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use TEC\Events\Custom_Tables\V1\Traits\With_String_Dictionary;
use TEC\Events_Pro\Custom_Tables\V1\Events\Converter\From_Rset_Converter;
use TEC\Events_Pro\Custom_Tables\V1\Traits\With_Blocks_Editor_Recurrence;
use TEC\Events_Pro\Custom_Tables\V1\Traits\With_Event_Recurrence;
use Tribe__Events__Main as TEC;
use TEC\Events_Pro\Custom_Tables\V1\Models\Series as Series_Model;
use TEC\Events_Pro\Custom_Tables\V1\Series\Relationship;
use Tribe__Events__Pro__Editor__Recurrence__Blocks_Meta as Blocks_Meta_Keys;

/**
 * Class Single_Rule_Event_Migration_Strategy.
 *
 * @since   6.0.0
 * @package TEC\Events_Pro\Custom_Tables\V1\Migration\Strategy;
 */
class Single_Rule_Event_Migration_Strategy implements Strategy_Interface {
	use With_Event_Recurrence;
	use With_Blocks_Editor_Recurrence;
	use With_String_Dictionary;

	/**
	 * {@inheritDoc}
	 */
	public static function get_slug() {
		return 'tec-ecp-single-rule-strategy';
	}

	/**
	 * Single_Rule_Event_Migration_Strategy constructor.
	 *
	 * @since 6.0.0
	 *
	 * @param int  $post_id The post ID of the Event to migrate.
	 * @param bool $dry_run Whether the migration should actually commit information,
	 *                      or run in dry-run mode.
	 *
	 * @throws Migration_Exception If the post is not an Event or the Event is not Recurring
	 *                             and with at most one RRULE.
	 */
	public function __construct( $post_id, $dry_run ) {
		$this->post_id = $post_id;

		if ( TEC::POSTTYPE !== get_post_type( $post_id ) ) {
			throw new Migration_Exception( 'Post is not an Event.' );
		}

		$recurrence_meta = get_post_meta( $post_id, '_EventRecurrence', true );

		if ( ! ( is_array( $recurrence_meta ) && isset( $recurrence_meta['rules'] ) ) ) {
			throw new Migration_Exception( 'Event Post is not recurring.' );
		}

		if ( $this->count_rrules( $recurrence_meta['rules'] ) > 1 ) {
			throw new Migration_Exception( 'Recurring Event has more than 1 RRULE.' );
		}

		$this->dry_run = $dry_run;
	}

	/**
	 * {@inheritDoc}
	 */
	public function apply( Event_Report $event_report ) {
		$upserted = Event::upsert( [ 'post_id' ], Event::data_from_post( $this->post_id ) );

		if ( $upserted === false ) {
			$errors       = Event::last_errors();
			$error_string = implode( '. ', $errors );
			$text         = tribe( String_Dictionary::class );

			$message = sprintf(
				$text->get( 'migration-error-k-upsert-failed' ),
				$this->get_event_link_markup( $this->post_id ),
				$error_string,
				'<a target="_blank" href="https://evnt.is/migrationhelp">',
				'</a>'
			);

			throw new Expected_Migration_Exception( $message );
		}

		$event_model = Event::find( $this->post_id, 'post_id' );

		if ( ! $event_model instanceof Event ) {
			throw new Migration_Exception( 'Event model could not be found.' );
		}

		if ( empty( $event_model->rset ) ) {
			throw new Migration_Exception( 'Event model does not have an RSET: it should at this stage.' );
		}

		/*
		 * The conversion might have modified the converted RSET (e.g. conflating multiple
		 * EXRULEs into a single EXRULE and EXDATEs); save again and update the `_EventRecurrence`
		 * meta to reflect the new state.
		 */
		$rset_converter = tribe( From_Rset_Converter::class );
		$event_recurrence_meta = $rset_converter->convert_to_event_recurrence( $event_model->rset, $this->post_id );
		update_post_meta( $this->post_id, '_EventRecurrence', $event_recurrence_meta );

		// If the Event had blocks format Rules, update those too.
		if ( ! empty( get_post_meta( $this->post_id, Blocks_Meta_Keys::$rules_key, true ) ) ) {
			$this->update_blocks_format_recurrence_meta( $this->post_id, $event_recurrence_meta );
		}

		$event_model->occurrences()->save_occurrences();
		$post = get_post( $this->post_id );

		$series_post_id = Series_Model::vinsert( [ 'title' => $post->post_title ], [ 'post_status' => $post->post_status ] );
		tribe( Relationship::class )->with_event( $event_model, [ $series_post_id ] );

		// Check if any occurrences created.
		$count = Occurrence::where( 'post_id', '=', $this->post_id )
		                   ->count();
		if ( $count === 0 ) {
			throw new Migration_Exception( 'No occurrences created.' );
		}

		$event_report->add_series( get_post( $series_post_id ) );

		return $event_report->add_strategy( self::get_slug() )
		                    ->set( 'is_single', false )
		                    ->migration_success();
	}
}