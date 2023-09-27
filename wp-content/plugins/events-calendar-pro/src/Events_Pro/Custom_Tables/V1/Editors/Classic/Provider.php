<?php
/**
 * Handles the registration of modifications done to the Classic Editor UI.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Editors\Classic
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Editors\Classic;


use TEC\Events_Pro\Custom_Tables\V1\Duplicate\Duplicate;
use TEC\Events_Pro\Custom_Tables\V1\Editors\Recurrence_Strings;
use TEC\Events_Pro\Custom_Tables\V1\Series\Post_Type as Series;
use TEC\Common\Contracts\Service_Provider;
use Tribe__Events__Main as TEC;
use WP_Post;

/**
 * Class Provider
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Editors\Classic
 */
class Provider extends Service_Provider {


	/**
	 * Registers the implementations, hooks and filters required to alter the Classic Editor UI flow.
	 *
	 * @since 6.0.0
	 */
	public function register() {
		$this->container->singleton( static::class, $this );
		$this->container->singleton( 'tec.custom-tables-v1.editors.classic.provider', $this );

		/*
		 * We register the metaboxes in any editor context, Classic or Blocks Editor.
		 * This is done to leverage current support the Blocks Editor provide for metaboxes.
		 */
		add_action( 'admin_menu', [ $this, 'register_metaboxes' ] );

		// Remove our post type from the list of linked posts that will render a default metabox in the admin UI.
		add_filter( 'tribe_events_linked_posts_should_render_meta_box', [
			$this,
			'remove_series_from_linked_metaboxes'
		] );

		// Duplicate Event hooks, always run.
		$this->duplicate_hooks();

		if ( $this->using_blocks_editor() || TEC::POSTTYPE === ! get_post_type() ) {
			return;
		}

		add_filter( 'tribe_events_pro_recurrence_recurrence_strings', [ $this, 'filter_recurrence_recurrence_strings' ] );
		add_filter( 'tribe_events_pro_recurrence_strings', [ $this, 'filter_recurrence_strings' ] );

		/**
		 * Recurrence rule filters.
		 */
		add_filter(
			'tribe_events_pro_recurrence_template_rule_type_buttons_after',
			[ $this, 'filter_recurrence_rule_type_buttons_after' ],
			10,
			1
		);
		add_filter(
			'tribe_events_pro_recurrence_admin_template_strings',
			[ $this, 'filter_recurrence_admin_template_strings' ],
			10,
			1
		);
		add_action(
			'tribe_events_date_display',
			[ $this, 'filter_recurrence_template_add_recurrence_button_after' ],
			15,
			1
		);
		add_filter(
			'tribe_events_pro_recurrence_template_recurrence_week_days_after',
			[ $this, 'filter_recurrence_week_days_after' ],
			10,
			1
		);
		add_filter(
			'tribe_events_pro_recurrence_template_custom_recurrence_months_before',
			[ $this, 'filter_custom_recurrence_months_before' ],
			10,
			1
		);
		add_filter(
			'tribe_events_pro_recurrence_template_recurrence_month_on_the_after',
			[ $this, 'filter_recurrence_month_on_the_after' ],
			10,
			1
		);
		add_filter(
			'tribe_events_pro_recurrence_template_year_same_day_select_before',
			[ $this, 'filter_year_same_day_select_before' ],
			10,
			1
		);
		add_filter(
			'tribe_events_pro_recurrence_template_year_not_same_day_after',
			[ $this, 'filter_year_not_same_day_after' ],
			10,
			1
		);

		/**
		 * Exclusion rule filters.
		 */
		add_filter(
			'tribe_events_pro_exclusion_template_rule_type_buttons_after',
			[ $this, 'filter_exclusion_rule_type_buttons_after' ],
			10,
			1
		);
		add_filter( 'tribe_pro_recurrence_template_months', [ $this, 'filter_monthly_exclusions_template' ], 10, 2 );
		add_filter( 'tribe_pro_recurrence_template_years', [ $this, 'filter_yearly_exclusions_template' ], 10, 2 );

		add_action( 'tec_events_pro_output_before_rules_ui', [ $this, 'print_locked_ui_notice' ] );
	}

