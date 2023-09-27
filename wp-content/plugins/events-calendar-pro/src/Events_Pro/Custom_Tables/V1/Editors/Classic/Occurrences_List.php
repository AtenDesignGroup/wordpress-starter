<?php
/**
 * Overwrite the WP_List_Table to render the list of events of a series.
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Editors\Classic;

use DateTime;
use Generator;
use TEC\Events\Custom_Tables\V1\Migration\String_Dictionary as Strings;
use TEC\Events\Custom_Tables\V1\Models\Event;
use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use TEC\Events_Pro\Custom_Tables\V1\Events\Provisional\ID_Generator as Provisional_ID_Generator;
use TEC\Events_Pro\Custom_Tables\V1\Migration\String_Dictionary;
use TEC\Events_Pro\Custom_Tables\V1\Tables\Events;
use TEC\Events_Pro\Custom_Tables\V1\Tables\Series_Relationships;
use TEC\Events_Pro\Custom_Tables\V1\Updates\Post_Actions;
use Tribe__Date_Utils as Dates;
use Tribe__Timezones as Timezones;
use WP_List_Table;
use WP_Post;

/**
 * Class Occurrences_List
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Editors\Classic
 */
class Occurrences_List extends WP_List_Table {
	/**
	 * The number of Recurring Events currently related to the Series post the table is being displayed for.
	 *
	 * @since 6.0.2.1
	 *
	 * @var int
	 */
	private $recurring_events_count = 0;

	/**
	 * An instance to the Post object where the items are rendered.
	 *
	 * @since 6.0.0
	 *
	 * @var WP_Post|null series_post
	 */
	private $series_post;

	/**
	 * Occurrences_List constructor.
	 *
	 * @since 6.0.0
	 *
	 * @param WP_Post|null         $series_post The instance of the series post.
	 */
	public function __construct( WP_Post $series_post = null ) {
		$this->series_post = $series_post;

		parent::__construct( [
				'singular' => 'series',
				'plural'   => 'series',
				'ajax'     => false,
		] );

		$this->items = [];
	}

	/**
	 * Text that is displayed when no events is associated with this event.
	 *
	 * @since 6.0.0
	 */
	public function no_items() {
		_e( 'No events are associated with this Series yet.', 'tribe-events-calendar-pro' );
	}

	/**
	 * Prepare and query the values against the DB.
	 *
	 * @since 6.0.0
	 */
	public function prepare_items() {
		global $wpdb;
		$this->_column_headers = [ $this->get_columns(), [], $this->get_sortable_columns() ];

		if ( $this->series_post === null ) {
			return;
		}

		/**
		 * Allows filtering the number of Occurrences that will show up in the Series post
		 * edit page.
		 *
		 * @since 6.0.0
		 *
		 * @param int $items_per_page The number of Occurrence to show, per page.
		 * @param int $series_id      The post ID of the current Series post.
		 */
		$items_per_page = apply_filters( 'tec_events_pro_custom_tables_v1_series_occurrent_list_metabox_per_page', 20, get_the_ID() );

		$events_table        = tribe( Events::class )->table_schema()::table_name( true );
		$series_events_table = Series_Relationships::table_name( true );
		// Don't fetch the trashed events.
		$query                    = "
			SELECT `{$series_events_table}`.event_post_id
			FROM `{$series_events_table}`
			INNER JOIN `{$events_table}`
				ON `{$series_events_table}`.event_id = `{$events_table}`.event_id
			INNER JOIN `{$wpdb->posts}`
				ON `{$wpdb->posts}`.ID = `{$events_table}`.post_id
			WHERE `{$wpdb->posts}`.post_status != 'trash'";
		$query                    .= $wpdb->prepare( " AND `{$series_events_table}`.`series_post_id` = %s", $this->series_post->ID );
		$all_series_relationships = $wpdb->get_results( $query );

		if ( $all_series_relationships instanceof Generator ) {
			$all_series_relationships = iterator_to_array( $all_series_relationships );
		}

		if ( count( $all_series_relationships ) !== 0 ) {
			$related_event_ids = wp_list_pluck( $all_series_relationships, 'event_post_id' );
			// Now's a good time to calculate this value.
			$query = "
			SELECT COUNT(*)
			FROM `{$events_table}`
			INNER JOIN `{$wpdb->posts}`
				ON `{$wpdb->posts}`.ID = `{$events_table}`.post_id
			WHERE `{$events_table}`.rset IS NOT NULL
				AND `{$events_table}`.rset != ''

				AND `{$events_table}`.post_id IN (" . implode( ",", $related_event_ids ) . ")";
			$this->recurring_events_count = $wpdb->get_var( $query );

			$builder           = Occurrence::where_in( 'post_id', $related_event_ids );
			$count_builder     = Occurrence::where_in( 'post_id', $related_event_ids );

			if ( tribe_get_request_var( 'view', 'upcoming' ) === 'upcoming' ) {
				$timezone = Timezones::build_timezone_object();
				$today    = new DateTime( 'now', $timezone );
				$builder->where( 'start_date', '>=', $today );
				$count_builder->where( 'start_date', '>=', $today );
			}

			$this->items = $builder->order_by( 'start_date_utc', 'ASC' )
								   ->limit( $items_per_page )
								   ->offset( ( $this->get_pagenum() - 1 ) * $items_per_page )
								   ->get();

			$total_items = $count_builder->count();
		} else {
			$this->items = [];
			$total_items = 0;
		}

		$this->set_pagination_args( [
			'total_items' => $total_items,
			'total_pages' => (int) ceil( $total_items / $items_per_page ),
			'per_page'    => $items_per_page,
		] );
	}

