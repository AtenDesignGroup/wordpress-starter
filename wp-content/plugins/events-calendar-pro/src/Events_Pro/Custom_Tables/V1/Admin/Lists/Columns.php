<?php
/**
 * Handles the filtering of the Event edit screen to fetch data from the Custom Tables v1 implementation.
 *
 * @since   6.0.11
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Admin\Lists;
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Admin\Lists;

use TEC\Events\Custom_Tables\V1\Models\Event;
use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use Tribe__Events__Main as TEC;
use Tribe__Date_Utils as Dates;
use WP_Screen;

/**
 * Class Columns.
 *
 * @since   6.0.11
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Admin\Lists;
 */
class Columns {
	/**
	 * Adds the Series columns to the Event admin list.
	 *
	 * @since 6.0.0
	 *
	 * @param array<string,string> $columns A list of the columns that will be shown to the user for
	 *                                      the Event post type as produced by WordPress or previous
	 *                                      filters.
	 *
	 * @return array<string,string> The filtered map of columns for the Event post type.
	 */
	public function filter_events_post_columns( array $columns ): array {
		$columns['series'] = __( 'Series', 'tribe-events-calendar-pro' );

		return $columns;
	}

	/**
	 * Create a sprite of symbols to resize the SVG using a viewBox property. The sprite is rendered before the full
	 * events table.
	 *
	 * @since 6.0.0
	 */
	public function render_recurrence_svg(): void {
		// The function might not exist in the context of the Customizer, not at this stage.
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

		if ( ! $screen instanceof WP_Screen ) {
			return;
		}

		if ( TEC::POSTTYPE !== $screen->post_type ) {
			return;
		}

		if ( $screen->base !== 'edit' ) {
			return;
		}

		?>
		<svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
			<symbol id="recurring" viewBox="0 0 20 20">
				<path
						d="M13.333 3.826c0 .065 0 .13-.02.174 0 .022-.02.065-.02.087a.9.9 0 0 1-.197.37L10.45 7.37a.797.797 0 0 1-.592.26.797.797 0 0 1-.593-.26c-.316-.348-.316-.935 0-1.305l1.225-1.348H6.3C3.753 4.717 1.66 7 1.66 9.827c0 1.369.474 2.651 1.363 3.608.316.348.316.935 0 1.304A.797.797 0 0 1 2.43 15a.797.797 0 0 1-.593-.26C.652 13.434 0 11.695 0 9.847c0-3.826 2.825-6.935 6.301-6.935h4.208L9.284 1.565c-.316-.348-.316-.935 0-1.304.316-.348.85-.348 1.185 0l2.647 2.913c.099.109.158.239.198.37 0 .021.02.065.02.086v.196zM20 10.152c0 3.826-2.825 6.935-6.301 6.935H9.49l1.225 1.348c.336.348.336.935 0 1.304a.797.797 0 0 1-.593.261.83.83 0 0 1-.592-.26l-2.627-2.936a.948.948 0 0 1-.198-.37c0-.021-.02-.064-.02-.086-.02-.065-.02-.109-.02-.174 0-.065 0-.13.02-.174 0-.022.02-.065.02-.087a.9.9 0 0 1 .198-.37L9.55 12.63c.316-.347.849-.347 1.185 0 .336.348.336.935 0 1.305L9.51 15.283h4.208c2.548 0 4.641-2.283 4.641-5.11 0-1.369-.474-2.651-1.362-3.608a.97.97 0 0 1 0-1.304c.316-.348.849-.348 1.185 0C19.348 6.543 20 8.283 20 10.152z"/>
			</symbol>
		</svg>
		<?php
	}

	/**
	 * Filters the columns used in the Admin UI posts list table to populate and output the data using CT1 information
	 * for Events.
	 *
	 * @since 6.0.0
	 *
	 * @param string $column_name The name of the column to filter.
	 * @param int    $post_id     The ID of the post to filter.
	 *
	 * @return void The filtered column value is echoed, if required.
	 */
	public function filter_events_post_custom_columns( string $column_name, int $post_id ): void {
		switch ( $column_name ) {
			case 'events-cats':
				$this->render_categories_column( $post_id );

				return;
			case 'start-date':
				$this->render_start_date_column( $post_id );

				return;
			case 'end-date':
				$this->render_end_date_column( $post_id );

				return;
			case 'series':
				$this->render_series_column( $post_id );

				return;
		}
	}

