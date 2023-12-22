<?php

namespace TEC\Events_Pro\Custom_Tables\V1\Migration\Strategy;
use TEC\Events\Custom_Tables\V1\Migration\Reports\Event_Report;
use TEC\Events\Custom_Tables\V1\Migration\String_Dictionary as TEC_String_Dictionary;

/**
 * Class Migration_Message_Override.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Migration\Strategy;
 */
class Migration_Message_Override {

	/**
	 * Will add a custom event report message, in order to apply multi rule only vars.
	 *
	 * @since 6.0.0
	 *
	 * @param string|null  $message      The message.
	 * @param Event_Report $event_report The event report which the override will apply to.
	 *
	 * @return string
	 */
	public function overwrite_multi_rule_message( $message, $event_report ) {
		$text = tribe( TEC_String_Dictionary::class );

		return sprintf(
			esc_html( $text->get( "migration-prompt-strategy-" . Multi_Rule_Event_Migration_Strategy::get_slug() ) ),
			count( $event_report->created_events ),
			count( $event_report->created_events )
		);
	}
}