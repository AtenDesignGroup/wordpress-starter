<?php

namespace TEC\Events_Pro\Custom_Tables\V1\Migration\Strategy;

use TEC\Events\Custom_Tables\V1\Migration\Expected_Migration_Exception;
use TEC\Events\Custom_Tables\V1\Migration\Migration_Exception;
use TEC\Events\Custom_Tables\V1\Migration\Reports\Event_Report;
use TEC\Events\Custom_Tables\V1\Migration\Strategies\Strategy_Interface;
use TEC\Events\Custom_Tables\V1\Migration\String_Dictionary;
use TEC\Events\Custom_Tables\V1\Traits\With_String_Dictionary;
use TEC\Events_Pro\Custom_Tables\V1\Traits\With_Event_Recurrence;
use Tribe__Events__Main as TEC;

/**
 * Class Event_Tickets_With_Rules_Migration_Strategy.
 *
 * @since   6.0.0
 * @package TEC\Events_Pro\Custom_Tables\V1\Migration\Strategy;
 */
class Event_Tickets_With_Rules_Migration_Strategy implements Strategy_Interface {
	use With_Event_Recurrence;
	use With_String_Dictionary;

	/**
	 * {@inheritDoc}
	 */
	public static function get_slug() {
		return 'tec-ecp-with-tickets-rule-strategy';
	}

	/**
	 * tribe_events_has_tickets constructor.
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
		$this->dry_run = $dry_run;

		if ( TEC::POSTTYPE !== get_post_type( $post_id ) ) {
			throw new Migration_Exception( 'Post is not an Event.' );
		}

		$recurrence_meta = get_post_meta( $post_id, '_EventRecurrence', true );

		if ( ! ( is_array( $recurrence_meta ) && isset( $recurrence_meta['rules'] ) ) ) {
			throw new Migration_Exception( 'Event Post is not recurring.' );
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function apply( Event_Report $event_report ) {
		// @todo Do we care about the ticket provider here?
		$event_report->set_tickets_provider( 'unknown' );
		$text = tribe( String_Dictionary::class );

		$message = sprintf(
			$text->get( 'migration-error-k-tickets-exception' ),
			$this->get_event_link_markup( $this->post_id ),
			'<a target="_blank" href="https://evnt.is/1b7a">',
			'</a>',
			'<a target="_blank" href="https://evnt.is/migrationhelp">',
			'</a>'
		);

		throw new Expected_Migration_Exception( $message );
	}
}