	/**
	 * Renders the end date column for an Event.
	 *
	 * @since 6.0.11
	 *
	 * @param int $post_id The ID of the Event post to render the end date for.
	 *
	 * @return void The end_date column value is echoed, if required.
	 */
	private function render_end_date_column( int $post_id ): void {
		$format = tribe_get_date_format( true );
		$event  = Event::find( $post_id, 'post_id' );

		if ( ! $event instanceof Event ) {
			echo tribe_get_display_end_date( $post_id, false, $format );

			return;
		}

		if ( $event->is_infinite() ) {
			echo '—';

			return;
		}

		$count = Occurrence::where( 'event_id', $event->event_id )
						   ->count();
		// If single occurrence and a multi-day, show end date
		if ( $event->is_multiday() && $count === 1 ) {
			$end_date = $event->end_date;

			// Single and recurring event, show the last occurrences start date
		} else {
			$occurrence = Occurrence::where( 'event_id', $event->event_id )
									->order_by( 'start_date', 'DESC' )
									->limit( 1 )
									->first();
			$end_date   = $occurrence->start_date;
		}

		$start_date = Dates::immutable( $end_date, $event->timezone );

		echo esc_html( $start_date->format( $format ) );
	}

	/**
	 * Renders the series column for an Event.
	 *
	 * @since 6.0.11
	 *
	 * @param int $post_id The ID of the Event post to render the series for.
	 *
	 * @return void The series column value is echoed, if required.
	 */
	private function render_series_column( int $post_id ): void {
		$series_map = (array) tribe_cache()[ Caches::SERIES_MAP_KEY ];

		if ( isset( $series_map[ $post_id ] ) ) {
			$admin_url    = add_query_arg( [
					'post'   => $series_map[ $post_id ],
					'action' => 'edit',
			],
					admin_url( 'post.php' )
			);
			$series_title = (string) get_the_title( $series_map[ $post_id ] );

			echo '<a href="' . $admin_url . '">' . $series_title . '</a>';
		}
	}

	/**
	 * Renders the start date column for an Event.
	 *
	 * @since 6.0.11
	 *
	 * @param int $post_id The ID of the Event post to render the start date for.
	 *
	 * @return void The start_date column value is echoed, if required.
	 */
	private function render_start_date_column( int $post_id ): void {
		$format = tribe_get_date_format( true );;
		$event = Event::find( $post_id, 'post_id' );
		if ( $event instanceof Event ) {
			$start_date = Dates::immutable( $event->start_date, $event->timezone );
			$total      = Occurrence::where( 'event_id', $event->event_id )->count();
			$title      = sprintf(
			/* translators: %d the number of total occurrences */
					_n( '%d occurrence', '%d occurrences', $total, 'tribe-events-calendar-pro' ),
					$total
			);

			if ( $event->has_recurrence() ) {
				echo '<div style="display: flex; align-items: start;">';
				echo '<span style="flex-grow: 1;">';
				echo esc_html( $start_date->format( $format ) );
				echo '</span>';
				echo '<svg style="margin-left: 10px; margin-top: 3px;" viewBox="0 0 12 12" width="12" height="12"><title>' . $title . '</title><use xlink:href="#recurring" /></svg>';
				echo '</div>';
			} else {
				echo esc_html( $start_date->format( $format ) );
			}
		} else {
			echo tribe_get_start_date( $post_id, false, $format );
		}
	}

	/**
	 * Renders the categories column for an Event.
	 *
	 * @since 6.0.11
	 *
	 * @param int $post_id The ID of the Event post to render the categories for.
	 *
	 * @return void The categories column value is echoed, if required.
	 */
	private function render_categories_column( int $post_id ): void {
		$event_cats      = wp_get_post_terms(
				$post_id,
				TEC::TAXONOMY,
				[
						'fields' => 'names',
				]
		);
		$categories_list = '-';
		if ( is_array( $event_cats ) ) {
			$event_cats      = array_values( array_filter( $event_cats, static function ( $cat ) {
				return is_string( $cat ) && $cat !== '';
			} ) );
			$categories_list = implode( ', ', $event_cats );
		}
		echo esc_html( $categories_list );
	}
}
