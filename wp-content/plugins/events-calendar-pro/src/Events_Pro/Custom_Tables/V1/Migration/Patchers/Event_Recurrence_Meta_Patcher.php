<?php
/**
 * Patches and updates the event recurrence meta to the current format.
 *
 * @since   6.0.1
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Migration\Patchers;
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Migration\Patchers;

use DateTimeImmutable;
use TEC\Events\Custom_Tables\V1\Migration\Migration_Exception;
use Tribe__Events__Pro__Recurrence__Meta_Builder as Meta_Builder;
use Tribe__Date_Utils as Dates;

/**
 * Class Event_Recurrence_Meta_Patcher.
 *
 * @since   6.0.1
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Migration\Patchers;
 */
class Event_Recurrence_Meta_Patcher {
	/**
	 * The recurrence meta to patch, if required.
	 *
	 * @since 6.0.1
	 *
	 * @var array{rules: array<string,mixed>, exclusions: array<string,mixed>, description:string}
	 */
	private $recurrence_meta;

	/**
	 * The event post ID the meta belongs to.
	 *
	 * @since 6.0.1
	 *
	 * @var int
	 */
	private $post_id;

	/**
	 * Event_Recurrence_Meta_Patcher constructor.
	 *
	 * since 6.0.1
	 *
	 * @param mixed $recurrence_meta The recurrence meta to patch.
	 * @param int   $post_id         The post ID the recurrence meta belongs to.
	 */
	public function __construct( array $recurrence_meta, int $post_id ) {
		$this->recurrence_meta = $recurrence_meta;
		$this->post_id = $post_id;
	}

	/**
	 * Returns the patched recurrence meta.
	 *
	 * @since 6.0.1
	 *
	 * @return array{rules: array<string,mixed>, exclusions: array<string,mixed>, description: string} The patched
	 *                                                                                                 recurrence meta.
	 * @throws Migration_Exception If the meta could not be patched.
	 */
	public function patch(): array {
		$rules = $this->recurrence_meta['rules'] ?? [];

		if ( empty( $rules ) ) {
			// If there are no rules, any other value is irrelevant.
			return [ 'rules' => [], 'exclusions' => [], 'description' => '' ];
		}

		$dtstart = get_post_meta( $this->post_id, '_EventStartDate', true );
		$dtend = get_post_meta( $this->post_id, '_EventEndDate', true );
		$meta_builder = new Meta_Builder( $this->post_id, [
			'recurrence'     => $this->recurrence_meta,
			'EventStartDate' => $dtstart,
			'EventEndDate'   => $dtend,
		] );

		$built = $meta_builder->build_meta();

		return $this->apply_additional_patches( $built, $dtstart, $dtend );
	}

	/**
	 * Applies additional patches to the recurrence meta that the legacy formatter might have missed
	 * to prepare it for use in the migration.
	 *
	 * @since 6.0.1
	 *
	 * @param array{rules: array, exclusions: array, description: string} $recurrence_meta The recurrence meta to
	 *                                                                                     patch.
	 * @param string                                                      $start_date      The event start date in
	 *                                                                                     string format.
	 * @param string                                                      $end_date        The event end date in string
	 *                                                                                     format.
	 *
	 * @return array{rules: array, exclusions: array, description: string} The patched recurrence meta.
	 *
	 * @throws Migration_Exception If a rule requiring patching cannot be patched.
	 */
	private function apply_additional_patches( array $recurrence_meta, string $start_date, string $end_date ): array {
		$dtstart = Dates::immutable( $start_date );
		$dtend = Dates::immutable( $end_date );

		if ( ! isset( $recurrence_meta['rules'] ) ) {
			return $recurrence_meta;
		}

		$rules = array_filter( (array) $recurrence_meta['rules'] );
		foreach ( $rules as $k => &$rule ) {
			$rule = $this->patch_rule( $rule, $dtstart, $dtend );

			if ( ! is_array( $rule ) ) {
				throw new Migration_Exception( 'Rule ' . sprintf( $rule, $k, ) );
			}
		}
		$recurrence_meta['rules'] = $rules;

		if ( isset( $recurrence_meta['exclusions'] ) ) {
			$exclusions = array_filter( (array) $recurrence_meta['exclusions'] );

			foreach ( $exclusions as $k => &$exclusion ) {
				$exclusion = $this->patch_rule( $exclusion, $dtstart, $dtend );

				if ( ! is_array( $exclusion ) ) {
					throw new Migration_Exception( 'Exclusion ' . sprintf( $exclusion, $k ) );
				}
			}
		}
		$recurrence_meta['exclusions'] = $exclusions ?? [];

		return $recurrence_meta;
	}

