<?php
/**
 * Manages the integration of the Custom Tables v1 implementation with Advanced Posts Manager.
 *
 * Following along the set of `Tribe__Events__Pro__APM_Filters__` classes, the integration with
 * APM is managed on this plugin.
 *
 * This class should be split into smaller and more specialized classes if it grows too big.
 *
 * @since   6.0.11
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Integrations\APM;
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Integrations\APM;

use TEC\Events_Pro\Custom_Tables\V1\Series\Post_Type as Series_Post_Type;
use TEC\Common\Contracts\Service_Provider;

/**
 * Class APM_Integration.
 *
 * @since   6.0.11
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Integrations\APM;
 */
class APM_Integration extends Service_Provider {

	/**
	 * Registers the integration with Advanced Posts Manager.
	 *
	 * @since 6.0.11
	 *
	 * @return void
	 */
	public function register() {
		$this->container->singleton( __CLASS__, __CLASS__ );

		/*
		 * To keep overhead at a minimum, the simple logic required is implemented directly in this provider.
		 * Should the logic grow too much, it should be moved to one or more dedicated classes.
		 */
		add_filter( 'tribe_events_pro_apm_filters_fallback_columns', [ $this, 'filter_fallback_columns' ] );
		add_filter( 'tribe_apm_column_headers', [ $this, 'filter_column_headers' ] );
	}

	/**
	 * Filters the Advanced Posts Manager fallback columns to replace the recurring Columns with the Series column.
	 *
	 * @since 6.0.11
	 *
	 * @param array<string> $fallback_columns The fallback columns.
	 *
	 * @return array<string> The filtered fallback columns.
	 */
	public function filter_fallback_columns( array $fallback_columns ): array {
		$recurring_pos = array_search( 'recurring', $fallback_columns, true );

		if ( $recurring_pos !== false ) {
			$fallback_columns[ $recurring_pos ] = 'series';
		} else {
			$fallback_columns[] = 'series';
		}

		return $fallback_columns;
	}

	/**
	 * Filters the Advanced Posts Manager column headers to add the Series column.
	 *
	 * @since 6.0.11
	 *
	 * @param array<string> $column_headers The column headers.
	 *
	 * @return array<string> The filtered column headers.
	 */
	public function filter_column_headers( array $column_headers ): array {
		$label_singular           = tribe( Series_Post_Type::class )->get_label_singular();
		$column_headers['series'] = $label_singular;

		return $column_headers;
	}
}