	/**
	 * Remove our series from the list of linked post types that will automatically render a metabox in the default
	 * location. We want to control this metabox rendering separately.
	 *
	 * @since 6.0.0
	 *
	 * @param array<string,mixed> $linked_posttypes The list of linked post types to filter.
	 *
	 * @return array<string,mixed> The filtered list of linked post types.
	 */
	public function remove_series_from_linked_metaboxes( $linked_posttypes ) {
		return $this->container->make( Series_Metaboxes::class )->remove_series_from_linked_metaboxes( $linked_posttypes );
	}

	/**
	 * Setup duplicate event hooks.
	 *
	 * @since 6.0.0
	 */
	public function duplicate_hooks() {
		add_action( 'post_submitbox_start', [ $this, 'add_duplicate_link' ] );
		add_filter( 'post_row_actions', [ $this, 'add_admin_list_duplicate_link' ], 10, 2 );
		add_action( 'admin_action_tec_events_pro_duplicate_event', [ $this, 'handle_duplicate_request' ] );

		add_action( 'tec_events_pro_custom_tables_v1_after_duplicate_event', [ $this, 'save_taxonomies' ], 10, 2 );
		add_action( 'tec_events_pro_custom_tables_v1_after_duplicate_event', [ $this, 'save_additional_meta' ], 10, 2 );
		add_action( 'tec_events_pro_custom_tables_v1_after_duplicate_event', [ $this, 'save_virtual_meta' ], 10, 2 );
		add_action( 'tec_events_pro_custom_tables_v1_after_duplicate_event', [ $this, 'save_duplicate_marker' ], 10 );

		$post_type = TEC::POSTTYPE;
		add_action( 'tribe_events_update_meta', [ $this, 'update_duplicate_marker' ], 100 );
		add_action( "rest_after_insert_{$post_type}", [ $this, 'update_rest_duplicate_marker' ], 100, 3 );
	}

	/**
	 * Checks whether the current request is using the Blocks Editor or not.
	 *
	 * @since 6.0.0
	 *
	 * @return bool Whether the current request is using the Blocks Editor or not.
	 */
	protected function using_blocks_editor() {
		if ( ! tribe()->isBound( 'events-pro.editor' ) ) {
			return false;
		}

		/** @var Pro_Editor $pro_editor */
		$pro_editor = tribe( 'events-pro.editor' );

		return $pro_editor->should_load_blocks();
	}

	/**
	 * Registers the plugin metaboxes or metaboxes modifications.
	 *
	 * @since 6.0.0
	 */
	public function register_metaboxes() {
		/** @var Series $series */
		$series = tribe( Series::class );

		add_meta_box(
			'tec_series_event_title_display',
			esc_html__( 'Series options', 'tribe-events-calendar-pro' ),
			tribe_callback( Series_Metaboxes::class, 'show_series_title' ),
			Series::POSTTYPE,
			'side',
			'core'
		);

		add_meta_box(
			'tec_event_series_relationship',
			$series->get_label_plural(),
			tribe_callback( Events_Metaboxes::class, 'relationship' ),
			TEC::POSTTYPE,
			'side',
			'default'
		);

		add_meta_box(
			'tec_series_event_relationship',
			esc_html__( 'Add events to Series', 'tribe-events-calendar-pro' ),
			tribe_callback( Series_Metaboxes::class, 'relationship' ),
			Series::POSTTYPE,
			'normal'
		);

		add_meta_box(
			'tec_series_events_list',
			esc_html__( 'Events in this Series', 'tribe-events-calendar-pro' ),
			tribe_callback( Series_Metaboxes::class, 'events_list' ),
			Series::POSTTYPE,
			'normal'
		);
	}

	/**
	 * Filter the recurrence strings for recurrence used in the Classic Editor.
	 * Recurrence strings have keys "date", "recurrence", and "exclusion", this only
	 * updates the value of the key "recurrence".
	 *
	 * @since 6.0.0
	 *
	 * @param array<string,string> $strings Strings to be updated.
	 *
	 * @return array<string,string> Updated strings.
	 */
	public function filter_recurrence_recurrence_strings( $strings ) {
		return $this->container->make( Recurrence_Strings::class )->update_recurrence_recurrence_strings( $strings );
	}

