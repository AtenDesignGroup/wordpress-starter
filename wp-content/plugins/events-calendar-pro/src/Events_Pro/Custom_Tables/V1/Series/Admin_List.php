<?php
/**
 * Class in charge of customizing the admin list of the series post type.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Series
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Series;


use TEC\Events\Custom_Tables\V1\Tables\Events;
use TEC\Events\Custom_Tables\V1\Tables\Events as Events_Schema;
use TEC\Events_Pro\Custom_Tables\V1\Models\Series_Relationship;
use TEC\Events_Pro\Custom_Tables\V1\Repository\Events as EventsRepo;
use TEC\Events_Pro\Custom_Tables\V1\Series\Post_Type as Series;
use TEC\Events_Pro\Custom_Tables\V1\Tables\Series_Relationships;
use Tribe__Date_Utils as Dates;
use WP_Query;

/**
 * Class Admin_List
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Series
 */
class Admin_List {
	/**
	 * Add custom columns for the Series post type.
	 *
	 * @since 6.0.0
	 *
	 * @param array<string, string> $columns An array with the available columns.
	 *
	 * @return array The filtered columns.
	 */
	public function include_custom_columns( $columns = [] ) {
		if ( ! is_array( $columns ) ) {
			return $columns;
		}

		$columns['start_date']  = __( 'Start Date', 'tribe-events-calendar-pro' );
		$columns['events'] = __( 'Events', 'tribe-events-calendar-pro' );
		// Remove the post type publish date.
		unset( $columns['date'] );

		return $columns;
	}

	/**
	 * Add the sortable columns for the Series List.
	 *
	 * @since 6.0.0
	 *
	 * @param array<string> $columns An array with the available columns.
	 *
	 * @return array The filtered columns.
	 */
	public function include_sortable_columns( $columns = [] ) {
		if ( ! is_array( $columns ) ) {
			return $columns;
		}

		$columns['start_date'] = [ 'start_date', 'desc' ];

		return $columns;
	}

	/**
	 * Render the custom columns of the Series type.
	 *
	 * @since 6.0.0
	 *
	 * @param string $column  The ID name of the column.
	 * @param int    $post_id The ID of the Series Post
	 */
	public function custom_column( string $column, int $post_id ) {
		switch ( $column ) {
			case 'start_date':
				$relationship = $this->get_start_date_relationship( $post_id );

				if ( $relationship !== null ) {
					$start_date = Dates::immutable( $relationship->start_date, $relationship->timezone );
					$format     = tribe_get_date_format( true );
					echo esc_html( $start_date->format( $format ) );

					return;
				}

				_ex(
					'-',
					'Placeholder used in the Series list Start Date column when a Series has no Occurrences.',
					'tribe-events-calendar-pro'
				);

				break;
			case 'events':
				$occurrences_count = $this->get_occurrence_count( $post_id );
				$recurring_events_count = $this->get_recurring_events_count( $post_id );
				$escaped_occurrences_count      = esc_attr( $occurrences_count );
				$escaped_recurring_events_count = esc_attr( $recurring_events_count );

				echo "<span id=\"series-occurrences-count-{$post_id}\" " .
				     "data-occurrences-count=\"{$escaped_occurrences_count}\" " .
				     "data-recurring-events-count=\"{$escaped_recurring_events_count}\" " .
				     ">" .
				     $escaped_occurrences_count .
				     "</span>";
				break;
		}
	}

	/**
	 * Fetches, from the database, the number of Recurring Events related to a Series post ID.
	 *
	 * @since 6.0.0
	 *
	 * @param int $post_id The Series post ID.
	 *
	 * @return int The number of Events related to the Series.
	 */
	private function get_recurring_events_count( int $post_id ): int {
		global $wpdb;
		$events                 = Events::table_name( true );
		$series_relationships   = Series_Relationships::table_name( true );
		$recurring_events_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT( $events.event_id )
					FROM $events
				JOIN $series_relationships
					ON $series_relationships.event_id = $events.event_id
					AND $series_relationships.series_post_id = %d
				JOIN {$wpdb->posts}
				    ON {$wpdb->posts}.ID = {$events}.post_id
				WHERE $events.rset != '' AND $events.rset IS NOT NULL AND {$wpdb->posts}.post_status != 'trash'",
				$post_id
			)
		);

		return (int) $recurring_events_count;
	}

	/**
	 * Returns the number of Occurrences related to a Series by means of the containing Event related to
	 * the Series.
	 *
	 * @since 6.0.0
	 *
	 * @param int $post_id The Series post ID.
	 *
	 * @return int The number of Occurrences related to a Series.
	 */
	protected function get_occurrence_count( int $post_id ): int {
		return tribe( EventsRepo::class )->get_occurrence_count_for_series( $post_id );
	}

	/**
	 * Returns a reference to the first Occurrence model, in start date order, related to the Series.
	 *
	 * @since 6.0.0
	 *
	 * @param int $post_id The post ID of the Series to find the first Occurrence for.
	 *
	 * @return Series_Relationship|null A reference to the Series Relationship for the Event or `null` if not found;
	 *                                  note the Model instance will include the first related Event date fields.
	 */
	protected function get_start_date_relationship( int $post_id ): ?Series_Relationship {
		/** @var Series_Relationship $series_relationship */
		$series_relationship = Series_Relationship::where( 'series_post_id', $post_id )
			->join( Events::table_name( true ), 'event_id', 'event_id' )
			->order_by( 'start_date' )
			->first();

		return $series_relationship;
	}

	/**
	 * Filters the primary admin Series list view query clauses.
	 *
	 * @since 6.0.0
	 *
	 * @param array<string,string> $clauses
	 * @param WP_Query             $query
	 *
	 * @return array<string,string>
	 */
	public function filter_series_rows_clauses( array $clauses, WP_Query $query ): array {
		global $wpdb;
		// Are we on the Series list view?
		$on_series_page = is_admin()
		                  && $query->is_main_query()
		                  && 'edit-' . Series::POSTTYPE === get_current_screen()->id;

		if ( ! $on_series_page ) {
			return $clauses;
		}

		$order = 'DESC';

		// normalize the value.
		$order_key = strtolower( $query->get( 'order', $order ) );

		// Use a map to validate.
		$order_map = [
			'desc' => 'DESC',
			'asc'  => 'ASC',
		];

		// Prevent un-mapped values.
		if ( isset( $order_map[ $order_key ] ) ) {
			$order = $order_map[ $order_key ];
		}

		// Add our join + order by.
		$series_table       = Series_Relationships::table_name();
		$events_table       = Events_Schema::table_name();
		$clauses['join']   .= "
						LEFT JOIN
					        $series_table ON {$wpdb->posts}.ID = $series_table.series_post_id
					    LEFT JOIN
					        $events_table ON $events_table.event_id = $series_table.event_id";
		$clauses['orderby'] = "$events_table.start_date_utc {$order}";
		$clauses['groupby'] = "{$wpdb->posts}.ID";

		return $clauses;
	}
}