	/**
	 * Render the available columns for the event list view.
	 *
	 * @since 6.0.0
	 * @return array<string, string> An array with the columns ID names and display name.
	 */
	public function get_columns(): array {
		return [
			'title'      => __( 'Title', 'tribe-events-calendar-pro' ),
			'start_date' => __( 'Start Date', 'tribe-events-calendar-pro' ),
			'actions'    => __( 'Actions', 'tribe-events-calendar-pro' ),
		];
	}

	/**
	 * Display all the rows associated with this series.
	 *
	 * @since 6.0.0
	 */
	public function display_rows() {
		/** @var Occurrence $occurrence */
		foreach ( $this->items as $occurrence ) {
			echo '<tr>';

			//Get the columns registered in the get_columns and get_sortable_columns methods
			list( $columns, $hidden ) = $this->get_column_info();

			foreach ( $columns as $column_name => $display_name ) {
				echo '<td>';
				//Display the cell
				switch ( $column_name ) {
					case 'title':
						$this->print_title( $occurrence );
						break;
					case 'start_date':
						$this->print_start_date_entry( $occurrence );

						break;
					case 'actions':
						$this->print_actions_entry( $occurrence );

						break;
				}
				echo '</td>';
			}
			echo '</tr>';
		}
	}

	/**
	 * Overwrite the table nav to remove the nonce generated of the table and make sure does not impact the
	 * editor nonce of the post editor.
	 *
	 * @since 6.0.0
	 *
	 * @param string $which Which section.
	 */
	protected function display_tablenav( $which ) {
		if ( $which === 'top'):
			// Render the SVG symbols with the recurring icon at the top of the results.
			?>
			<svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
				<symbol id="recurring" viewBox="0 0 20 20">
					<path d="M13.333 3.826c0 .065 0 .13-.02.174 0 .022-.02.065-.02.087a.9.9 0 0 1-.197.37L10.45 7.37a.797.797 0 0 1-.592.26.797.797 0 0 1-.593-.26c-.316-.348-.316-.935 0-1.305l1.225-1.348H6.3C3.753 4.717 1.66 7 1.66 9.827c0 1.369.474 2.651 1.363 3.608.316.348.316.935 0 1.304A.797.797 0 0 1 2.43 15a.797.797 0 0 1-.593-.26C.652 13.434 0 11.695 0 9.847c0-3.826 2.825-6.935 6.301-6.935h4.208L9.284 1.565c-.316-.348-.316-.935 0-1.304.316-.348.85-.348 1.185 0l2.647 2.913c.099.109.158.239.198.37 0 .021.02.065.02.086v.196zM20 10.152c0 3.826-2.825 6.935-6.301 6.935H9.49l1.225 1.348c.336.348.336.935 0 1.304a.797.797 0 0 1-.593.261.83.83 0 0 1-.592-.26l-2.627-2.936a.948.948 0 0 1-.198-.37c0-.021-.02-.064-.02-.086-.02-.065-.02-.109-.02-.174 0-.065 0-.13.02-.174 0-.022.02-.065.02-.087a.9.9 0 0 1 .198-.37L9.55 12.63c.316-.347.849-.347 1.185 0 .336.348.336.935 0 1.305L9.51 15.283h4.208c2.548 0 4.641-2.283 4.641-5.11 0-1.369-.474-2.651-1.362-3.608a.97.97 0 0 1 0-1.304c.316-.348.849-.348 1.185 0C19.348 6.543 20 8.283 20 10.152z"/>
				</symbol>
			</svg>
		<?php endif; ?>
		<div class="tablenav <?php echo esc_attr( $which ); ?>">

			<?php if ( $this->has_items() ) : ?>
				<div class="alignleft actions bulkactions">
					<?php $this->bulk_actions( $which ); ?>
				</div>
			<?php
			endif;
			$this->extra_tablenav( $which );
			$this->pagination( $which );
			?>

			<br class="clear"/>
		</div>
		<?php
	}

