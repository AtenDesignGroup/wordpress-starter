<?php

use TEC\Events_Pro\Base\Query_Filters as Base_Query_Filters;
use TEC\Events_Pro\Compatibility\Event_Automator\Zapier\Zapier_Provider;
use TEC\Events_Pro\Legacy\Query_Filters as Legacy_Query_Filters;
use Tribe\Events\Pro\Views\V2\Views\Map_View;
use Tribe\Events\Pro\Views\V2\Views\Photo_View;
use Tribe\Events\Pro\Views\V2\Views\Summary_View;
use Tribe\Events\Pro\Views\V2\Views\Week_View;
use Tribe\Events\Views\V2\Views\Day_View;
use Tribe\Events\Views\V2\Views\List_View;
use Tribe\Events\Views\V2\Views\Month_View;

if ( ! class_exists( 'Tribe__Events__Pro__Main' ) ) {
	class Tribe__Events__Pro__Main {

		private static $instance;

		public $pluginDir;
		public $pluginPath;
		public $pluginUrl;
		public $pluginSlug;

		/**
		 * Used when forming recurring events /all/ view permalinks.
		 *
		 * @since 4.4.14
		 *
		 * @var string
		 */
		public $all_slug  = 'all';
		public $weekSlug  = 'week';
		public $photoSlug = 'photo';

		public $singular_event_label;
		public $singular_event_label_lowercase;
		public $plural_event_label;
		public $plural_event_label_lowercase;

		/** @var Tribe__Events__Pro__Recurrence__Permalinks */
		public $permalink_editor = null;

		/**
		 * @var Tribe__Events__Pro__Single_Event_Meta
		 */
		public $single_event_meta;

		/** @var Tribe__Events__Pro__Recurrence__Single_Event_Overrides */
		public $single_event_overrides;

		/** @var Tribe__Events__Pro__Admin__Custom_Meta_Tools */
		public $custom_meta_tools;

		/** @var Tribe__Events__Pro__Recurrence__Queue_Processor */
		public $queue_processor;

		/**
		 * @var Tribe__Events__Pro__Recurrence__Queue_Realtime
		 */
		public $queue_realtime;

		/**
		 * @var Tribe__Events__Pro__Recurrence__Aggregator
		 */
		public $aggregator;

		/**
		 * @var Tribe__Events__Pro__Embedded_Maps
		 */
		public $embedded_maps;

		/**
		 * @var Tribe__Events__Pro__Shortcodes__Register
		 */
		public $shortcodes;

		/**
		 * Where in the themes we will look for templates
		 *
		 * @since 4.5
		 *
		 * @var string
		 */
		public $template_namespace = 'events-pro';

		const VERSION = '6.2.0';

	    /**
		 * The Events Calendar Required Version
		 * Use Tribe__Events__Pro__Plugin_Register instead
		 *
		 * @deprecated 4.6
		 *
		 */
		const REQUIRED_TEC_VERSION = '6.1.0';

		private function __construct() {
			$this->pluginDir = trailingslashit( basename( EVENTS_CALENDAR_PRO_DIR ) );
			$this->pluginPath = trailingslashit( EVENTS_CALENDAR_PRO_DIR );
			$this->pluginUrl = plugins_url( $this->pluginDir, EVENTS_CALENDAR_PRO_DIR );
			$this->pluginSlug = 'events-calendar-pro';

			require_once( $this->pluginPath . 'src/functions/template-tags/general.php' );
			require_once( $this->pluginPath . 'src/functions/template-tags/organizer.php' );
			require_once( $this->pluginPath . 'src/functions/template-tags/venue.php' );
			require_once( $this->pluginPath . 'src/functions/template-tags/organizer.php' );
			require_once( $this->pluginPath . 'src/functions/template-tags/widgets.php' );
			require_once( $this->pluginPath . 'src/functions/template-tags/ical.php' );
			require_once( $this->pluginPath . 'src/functions/template-tags/series.php' );

			// Load Deprecated Template Tags
			if ( ! defined( 'TRIBE_DISABLE_DEPRECATED_TAGS' ) ) {
				require_once $this->pluginPath . 'src/functions/template-tags/deprecated.php';
			}

			add_action( 'admin_init', [ $this, 'run_updates' ], 10, 0 );

			add_action( 'init', [ $this, 'init' ], 10 );
			add_action( 'tribe_load_text_domains', [ $this, 'loadTextDomain' ] );

			tribe_singleton( Base_Query_Filters::class, Base_Query_Filters::class );
			tribe_singleton( Legacy_Query_Filters::class, Legacy_Query_Filters::class );
			add_action( 'parse_query', [ $this, 'parse_query' ], 100 );
			add_action( 'parse_query', [ $this, 'set_post_id_for_recurring_event_query' ], 101 );

			add_action( 'tribe_settings_do_tabs', [ $this, 'add_settings_tabs' ] );
			add_filter( 'tec_events_display_settings_tab_fields', [ $this, 'filter_display_settings_tab_fields' ], 10 );

			add_filter( 'tribe_events_template_paths', [ $this, 'template_paths' ] );

			add_filter( 'tribe_help_tab_getting_started_text', [ $this, 'add_help_tab_getting_started_text' ] );
			add_filter( 'tribe_help_tab_introtext', [ $this, 'add_help_tab_intro_text' ] );
			add_filter( 'tribe_help_tab_forumtext', [ $this, 'add_help_tab_forumtext' ] );
			add_filter( 'tribe_support_registered_template_systems', [ $this, 'register_template_updates' ] );

			add_action( 'wp_loaded', [ $this, 'allow_cpt_search' ] );
			add_action( 'plugin_row_meta', [ $this, 'addMetaLinks' ], 10, 2 );
			add_filter( 'tribe_get_events_title', [ $this, 'reset_page_title' ], 10, 2 );

			add_filter( 'tribe_help_tab_forums_url', [ $this, 'helpTabForumsLink' ] );
			add_action( 'plugin_action_links_' . plugin_basename( __FILE__ ), [ $this, 'addLinksToPluginActions' ] );

			add_filter( 'tribe_events_before_html', [ $this, 'events_before_html' ], 10 );

			add_filter( 'tribe_events_event_schedule_details', [ $this, 'append_recurring_info_tooltip' ], 9, 2 );

			// add custom fields to "the_meta" on single event template
			add_filter( 'tribe_events_single_event_the_meta_addon', [ $this, 'single_event_the_meta_addon' ], 10, 2 );
			add_filter( 'tribe_events_single_event_meta_group_template_keys', [ $this, 'single_event_meta_group_template_keys' ], 10 );
			add_filter( 'tribe_events_single_event_meta_template_keys', [ $this, 'single_event_meta_template_keys' ], 10 );
			add_filter( 'tribe_event_meta_venue_name', [ 'Tribe__Events__Pro__Single_Event_Meta', 'venue_name' ], 10, 2 );
			add_filter( 'tribe_event_meta_organizer_name', [ 'Tribe__Events__Pro__Single_Event_Meta', 'organizer_name' ], 10, 2 );

			add_action( 'tribe_events_single_event_after_the_meta', array( $this, 'register_related_events_view' ) );

			// see function tribe_convert_units( $value, $unit_from, $unit_to )
			add_filter( 'tribe_convert_kms_to_miles_ratio', [ $this, 'kms_to_miles_ratio' ] );
			add_filter( 'tribe_convert_miles_to_kms_ratio', [ $this, 'miles_to_kms_ratio' ] );

			add_filter( 'tribe_events_ugly_link', [ $this, 'ugly_link' ], 10, 3 );
			add_filter( 'tribe_events_get_link', [ $this, 'get_link' ], 10, 4 );

			add_filter( 'wp', [ $this, 'detect_recurrence_redirect' ] );
			add_filter( 'template_redirect', [ $this, 'filter_canonical_link_on_recurring_events' ], 10, 1 );

			$this->permalink_editor = apply_filters( 'tribe_events_permalink_editor', new Tribe__Events__Pro__Recurrence__Permalinks() );
			add_filter( 'post_type_link', [ $this->permalink_editor, 'filter_recurring_event_permalinks' ], 10, 4 );
			add_filter( 'get_sample_permalink', [ $this->permalink_editor, 'filter_sample_permalink' ], 10, 2 );

			add_filter( 'tribe_events_register_venue_type_args', [ $this, 'addSupportsThumbnail' ], 10, 1 );
			add_filter( 'tribe_events_register_organizer_type_args', [ $this, 'addSupportsThumbnail' ], 10, 1 );
			add_action( 'post_updated_messages', [ $this, 'updatePostMessages' ], 20 );

			add_filter( 'tribe_events_default_value_strategy', [ $this, 'set_default_value_strategy' ] );
			add_action( 'plugins_loaded', [ $this, 'init_apm_filters' ] );

			// Event CSV import additions
			add_filter( 'tribe_events_importer_venue_column_names', [ Tribe__Events__Pro__CSV_Importer__Fields::instance(), 'filter_venue_column_names' ], 10, 1 );
			add_filter( 'tribe_events_importer_venue_array', [ Tribe__Events__Pro__CSV_Importer__Fields::instance(), 'filter_venue_array' ], 10, 4 );

			add_filter( 'oembed_discovery_links', [ $this, 'oembed_discovery_links_for_recurring_events' ] );
			add_filter( 'oembed_request_post_id', [ $this, 'oembed_request_post_id_for_recurring_events' ], 10, 2 );

			add_action( 'wp_ajax_tribe_widget_dropdown_terms', [ $this, 'ajax_widget_get_terms' ] );

			// Start the integrations manager
			Tribe__Events__Pro__Integrations__Manager::instance()->load_integrations();

			add_action( 'plugins_loaded', [ $this, 'on_plugins_loaded' ] );

			add_filter( 'tribe_customizer_inline_stylesheets', [ $this, 'customizer_inline_stylesheets' ], 10, 2 );
		}

		/**
		 * @deprecated 6.0.0 This filter no longer valid, related to V1 views.
		 */
		public function filter_month_week_customizer_label( $args, $section_id, $customizer ) {
			_deprecated_function( __METHOD__, '6.0.0', 'This section of the customizer no longer exists.' );
		}

		/**
		 * AJAX handler for the Widget Term Select2.
		 *
		 * @todo   We need to move this to use Tribe__Ajax__Dropdown class
		 *
		 * @return void
		 */
		public function ajax_widget_get_terms() {
			$disabled = (array) tribe_get_request_var( 'disabled', [] );
			$search   = tribe_get_request_var( [ 'search', 'term' ], false );

			$taxonomies = get_object_taxonomies( Tribe__Events__Main::POSTTYPE, 'objects' );
			$taxonomies = array_reverse( $taxonomies );

			$results = [];
			foreach ( $taxonomies as $tax ) {
				$group = [
					'text' => esc_attr( $tax->labels->name ),
					'children' => [],
					'tax' => $tax,
				];

				// echo sprintf( "<optgroup id='%s' label='%s'>", esc_attr( $tax->name ), esc_attr( $tax->labels->name ) );
				$terms = get_terms( $tax->name, [ 'hide_empty' => false ] );
				if ( empty( $terms ) ) {
					continue;
				}

				foreach ( $terms as $term ) {
					// This is a workaround to make #93598 work
					if ( $search && false === strpos( $term->name, $search ) ) {
						continue;
					}

					$group['children'][] = [
						'id' => esc_attr( $term->term_id ),
						'text' => esc_html( $term->name ),
						'taxonomy' => $tax,
						'disabled' => in_array( $term->term_id, $disabled ),
					];
				}

				$results[] = $group;
			}

			wp_send_json_success( [ 'results' => $results ] );
		}

		/**
		 * Make necessary database updates on admin_init
		 *
		 * @return void
		 */
		public function run_updates() {
			if ( ! class_exists( 'Tribe__Events__Updater' ) ) {
				return; // core needs to be updated for compatibility
			}
			$updater = new Tribe__Events__Pro__Updater( self::VERSION );
			if ( $updater->update_required() ) {
				$updater->do_updates();
			}
		}

		/**
		 * @todo Move this to the Related Events template.
		 *
		 * @return bool Whether related events should be shown in the single view
		 */
		public function show_related_events() {
			if ( tribe_get_option( 'hideRelatedEvents', false ) == true ) {
				return false;
			}

			return true;
		}

		/**
		 * add related events to single event view
		 *
		 * @todo Move this to the Related Events template.
		 *
		 * @return void
		 */
		public function register_related_events_view() {
			if ( $this->show_related_events() ) {
				tribe_single_related_events();
			}
		}

		/**
		 * Append the recurring info tooltip after an event schedule
		 *
		 * @param string $schedule_details
		 * @param int $event_id
		 *
		 * @return string
		 */
		public function append_recurring_info_tooltip( $schedule_details, $event_id = 0 ) {
			$tooltip = tribe_events_recurrence_tooltip( $event_id );

			return $schedule_details . $tooltip;
		}

		/**
		 * @deprecated 6.0.0
		 */
		public function enable_recurring_info_tooltip() {
			_deprecated_function( __METHOD__, '6.0.0' );
		}

		/**
		 * @deprecated 6.0.0
		 */
		public function disable_recurring_info_tooltip() {
			_deprecated_function( __METHOD__, '6.0.0' );
		}

		public function recurring_info_tooltip_status() {
			if ( has_filter( 'tribe_events_event_schedule_details', [ $this, 'append_recurring_info_tooltip' ] ) ) {
				return true;
			}

			return false;
		}

		/**
		 * Filters in a meta walker group for new items regarding the PRO addon.
		 *
		 * @param string $html The current HTML for the event meta..
		 * @param int $event_id The post_id of the current event.
		 *
		 * @return string The modified HTML for the event meta.
		 */
		public function single_event_the_meta_addon( $html, $event_id ) {

			// add custom meta if it's available
			$html .= tribe_get_meta_group( 'tribe_event_group_custom_meta' );

			return $html;
		}

		/**
		 * Adds for the meta walker a key for custom meta to do with PRO addon.
		 *
		 * @param array $keys The current array of meta keys.
		 *
		 * @return array The modified array.
		 */
		public function single_event_meta_template_keys( $keys ) {
			$keys[] = 'tribe_event_custom_meta';

			return $keys;
		}

		/**
		 * Adds for the meta walker a key for custom meta groups to do with PRO addon.
		 *
		 * @param array $keys The current array of meta keys.
		 *
		 * @return array The modified array.
		 */
		public function single_event_meta_group_template_keys( $keys ) {
			$keys[] = 'tribe_event_group_custom_meta';

			return $keys;
		}

		/**
		 * @deprecated 6.0.0
		 */
		public function single_event_the_meta_group_venue( $status, $event_id ) {
			_deprecated_function( __METHOD__, '6.0.0' );
			return $status;
		}

		/**
		 * Gets the events_before_html content.
		 *
		 * @param string $html The events_before_html currently.
		 *
		 * @return string The modified html.
		 */
		public function events_before_html( $html ) {
			$wp_query = tribe_get_global_query_object();

			if ( is_null( $wp_query ) ) {
				return $html;
			}

			if ( $wp_query->tribe_is_event_venue || $wp_query->tribe_is_event_organizer ) {
				add_filter( 'tribe-events-bar-should-show', '__return_false' );
			}

			return $html;
		}

		/**
		 * Sets the page title for the various PRO views.
		 *
		 * @param string $title The current title.
		 *
		 * @return string The modified title.
		 */
		public function reset_page_title( $title, $depth = true ) {
			$wp_query = tribe_get_global_query_object();

			if ( is_null( $wp_query ) ) {
				return $title;
			}

			$tec = Tribe__Events__Main::instance();
			$date_format = apply_filters( 'tribe_events_pro_page_title_date_format', tribe_get_date_format( true ) );

			if ( tribe_is_showing_all() ) {
				$reset_title = sprintf( __( 'All %1$s for %2$s', 'tribe-events-calendar-pro' ), $this->plural_event_label_lowercase, get_the_title() );
			}

			// week view title
			if ( tribe_is_week() ) {
				$reset_title = sprintf(
					__( '%1$s for week of %2$s', 'tribe-events-calendar-pro' ),
					$this->plural_event_label,
					date_i18n( $date_format, strtotime( tribe_get_first_week_day( $wp_query->get( 'start_date' ) ) ) )
				);
			}

			if ( ! empty( $reset_title ) && is_tax( $tec->get_event_taxonomy() ) && $depth ) {
				$cat = get_queried_object();
				$reset_title = '<a href="' . tribe_get_events_link() . '">' . $reset_title . '</a>';
				$reset_title .= ' &#8250; ' . $cat->name;
			}

			return isset( $reset_title ) ? $reset_title : $title;
		}

		/**
		 * The class init function.
		 *
		 * @return void
		 */
		public function init() {
			Tribe__Events__Pro__Custom_Meta::init();
			Tribe__Events__Pro__Geo_Loc::instance();
			Tribe__Events__Pro__Community_Modifications::init();
			$this->custom_meta_tools = new Tribe__Events__Pro__Admin__Custom_Meta_Tools;
			$this->single_event_meta = new Tribe__Events__Pro__Single_Event_Meta;
			$this->single_event_overrides = new Tribe__Events__Pro__Recurrence__Single_Event_Overrides;
			$this->embedded_maps = new Tribe__Events__Pro__Embedded_Maps;
			$this->shortcodes = new Tribe__Events__Pro__Shortcodes__Register;
			$this->singular_event_label = tribe_get_event_label_singular();
			$this->plural_event_label = tribe_get_event_label_plural();
			$this->singular_event_label_lowercase = tribe_get_event_label_singular_lowercase();
			$this->plural_event_label_lowercase = tribe_get_event_label_plural_lowercase();

			// if enabled views have never been set then set those to all PRO views
			if ( false === tribe_get_option( 'tribeEnableViews', false ) ) {
				tribe_update_option(
					'tribeEnableViews',
					[
						Day_View::get_view_slug(),
						List_View::get_view_slug(),
						Month_View::get_view_slug(),
						Map_View::get_view_slug(),
						Photo_View::get_view_slug(),
						Summary_View::get_view_slug(),
						Week_View::get_view_slug(),
					]
				);
				// After setting the enabled view we Flush the rewrite rules
				flush_rewrite_rules();
			}
		}

		/**
		 * At the pre_get_post hook detect if we should redirect to a particular instance
		 * for an invalid 404 recurrence entries.
		 *
		 * @return mixed
		 */
		public function detect_recurrence_redirect() {
			global $wp;

			$wp_query = tribe_get_global_query_object();

			if ( is_null( $wp_query ) || ! isset( $wp_query->query_vars['eventDisplay'] ) ) {
				return false;
			}

			$current_url = null;
			$problem = _x( 'Unknown', 'debug recurrence', 'tribe-events-calendar-pro' );

			switch ( $wp_query->query_vars['eventDisplay'] ) {
				case 'single-event':
					// a recurrence event with a bad date will throw 404 because of WP_Query limiting by date range
					if ( is_404() || empty( $wp_query->query['eventDate'] ) ) {
						$recurrence_check = array_merge( array( 'posts_per_page' => 1 ), $wp_query->query );
						unset( $recurrence_check['eventDate'] );
						unset( $recurrence_check['tribe_events'] );

						// retrieve event object
						$get_recurrence_event = new WP_Query( $recurrence_check );
						// If a recurrence event actually exists then proceed with redirection.
						if (
							! empty( $get_recurrence_event->posts )
							&& tribe_is_recurring_event( $get_recurrence_event->posts[0]->ID )
							&& 'publish' === get_post_status( $get_recurrence_event->posts[0] )
						) {
							$problem = _x( 'invalid date', 'debug recurrence', 'tribe-events-calendar-pro' )
									. empty( $wp_query->query['eventDate'] ) ? '' : ': ' . $wp_query->query['eventDate'];

							$current_url = Tribe__Events__Main::instance()->getLink( 'all', $get_recurrence_event->posts[0]->ID );
						}
						break;
					}

					// We are receiving the event date
					if ( ! empty( $wp_query->query['eventDate'] ) ) {
						$event_id = get_the_id();
						// if is a recurring event
						if ( tribe_is_recurring_event( $event_id ) ) {

							$event = get_post( $event_id );
							// if no post parent (ether the post parent or inexistent)
							if ( ! $event->post_parent ) {
								// get all the recursive event dates
								$dates = tribe_get_recurrence_start_dates( $event_id );

								$exist = false;
								foreach ( $dates as $date ) {
									// check if the date exists in any of the recurring event set
									if ( 0 === strpos( $date, $wp_query->query['eventDate'] ) ) {
										$exist = true;
										break;
									}
								}

								// if the event date coming on the URL doesn't exist, display the /all/ page
								if ( ! $exist ) {
									$problem = _x( 'incorrect slug', 'debug recurrence', 'tribe-events-calendar-pro' );
									$current_url = Tribe__Events__Main::instance()->getLink( 'all', $event_id );
									break;
								}
							}
						}
					}

					// A child event should be using its parent's slug. If it's using its own, redirect.
					if ( tribe_is_recurring_event( get_the_ID() ) && '' !== get_option( 'permalink_structure' ) ) {
						$event = get_post( get_the_ID() );
						if ( ! empty( $event->post_parent ) ) {
							if ( isset( $wp_query->query['name'] ) && $wp_query->query['name'] == $event->post_name ) {
								$problem = _x( 'incorrect slug', 'debug recurrence', 'tribe-events-calendar-pro' );
								$current_url = get_permalink( $event->ID );
							}
						}
					}
					break;

			}

			/**
			 * Provides an opportunity to modify the redirection URL prior to the actual redirection.
			 *
			 * @param string $current_url
			 */
			$current_url = apply_filters( 'tribe_events_pro_recurrence_redirect_url', $current_url );

			if ( ! empty( $current_url ) ) {
				// redirect user with 301
				$confirm_redirect = apply_filters( 'tribe_events_pro_detect_recurrence_redirect', true, $wp_query->query_vars['eventDisplay'] );
				do_action( 'tribe_events_pro_detect_recurrence_redirect', $wp_query->query_vars['eventDisplay'] );
				if ( $confirm_redirect ) {
					tribe( 'logger' )->log_warning(
						sprintf(
							/* Translators: 1: Error message, 2: URL */
							_x( 'Invalid instance of a recurring event was requested (%1$s) redirecting to %2$s', 'debug recurrence', 'tribe-events-calendar-pro' ),
							$problem,
							esc_url( $current_url )
						),
						__METHOD__
					);

					wp_safe_redirect( $current_url, 301 );
					exit;
				}
			}
		}

		public function filter_canonical_link_on_recurring_events() {
			if ( is_feed() ) {
				return;
			}

			if ( is_singular( Tribe__Events__Main::POSTTYPE ) && get_query_var( 'eventDate' ) && has_action( 'wp_head', 'rel_canonical' ) ) {
				remove_action( 'wp_head', 'rel_canonical' );
				add_action( 'wp_head', [ $this, 'output_recurring_event_canonical_link' ] );
			}
		}

		public function output_recurring_event_canonical_link() {
			// set the EventStartDate so Tribe__Events__Main can filter the permalink appropriately
			$post = get_post( get_queried_object_id() );
			$post->EventStartDate = get_query_var( 'eventDate' );

			// use get_post_permalink instead of get_permalink so that the post isn't converted
			// back to an ID, then to a post again (without the EventStartDate)
			$link = get_post_permalink( $post );

			echo "<link rel='canonical' href='" . esc_url( $link ) . "' />\n";
		}

		/**
		 * Loop through recurrence posts array and find out the next recurring instance from right now
		 *
		 * @param WP_Post[] $event_list
		 *
		 * @return int
		 */
		public function get_last_recurrence_id( $event_list ) {

			$wp_query = tribe_get_global_query_object();

			if ( ! is_null( $wp_query ) && empty( $event_list ) ) {
				$event_list = $wp_query->posts;
			}

			$right_now = current_time( 'timestamp' );
			$next_recurrence = 0;

			// find next recurrence date by loop
			foreach ( $event_list as $key => $event ) {
				if ( $right_now < strtotime( $event->EventStartDate ) ) {
					$next_recurrence = $event;
				}
			}
			if ( empty( $next_recurrence ) && ! empty( $event_list ) ) {
				$next_recurrence = reset( $event_list );
			}

			return apply_filters( 'tribe_events_pro_get_last_recurrence_id', $next_recurrence->ID, $event_list, $right_now );
		}

		/**
		 * @deprecated 6.0.0
		 */
		public function helpersLoaded() {
			_deprecated_function( __METHOD__, '6.0.0' );
		}

		/**
		 * Add the default settings tab
		 *
		 * @return void
		 */
		public function add_settings_tabs( $admin_page ) {
			$tec_settings_page_id = tribe( 'tec.main' )->settings()::$settings_page_id;

			if ( ! empty( $admin_page ) && $tec_settings_page_id !== $admin_page ) {
				return;
			}

			add_filter(
				'tec_events_settings_tabs_ids',
				function( $tabs ) {
					$tabs[] = 'defaults';
					$tabs[] = 'additional-fields';

					return $tabs;
				}
			);

			require_once( $this->pluginPath . 'src/admin-views/tribe-options-defaults.php' );
			new Tribe__Settings_Tab( 'defaults', __( 'Default Content', 'tribe-events-calendar-pro' ), $defaultsTab );
			// The single-entry array at the end allows for the save settings button to be displayed.
			new Tribe__Settings_Tab( 'additional-fields', __( 'Additional Fields', 'tribe-events-calendar-pro' ), array(
				'priority' => 35,
				'fields'   => array( null ),
			) );
		}

		/**
		 * Filter the display settings fields.
		 *
		 * @deprecated 6.0.4
		 *
		 * @param array $fields
		 * @param string $tab
		 */
		public function filter_settings_tab_fields( $fields, $tab ) {
			_deprecated_function( __METHOD__, '6.0.4', 'filter_display_settings_tab_fields' );
			return $this->filter_display_settings_tab_fields( $fields, $tab );
		}

		/**
		 * Filter the display settings fields.
		 *
		 * @since 6.0.4
		 *
		 * @param array $fields
		 */
		public function filter_display_settings_tab_fields( $fields ) {

			$fields = Tribe__Main::array_insert_after_key(
				'tribeDisableTribeBar',
				$fields,
				array(
					'hideRelatedEvents' => array(
						'type'            => 'checkbox_bool',
						'label'           => __( 'Hide related events', 'tribe-events-calendar-pro' ),
						'tooltip'         => __( 'Remove related events from the single event view (with classic editor)', 'tribe-events-calendar-pro' ),
						'default'         => false,
						'validation_type' => 'boolean',
					),
				)
			);
			$fields = Tribe__Main::array_insert_after_key(
				'hideRelatedEvents',
				$fields,
				array(
					'week_view_hide_weekends' => array(
						'type'            => 'checkbox_bool',
						'label'           => __( 'Hide weekends on Week View', 'tribe-events-calendar-pro' ),
						'tooltip'         => __( 'Check this to only show weekdays on Week View. This also affects the Events by Week widget.', 'tribe-events-calendar-pro' ),
						'default'         => false,
						'validation_type' => 'boolean',
					),
				)
			);
			$fields = Tribe__Main::array_insert_before_key(
				'tribeEventsBeforeHTML',
				$fields,
				array(
					'tribeEventsShortcodeBeforeHTML' => array(
						'type'            => 'checkbox_bool',
						'label'           => __( 'Enable the Before HTML (below) on shortcodes.', 'tribe-events-calendar-pro' ),
						'tooltip'         => __( 'Check this to show the Before HTML from the text area below on events displayed via shortcode.', 'tribe-events-calendar-pro' ),
						'default'         => false,
						'validation_type' => 'boolean',
					),
				)
			);
			$fields = Tribe__Main::array_insert_before_key(
				'tribeEventsAfterHTML',
				$fields,
				array(
					'tribeEventsShortcodeAfterHTML' => array(
						'type'            => 'checkbox_bool',
						'label'           => __( 'Enable the After HTML (below) on shortcodes.', 'tribe-events-calendar-pro' ),
						'tooltip'         => __( 'Check this to show the After HTML from the text area below on events displayed via shortcode.', 'tribe-events-calendar-pro' ),
						'default'         => false,
						'validation_type' => 'boolean',
					),
				)
			);
			$sample_date = strtotime( 'January 15 ' . date( 'Y' ) );

			$fields = Tribe__Main::array_insert_after_key(
				'monthAndYearFormat',
				$fields,
				array(
					'weekDayFormat' => array(
						'type'            => 'text',
						'label'           => __( 'Week day format', 'tribe-events-calendar-pro' ),
						'tooltip'         => sprintf(
							esc_html__( 'Enter the format to use for week days. Used when showing days of the week in Week view. Example: %1$s', 'tribe-events-calendar-pro' ),
							date( get_option( 'weekDayFormat', 'D jS' ), $sample_date )
						),
						'default'         => 'D jS',
						'size'            => 'medium',
						'validation_type' => 'not_empty',
					),
				)
			);


			// We add weekDayFormat above, so there are four fields.
			$fields['tribeEventsDateFormatExplanation']   = [
				'type' => 'html',
				'html' => '<p>'
					. sprintf(
						__( 'The first four fields accept the date format options available to the PHP %1$s function. <a href="%2$s" target="_blank">Learn how to make your own date format here</a>.', 'tribe-events-calendar-pro' ),
						'<code>date()</code>',
						'https://wordpress.org/support/article/formatting-date-and-time/'
					)
					. '</p>',
			];

			return $fields;
		}

		/**
		 * Filter the dates settings fields.
		 *
		 * @since 6.0.4
		 *
		 * @param array $fields
		 * @param string $tab
		 */
		public function filter_dates_settings_tab_fields( $fields ) {


			return $fields;
		}

		/**
		 * Add the "Getting Started" text to the help tab for PRO addon.
		 *
		 * @return string The modified content.
		 */
		public function add_help_tab_getting_started_text() {
			$getting_started_text[] = sprintf( __( "Thanks for buying Events Calendar PRO! From all of us at The Events Calendar, we sincerely appreciate it. If you're looking for help with Events Calendar PRO, you've come to the right place. We are committed to helping make your calendar be spectacular... and hope the resources provided below will help get you there.", 'tribe-events-calendar-pro' ) );
			$content = implode( $getting_started_text );

			return $content;
		}

		/**
		 * Add the intro text that concerns PRO to the help tab.
		 *
		 * @return string The modified content.
		 */
		public function add_help_tab_intro_text() {
			$intro_text[] = '<p>' . __( "If this is your first time using The Events Calendar Pro, you're in for a treat and are already well on your way to creating a first event. Here are some basics we've found helpful for users jumping into it for the first time:", 'tribe-events-calendar-pro' ) . '</p>';
			$intro_text[] = '<ul>';
			$intro_text[] = '<li>';
			$intro_text[] = sprintf( __( '%sOur New User Primer%s was designed for folks in your exact position. Featuring both step-by-step videos and written walkthroughs that feature accompanying screenshots, the primer aims to take you from zero to hero in no time.', 'tribe-events-calendar-pro' ), '<a href="https://evnt.is/4t" target="blank">', '</a>' );
			$intro_text[] = '</li><li>';
			$intro_text[] = sprintf( __( '%sInstallation/Setup FAQs%s from our support page can help give an overview of what the plugin can and cannot do. This section of the FAQs may be helpful as it aims to address any basic install questions not addressed by the new user primer.', 'tribe-events-calendar-pro' ), '<a href="https://evnt.is/4u" target="blank">', '</a>' );
			$intro_text[] = '</li><li>';
			$intro_text[] = sprintf( __( "Take care of your license key. Though not required to create your first event, you'll want to get it in place as soon as possible to guarantee your access to support and upgrades. %sHere's how to find your license key%s, if you don't have it handy.", 'tribe-events-calendar-pro' ), '<a href="https://evnt.is/4v" target="blank">', '</a>' );
			$intro_text[] = '</li></ul><p>';
			$intro_text[] = __( "Otherwise, if you're feeling adventurous, you can get started by heading to the Events menu and adding your first event.", 'tribe-events-calendar-pro' );
			$intro_text[] = '</p>';
			$intro_text = implode( $intro_text );

			return $intro_text;
		}

		/**
		 * Add help text regarding the Tribe forums to the help tab.
		 *
		 * @return string The content.
		 */
		public function add_help_tab_forumtext() {
			$forum_text[] = '<p>' . sprintf( __( 'Written documentation can only take things so far...sometimes, you need help from a real person. This is where our %ssupport forums%s come into play.', 'tribe-events-calendar-pro' ), '<a href="https://evnt.is/4w/" target="blank">', '</a>' ) . '</p>';
			$forum_text[] = '<p>' . sprintf( __( "Users who have purchased an Events Calendar PRO license are granted total access to our %spremium support forums%s. Unlike at the %sWordPress.org support forum%s, where our involvement is limited to identifying and patching bugs, we have a dedicated support team for PRO users. We're on the PRO forums daily throughout the business week, and no thread should go more than 24-hours without a response.", 'tribe-events-calendar-pro' ), '<a href="https://evnt.is/4w/" target="blank">', '</a>', '<a href="http://wordpress.org/support/plugin/the-events-calendar" target="blank">', '</a>' ) . '</p>';
			$forum_text[] = '<p>' . __( "Our number one goal is helping you succeed, and to whatever extent possible, we'll help troubleshoot and guide your customizations or tweaks. While we won't build your site for you, and we can't guarantee we'll be able to get you 100% integrated with every theme or plugin out there, we'll do all we can to point you in the right direction and to make you -- and your client, as is often more importantly the case -- satisfied.", 'tribe-events-calendar-pro' ) . '</p>';
			$forum_text[] = '<p>' . __( "Before posting a new thread, please do a search to make sure your issue hasn't already been addressed. When posting please make sure to provide as much detail about the problem as you can (with screenshots or screencasts if feasible), and make sure that you've identified whether a plugin / theme conflict could be at play in your initial message.", 'tribe-events-calendar-pro' ) . '</p>';
			$forum_text = implode( $forum_text );

			return $forum_text;
		}

		/**
		 * If the user has chosen to replace default values, set up
		 * the Pro class to read those defaults from options
		 *
		 * @param Tribe__Events__Default_Values $strategy
		 * @return Tribe__Events__Default_Values
		 */
		public function set_default_value_strategy( $strategy ) {
			return new Tribe__Events__Pro__Default_Values();
		}

		/**
		 * Adds the proper css class(es) to the body tag.
		 *
		 * @deprecated 6.0.0
		 *
		 * @param array $classes The current array of body classes.
		 *
		 * @return array The modified array of body classes.
		 */
		public function body_class( $classes ) {
			_deprecated_function( __METHOD__, '6.0.0' );

			return $classes;
		}

		/**
		 * Set PRO query flags.
		 *
		 * @since 6.0.0 Uses the values from Views V2 to determine old V1 variables that should still be around.
		 *
		 * @param WP_Query $query The current query object.
		 *
		 * @return WP_Query The modified query object.
		 **/
		public function parse_query( $query ) {
			return tribe( Base_Query_Filters::class )->parse_query( $query );
		}

		/**
		 * Add custom query modification to the pre_get_posts hook as necessary for PRO.
		 *
		 * @deprecated 6.0.0 Any modifications to the query no longer happen outside the views.
		 *
		 * @param WP_Query $query The current query object.
		 *
		 * @return WP_Query The modified query object.
		 */
		public function pre_get_posts( $query ) {
			_deprecated_function( __METHOD__, '6.0.0', 'Any modifications to the query no longer happen outside the views.' );
			return $query;
		}

		/**
		 * Get the path to the current events template.
		 *
		 * @deprecated 6.0.0
		 *
		 * @param string $template The current template path.
		 *
		 * @return string The modified template path.
		 */
		public function select_page_template( $template ) {
			_deprecated_function( __METHOD__, '6.0.0', 'Template class path is no longer something we use after V1 removal.' );
			return $template;
		}

		/**
		 * Check the ajax request action looking for pro views
		 *
		 * @deprecated 6.0.0 No longer in use.
		 *
		 * @param $is_ajax_view_request bool
		 */
		public function is_pro_ajax_view_request( $is_ajax_view_request, $view ) {
			_deprecated_function( __METHOD__, '6.0.0', 'No longer a check we use for View V2' );
		}

		/**
		 * Specify the PHP class for the current page template
		 *
		 * @deprecated 6.0.0 Views V2 doesn't use template classes separately from views.
		 *
		 * @param string $class The current class we are filtering.
		 *
		 * @return string The class.
		 */
		public function get_current_template_class( $class ) {
			_deprecated_function( __METHOD__, '6.0.0', 'Template class path is no longer something we use after V1 removal.' );

			return $class;
		}

		/**
		 * Add premium plugin paths for each file in the templates array
		 *
		 * @param $template_paths array
		 *
		 * @return array
		 */
		public function template_paths( $template_paths = array() ) {
			$template_paths['pro'] = $this->pluginPath;

			return $template_paths;
		}

		/**
		 * Add premium plugin paths for each file in the templates array
		 *
		 * @deprecated 6.0.0
		 **/
		public function template_class_path( $template_class_paths = array() ) {
			_deprecated_function( __METHOD__, '6.0.0', 'Template class path is no longer something we use after V1 removal.' );
		}

		/**
		 * Enqueues the necessary JS for the admin side of things.
		 * @deprecated 6.0.0 Moved to the assets class.
		 *
		 * @return void
		 */
		public function admin_enqueue_scripts() {
			_deprecated_function( __METHOD__, '6.0.0', 'Tribe__Events__Pro__Assets' );
		}

		/**
		 * @deprecated 6.0.0
		 */
		public function load_widget_assets( $hook = null ) {
			_deprecated_file( __METHOD__, '6.0.0' );
		}

		/**
		 * @deprecated 6.0.0
		 */
		public function load_widget_assets_on_block_editor( $load_assets, $hook ) {
			_deprecated_file( __METHOD__, '6.0.0' );
		}

		/**
		 * @deprecated 6.0.0 Don't enqueue assets from the Main class.
		 */
		public function admin_enqueue_styles() {
			_deprecated_function( __METHOD__, '6.0.0', "tribe_asset_enqueue( 'tribe-select2-css' )" );
		}

		/**
		 * @deprecated 6.0.0 Don't enqueue assets from the Main class.
		 */
		public function enqueue_styles() {
			_deprecated_function( __METHOD__, '6.0.0', "Tribe__Events__Pro__Assets" );
		}

		/**
		 * @deprecated 6.0.0 Don't enqueue assets from the Main class.
		 */
		public function enqueue_pro_scripts( $force = false, $footer = false ) {
			_deprecated_function( __METHOD__, '6.0.0', "Tribe__Events__Pro__Assets" );
		}

		/**
		 * @deprecated 6.0.0 All Query modifications are now made from the Views internally.
		 */
		public function setup_hide_recurrence_in_query( $query ) {
			_deprecated_function( __METHOD__, '6.0.0', 'All Query modifications are now made from the Views internally.' );
		}

		/**
		 * Returns whether or not we show only the first instance of each recurring event in listview
		 *
		 * @param WP_Query $query The current query object.
		 *
		 * @return boolean
		 */
		public function should_hide_recurrence( $query = null ) {
			$hide = false;

			if ( tribe_is_showing_all() ) {
				// let's not hide recurrence if we are showing all recurrence events
				$hide = false;
			} elseif ( defined( 'REST_REQUEST' ) && true === REST_REQUEST ) {
				// let's not hide recurrence if we are processing a REST request
				$hide = false;
			} elseif ( ! empty( $_GET['tribe_post_parent'] ) ) {
				// let's not hide recurrence if we are showing all recurrence events via AJAX
				$hide = false;
			} elseif ( ! empty( $_POST['tribe_post_parent'] ) ) {
				// let's not hide recurrence if we are showing all recurrence events via AJAX
				$hide = false;
			} elseif (
				is_object( $query )
				&& ! empty( $query->query['eventDisplay'] )
				&& in_array(
						$query->query['eventDisplay'],
						[
							Month_View::get_view_slug(),
							Week_View::get_view_slug(),
						]
					)
			) {
				// let's not hide recurrence if we are on month or week view
				$hide = false;
			} elseif ( tribe_get_option( 'hideSubsequentRecurrencesDefault', false ) ) {
				// let's HIDE recurrence events if we've set the option
				$hide = true;
			} elseif ( isset( $_GET['tribeHideRecurrence'] ) && 1 == $_GET['tribeHideRecurrence'] ) {
				// let's HIDE recurrence events if tribeHideRecurrence via GET
				$hide = true;
			} elseif ( isset( $_POST['tribeHideRecurrence'] ) && 1 == $_POST['tribeHideRecurrence'] ) {
				// let's HIDE recurrence events if tribeHideRecurrence via POST
				$hide = true;
			}

			/**
			 * Filters whether recurring event instances should be hidden or not.
			 *
			 * @since 4.4.29
			 *
			 * @param bool $hide
			 * @param WP_Query|null $query
			 */
			$hide = apply_filters( 'tribe_events_pro_should_hide_recurrence', $hide, $query );

			return (bool) $hide;
		}

		/**
		 * Return the forums link as it should appear in the help tab.
		 *
		 * @return string
		 */
		public function helpTabForumsLink( $content ) {
			if ( get_option( 'pue_install_key_events_calendar_pro ' ) ) {
				return 'https://evnt.is/4x';
			} else {
				return 'https://evnt.is/4w';
			}
		}

		/**
		 * Return additional action for the plugin on the plugins page.
		 *
		 * @return array
		 */
		public function addLinksToPluginActions( $actions ) {
			if ( class_exists( 'Tribe__Events__Main' ) ) {
				$actions['settings'] = '<a href="' . tribe( 'tec.main' )->settings()->get_url() . '">' . esc_html__( 'Settings', 'tribe-events-calendar-pro' ) . '</a>';
			}

			return $actions;
		}

		/**
		 * Adds thumbnail/featured image support to Organizers and Venues when PRO is activated.
		 *
		 * @param array $post_type_args The current register_post_type args.
		 *
		 * @return array The new register_post_type args.
		 */
		public function addSupportsThumbnail( $post_type_args ) {
			$post_type_args['supports'][] = 'thumbnail';

			return $post_type_args;
		}

		/**
		 * Enable "view post" links on metaposts.
		 *
		 * @param $messages array
		 * @return array
		 */
		public function updatePostMessages( $messages ) {
			global $post, $post_ID;

			$messages[ Tribe__Events__Main::VENUE_POST_TYPE ][1] = sprintf( __( 'Venue updated. <a href="%s">View venue</a>', 'tribe-events-calendar-pro' ), esc_url( get_permalink( $post_ID ) ) );
			/* translators: %s: date and time of the revision */
			$messages[ Tribe__Events__Main::VENUE_POST_TYPE ][6] = sprintf( __( 'Venue published. <a href="%s">View venue</a>', 'tribe-events-calendar-pro' ), esc_url( get_permalink( $post_ID ) ) );
			$messages[ Tribe__Events__Main::VENUE_POST_TYPE ][8] = sprintf( __( 'Venue submitted. <a target="_blank" href="%s">Preview venue</a>', 'tribe-events-calendar-pro' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) );
			$messages[ Tribe__Events__Main::VENUE_POST_TYPE ][9]  = sprintf(
				__( 'Venue scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview venue</a>', 'tribe-events-calendar-pro' ),
				// translators: Publish box date format, see http://php.net/date
				date_i18n( __( 'M j, Y @ G:i', 'tribe-events-calendar-pro' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_ID ) )
			);
			$messages[ Tribe__Events__Main::VENUE_POST_TYPE ][10] = sprintf( __( 'Venue draft updated. <a target="_blank" href="%s">Preview venue</a>', 'tribe-events-calendar-pro' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) );

			$messages[ Tribe__Events__Main::ORGANIZER_POST_TYPE ][1] = sprintf( __( 'Organizer updated. <a href="%s">View organizer</a>', 'tribe-events-calendar-pro' ), esc_url( get_permalink( $post_ID ) ) );
			$messages[ Tribe__Events__Main::ORGANIZER_POST_TYPE ][6] = sprintf( __( 'Organizer published. <a href="%s">View organizer</a>', 'tribe-events-calendar-pro' ), esc_url( get_permalink( $post_ID ) ) );
			$messages[ Tribe__Events__Main::ORGANIZER_POST_TYPE ][8] = sprintf( __( 'Organizer submitted. <a target="_blank" href="%s">Preview organizer</a>', 'tribe-events-calendar-pro' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) );
			$messages[ Tribe__Events__Main::ORGANIZER_POST_TYPE ][9]  = sprintf(
				__( 'Organizer scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview organizer</a>', 'tribe-events-calendar-pro' ),
				// translators: Publish box date format, see http://php.net/date
				date_i18n( __( 'M j, Y @ G:i', 'tribe-events-calendar-pro' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_ID ) )
			);
			$messages[ Tribe__Events__Main::ORGANIZER_POST_TYPE ][10] = sprintf( __( 'Organizer draft updated. <a target="_blank" href="%s">Preview organizer</a>', 'tribe-events-calendar-pro' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) );

			return $messages;
		}

		/**
		 * Includes and handles registration/de-registration of the advanced list widget. Idea from John Gadbois.
		 *
		 * @deprecated 6.0.0
		 *
		 * @return void
		 */
		public function pro_widgets_init() {
			_deprecated_function( __METHOD__, '6.0.0' );
		}

		/**
		 * Load textdomain for localization
		 *
		 * @return void
		 */
		public function loadTextDomain() {
			$mopath = $this->pluginDir . 'lang/';
			$domain = 'tribe-events-calendar-pro';

			// If we don't have Common classes load the old fashioned way
			if ( ! class_exists( 'Tribe__Main' ) ) {
				load_plugin_textdomain( $domain, false, $mopath );
			} else {
				// This will load `wp-content/languages/plugins` files first
				Tribe__Main::instance()->load_text_domain( $domain, $mopath );
			}
		}

		/**
		 * Re-registers the custom post types for venues so they allow search from the frontend.
		 *
		 * @return void
		 */
		public function allow_cpt_search() {
			$venue_args = tribe( 'tec.linked-posts.venue' )->post_type_args;
			$venue_args['exclude_from_search'] = false;
			register_post_type( Tribe__Events__Main::VENUE_POST_TYPE, apply_filters( 'tribe_events_register_venue_type_args', $venue_args ) );
		}

		/**
		 * Add meta links on the plugins page.
		 *
		 * @param array $links The current array of links to display.
		 * @param string $file The plugin to add meta links to.
		 *
		 * @return array The modified array of links to display.
		 */
		public function addMetaLinks( $links, $file ) {
			if ( $file == $this->pluginDir . 'events-calendar-pro.php' ) {
				$anchor = __( 'Support', 'tribe-events-calendar-pro' );
				$links[] = '<a href="https://evnt.is/4z">' . $anchor . '</a>';
				$anchor = __( 'View All Add-Ons', 'tribe-events-calendar-pro' );
				$links[] = '<a href="https://evnt.is/50">' . $anchor . '</a>';
			}

			return $links;
		}

		/**
		 * Add support for ugly links for ugly links with PRO views.
		 *
		 * @param string $eventUrl The current URL.
		 * @param string $type The type of endpoint/view whose link was requested.
		 * @param string $secondary More data that is necessary for generating the link.
		 *
		 * @return string The ugly-linked URL.
		 */
		public function ugly_link( $eventUrl, $type, $secondary ) {
			switch ( $type ) {
				case 'week':
					if ( ! apply_filters( 'tribe_events_force_ugly_link', false ) && empty( $_POST['baseurl'] ) ) {
						$eventUrl = add_query_arg( 'post_type', Tribe__Events__Main::POSTTYPE, $eventUrl );
					}

					$eventUrl = add_query_arg( [ 'tribe_event_display' => $type ], $eventUrl );
					if ( $secondary ) {
						$eventUrl = add_query_arg( [ 'date' => $secondary ], $eventUrl );
					}
					break;
				case 'photo':
				case 'map':
					$eventUrl = add_query_arg( [ 'tribe_event_display' => $type ], $eventUrl );
					break;
				case 'all':
					remove_filter(
						'post_type_link',
						[ $this->permalink_editor, 'filter_recurring_event_permalinks' ],
						10, 4
					);
					$post_id = $secondary ? $secondary : get_the_ID();
					$parent_id = wp_get_post_parent_id( $post_id );
					if ( ! empty( $parent_id ) ) {
						$post_id = $parent_id;
					}

					/**
					 * Filters the "all" part of the all recurrences link for a recurring event.
					 *
					 * @param string $all_frag Defaults to the localized versions of the "all" word.
					 * @param int $post_id The event post object ID.
					 * @param int $parent_id The event post object parent ID; this value will be the same as
					 *                             `$post_id` if the event has no parent.
					 */
					$all_frag = apply_filters(
						'tribe_events_pro_all_link_frag',
						$this->all_slug,
						$post_id,
						$parent_id
					);

					$eventUrl = add_query_arg( 'eventDisplay', $all_frag, get_permalink( $post_id ) );
					add_filter(
						'post_type_link',
						[ $this->permalink_editor, 'filter_recurring_event_permalinks' ],
						10, 4
					);
					break;
				default:
					break;
			}

			return apply_filters( 'tribe_events_pro_ugly_link', $eventUrl, $type, $secondary );
		}

		/**
		 * filter Tribe__Events__Main::getLink for pro views
		 *
		 * @param  string $event_url
		 * @param  string $type
		 * @param  string $secondary
		 * @param  string $term
		 *
		 * @return string
		 */
		public function get_link( $event_url, $type, $secondary, $term ) {
			switch ( $type ) {
				case 'week':
					$event_url = trailingslashit( esc_url_raw( $event_url . $this->weekSlug ) );
					if ( ! empty( $secondary ) ) {
						$event_url = esc_url_raw( trailingslashit( $event_url ) . $secondary );
					}
					break;
				case 'photo':
					$event_url = trailingslashit( esc_url_raw( $event_url . $this->photoSlug ) );
					if ( ! empty( $secondary ) ) {
						$event_url = esc_url_raw( trailingslashit( $event_url ) . $secondary );
					}
					break;
				case 'map':
					$event_url = trailingslashit( esc_url_raw( $event_url . Tribe__Events__Pro__Geo_Loc::instance()->rewrite_slug ) );
					if ( ! empty( $secondary ) ) {
						$event_url = esc_url_raw( trailingslashit( $event_url ) . $secondary );
					}
					break;
				case 'all':
					// Temporarily disable the post_type_link filter for recurring events
					$link_filter = [ $this->permalink_editor, 'filter_recurring_event_permalinks' ];
					remove_filter( 'post_type_link', $link_filter, 10, 4 );

					// Obtain the ID of the parent event
					$post_id   = $secondary ? $secondary : get_the_ID();
					$parent_id = wp_get_post_parent_id( $post_id );
					$event_id  = ( 0 === $parent_id ) ? $post_id : $parent_id;

					/**
					 * Filters the "all" part of the all recurrences link for a recurring event.
					 *
					 * @param string $all_frag  Defaults to the localized versions of the "all" word.
					 * @param int    $post_id   The event post object ID.
					 * @param int    $parent_id The event post object parent ID; this value will be the same as
					 *                          `$post_id` if the event has no parent.
					 */
					$all_frag = apply_filters(
						'tribe_events_pro_all_link_frag',
						$this->all_slug,
						$event_id,
						$parent_id
					);

					$permalink = get_permalink( $event_id );

					$event_url = tribe_append_path( $permalink, $all_frag );

					// Restore the temporarily disabled permalink filter
					add_filter( 'post_type_link', $link_filter, 10, 4 );

					/**
					 * Filters the link to the "all" recurrences view for a recurring event.
					 *
					 * @param string $event_url The link to the "all" recurrences view for the event
					 * @param int $event_id The recurring event post ID
					 */
					$event_url = apply_filters( 'tribe_events_pro_get_all_link', $event_url, $event_id );
					break;
				default:
					break;
			}

			return apply_filters( 'tribe_events_pro_get_link', $event_url, $type, $secondary, $term );
		}

		/**
		 * When showing All events for a recurring event, override the default link.
		 *
		 * @deprecated 6.0.0
		 *
		 * @param string $link Current page link
		 *
		 * @return string Recurrence compatible current page link
		 */
		public function get_all_link( $link ) {
			_deprecated_function( __METHOD__, '6.0.0' );

			if ( ! tribe_is_showing_all() && ! isset( $_POST['tribe_post_parent'] ) ) {
				return $link;
			}

			return $this->get_link( null, 'all', null, null );
		}

		/**
		 * When showing All events for a recurring event, override the default directional link to
		 * view "all" rather than "list"
		 *
		 * @deprecated 6.0.0
		 *
		 * @param string $link Current page link
		 *
		 * @return string Recurrence compatible current page link
		 */
		public function get_all_dir_link( $link ) {
			_deprecated_function( __METHOD__, '6.0.0' );

			if ( ! tribe_is_showing_all() && ! isset( $_POST['tribe_post_parent'] ) ) {
				return $link;
			}

			$link = preg_replace( '#tribe_event_display=list#', 'tribe_event_display=all', $link );

			return $link;
		}

		/**
		 * If an ajax request has come in with tribe_post_parent, make sure we limit results
		 * to by post_parent
		 *
		 * @deprecated 6.0.0
		 *
		 * @param array $args Arguments for fetching events on the listview template
		 * @param array $posted_data POST data from listview ajax request
		 *
		 * @return array
		 */
		public function override_listview_get_event_args( $args, $posted_data ) {
			_deprecated_function( __METHOD__, '6.0.0' );
			return $args;
		}

		/**
		 * overrides the "displaying" setting of the Tribe__Events__Main instance if we are displaying
		 * "all" recurring events"
		 *
		 * @deprecated 6.0.0
		 *
		 * @param string $displaying The current eventDisplay value
		 * @param array $args get_event args used to fetch events that are visible in the ajax rendered listview
		 *
		 * @return string
		 */
		public function override_listview_display_setting( $displaying, $args ) {
			_deprecated_function( __METHOD__, '6.0.0' );
			return $displaying;
		}

		/**
		 * Add week view to the views selector in the tribe events bar.
		 *
		 * @deprecated 6.0.0
		 *
		 * @param array $views The current array of views registered to the tribe bar.
		 *
		 * @return array The views registered with week view added.
		 */
		public function setup_weekview_in_bar( $views ) {
			_deprecated_function( __METHOD__, '6.0.0' );
			return $views;
		}

		/**
		 * Add photo view to the views selector in the tribe events bar.
		 *
		 * @deprecated 6.0.0
		 *
		 * @param array $views The current array of views registered to the tribe bar.
		 *
		 * @return array The views registered with photo view added.
		 */
		public function setup_photoview_in_bar( $views ) {
			_deprecated_function( __METHOD__, '6.0.0' );
			return $views;
		}

		/**
		 * Change the datepicker label, depending on what view the user is on.
		 *
		 * @deprecated 6.0.0
		 *
		 * @param string $caption The current caption for the datepicker.
		 *
		 * @return string The new caption.
		 */
		public function setup_datepicker_label ( $caption ) {
			_deprecated_function( __METHOD__, '6.0.0' );
			return $caption;
		}

		/**
		 * Echo the setting for hiding subsequent occurrences of recurring events to frontend.
		 * Old function name contained a typo ("occurance") - this fixes it
		 * without breaking anything where users may be calling the old function.
		 *
		 * @deprecated 6.0.0
		 */
		public function add_recurring_occurance_setting_to_list () {
			_deprecated_function( __METHOD__, '6.0.0' );
			return $this->add_recurring_occurrence_setting_to_list();
		}

		/**
		 * @deprecated 6.0.0
		 */
		public function add_recurring_occurrence_setting_to_list() {
			_deprecated_function( __METHOD__, '6.0.0' );
		}

		/**
		 * Returns he ratio of kilometers to miles.
		 *
		 * @return float The ratio.
		 */
		public function kms_to_miles_ratio() {
			return 0.621371;
		}

		/**
		 * Returns he ratio of miles to kilometers.
		 *
		 * @return float The ratio.
		 */
		public function miles_to_kms_ratio() {
			return 1.60934;
		}

		/**
		 * Instances the filters.
		 */
		public function init_apm_filters() {
			new Tribe__Events__Pro__APM_Filters__APM_Filters( );
			new Tribe__Events__Pro__APM_Filters__Date_Filter( );
			new Tribe__Events__Pro__APM_Filters__Recur_Filter( );
			new Tribe__Events__Pro__APM_Filters__Content_Filter( );
			new Tribe__Events__Pro__APM_Filters__Title_Filter( );
			new Tribe__Events__Pro__APM_Filters__Venue_Filter( );
			new Tribe__Events__Pro__APM_Filters__Organizer_Filter( );

			/**
			 * Fires after APM filters have been instantiated.
			 *
			 * This is the action additional filters defining should hook to instantiate those filters.
			 *
			 * @since 4.1
			 */
			do_action( 'tribe_events_pro_init_apm_filters' );
		}

		/**
		 * Registers The Events Calendar with the views/overrides update checker.
		 *
		 * @param array $plugins
		 *
		 * @return array
		 */
		public function register_template_updates( $plugins ) {
			$plugins[ __( 'Events Calendar PRO', 'tribe-events-calendar-pro' ) ] = array(
				self::VERSION,
				$this->pluginPath . 'src/views/pro',
				trailingslashit( get_stylesheet_directory() ) . 'tribe-events/pro',
			);

			return $plugins;
		}

		/**
		 * plugin deactivation callback
		 * @see register_deactivation_hook()
		 *
		 * @param bool $network_deactivating
		 */
		public static function deactivate( $network_deactivating ) {
			if ( ! class_exists( 'Tribe__Events__Main' ) ) {
				return; // can't do anything since core isn't around
			}
			$deactivation = new Tribe__Events__Pro__Deactivation( $network_deactivating );
			add_action( 'shutdown', array( $deactivation, 'deactivate' ) );
		}

		/**
		 * The singleton function.
		 *
		 * @return Tribe__Events__Pro__Main The instance.
		 */
		public static function instance() {
			if ( ! isset( static::$instance ) ) {
				static::$instance = new static;
			}

			return static::$instance;
		}

		/**
		 * Outputs oembed resource links on the /all/ pages for recurring events
		 *
		 * @since 4.2
		 *
		 * @param string $output Resource links to output
		 *
		 * @return string
		 */
		public function oembed_discovery_links_for_recurring_events( $output ) {
			$wp_query = tribe_get_global_query_object();

			if ( $output ) {
				return $output;
			}

			if ( ! tribe_is_showing_all() ) {
				return $output;
			}

			if ( is_null( $wp_query ) || empty( $wp_query->posts[0] ) ) {
				return $output;
			}

			$post = $wp_query->posts[0];
			$post_id = $post->ID;

			$output = '<link rel="alternate" type="application/json+oembed" href="' . esc_url( get_oembed_endpoint_url( add_query_arg( 'post_id', $post_id, get_permalink( $post_id ) ) ) ) . '" />' . "\n";

			if ( class_exists( 'SimpleXMLElement' ) ) {
				$output .= '<link rel="alternate" type="text/xml+oembed" href="' . esc_url( get_oembed_endpoint_url( add_query_arg( 'post_id', $post_id, get_permalink( $post_id ) ), 'xml' ) ) . '" />' . "\n";
			}

			return $output;
		}

		/**
		 * Convert a /all/ URL to an upcoming post id for oembeds
		 *
		 * @since 4.2
		 *
		 * @param int $post_id Post ID of the event
		 * @param string $url URL of the oembed resource
		 *
		 * @return int
		 */
		public function oembed_request_post_id_for_recurring_events( $post_id, $url ) {
			if ( $post_id ) {
				return $post_id;
			}

			$recurring_event_id = tribe_get_upcoming_recurring_event_id_from_url( $url );
			if ( $recurring_event_id ) {
				return $recurring_event_id;
			}

			// we weren't able to find something better, so return the original value
			return $post_id;
		}

		/**
		 * Instances all classes that should be built at `plugins_loaded` time.
		 *
		 * Classes are bound using the `tribe_singleton` function before and then
		 * built calling the `tribe` function.
		 */
		public function on_plugins_loaded() {
			$this->all_slug = sanitize_title( __( 'all', 'tribe-events-calendar-pro' ) );
			$this->weekSlug = sanitize_title( __( 'week', 'tribe-events-calendar-pro' ) );
			$this->photoSlug = sanitize_title( __( 'photo', 'tribe-events-calendar-pro' ) );

			tribe_singleton( 'events-pro.main', $this );

			// Assets loader
			tribe_singleton( 'events-pro.assets', 'Tribe__Events__Pro__Assets', array( 'register' ) );
			tribe_singleton( 'events-pro.admin.settings', 'Tribe__Events__Pro__Admin__Settings', array( 'hook' ) );

			if ( ! tribe_events_views_v2_is_enabled() ) {
				tribe_singleton( 'events-pro.customizer.photo-view', 'Tribe__Events__Pro__Customizer__Photo_View' );
				tribe( 'events-pro.customizer.photo-view' );
			}
			tribe_singleton( 'events-pro.ical', 'Tribe__Events__Pro__iCal', [ 'hook' ] );
			tribe_register_provider( 'Tribe__Events__Pro__Editor__Provider' );
			tribe_register_provider( 'Tribe__Events__Pro__Service_Providers__RBE' );
			tribe_singleton( Tribe__Events__Pro__Geo_Loc::class, Tribe__Events__Pro__Geo_Loc::instance() );
			tribe_register_provider( 'Tribe__Events__Pro__Service_Providers__ORM' );

			tribe_register_provider( Tribe\Events\Pro\Views\V2\Service_Provider::class );
			tribe_register_provider( Tribe\Events\Pro\Models\Service_Provider::class );
			tribe_register_provider( Tribe__Events__Pro__Service_Providers__Templates::class );

			// Rewrite support.
			tribe_register_provider( Tribe\Events\Pro\Rewrite\Provider::class );

			// Context support.
			tribe_register_provider( Tribe\Events\Pro\Service_Providers\Context::class );

			tribe_register_provider( Tribe\Events\Pro\Admin\Manager\Provider::class );

			// Custom tables v1 implementation.
			if ( class_exists( '\\TEC\\Events_Pro\\Custom_Tables\\V1\\Provider' ) ) {
				tribe_register_provider( '\\TEC\\Events_Pro\\Custom_Tables\\V1\\Provider' );
			}

			// Set up Site Health
			tribe_register_provider( TEC\Events_Pro\Site_Health\Provider::class );
			// Set up Telemetry
			tribe_register_provider( TEC\Events_Pro\Telemetry\Provider::class );

			tribe_register_provider( TEC\Events_Pro\Linked_Posts\Controller::class );

			if ( class_exists( Zapier_Provider::class ) ) {
				tribe_register_provider( Zapier_Provider::class );
			}

			tribe( 'events-pro.admin.settings' );
			tribe( 'events-pro.ical' );
			tribe( 'events-pro.assets' );
		}

		/**
		 * Add legacy stylesheets to customizer styles array to check.
		 *
		 * @param array<string> $sheets Array of sheets to search for.
		 * @param string        $css_template String containing the inline css to add.
		 *
		 * @return array Modified array of sheets to search for.
		 */
		public function customizer_inline_stylesheets( $sheets, $css_template ) {
			$pro_sheets = [
				'tribe-events-calendar-pro-style',
				'widget-calendar-pro-style',
			];

			return array_merge( $sheets, $pro_sheets );
		}

		/**
		 * Enqueue the dependency on any block editor page since because of widgets we might have special
		 * needs for these pages.
		 *
		 * @since 5.12.0
		 * @deprecated 6.0.0 Moved to the assets class.
		 */
		public function enqueue_dependencies() {
			_deprecated_function( __METHOD__, '6.0.0', 'Tribe__Events__Pro__Assets::enqueue_dependencies' );
		}

		/**
		 * Maps the request post name and ID to a recurring Event child post.
		 *
		 * @since 4.2
		 * @since 6.0.2.1 Open the method to public, redirect to Legacy Query Filters.
		 *
		 * @param WP_Query $query The WP_Query object reference.
		 *
		 * @return void The query object is modified by reference.
		 */
		public function set_post_id_for_recurring_event_query( $query ): void {
			tribe( Legacy_Query_Filters::class )->set_post_id_for_recurring_event_query( $query );
		}
	}
}
