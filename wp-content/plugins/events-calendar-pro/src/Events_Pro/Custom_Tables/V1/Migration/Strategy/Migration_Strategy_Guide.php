<?php

namespace TEC\Events_Pro\Custom_Tables\V1\Migration\Strategy;
use TEC\Events\Custom_Tables\V1\Migration\Migration_Exception;
use TEC\Events\Custom_Tables\V1\Migration\Strategies\Strategy_Interface;
use TEC\Events_Pro\Custom_Tables\V1\Traits\With_Event_Recurrence;

/**
 * Class Migration_Strategy_Guide.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Migration\Strategy;
 */
class Migration_Strategy_Guide {
	use With_Event_Recurrence;

	/**
	 * Inspects the event to determine which strategy to apply for the
	 * migration.
	 *
	 * @since 6.0.0
	 *
	 * @param Strategy_Interface|null $current_strategy A reference to the migration
	 *                                                  strategy proposed for the Event
	 *                                                  by previous filtering code.
	 * @param int                     $post_id          The post ID of the Event to migrate.
	 * @param bool                    $dry_run          Whether the migration should run in preview (dry-run) mode
	 *                                                  or not.
	 *
	 * @return Strategy_Interface|null A reference to the migration strategy to use, or `null`
	 *                                 if no apt migration strategy could be found.
	 *
	 * @throws Migration_Exception If there's an issue building the migration strategy.
	 */
	public function strategy_loader( $current_strategy, $post_id, $dry_run ) {
		// Let someone override us.
		if ( null !== $current_strategy ) {
			return $current_strategy;
		}

		// Check if recurrence.
		$recurrence = get_post_meta( $post_id, '_EventRecurrence', true );

		// Not a Recurring Event? Let someone else handle it.
		if ( empty( $recurrence ) || empty( $recurrence['rules'] ) ) {
			return null;
		}

		// ET with any RRULE
		if ( function_exists( 'tribe_events_has_tickets' ) && tribe_events_has_tickets( get_post( $post_id ) ) ) {
			return new Event_Tickets_With_Rules_Migration_Strategy( $post_id, $dry_run );
		}

		// Only 1 RRULE
		if ( $this->count_rrules( $recurrence['rules'] ) <= 1 ) {
			return new Single_Rule_Event_Migration_Strategy( $post_id, $dry_run );
		}

		// More than 1 RRULE
		if ( $this->count_rrules( $recurrence['rules'] ) > 1 ) {
			return new Multi_Rule_Event_Migration_Strategy( $post_id, $dry_run );
		}
	}
}