	/**
	 * Add custom links to control the views.
	 *
	 * @since 6.0.0
	 * @return array<string, string> An array to control the views that are displayed above the list of events.
	 */
	protected function extra_tablenav( $which ): void {
		$current = tribe_get_request_var( 'view', 'upcoming' );
		?>
		<input
			type="hidden"
			id="series-occurrences-count-<?php echo esc_attr( get_the_ID() ); ?>"
			data-recurring-events-count="<?php echo esc_attr( $this->recurring_events_count ); ?>"
		>
		<div style="float: left">
			<?php if ( $current === 'upcoming') : ?>
				<?php esc_html_e( 'Upcoming', 'tribe-events-calendar-pro' ); ?>
			<?php else: ?>
				<a href="<?php echo esc_url( add_query_arg( 'view', 'upcoming' ) ); ?>">
					<?php esc_html_e( 'Upcoming', 'tribe-events-calendar-pro' ); ?>
				</a>
			<?php endif; ?>
			|
			<?php if ( $current === 'all') : ?>
				<?php esc_html_e( 'All', 'tribe-events-calendar-pro' ); ?>
			<?php else: ?>
				<a href="<?php echo esc_url(  add_query_arg( 'view', 'all' ) ); ?>">
					<?php esc_html_e( 'All', 'tribe-events-calendar-pro' ); ?>
				</a>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Print the content of the actions column.
	 *
	 * @since 6.0.1
	 *
	 * @param Occurrence $occurrence The occurrence object.
	 *
	 * @return void Prints the content of the actions column.
	 */
	private function print_actions_entry( Occurrence $occurrence ): void {
		$occurrence_post_id = tribe( Provisional_ID_Generator::class )->current() + $occurrence->occurrence_id;

		$actions = [
				'edit' => sprintf( '<a href="%s" target="_blank" rel="noreferrer noopener">%s</a>', get_edit_post_link( $occurrence_post_id ), 'Edit' ),
				'view' => sprintf( '<a href="%s" target="_blank" rel="noreferrer noopener">%s</a>', get_permalink( $occurrence_post_id ), 'View' ),
		];

		$actions = array_merge( $actions, tribe( Post_Actions::class )->get_occurrence_action_links( $occurrence ) );

		/**
		 * Allows filtering of the actions available for an Occurrence in the list view.
		 *
		 * @since 6.0.1
		 *
		 * @param array<string, string> $actions    An array of actions with the action name as the key and the action link as the value.
		 * @param Occurrence            $occurrence A reference to the Occurrence object.
		 */
		$actions = apply_filters( 'tec_events_pro_custom_tables_v1_occurrence_list_actions', $actions, $occurrence );

		echo wp_kses( implode( ' - ', $actions ), [
				'a' => [
						'class'      => [],
						'data-*'     => true,
						'href'       => [],
						'target'     => [],
						'rel'        => [],
						'noreferrer' => [],
						'noopener'   => [],
				],
		] );
	}

	/**
	 * Print the content of the start date column.
	 *
	 * @since 6.0.1
	 *
	 * @param Occurrence $occurrence The occurrence object.
	 *
	 * @return void Prints the content of the start date column.
	 */
	private function print_start_date_entry( Occurrence $occurrence ): void {
		$event = Event::where( 'event_id', $occurrence->event_id )->first();
		$timezone = $event instanceof Event ? $event->timezone : 'UTC';
		$format = tribe_get_date_format( true );
		echo Dates::immutable( $occurrence->start_date, $timezone )->format( $format );

		if ( $occurrence->has_recurrence ) {
			echo '<svg style="margin-left: 10px;" viewBox="0 0 12 12" width="12" height="12"><title>' . __( 'Recurring', 'tribe-events-calendar-pro' ) . '</title><use xlink:href="#recurring" /></svg>';
		}
	}


	/**
	 * Print the content of the title column.
	 *
	 * @since 6.0.1
	 *
	 * @param Occurrence $occurrence The occurrence object.
	 *
	 * @return void Prints the content of the title column.
	 */
	private function print_title( Occurrence $occurrence ): void {
		$post = get_post( $occurrence->post_id );
		if ( $post instanceof WP_Post ) {
			echo esc_html( _draft_or_post_title( $post ) );
			_post_states( $post );
		}
	}
}