	/**
	 * Patches a single rule making sure the deductible required pieced will be there.
	 *
	 * @since 6.0.1
	 *
	 * @param array<string,mixed> $rule    The rule to patch.
	 * @param DateTimeImmutable   $dtstart The event start date.
	 * @param DateTimeImmutable   $dtend   The event end date.
	 *
	 * @return array<string,mixed>|string The patched rule or a string format for the exception to throw.
	 */
	private function patch_rule( array $rule, DateTimeImmutable $dtstart, DateTimeImmutable $dtend ) {
		if ( ! isset( $rule['type'] ) ) {
			return "rule %d rule type is missing";
		}

		$type = $rule['type'];
		if ( $type === 'Custom' && isset( $rule['custom']['type'] ) ) {
			$type = $rule['custom']['type'];
		}

		$rule['EventStartDate'] = $dtstart->format( Dates::DBDATETIMEFORMAT );
		$rule['EventEndDate'] = $dtend->format( Dates::DBDATETIMEFORMAT );

		if ( ! in_array( $type, [ 'Date', 'Daily', 'Weekly', 'Monthly', 'Yearly' ], true ) ) {
			return "rule %d rule type is invalid ($type)";
		}

		$patched = $rule;
		switch ( $type ) {
			case 'Weekly':
				$patched['type'] = 'Custom';
				$patched['custom']['type'] = 'Weekly';
				$patched['custom'] = $patched['custom'] ?? ['week' => []] ;
				$patched['custom']['week'] = $patched['custom']['week'] ?? [];
				$custom_week_day = $patched['custom']['week']['day'] ?? null;
				$patched['custom']['week']['day'] = $custom_week_day ?: [ $dtstart->format( 'N' ) ];
				break;
			case 'Date':
				// When the Date should be the same as the DTSTART, previous installations would set it to `false`.
				$patched['custom'] = $patched['custom'] ?? ['date' => []] ;
				$patched['custom']['date'] = $patched['custom']['date'] ?? [];
				$custom_date = $patched['custom']['date']['date'] ?? null;
				$patched['custom']['date']['date'] = $custom_date ?: $dtstart->format( Dates::DBDATEFORMAT );
				break;
			default:
				$patched['type'] = 'Custom';
				$patched['custom']['type'] = $type;
				break;
		}

		$patched = $this->normalize_rule_end( $patched );

		return $patched;
	}

	/**
	 * Normalizes a rule end type and end information.
	 *
	 * Note: when the rule end type is `After` or `On` and the information found is not coherent,
	 * then the rule end type will be set to `Never`.
	 *
	 * @since 6.0.1
	 *
	 * @param array<string,mixed> $patched The rule to normalize, in the format used by the `_EventRecurrence` meta.
	 *
	 * @return array<string,mixed> The normalized rule.
	 */
	private function normalize_rule_end( array $patched ): array {
		$end_type = $patched['end-type'] ?? 'Never';

		switch ( $end_type ) {
			case 'Never':
				unset( $patched['end-count'], $patched['end'] );
				break;
			case 'After':
				unset( $patched['end'] );
				$patched['end-count'] = $patched['end-count'] ?? null;

				if ( $patched['end-count'] === null ) {
					// Since the count is empty, this rule will never end.
					$patched['end-type'] = 'Never';

					return $this->normalize_rule_end( $patched );
				}

				// Remove any non-numeric characters and cast to integer.
				$patched['end-count'] = (int) ( preg_replace( '/\D/', '', $patched['end-count'] ) );
				break;
			case 'On':
				unset( $patched['end-count'] );
				$patched['end'] = $patched['end'] ?? null;

				if ( $patched['end'] === null || ! Dates::is_valid_date( $patched['end'] ) ) {
					// The end date is not valid, this rule will never end.
					$patched['end-type'] = 'Never';

					return $this->normalize_rule_end( $patched );
				}

				// Make sure the end date is in the expected format.
				$patched['end'] = Dates::immutable( $patched['end'] )->format( Dates::DBDATEFORMAT );
				break;
		}

		return $patched;
	}
}