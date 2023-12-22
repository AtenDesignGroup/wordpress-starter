<?php
/**
 * A value object providing information about an Event migration.
 *
 * @since   6.0.0
 * @package TEC\Events_Pro\Custom_Tables\V1\Migration\Reports;
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Migration\Reports;

use TEC\Events\Custom_Tables\V1\Migration\State;
use TEC\Events\Custom_Tables\V1\Migration\String_Dictionary;
use TEC\Events_Pro\Custom_Tables\V1\Migration\Strategy\Multi_Rule_Event_Migration_Strategy;
use TEC\Events_Pro\Custom_Tables\V1\Migration\Strategy\Single_Rule_Event_Migration_Strategy;


class Event_Report_Categories {


	/**
	 * Add the migration event report categories.
	 *
	 * @since 6.0.0
	 *
	 * @param array<array{ key:string, label:string }> $categories The report categories.
	 * @param String_Dictionary                        $text       The string translation object.
	 *
	 * @return array<array{ key:string, label:string }>
	 */
	public function add_categories( array $categories, String_Dictionary $text ) {
		$phase = tribe( State::class )->get_phase();
		$label = $text->get( "$phase-strategy-" . Single_Rule_Event_Migration_Strategy::get_slug() );

		array_unshift( $categories,
			[
				'key'   => Single_Rule_Event_Migration_Strategy::get_slug(),
				'label' => $label
			]
		);
		$label = $text->get( "$phase-strategy-" . Multi_Rule_Event_Migration_Strategy::get_slug() );
		array_unshift( $categories,
			[
				'key'   => Multi_Rule_Event_Migration_Strategy::get_slug(),
				'label' => $label
			]
		);

		return $categories;
	}

}