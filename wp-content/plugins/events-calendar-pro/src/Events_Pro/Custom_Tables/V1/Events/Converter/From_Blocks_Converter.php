<?php
/**
 * Handles the conversion of Blocks Editor format recurrence and exclusion rules to
 * the format used in the `_EventRecurrence` meta field.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Events\Converter;
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Events\Converter;

use DateTimeImmutable;
use Tribe__Events__Pro__Editor__Recurrence__Classic as Blocks_To_Classic_Converter;
use Tribe__Date_Utils as Dates;

/**
 * Class From_Blocks_Converter.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Events\Converter;
 */
class From_Blocks_Converter {
	/**
	 * The set of rules in Blocks Editor format.
	 *
	 * @since 6.0.0
	 *
	 * @var array
	 */
	private $rules;
	/**
	 * A reference to the DTSTART that should be used for the converted rules.
	 *
	 * @since 6.0.0
	 *
	 * @var DateTimeImmutable
	 */
	private $dtstart;
	/**
	 * A reference to the DTEND that should be used for the converted rules.
	 *
	 * @since 6.0.0
	 *
	 * @var DateTimeImmutable
	 */
	private $dtend;

	/**
	 * From_Blocks_Converter constructor.
	 *
	 * since 6.0.0
	 *
	 * @param array             $rules   The set of rules in Blocks Editor format to convert.
	 * @param DateTimeImmutable $dtstart A reference to the DTSTART that should be used for the converted rules.
	 * @param DateTimeImmutable $dtend   A reference to the DTEND that should be used for the converted rules.
	 */
	public function __construct( array $rules, DateTimeImmutable $dtstart, DateTimeImmutable $dtend ) {
		$this->rules = $rules;
		$this->dtstart = $dtstart;
		$this->dtend = $dtend;
	}

	/**
	 * Converts the set of rules from the Blocks Format to the one used in the `_EventRecurrence` meta field.
	 *
	 * @since 6.0.0
	 *
	 * @return array The set of rules converted to the format used in the `_EventRecurrence` meta field.
	 */
	public function to_event_recurrence_format(): array {
		$event_start_date = $this->dtstart->format( Dates::DBDATETIMEFORMAT );
		$event_end_date = $this->dtend->format( Dates::DBDATETIMEFORMAT );
		$converted = [];
		foreach ( $this->rules as $rule ) {
			$converter = new Blocks_To_Classic_Converter( $rule );
			$converter->parse();
			$parsed = $converter->get_parsed();
			$parsed['EventStartDate'] = $event_start_date;
			$parsed['EventEndDate'] = $event_end_date;
			$parsed ['custom']['type'] = isset( $parsed['type'] ) && $parsed['type'] !== 'Custom' ?
				$parsed['type']
				: $parsed['custom']['type'];
			$converted[] = $parsed;
		}

		return $converted;
	}
}