	/**
	 * Filter the recurrence strings used in the Classic Editor.
	 *
	 * @since 6.0.0
	 *
	 * @param array<string,array> $strings Strings to be updated.
	 *
	 * @return array<string,array> Updated strings.
	 */
	public function filter_recurrence_strings( $strings ) {
		return $this->container->make( Recurrence_Strings::class )->update_recurrence_strings( $strings );
	}

	/**
	 * Add a duplicate link to single event classic editor.
	 *
	 * @since 6.0.0
	 *
	 * @param WP_Post|null $post The post object that can be duplicated or null.
	 */
	public function add_duplicate_link( $post = null ) {
		return $this->container->make( Duplicate::class )->add_duplicate_link( $post );
	}

	/**
	 * Add a duplicate link to event admin list table actions.
	 *
	 * @since 6.0.0
	 *
	 * @param array<int|string> $actions An array of row action links.
	 * @param WP_Post           $post    The post object that can be duplicated.
	 */
	public function add_admin_list_duplicate_link( $actions, $post ) {
		return $this->container->make( Duplicate::class )->add_admin_list_duplicate_link( $actions, $post );
	}

	/**
	 * Handles the duplicate action for an event.
	 *
	 * @since 6.0.0
	 */
	public function handle_duplicate_request() {
		return $this->container->make( Duplicate::class )->handle_duplicate_request();
	}

	/**
	 * Save the taxonomies for a duplicated event.
	 *
	 * @since 6.0.0
	 *
	 * @param WP_Post $duplicated The duplicated event post object, as decorated by the `tribe_get_event` function.
	 * @param WP_Post $event      The current event post object, as decorated by the `tribe_get_event` function.
	 */
	public function save_taxonomies( $duplicated, $event ) {
		return $this->container->make( Duplicate::class )->save_taxonomies( $duplicated, $event );
	}

	/**
	 * Save the recurrence for the duplicated event.
	 *
	 * @since 6.0.0
	 *
	 * @param WP_Post $duplicated The duplicated event post object, as decorated by the `tribe_get_event` function.
	 * @param WP_Post $event      The current event post object, as decorated by the `tribe_get_event` function.
	 */
	public function save_recurrences( $duplicated, $event ) {
		return $this->container->make( Duplicate::class )->save_recurrences( $duplicated, $event );
	}

	/**
	 * Save the additional meta.
	 *
	 * @since 6.0.0
	 *
	 * @param WP_Post $duplicated The duplicated event post object, as decorated by the `tribe_get_event` function.
	 * @param WP_Post $event      The current event post object, as decorated by the `tribe_get_event` function.
	 */
	public function save_additional_meta( $duplicated, $event ) {
		return $this->container->make( Duplicate::class )->save_additional_meta( $duplicated, $event );
	}

	/**
	 * Save the virtual meta.
	 *
	 * @since 6.0.0
	 *
	 * @param WP_Post $duplicated The duplicated event post object, as decorated by the `tribe_get_event` function.
	 * @param WP_Post $event      The current event post object, as decorated by the `tribe_get_event` function.
	 */
	public function save_virtual_meta( $duplicated, $event ) {
		return $this->container->make( Duplicate::class )->save_virtual_meta( $duplicated, $event );
	}

	/**
	 * Save the duplicate marker to the event.
	 *
	 * @since 6.0.0
	 *
	 * @param WP_Post $duplicated The duplicated event post object, as decorated by the `tribe_get_event` function.
	 */
	public function save_duplicate_marker( $duplicated ) {
		return $this->container->make( Duplicate::class )->save_duplicate_marker( $duplicated );
	}

	/**
	 * Update the Duplicate Marker in classic editor.
	 *
	 * @since 6.0.0
	 *
	 * @param int $event_id The event ID we are modifying meta for.
	 */
	public function update_duplicate_marker( $event_id ) {
		return $this->container->make( Duplicate::class )->update_duplicate_marker( $event_id );
	}

	/**
	 * Update Duplicate Marker in block editor.
	 *
	 * @since 6.0.0
	 *
	 * @param WP_Post         $post     Inserted or updated post object.
	 * @param WP_REST_Request $request  Request object.
	 * @param bool            $creating True when creating a post, false when updating.
	 */
	public function update_rest_duplicate_marker( $post, $request, $creating ) {
		if ( $creating ) {
			return;
		}

		if ( ! $post instanceof WP_Post ) {
			return;
		}

		return $this->container->make( Duplicate::class )->update_duplicate_marker( $post->ID );
	}

