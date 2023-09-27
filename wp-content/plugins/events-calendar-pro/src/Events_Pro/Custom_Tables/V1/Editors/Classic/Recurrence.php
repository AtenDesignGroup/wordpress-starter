<?php
/**
 * Class to handle recurrence for the classic editor.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Editors\Classic
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Editors\Classic;

/**
 * Class Recurrence
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Editors\Classic
 */
class Recurrence {

	/**
	 * Add recurrence rule type dropdown to the end of rule type row.
	 *
	 * @since 6.0.0
	 *
	 * @param string $template Recurrence rule type after markup.
	 *
	 * @return string Updated recurrence rule type after markup.
	 */
	public function add_recurrence_rule_type_dropdown( $template ) {
		ob_start();
		include __DIR__ . '/partials/recurrence-rule-type-dropdown.php';
		return ob_get_clean();
	}

	/**
	 * Filter the recurrence admin template strings.
	 *
	 * @since 6.0.0
	 *
	 * @param array<string,string> $strings Recurrence admin template strings.
	 *
	 * @return array<string,string> Updated recurrence admin template strings.
	 */
	public function filter_recurrence_admin_template_strings( $strings ) {
		$strings[ 'time-recurrence-start' ]               = _x( 'From', 'Begins the line indicating when a recurrence time starts', 'tribe-events-calendar-pro' );
		$strings[ 'time-recurrence-time-date-separator' ] = _x( 'on the', 'custom recurrence time/date separator', 'tribe-events-calendar-pro' );

		return $strings;
	}

	/**
	 * Add recurrence not supported with tickets message after add recurrence button.
	 *
	 * @since 6.0.0
	 *
	 * @param string $template Add recurrence button after markup.
	 *
	 * @return string Updated Add recurrence button after markup.
	 */
	public function add_recurrence_not_supported_with_tickets_message( $template ) {
		if ( ! class_exists( 'Tribe__Tickets__Main' ) ) {
			return '';
		}

		include __DIR__ . '/partials/recurrence-not-supported-with-tickets.php';
	}

	/**
	 * Add recurrence week days overlay to the end of week days row.
	 *
	 * @since 6.0.0
	 *
	 * @param string $template Recurrence week days after markup.
	 *
	 * @return string Updated recurrence week days after markup.
	 */
	public function add_recurrence_week_days_overlay( $template ) {
		ob_start();
		include __DIR__ . '/partials/recurrence-week-days-overlay.php';
		return ob_get_clean();
	}

	/**
	 * Add label to the start of custom recurrence months row.
	 *
	 * @since 6.0.0
	 *
	 * @param string $template Custom recurrence months before markup.
	 *
	 * @return string Updated custom recurrence months before markup.
	 */
	public function add_custom_recurrence_months_before_label( $template ) {
		ob_start();
		include __DIR__ . '/partials/custom-recurrence-months-before-label.php';
		return ob_get_clean();
	}

	/**
	 * Add recurrence month on the dropdown to the end of custom recurrence months row.
	 *
	 * @param string $template Recurrence month on the after markup.
	 *
	 * @return string Updated recurrence month on the after markup.
	 */
	public function add_recurrence_month_on_the_dropdown( $template ) {
		ob_start();
		include __DIR__ . '/partials/recurrence-month-on-the-dropdown.php';
		return ob_get_clean();
	}

	/**
	 * Add label to the start of year same day select row.
	 *
	 * @param string $template Year same day select before markup.
	 *
	 * @return string Updated year same day select before markup.
	 */
	public function add_year_same_day_select_before_label( $template ) {
		ob_start();
		include __DIR__ . '/partials/year-same-day-select-before-label.php';
		return ob_get_clean();
	}

	/**
	 * Add year not same day dropdown to the end of year not same day row.
	 *
	 * @since 6.0.0
	 *
	 * @param string $template Year not same day after markup.
	 *
	 * @return string Updated year not same day after markup.
	 */
	public function add_year_not_same_day_after_dropdown( $template ) {
		ob_start();
		include __DIR__ . '/partials/year-not-same-day-after-dropdown.php';
		return ob_get_clean();
	}

	/**
	 * Add exclusion rule type dropdown to the end of rule type row.
	 *
	 * @since 6.0.0
	 *
	 * @param string $template Exclusion rule type after markup.
	 *
	 * @return string Updated exclusion rule type after markup.
	 */
	public function add_exclusion_rule_type_dropdown( $template ) {
		ob_start();
		include __DIR__ . '/partials/exclusion-rule-type-dropdown.php';
		return ob_get_clean();
	}

	/**
	 * Will filter the monthly exclusion template to replace the occurrence template for the classic editor.
	 *
	 * @since 6.0.0
	 *
	 * @param string $path
	 * @param string $rule_type
	 *
	 * @return string
	 */
	public function filter_monthly_exclusions_template( string $path, string $rule_type ) {
		if ( $rule_type !== 'exclusions' ) {
			return $path;
		}

		return __DIR__ . '/partials/months.php';
	}

	/**
	 * Will filter the yearly exclusion template to replace the occurrence template for the classic editor.
	 *
	 * @since 6.0.0
	 *
	 * @param string $path
	 * @param string $rule_type
	 *
	 * @return string
	 */
	public function filter_yearly_exclusions_template( string $path, string $rule_type ) {
		if ( $rule_type !== 'exclusions' ) {
			return $path;
		}

		return __DIR__ . "/partials/years.php";
	}
}