	/**
	 * Filter the recurrence rule type buttons after markup used in the classic editor.
	 *
	 * @param string $template Recurrence rule type buttons after markup.
	 *
	 * @return string Updated recurrence rule type buttons after markup.
	 */
	public function filter_recurrence_rule_type_buttons_after( $template ) {
		return $this->container->make( Recurrence::class )->add_recurrence_rule_type_dropdown( $template );
	}

	/**
	 * Filter the recurrence admin template strings used in the classic editor.
	 *
	 * @param array<string,string> $strings Recurrence admin template strings.
	 *
	 * @return array<string,string> Updated recurrence admin template strings.
	 */
	public function filter_recurrence_admin_template_strings( $template ) {
		return $this->container->make( Recurrence::class )->filter_recurrence_admin_template_strings( $template );
	}

	/**
	 * Filter the recurrence add recurrence button after markup used in the classic editor.
	 *
	 * @param array<string,string> $strings Recurrence add recurrence button after markup.
	 *
	 * @return array<string,string> Updated recurrence add recurrence button after markup.
	 */
	public function filter_recurrence_template_add_recurrence_button_after( $template ) {
		return $this->container->make( Recurrence::class )->add_recurrence_not_supported_with_tickets_message( $template );
	}

	/**
	 * Filter the recurrence week days after markup used in the classic editor.
	 *
	 * @param string $template Recurrence week days after markup.
	 *
	 * @return string Updated recurrence week days after markup.
	 */
	public function filter_recurrence_week_days_after( $template ) {
		return $this->container->make( Recurrence::class )->add_recurrence_week_days_overlay( $template );
	}

	/**
	 * Filter the custom recurrence months before markup used in the classic editor.
	 *
	 * @param string $template Custom recurrence months before markup.
	 *
	 * @return string Updated custom recurrence months before markup.
	 */
	public function filter_custom_recurrence_months_before( $template ) {
		return $this->container->make( Recurrence::class )->add_custom_recurrence_months_before_label( $template );
	}

	/**
	 * Filter the recurrence month on the after markup used in the classic editor.
	 *
	 * @param string $template Recurrence month on the after markup.
	 *
	 * @return string Updated recurrence month on the after markup.
	 */
	public function filter_recurrence_month_on_the_after( $template ) {
		return $this->container->make( Recurrence::class )->add_recurrence_month_on_the_dropdown( $template );
	}

	/**
	 * Filter the year same day select before markup used in the classic editor.
	 *
	 * @param string $template Year same day select before markup.
	 *
	 * @return string Updated year same day select before markup.
	 */
	public function filter_year_same_day_select_before( $template ) {
		return $this->container->make( Recurrence::class )->add_year_same_day_select_before_label( $template );
	}

	/**
	 * Filter the year not same day after markup used in the classic editor.
	 *
	 * @param string $template Year not same day after markup.
	 *
	 * @return string Updated year not same day after markup.
	 */
	public function filter_year_not_same_day_after( $template ) {
		return $this->container->make( Recurrence::class )->add_year_not_same_day_after_dropdown( $template );
	}

	/**
	 * Filter the exclusion rule type buttons after markup used in the classic editor.
	 *
	 * @param string $template Exclusion rule type buttons after markup.
	 *
	 * @return string Updated exclusion rule type buttons after markup.
	 */
	public function filter_exclusion_rule_type_buttons_after( $template ) {
		return $this->container->make( Recurrence::class )->add_exclusion_rule_type_dropdown( $template );
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
		return $this->container->make( Recurrence::class )->filter_yearly_exclusions_template( $path, $rule_type );
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
		return $this->container->make( Recurrence::class )->filter_monthly_exclusions_template( $path, $rule_type );
	}

	/**
	 * Prints the locked UI notice, if required.
	 *
	 * @since 6.0.0
	 *
	 * @return void Prints the locked UI notice.
	 */
	public function print_locked_ui_notice(): void {
		$this->container->make( UI_Lock::class )->print_notice();
	}
}
