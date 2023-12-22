<?php

namespace ForGravity\Fillable_PDFs;

use Exception;

use ForGravity\Fillable_PDFs\Utils\File_Path;
use ForGravity\Fillable_PDFs\Metaboxes\Documents as Metabox_Documents;

use WP_Error;

use GFAPI;
use GFCommon;
use GFEntryDetail;
use GFExport;
use GFFeedAddOn;
use GFForms;
use GFFormsModel;
use Gravity_Forms\Gravity_Forms\Settings\Fields as Settings_API_Fields;
use Gravity_Forms\Gravity_Forms\Settings\Settings as Settings_API;

use GFChart_API;

GFForms::include_feed_addon_framework();

/**
 * Fillable PDFs for Gravity Forms.
 *
 * @since     1.0
 * @package   FillablePDFs
 * @author    ForGravity
 * @copyright Copyright (c) 2017, ForGravity
 */
class Fillable_PDFs extends GFFeedAddOn {

	/**
	 * Contains an instance of this class, if available.
	 *
	 * @since  1.0
	 * @access private
	 * @var    object $_instance If available, contains an instance of this class.
	 */
	protected static $_instance = null;

	/**
	 * Defines the version of the Fillable PDFs for Gravity Forms.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_version Contains the version, defined from fillablepdfs.php
	 */
	protected $_version = FG_FILLABLEPDFS_VERSION;

	/**
	 * Defines the minimum Gravity Forms version required.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_min_gravityforms_version The minimum version required.
	 */
	protected $_min_gravityforms_version = '2.0.7.7';

	/**
	 * Defines the plugin slug.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_slug The slug used for this plugin.
	 */
	protected $_slug = 'forgravity-fillablepdfs';

	/**
	 * Defines the main plugin file.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_path The path to the main plugin file, relative to the plugins folder.
	 */
	protected $_path = 'forgravity-fillablepdfs/fillablepdfs.php';

	/**
	 * Defines the full path to this class file.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_full_path The full path.
	 */
	protected $_full_path = __FILE__;

	/**
	 * Defines the URL where this Add-On can be found.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string The URL of the Add-On.
	 */
	protected $_url = 'https://forgravity.com';

	/**
	 * Defines the title of this Add-On.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_title The title of the Add-On.
	 */
	protected $_title = 'Fillable PDFs for Gravity Forms';

	/**
	 * Defines the short title of the Add-On.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_short_title The short title.
	 */
	protected $_short_title = 'Fillable PDFs';

	/**
	 * Defines the capability needed to access the Add-On settings page.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_capabilities_settings_page The capability needed to access the Add-On settings page.
	 */
	protected $_capabilities_settings_page = 'forgravity_fillablepdfs';

	/**
	 * Defines the capability needed to access the Add-On settings page.
	 *
	 * @since  2.2.3
	 * @access protected
	 * @var    string $_capabilities_settings_page The capability needed to access the Add-On settings page.
	 */
	protected $_capabilities_plugin_page = 'forgravity_fillablepdfs';

	/**
	 * Defines the capability needed to access the Add-On form settings page.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_capabilities_form_settings The capability needed to access the Add-On form settings page.
	 */
	protected $_capabilities_form_settings = 'forgravity_fillablepdfs';

	/**
	 * Defines the capability needed to uninstall the Add-On.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_capabilities_uninstall The capability needed to uninstall the Add-On.
	 */
	protected $_capabilities_uninstall = 'forgravity_fillablepdfs_uninstall';

	/**
	 * Defines the capabilities needed for Fillable PDFs for Gravity Forms.
	 *
	 * @since  1.0
	 * @since  3.0 Added the `forgravity_fillablepdfs_view_generated_pdfs` capability.
	 *
	 * @var    array $_capabilities The capabilities needed for the Add-On
	 */
	protected $_capabilities = [ 'forgravity_fillablepdfs', 'forgravity_fillablepdfs_uninstall', 'forgravity_fillablepdfs_view_generated_pdfs' ];

	/**
	 * Contains an instance of the Fillable PDFs API library, if available.
	 *
	 * @since     1.0
	 * @depecated 3.4 Use fg_pdfs_api().
	 * @var       API $api If available, contains an instance of the Fillable PDFs API library.
	 */
	public $api = null;

	/**
	 * The Merge Tags utility object.
	 *
	 * @since 3.4
	 *
	 * @var Utils\Merge_Tags
	 */
	protected $merge_tags;

	/**
	 * The Blocks object.
	 *
	 * @since 3.4
	 *
	 * @var Blocks
	 */
	protected $blocks;

	/**
	 * Stores the third party Integration instances.
	 *
	 * @since 4.0
	 *
	 * @var Integrations\Base[]
	 */
	protected $integrations = [];

	/**
	 * Stores the PDFs Server instance.
	 *
	 * @since 4.4
	 *
	 * @var Server
	 */
	protected $server;

	/**
	 * Get instance of this class.
	 *
	 * @since  1.0
	 * @static
	 *
	 * @return Fillable_PDFs
	 */
	public static function get_instance() {

		if ( null === self::$_instance ) {

			// Initialize Add-On.
			$instance             = new self();
			$instance->merge_tags = new Utils\Merge_Tags();
			$instance->blocks     = new Blocks();
			$instance->server     = new Server();

			$instance->integrations['dropbox']     = new Integrations\Dropbox();
			$instance->integrations['googledrive'] = new Integrations\GoogleDrive();

			// Assign instance.
			self::$_instance = $instance;
		}

		return self::$_instance;

	}

	/**
	 * Get all or a specific capability for Add-On.
	 *
	 * @since  5.0
	 *
	 * @param string $capability Capability to return.
	 *
	 * @return string|array
	 */
	public function get_capabilities( $capability = '' ) {

		if ( $capability === 'view_pdf' ) {

			/**
			 * WordPress user capability required to view all PDFs.
			 *
			 * @since 2.3
			 * @since 5.0 Updated to use settings_page and "forgravity_fillablepdfs_view_generated_pdfs" capabilities.
			 *
			 * @param string|array $capability Capability required to view all PDFs.
			 */
			return fg_pdfs_apply_filters( 'view_pdf_capabilities', [
				$this->_capabilities_settings_page,
				'forgravity_fillablepdfs_view_generated_pdfs',
			] );

		}

		return parent::get_capabilities( $capability );

	}

	/**
	 * Register needed hooks.
	 *
	 * @since 2.3
	 */
	public function pre_init() {

		parent::pre_init();

		// Import and Export Feeds.
		add_filter( 'gform_export_form', [ $this, 'filter_gform_export_form' ] );
		add_action( 'gform_forms_post_import', [ $this, 'action_gform_forms_post_import' ] );

		// Add cron action.
		add_action( FG_FILLABLEPDFS_PATH_CHECK_ACTION, [ $this, 'check_base_pdf_path_public' ] );
		add_filter( 'cron_schedules', [ $this, 'filter_cron_schedules' ] );

		// Schedule action.
		if ( ! wp_next_scheduled( FG_FILLABLEPDFS_PATH_CHECK_ACTION ) ) {
			wp_schedule_event( current_time( 'timestamp' ), 'fg_fillablepdfs_weekly', FG_FILLABLEPDFS_PATH_CHECK_ACTION );
		}

		add_action( 'update_site_option_auto_update_plugins', [ $this, 'action_update_site_option_auto_update_plugins' ], 10, 3 );

		// Run delayed Fillable PDFs feeds immediately after payment is processed.
		add_action( 'gform_post_payment_action', [ $this, 'action_gform_post_payment_action' ], 10, 2 );

	}

	/**
	 * Register needed hooks.
	 *
	 * @since  1.0
	 */
	public function init() {

		// Add filters before Plugin/Feed Settings are initialized.
		foreach ( $this->integrations as $integration ) {
			$integration->add_hooks();
		}

		parent::init();

		$this->merge_tags->add_hooks();
		$this->server->add_hooks();

		$this->blocks->register();

		// Add delayed payment support.
		$this->add_delayed_payment_support(
			[
				'option_label' => esc_html__( 'Generate PDF only when payment is received', 'forgravity_fillablepdfs' ),
			]
		);

		add_filter( 'gform_notification', [ $this, 'attach_generated_pdfs' ], 10, 3 );
		add_filter( 'gform_entry_detail_meta_boxes', [ $this, 'register_metaboxes' ], 10, 3 );

		add_action( 'gform_delete_entry', [ $this, 'delete_entry_pdfs' ] );

		add_filter( 'gform_entry_list_bulk_actions', [ $this, 'filter_gform_entry_list_bulk_actions' ], 10, 2 );
		add_action( 'gform_entry_list_action_fillablepdfs', [ $this, 'action_gform_entry_list_action_fillablepdfs' ], 10, 3 );

		add_action( 'admin_init', [ $this, 'maybe_regenerate_pdfs' ] );

		add_filter( 'gform_personal_data', [ $this, 'filter_gform_personal_data' ], 10, 2 );

		add_filter( 'auto_update_plugin', [ $this, 'filter_auto_update_plugin' ], 10, 2 );

		// Remove deleted fields from mapping.
		add_action( 'gform_after_delete_field', [ $this, 'action_gform_after_delete_field' ], 10, 2 );

		// Regenerate PDFs after updating entry.
		add_action( 'gform_after_update_entry', [ $this, 'action_gform_after_update_entry' ], 10, 2 );

	}

	/**
	 * Register needed admin hooks.
	 *
	 * @since  1.0
	 */
	public function init_admin() {

		parent::init_admin();

		remove_action( 'after_plugin_row_' . $this->get_path(), array( $this, 'plugin_row' ), 10, 2 );

		// Add plugin row message.
		if ( isset( $this->_min_gravityforms_version ) && RG_CURRENT_PAGE == 'plugins.php' && false === $this->_enable_rg_autoupgrade ) {
			add_action( 'after_plugin_row_' . $this->_path, [ $this, 'action_after_plugin_row' ], 10 );
		}

		// If current user can access plugin settings, add settings page to plugin action links.
		if ( $this->current_user_can_any( $this->_capabilities_settings_page ) ) {
			add_filter( 'plugin_action_links', [ $this, 'plugin_settings_link' ], 10, 2 );
		}

		if ( $this->is_gravityforms_supported( '2.5-dev-1' ) ) {
			add_action( 'admin_init', [ fg_fillablepdfs_templates(), 'initialize' ] );
		}

		add_filter( 'gform_system_report', [ $this, 'filter_gform_system_report' ] );

		// Register Templates screen option hooks.
		add_action( 'load-forms_page_forgravity-fillablepdfs', [ 'ForGravity\Fillable_PDFs\Templates', 'action_load_Forms_page_forgravity_fillablepdfs' ] );
		add_filter( 'set-screen-option', [ 'ForGravity\Fillable_PDFs\Templates', 'filter_set_screen_option_fg_fillablepdfs_templates_per_page' ], 10, 3 );
		add_filter( 'set_screen_option_fg_fillablepdfs_templates_per_page', [ 'ForGravity\Fillable_PDFs\Templates', 'filter_set_screen_option_fg_fillablepdfs_templates_per_page' ], 10, 3 );

	}

	/**
	 * Register needed AJAX hooks.
	 *
	 * @since 3.0
	 */
	public function init_ajax() {

		parent::init_ajax();

		if ( ! $this->is_gravityforms_supported( '2.5-beta-1' ) ) {
			return;
		}

		// Initialize Settings API fields.
		require_once( GFCommon::get_base_path() . '/includes/settings/class-fields.php' );

		add_action( 'wp_ajax_fg_fillablepdfs_metabox_delete', [ 'ForGravity\Fillable_PDFs\Metaboxes\Documents', 'ajax_delete' ] );
		add_action( 'wp_ajax_fg_fillablepdfs_get_feed_template', [ 'ForGravity\Fillable_PDFs\Settings\Fields\Feed_Template', 'ajax_get_template' ] );
		add_action( 'wp_ajax_fg_fillablepdfs_get_nested_form', [ 'ForGravity\Fillable_PDFs\Settings\Fields\Feed_Template', 'ajax_get_nested_form' ] );

	}

	/**
	 * Enqueue needed scripts.
	 *
	 * @since  1.0
	 *
	 * @return array
	 */
	public function scripts() {

		// Get minification string.
		$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || isset( $_GET['gform_debug'] ) ? '' : '.min';

		$scripts = [
			[
				'handle'    => 'fg_fillablepdfs_admin',
				'src'       => $this->get_asset_url( "dist/js/admin{$min}.js" ),
				'version'   => $min ? $this->_version : $this->get_asset_filemtime( 'dist/js/admin.js' ),
				'in_footer' => true,
				'enqueue'   => [
					[
						'admin_page' => [ 'form_settings', 'plugin_page' ],
						'tab'        => $this->get_slug(),
					],
				],
				'strings'   => [
					'illegal_file_type' => esc_html__( 'You must upload a valid PDF file.', 'forgravity_fillablepdfs' ),
					'too_many_files'    => esc_html__( 'You can only upload one file.', 'forgravity_fillablepdfs' ),
				],
			],
			[
				'handle'    => 'fg_fillablepdfs_feed_settings',
				'src'       => $this->get_asset_url( "dist/js/feed-settings{$min}.js" ),
				'version'   => $min ? $this->_version : $this->get_asset_filemtime( 'dist/js/feed-settings.js' ),
				'deps'      => [ 'jquery' ],
				'in_footer' => true,
				'enqueue'   => [
					[
						'admin_page' => [ 'form_settings' ],
						'tab'        => $this->get_slug(),
					],
				],
				'strings'   => [
					'ownerPassword' => [
						'noUserPassword'              => esc_html__( 'You must set an Owner Password when setting a User Password.', 'forgravity_fillablepdfs' ),
						'noPermissions'               => esc_html__( 'You must set an Owner Password when setting File Permissions.', 'forgravity_fillablepdfs' ),
						'noUserPasswordOrPermissions' => esc_html__( 'You must set an Owner Password when setting a User Password and File Permissions.', 'forgravity_fillablepdfs' ),
					],
				],
			],
			[
				'handle'    => 'fg_fillablepdfs_integrations',
				'src'       => $this->get_asset_url( "dist/js/integrations{$min}.js" ),
				'version'   => $min ? $this->_version : $this->get_asset_filemtime( 'dist/js/integrations.js' ),
				'in_footer' => true,
				'enqueue'   => [
					[
						'admin_page' => [ 'plugin_page' ],
						'tab'        => $this->get_slug(),
					],
				],
				'strings'   => [
					'nonce' => wp_create_nonce( $this->get_slug() ),
				],
			],
			[
				'handle'  => 'fg_fillablepdfs_metabox',
				'src'     => $this->get_asset_url( "dist/js/metabox{$min}.js" ),
				'version' => $min ? $this->_version : $this->get_asset_filemtime( 'dist/js/metabox.js' ),
				'deps'    => [ 'wp-i18n' ],
				'enqueue' => [ [ 'admin_page' => [ 'entry_view' ] ] ],
			],
			[
				'handle'  => 'fg_fillablepdfs_import',
				'src'     => $this->get_asset_url( "dist/js/import{$min}.js" ),
				'version' => $min ? $this->_version : $this->get_asset_filemtime( 'dist/js/import.js' ),
				'deps'    => [ 'wp-element', 'wp-components' ],
				'enqueue' => [
					[ 'query' => 'page=' . $this->get_slug() . '&subview=import' ],
				],
				'strings' => [
					'choices'        => [
						'buttonImage'  => $this->get_asset_url( 'dist/images/import/choices.svg' ),
						'modalTitle'   => esc_html__( 'Define Field Choices', 'forgravity_fillablepdfs' ),
						'modalButtons' => [
							'save'   => esc_html__( 'Save Choices', 'forgravity_fillablepdfs' ),
							'cancel' => esc_html__( 'Cancel', 'forgravity_fillablepdfs' ),
						],
					],
					'choices_fields' => Import::get_fields_with_choices(),
					'field_groups'   => Import::get_field_groups(),
				],
			],
		];

		return array_merge( parent::scripts(), $scripts );

	}

	/**
	 * Enqueue needed stylesheets.
	 *
	 * @since  1.0
	 *
	 * @return array
	 */
	public function styles() {

		// Get minification string.
		$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || isset( $_GET['gform_debug'] ) ? '' : '.min';

		// Prepare stylesheets.
		$styles = [
			[
				'handle'  => 'fg_fillablepdfs_admin',
				'src'     => $this->get_asset_url( "dist/css/admin{$min}.css" ),
				'version' => $min ? $this->_version : $this->get_asset_filemtime( 'dist/css/admin.css' ),
				'enqueue' => [
					[
						'admin_page' => [ 'form_settings', 'plugin_page' ],
						'tab'        => $this->get_slug(),
					],
					[
						'query' => 'page=gf_export&view=import_pdf',
					],
				],
			],
			[
				'handle'  => 'fg_fillablepdfs_integrations',
				'src'     => $this->get_asset_url( "dist/css/integrations{$min}.css" ),
				'version' => $min ? $this->_version : $this->get_asset_filemtime( 'dist/css/integrations.css' ),
				'enqueue' => [
					[
						'admin_page' => [ 'form_settings', 'plugin_page' ],
						'tab'        => $this->get_slug(),
					],
				],
			],
			[
				'handle'  => 'fg_fillablepdfs_metabox',
				'src'     => $this->get_asset_url( "dist/css/metabox{$min}.css" ),
				'version' => $min ? $this->_version : $this->get_asset_filemtime( 'dist/css/metabox.css' ),
				'enqueue' => [ [ 'admin_page' => [ 'entry_view', 'entry_edit' ] ] ],
			],
			[
				'handle'  => 'fg_fillablepdfs_import',
				'src'     => $this->get_asset_url( "/dist/css/import{$min}.css" ),
				'version' => $min ? $this->_version : $this->get_asset_filemtime( 'dist/css/import.css' ),
				'enqueue' => [
					[ 'query' => 'page=' . $this->get_slug() . '&subview=import' ],
				],
			],
			[
				'handle'  => 'forgravity_dashicons',
				'src'     => $this->get_base_url() . '/dist/css/dashicons.css',
				'version' => $this->get_version(),
				'enqueue' => [
					[ 'query' => 'page=roles&action=edit' ],
				],
			],
		];

		return array_merge( parent::styles(), $styles );

	}

	/**
	 * Remove a script from registered scripts.
	 *
	 * @since 3.4
	 *
	 * @param string $handle  Script handle.
	 * @param array  $scripts Registered scripts.
	 */
	public function remove_script( $handle, &$scripts ) {

		$index = array_search( $handle, wp_list_pluck( $scripts, 'handle' ) );

		if ( $index ) {
			unset( $scripts[ $index ] );
		}

	}

	/**
	 * Remove a style from registered styles.
	 *
	 * @since 3.4
	 *
	 * @param string $handle Style handle.
	 * @param array  $styles Registered styles.
	 */
	public function remove_style( $handle, &$styles ) {

		$this->remove_script( $handle, $styles );

	}

	/**
	 * Remove scheduled events on uninstall.
	 *
	 * @since 2.3
	 */
	public function uninstall() {

		self::clear_scheduled_events();

		parent::uninstall();

	}





	// # PLUGIN PAGE ---------------------------------------------------------------------------------------------------

	/**
	 * Initialize plugin page.
	 * Displays plugin settings and template management.
	 *
	 * @since  1.0
	 */
	public function plugin_page() {

		// Display plugin page header.
		$this->plugin_page_header();

		// Get current subview.
		$subview = $this->get_current_subview( false );

		// Display current subview if exists.
		if ( is_callable( $subview['callback'] ) ) {
			call_user_func( $subview['callback'] );
		} else {
			die( esc_html__( 'This subview does not exist.', 'forgravity_fillablepdfs' ) );
		}

		// Display plugin page footer.
		$this->plugin_page_footer();

	}

	/**
	 * Remove plugin page container for Gravity Forms 2.5.
	 *
	 * @since 2.5
	 */
	public function plugin_page_container() {

		$this->plugin_page();

	}

	/**
	 * Plugin page footer.
	 *
	 * @since  1.0
	 */
	public function plugin_page_footer() {

		$this->app_tab_page_footer();

	}

	/**
	 * Get current plugin page subview.
	 *
	 * @since  1.0
	 *
	 * @param bool $return_name Return only subview name.
	 *
	 * @return string|array|bool
	 */
	public function get_current_subview( $return_name = true ) {

		// Get subviews.
		$subviews = $this->get_subviews();

		// Get current subview.
		$current_subview = rgempty( 'subview', $_GET ) ? $subviews[0]['name'] : rgget( 'subview' );

		// If returning name, return.
		if ( $return_name ) {
			return $current_subview;
		}

		// Loop through subviews.
		foreach ( $subviews as $subview ) {

			// If this is the current subview, return.
			if ( $current_subview === $subview['name'] ) {
				return $subview;
			}

		}

		return false;

	}

	/**
	 * Get plugin page subviews.
	 *
	 * @since  1.0
	 * @since  3.3 Updated to use get_svg_content().
	 *
	 * @return array
	 */
	public function get_subviews() {

		// Initialize subviews.
		$subviews = [
			[
				'name'     => 'settings',
				'icon'     => $this->get_svg_content( '/dist/images/menu/settings.svg' ),
				'label'    => esc_html__( 'Settings', 'forgravity_fillablepdfs' ),
				'callback' => [ $this, 'settings_page' ],
			],
		];

		// If API is not initialized, return.
		if ( ! fg_pdfs_api() ) {
			return $subviews;
		}

		// Add additional subviews.
		$subviews[] = [
			'name'     => 'templates',
			'icon'     => $this->get_svg_content( '/dist/images/menu/templates.svg' ),
			'label'    => esc_html__( 'Templates', 'forgravity_fillablepdfs' ),
			'callback' => [ fg_fillablepdfs_templates(), 'templates_page' ],
		];
		$subviews[] = [
			'name'     => 'import',
			'icon'     => $this->get_svg_content( '/dist/images/menu/import.svg' ),
			'label'    => esc_html__( 'Import PDFs', 'forgravity_fillablepdfs' ),
			'callback' => [ fg_fillablepdfs_import(), 'import_page' ],
		];

		return $subviews;

	}





	// # PLUGIN SETTINGS -----------------------------------------------------------------------------------------------

	/**
	 * Prevent plugin settings page from appearing on Gravity Forms settings page.
	 *
	 * @since  1.0
	 */
	public function plugin_settings_init() {

		// If this is not the plugin settings page, exit.
		if ( rgget( 'page' ) !== $this->_slug && rgget( 'subview' ) !== 'settings' ) {
			return;
		}

		// Get fields.
		$sections = $this->plugin_settings_fields();
		$sections = $this->prepare_settings_sections( $sections, 'plugin_settings' );

		// Initialize new settings renderer.
		$renderer = new Settings_API(
			[
				'capability'     => $this->_capabilities_settings_page,
				'fields'         => $sections,
				'initial_values' => $this->get_plugin_settings(),
				'save_callback'  => [ $this, 'update_plugin_settings' ],
			]
		);

		// Save renderer to instance.
		$this->set_settings_renderer( $renderer );

		if ( $this->get_settings_renderer()->is_save_postback() && rgget( 'page' ) === $this->_slug && ( ! rgget( 'subview' ) || rgget( 'subview' ) === 'settings' ) ) {
			$this->get_settings_renderer()->process_postback();
		}

	}

	/**
	 * Get the plugin settings fields. Fallback for Gravity Forms 2.4
	 *
	 * @since 2.4
	 *
	 * @return array
	 */
	public function plugin_settings_fields() {

		require_once( GFCommon::get_base_path() . '/includes/settings/class-fields.php' );

		Settings_API_Fields::register( 'fg_fillablepdfs_license_features', '\ForGravity\Fillable_PDFs\Settings\Fields\License_Features' );
		Settings_API_Fields::register( 'fg_fillablepdfs_license_meta', '\ForGravity\Fillable_PDFs\Settings\Fields\License_Meta' );

		$settings = [
			[
				'id'       => 'fillablepdfs-license',
				'title'    => esc_html__( 'License', 'forgravity_fillablepdfs' ),
				'sections' => [
					[
						'title'  => esc_html__( 'Fillable PDFs Settings', 'forgravity_fillablepdfs' ),
						'fields' => [
							[
								'name'                => 'license_key',
								'label'               => esc_html__( 'License Key', 'forgravity_fillablepdfs' ),
								'type'                => 'text',
								'class'               => 'medium',
								'default_value'       => '',
								'description'         => esc_html__( 'The license key is used for access to PDF generation, automatic upgrades and support.', 'forgravity_fillablepdfs' ),
								'error_message'       => esc_html__( 'Invalid License', 'forgravity_fillablepdfs' ),
								'feedback_callback'   => [ $this, 'license_feedback' ],
								'validation_callback' => [ $this, 'license_validation' ],
							],
							[
								'name'       => 'license_meta',
								'type'       => 'fg_fillablepdfs_license_meta',
								'dependency' => [ $this, 'can_show_license_fields' ],
							],
							[
								'name'          => 'background_updates',
								'label'         => esc_html__( 'Background Updates', 'forgravity_fillablepdfs' ),
								'type'          => 'radio',
								'horizontal'    => true,
								'default_value' => true,
								'choices'       => [
									[
										'label' => esc_html__( 'On', 'forgravity_fillablepdfs' ),
										'value' => true,
									],
									[
										'label' => esc_html__( 'Off', 'forgravity_fillablepdfs' ),
										'value' => false,
									],
								],
							],
							[
								'name'       => 'license_features',
								'label'      => esc_html__( 'Features', 'forgravity_fillablepdfs' ),
								'type'       => 'fg_fillablepdfs_license_features',
								'dependency' => [ $this, 'can_show_license_fields' ],
							],
						],
					],
				],
			],
		];

		$settings = fg_pdfs_apply_filters( 'plugin_settings_fields', $settings );

		return $settings;

	}

	/**
	 * Determines if License Meta and License Features plugin settings fields should be shown.
	 *
	 * @since 3.4
	 *
	 * @return bool
	 */
	public function can_show_license_fields() {

		return is_object( fg_pdfs_api() );

	}

	/**
	 * Get license validity for plugin settings field.
	 *
	 * @since  1.0
	 *
	 * @param string $value Plugin setting value.
	 * @param array  $field Plugin setting field.
	 *
	 * @return null|bool
	 */
	public function license_feedback( $value, $field ) {

		// If no license key is provided, return.
		if ( empty( $value ) ) {
			return null;
		}

		// Get license data.
		$license_data = $this->check_license( $value );

		// If no license data was returned or license is invalid, return false.
		if ( empty( $license_data ) || 'invalid' === $license_data->license ) {
			return false;
		} else if ( 'valid' === $license_data->license ) {
			return true;
		}

		return false;

	}

	/**
	 * Activate license on plugin settings save.
	 *
	 * @since  1.0
	 *
	 * @param array  $field         Plugin setting field.
	 * @param string $field_setting Plugin setting value.
	 */
	public function license_validation( $field, $field_setting ) {

		// Get old license.
		$old_license = $this->get_plugin_setting( 'license_key' );

		// If an old license key exists and a new license is being saved, deactivate old license.
		if ( $old_license && $field_setting != $old_license ) {

			// Deactivate license.
			$deactivate_license = $this->process_license_request( 'deactivate_license', $old_license );

			// Log response.
			$this->log_debug( __METHOD__ . '(): Deactivate license: ' . print_r( $deactivate_license, true ) );

		}

		// If field setting is empty, return.
		if ( empty( $field_setting ) ) {
			return;
		}

		// Activate license.
		$this->activate_license( $field_setting );

	}

	/**
	 * Add link to settings to plugin action links.
	 *
	 * @since  1.0
	 *
	 * @param array  $links An array of plugin action links.
	 * @param string $file  Path to the plugin file.
	 *
	 * @return array
	 */
	public function plugin_settings_link( $links, $file ) {

		// If plugin being filtered is not Fillable PDFs, return links.
		if ( $file !== $this->get_path() ) {
			return $links;
		}

		// Prepare settings URL.
		$settings_url = add_query_arg( [ 'page' => $this->get_slug() ], admin_url( 'admin.php?subview=settings' ) );

		// Prepare link.
		$link = sprintf(
			'<a href="%s">%s</a>',
			esc_url( $settings_url ),
			esc_html__( 'Settings', 'forgravity_fillablepdfs' )
		);

		// Add settings page to links.
		array_unshift( $links, $link );

		return $links;

	}

	/**
	 * Display plugin settings page as settings subview.
	 *
	 * @since  1.0
	 */
	public function settings_page() {

		$this->plugin_settings_page();

	}

	/**
	 * Updates the plugin settings with the provided settings.
	 *
	 * @since 3.1
	 *
	 * @param array $settings The settings to be saved.
	 */
	public function update_plugin_settings( $settings ) {

		if ( $this->is_save_postback() ) {

			$previous_settings = $this->get_previous_settings();

			if ( $settings['background_updates'] != $previous_settings['background_updates'] ) {
				$this->update_wp_auto_updates( $settings['background_updates'] );
			}

		}

		parent::update_plugin_settings( $settings );

	}





	// # FEED SETTINGS -------------------------------------------------------------------------------------------------

	/**
	 * Setup fields for feed settings.
	 *
	 * @since  1.0
	 *
	 * @return array
	 */
	public function feed_settings_fields() {

		require_once( GFCommon::get_base_path() . '/includes/settings/class-fields.php' );

		\Gravity_Forms\Gravity_Forms\Settings\Fields::register( 'fg_fillablepdfs_feed_template', '\ForGravity\Fillable_PDFs\Settings\Fields\Feed_Template' );

		$template_dependency = [
			'live'   => true,
			'fields' => [
				[
					'field' => 'templateID',
				],
			],
		];

		return [
			[
				'id'       => 'fillablepdfs-general',
				'title'    => esc_html__( 'General', 'forgravity_fillablepdfs' ),
				'sections' => [
					[
						'fields' => [
							[
								'name'          => 'feedName',
								'type'          => 'text',
								'label'         => esc_html__( 'Feed Name', 'forgravity_fillablepdfs' ),
								'required'      => true,
								'default_value' => $this->get_default_feed_name(),
							],
							[
								'name'          => 'fieldMap',
								'type'          => 'hidden',
								'default_value' => '{}',
							],
							[
								'name'     => 'templateID',
								'type'     => 'fg_fillablepdfs_feed_template',
								'label'    => esc_html__( 'PDF Template', 'forgravity_fillablepdfs' ),
								'required' => true,
								'choices'  => $this->get_templates_as_choices(),
							],
							[
								'name'          => 'fileName',
								'type'          => 'text',
								'label'         => esc_html__( 'Output File Name', 'forgravity_fillablepdfs' ),
								'required'      => true,
								'class'         => 'merge-tag-support mt-position-right mt-hide_all_fields',
								'dependency'    => $template_dependency,
								'default_value' => $this->get_default_file_name(),
							],
							[
								'name'             => 'notifications[]',
								'type'             => 'select',
								'label'            => esc_html__( 'Notifications', 'forgravity_fillablepdfs' ),
								'description'      => esc_html__( 'Select what notifications this generated PDF will be attached to', 'forgravity_fillablepdfs' ),
								'choices'          => $this->get_notifications_as_choices(),
								'multiple'         => true,
								'enhanced_ui'      => true,
								'data-placeholder' => esc_html__( 'Select Notifications', 'forgravity_fillablepdfs' ),
								'dependency'       => $template_dependency,
							],
							[
								'name'           => 'feed_condition',
								'type'           => 'conditional_logic',
								'object_type'    => 'feed_condition',
								'class'          => 'testclass',
								'label'          => esc_html__( 'Conditional Logic', 'forgravity_fillablepdfs' ),
								'checkbox_label' => esc_html__( 'Enable', 'forgravity_fillablepdfs' ),
								'instructions'   => esc_html__( 'Generate PDF if', 'forgravity_fillablepdfs' ),
								'dependency'     => $template_dependency,
							],
						],
					],
				],
			],
			[
				'id'         => 'fillablepdfs-advanced',
				'title'      => esc_html__( 'Advanced Settings', 'forgravity_fillablepdfs' ),
				'dependency' => $template_dependency,
				'sections'   => [
					[
						'fields' => [
							[
								'name'                => 'password',
								'label'               => esc_html__( 'Owner Password', 'forgravity_fillablepdfs' ),
								'type'                => 'text',
								'class'               => 'medium merge-tag-support mt-position-right mt-hide_all_fields',
								'description'         => esc_html__( 'Owner Password is required when setting User Password and/or File Permissions.', 'forgravity_fillablepdfs' ),
								'validation_callback' => [ $this, 'validate_owner_password' ],
							],
							[
								'name'        => 'userPassword',
								'label'       => esc_html__( 'User Password', 'forgravity_fillablepdfs' ),
								'type'        => 'text',
								'class'       => 'medium merge-tag-support mt-position-right mt-hide_all_fields',
								'description' => esc_html__( 'Set password required to view PDF. Requires an Owner Password.', 'forgravity_fillablepdfs' ),
							],
							[
								'name'        => 'filePermissions[]',
								'label'       => esc_html__( 'File Permissions', 'forgravity_fillablepdfs' ),
								'type'        => 'select',
								'multiple'    => true,
								'enhanced_ui' => true,
								'description' => esc_html__( 'Select what users are allowed to do with the generated PDF. Requires an Owner Password.', 'forgravity_fillablepdfs' ),
								'choices'     => [
									[
										'label' => esc_html__( 'Select All', 'forgravity_fillablepdfs' ),
										'value' => '_select_all',
									],
									[
										'label' => esc_html__( 'Print - High Resolution', 'forgravity_fillablepdfs' ),
										'value' => 'Printing',
									],
									[
										'label' => esc_html__( 'Print - Low Resolution', 'forgravity_fillablepdfs' ),
										'value' => 'DegradedPrinting',
									],
									[
										'label' => esc_html__( 'Modify', 'forgravity_fillablepdfs' ),
										'value' => 'ModifyContents',
									],
									[
										'label' => esc_html__( 'Assembly', 'forgravity_fillablepdfs' ),
										'value' => 'Assembly',
									],
									[
										'label' => esc_html__( 'Copy', 'forgravity_fillablepdfs' ),
										'value' => 'CopyContents',
									],
									[
										'label' => esc_html__( 'Screen Reading', 'forgravity_fillablepdfs' ),
										'value' => 'ScreenReaders',
									],
									[
										'label' => esc_html__( 'Annotate', 'forgravity_fillablepdfs' ),
										'value' => 'ModifyAnnotations',
									],
									[
										'label' => esc_html__( 'Fill Forms', 'forgravity_fillablepdfs' ),
										'value' => 'FillIn',
									],
								],
							],
							[
								'name'          => 'publicAccess',
								'label'         => esc_html__( 'Enable Public Access', 'forgravity_fillablepdfs' ),
								'type'          => 'radio',
								'required'      => true,
								'default_value' => '0',
								'description'   => esc_html__( 'Enabling this setting allows anyone to download the generated PDF.', 'forgravity_fillablepdfs' ),
								'horizontal'    => true,
								'choices'       => [
									[
										'value' => '1',
										'label' => esc_html__( 'Enable Public Access', 'forgravity_fillablepdfs' ),
										'icon'  => $this->get_svg_content( '/dist/images/feed/public-access/enable.svg' ),
									],
									[
										'value' => '0',
										'label' => esc_html__( 'Disable Public Access', 'forgravity_fillablepdfs' ),
										'icon'  => $this->get_svg_content( '/dist/images/feed/public-access/disable.svg' ),
									],
								],
							],
							[
								'name'  => 'flatten',
								'label' => esc_html__( 'Remove interactive form fields', 'forgravity_fillablepdfs' ),
								'type'  => 'toggle',
							],
							[
								'name'  => 'regenerateOnEdit',
								'label' => esc_html__( 'Regenerate PDF when entry is edited', 'forgravity_fillablepdfs' ),
								'type'  => 'toggle',
							],
						],
					],
				],
			],
		];

	}

	/**
	 * Get default PDF file name.
	 *
	 * @since  1.0
	 *
	 * @return string
	 */
	public function get_default_file_name() {

		// If API cannot be initialized, return.
		if ( ! fg_pdfs_api() ) {
			return '';
		}

		// Get template setting.
		$template_id = $this->get_setting( 'templateID' );

		// If template is not selected, return.
		if ( ! $template_id ) {
			return '';
		}

		try {

			// Get template.
			$template = fg_pdfs_api()->get_template( $template_id );

		} catch ( Exception $e ) {

			return '';

		}

		return sanitize_file_name( $template['name'] ) . '.pdf';

	}

	/**
	 * Get form notifications as choices for feed settings field.
	 *
	 * @since  1.0
	 *
	 * @return array
	 */
	public function get_notifications_as_choices() {

		// Initialize choices array.
		$choices = [];

		// Get current form.
		$form = $this->get_current_form();

		// If form was not found, return choices.
		if ( ! $form ) {
			return $choices;
		}

		// Loop through notifications.
		foreach ( $form['notifications'] as $notification ) {

			// Add notification as choice.
			$choices[] = [
				'label' => esc_html( $notification['name'] ),
				'value' => esc_attr( $notification['id'] ),
			];

		}

		return $choices;

	}

	/**
	 * Get PDF templates as choices for feed settings field.
	 *
	 * @since  1.0
	 *
	 * @return array
	 */
	public function get_templates_as_choices() {

		static $choices;

		if ( is_array( $choices ) ) {
			return $choices;
		}

		// If API is not initialized, return empty array.
		if ( ! fg_pdfs_api() ) {
			return [];
		}

		try {

			// Get templates.
			$templates = fg_pdfs_api()->get_templates( 1, -1 );

		} catch ( Exception $e ) {

			// Log that templates could not be retrieved.
			$this->log_error( __METHOD__ . '(): Unable to retrieve templates; ' . $e->getMessage() );

			return [];

		}

		// Initialize choices array.
		$choices = [];

		// If template exist, add them as choices.
		if ( $templates ) {

			// Add initial choice.
			$choices[] = [
				'label' => esc_html__( 'Select a Template', 'forgravity_fillablepdfs' ),
				'value' => '0',
			];

			// Loop through templates.
			foreach ( $templates as $template ) {

				// Add template as choice.
				$choices[] = [
					'label' => esc_html( $template['name'] ),
					'value' => esc_attr( $template['template_id'] ),
				];

			}

		}

		return $choices;

	}

	/**
	 * Ensure User Password is set when defining an Owner Password.
	 *
	 * @since 3.3
	 *
	 * @param Settings_API_Fields\Text $field Owner Password field object.
	 * @param string                   $value Submitted field value.
	 */
	public function validate_owner_password( $field, $value ) {

		$user_password = $field->settings->get_value( 'userPassword' );
		$permissions   = $field->settings->get_value( 'filePermissions' );

		if ( empty( $user_password ) && empty( $permissions ) ) {
			return;
		}

		if ( ! empty( $value ) ) {
			return;
		}

		if ( ! empty( $user_password ) && ! empty( $permissions ) ) {
			$field->set_error( esc_html__( 'You must set an Owner Password when setting a User Password and File Permissions.', 'forgravity_fillablepdfs' ) );
		} elseif ( ! empty( $user_password ) ) {
			$field->set_error( esc_html__( 'You must set an Owner Password when setting a User Password.', 'forgravity_fillablepdfs' ) );
		} else {
			$field->set_error( esc_html__( 'You must set an Owner Password when setting File Permissions.', 'forgravity_fillablepdfs' ) );
		}

	}

	/**
	 * Return the plugin's icon for the plugin/form settings menu.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public function get_menu_icon() {

		return $this->get_svg_content( '/dist/images/menu/logo.svg' );

	}





	// # FEED LIST -------------------------------------------------------------------------------------------------

	/**
	 * Displays the feeds for a form.
	 * Caches all templates when enabled via filter.
	 *
	 * @since 3.4
	 *
	 * @param array $form The current Form object.
	 */
	public function feed_list_page( $form = null ) {

		/**
		 * Fetch all templates in a single request when displaying feed list.
		 *
		 * @since 3.4
		 *
		 * @param bool $cache_templates
		 */
		$cache_templates = apply_filters( 'fg_fillablepdfs_feed_list_load_all_templates', false );

		if ( $cache_templates && $this->initialize_api() ) {
			try {
				$this->api->get_templates( 1, -1 );
			} catch ( Exception $e ) { // phpcs:ignore
			}
		}

		parent::feed_list_page( $form );

	}

	/**
	 * Set if feeds can be created.
	 *
	 * @since  1.0
	 *
	 * @return bool
	 */
	public function can_create_feed() {

		return fg_pdfs_api();

	}

	/**
	 * Enable feed duplication.
	 *
	 * @since  1.0
	 *
	 * @param string $id Feed ID requesting duplication.
	 *
	 * @return bool
	 */
	public function can_duplicate_feed( $id ) {

		return true;

	}

	/**
	 * Display message to configure Add-On before setting up feeds.
	 *
	 * @since  1.0
	 *
	 * @return string
	 */
	public function configure_addon_message() {

		$settings_label = sprintf( __( '%s Settings', 'forgravity_fillablepdfs' ), $this->get_short_title() );
		$settings_url   = add_query_arg( [ 'page' => $this->get_slug() ], admin_url( 'admin.php' ) );
		$settings_link  = sprintf( '<a href="%s">%s</a>', esc_url( $settings_url ), $settings_label );

		return sprintf( __( 'To get started, please configure your %s.', 'gravityforms' ), $settings_link );

	}

	/**
	 * Setup columns for feed list table.
	 *
	 * @since  1.0
	 *
	 * @return array
	 */
	public function feed_list_columns() {

		return [
			'feedName' => esc_html__( 'Name', 'forgravity_fillablepdfs' ),
			'template' => esc_html__( 'Template', 'forgravity_fillablepdfs' ),
		];

	}

	/**
	 * Returns the value to be displayed in the template feed list column.
	 *
	 * @since  1.0
	 *
	 * @param array $feed The feed being included in the feed list.
	 *
	 * @return string
	 */
	public function get_column_value_template( $feed ) {

		// If API is not initialized, return template ID.
		if ( ! fg_pdfs_api() || ! rgars( $feed, 'meta/templateID' ) ) {
			return rgars( $feed, 'meta/templateID' );
		}

		try {

			// Get template.
			$template = fg_pdfs_api()->get_template( rgars( $feed, 'meta/templateID' ) );

			return esc_html( $template['name'] );

		} catch ( Exception $e ) {

			// Log that template could not be retrieved.
			$this->log_error( __METHOD__ . '(): Unable to retrieve template; ' . $e->getMessage() );

			return rgars( $feed, 'meta/templateID' );

		}

	}





	// # FORM SUBMISSION -----------------------------------------------------------------------------------------------

	/**
	 * Process feed.
	 *
	 * @since  1.0
	 *
	 * @param array $feed  The Feed object to be processed.
	 * @param array $entry The Entry object currently being processed.
	 * @param array $form  The Form object currently being processed.
	 */
	public function process_feed( $feed, $entry, $form ) {

		// If API could not be initialized, return.
		if ( ! fg_pdfs_api() ) {

			// Add feed error explaining why PDF could not be generated.
			$this->add_feed_error( esc_html__( 'PDF could not be generated because API could not be initialized.', 'forgravity_fillablepdfs' ), $feed, $entry, $form );

			return;

		}

		$generator = new Generator( $feed, $entry, $form );
		$generated = $generator->generate();

		if ( is_wp_error( $generated ) ) {
			$this->add_feed_error( $generated->get_error_message(), $feed, $entry, $form );
		}

	}

	/**
	 * Attach generated PDFs to notifications.
	 *
	 * @since  1.0
	 *
	 * @param array $notification An array of properties which make up a notification object.
	 * @param array $form         The form object for which the notification is being sent.
	 * @param array $entry        The entry object for which the notification is being sent.
	 *
	 * @return array
	 */
	public function attach_generated_pdfs( $notification, $form, $entry ) {

		// Get Fillable PDFs for entry.
		$entry_pdfs = gform_get_meta( $entry['id'], 'fillablepdfs' );

		// If no PDFs were found, return.
		if ( ! $entry_pdfs || empty( $entry_pdfs ) ) {
			return $notification;
		}

		// Initialize notification PDFs array.
		$notification_pdfs = [];

		// Loop through entry PDFs.
		foreach ( $entry_pdfs as $feed_id => $entry_pdf_id ) {

			// Get feed for entry PDF.
			$feed = $this->get_feed( $feed_id );

			// If feed condition is not met, skip.
			if ( ! $this->is_feed_condition_met( $feed, $form, $entry ) ) {
				continue;
			}

			// Get feed notifications.
			$feed_notifications = rgars( $feed, 'meta/notifications' ) ? $feed['meta']['notifications'] : [];

			// If this notification is not setup for this feed, skip.
			if ( ! in_array( $notification['id'], $feed_notifications ) ) {
				continue;
			}

			// Get entry PDF.
			$entry_pdf = gform_get_meta( $entry['id'], 'fillablepdfs_' . $entry_pdf_id );

			// Add PDF to notification PDFs.
			$notification_pdfs[] = fg_fillablepdfs()->get_physical_file_path( $entry_pdf );

		}

		// Initialize attachments array.
		if ( ! isset( $notification['attachments'] ) ) {
			$notification['attachments'] = [];
		}

		// Add attachments.
		$notification['attachments'] = array_merge( $notification['attachments'], $notification_pdfs );

		return $notification;

	}

	/**
	 * Regenerates PDFs after entry has been updated.
	 *
	 * @since 3.0
	 *
	 * @param array $form     Form object.
	 * @param int   $entry_id Entry ID being updated.
	 */
	public function action_gform_after_update_entry( $form, $entry_id ) {

		$feeds = $this->get_feeds( $form['id'] );

		if ( empty( $feeds ) ) {
			return;
		}

		$entry = GFAPI::get_entry( $entry_id );

		foreach ( $feeds as $feed ) {

			if ( ! (bool) rgars( $feed, 'meta/regenerateOnEdit', false ) ) {
				continue;
			}

			if ( ! $this->is_feed_condition_met( $feed, $form, $entry ) ) {
				continue;
			}

			$this->process_feed( $feed, $entry, $form );

		}

	}





	// # ENTRY LIST ----------------------------------------------------------------------------------------------------

	/**
	 * Add Regenerate PDFs to entry list bulk actions.
	 *
	 * @since  2.0
	 *
	 * @param array $actions Bulk actions.
	 * @param int   $form_id The current form ID.
	 *
	 * @return array
	 */
	public function filter_gform_entry_list_bulk_actions( $actions = [], $form_id = 0 ) {

		// Add action.
		$actions['fillablepdfs'] = esc_html__( 'Regenerate PDFs', 'forgravity_fillablepdfs' );

		return $actions;

	}

	/**
	 * Process Fillable PDFs entry list bulk actions.
	 *
	 * @since  1.4.2
	 *
	 * @param string $action  Action being performed.
	 * @param array  $entries The entry IDs the action is being applied to.
	 * @param int    $form_id The current form ID.
	 */
	public function action_gform_entry_list_action_fillablepdfs( $action = '', $entries = [], $form_id = 0 ) {

		// If no entries are being processed or this is not the Fillable PDFs action, return.
		if ( empty( $entries ) || 'fillablepdfs' !== $action ) {
			return;
		}

		// Get the current form.
		$form = GFAPI::get_form( $form_id );

		// Loop through entries.
		foreach ( $entries as $entry_id ) {

			// Get the entry.
			$entry = GFAPI::get_entry( $entry_id );

			// Process feeds.
			$this->maybe_process_feed( $entry, $form );

		}

	}





	// # ENTRY DELETION ------------------------------------------------------------------------------------------------

	/**
	 * Delete PDFs upon entry deletion.
	 *
	 * @since  1.0
	 *
	 * @param int $entry_id Entry ID being deleted.
	 */
	public function delete_entry_pdfs( $entry_id ) {

		$pdfs = $this->get_entry_pdfs( $entry_id );

		if ( empty( $pdfs ) ) {
			return;
		}

		foreach ( $pdfs as $pdf ) {
			$this->delete_pdf( $pdf );
		}

	}

	/**
	 * Delete a PDF from an entry.
	 *
	 * @since 3.0
	 *
	 * @param array|string $pdf_meta_or_id PDF meta object or PDF ID.
	 *
	 * @return bool
	 */
	public function delete_pdf( $pdf_meta_or_id ) {

		if ( is_array( $pdf_meta_or_id ) ) {
			$pdf_meta = $pdf_meta_or_id;
		} else {
			$pdf_meta = self::get_pdf_meta( $pdf_meta_or_id );
		}

		if ( ! $pdf_meta ) {
			return false;
		}

		$entry = $this->get_entry_for_pdf( $pdf_meta['pdf_id'] );

		if ( ! $entry ) {
			return false;
		}

		// Delete file.
		$file_path = $this->get_physical_file_path( $pdf_meta );
		wp_delete_file( $file_path );

		// Delete file meta.
		gform_delete_meta( $entry['id'], 'fillablepdfs_' . $pdf_meta['pdf_id'] );

		// Delete file ID from entry meta.
		$entry_meta = gform_get_meta( $entry['id'], 'fillablepdfs' );
		if ( ! is_array( $entry_meta ) ) {
			return true;
		}

		unset( $entry_meta[ $pdf_meta['feed_id'] ] );
		gform_update_meta( $entry['id'], 'fillablepdfs', $entry_meta );

		return true;

	}





	// # ENTRY DETAILS -------------------------------------------------------------------------------------------------

	/**
	 * Add Fillable PDFs posts meta box to the entry detail page.
	 *
	 * @since  1.0
	 * @since  5.0  Added "forgravity_fillablepdfs_view_generated_pdfs" capability.
	 *
	 * @param array $meta_boxes The properties for the meta boxes.
	 * @param array $entry      The entry currently being viewed/edited.
	 * @param array $form       The form object used to process the current entry.
	 *
	 * @return array
	 */
	public function register_metaboxes( $meta_boxes, $entry, $form ) {

		if ( ! GFCommon::current_user_can_any( $this->get_capabilities( 'view_pdf' ) ) ) {
			return $meta_boxes;
		}

		// Register metabox.
		$meta_boxes[ Metabox_Documents::$id ] = [
			'title'    => esc_html__( 'Generated PDFs', 'forgravity_fillablepdfs' ),
			'context'  => Metabox_Documents::$context,
			'callback' => [ '\ForGravity\Fillable_PDFs\Metaboxes\Documents', 'render' ],
		];

		return $meta_boxes;

	}

	/**
	 * Regenerate PDFs on the entry detail page.
	 *
	 * @since  2.0
	 */
	public function maybe_regenerate_pdfs() {

		// If we're not on the entry view page, return.
		if ( rgget( 'page' ) !== 'gf_entries' || rgget( 'view' ) !== 'entry' || rgget( $this->_slug ) !== 'regenerate' ) {
			return;
		}

		// Get the current form and entry.
		$form  = GFAPI::get_form( rgget( 'id' ) );
		$entry = $this->get_current_entry();

		// Process feeds.
		$this->maybe_process_feed( $entry, $form );

	}





	// # PERSONAL DATA -------------------------------------------------------------------------------------------------

	/**
	 * Register Fillable PDFs as a personal data item.
	 *
	 * @since  2.0
	 *
	 * @param array $items An associative array with the field id as the key and the value as the label.
	 * @param array $form  The current Form object.
	 *
	 * @return array
	 */
	public function filter_gform_personal_data( $items, $form ) {

	    // Get feeds for form.
        $feeds = $this->get_feeds( $form['id'] );

        // If form has no feeds, return.
        if ( empty( $feeds ) ) {
            return $items;
        }

		// Add Fillable PDFs item.
		$items['forgravity_fillablepdfs'] = [
			'label'             => esc_html__( 'Fillable PDFs', 'forgravity_fillablepdfs' ),
			'exporter_callback' => [ $this, 'export_personal_data' ],
			'eraser_callback'   => [ $this, 'erase_personal_data' ],
		];

	    return $items;

    }

	/**
	 * Export Fillable PDFs for entry.
	 *
	 * @since  2.0
	 *
	 * @param array $form  The current Form object.
	 * @param array $entry The current Entry object.
	 *
	 * @return null|array
	 */
	public function export_personal_data( $form, $entry ) {

		// Get PDFs for form.
		$pdf_ids = gform_get_meta( $entry['id'], 'fillablepdfs' );

		// If no PDFs were found for entry, continue.
		if ( ! $pdf_ids ) {
			return null;
		}

		// Initialize PDF URLs array.
		$pdf_urls = [];

		// Get PDF URLs from meta.
		foreach ( $pdf_ids as $pdf_id ) {

			// Get PDF meta.
			$pdf_meta = gform_get_meta( $entry['id'], 'fillablepdfs_' . $pdf_id );

			// Build URL.
			$pdf_urls[] = $this->build_pdf_url( $pdf_meta, true );

		}

		return [
			'name'  => esc_html__( 'Generated PDFs', 'forgravity_fillablepdfs' ),
			'value' => implode( '<br />', $pdf_urls ),
		];

	}

	/**
	 * Delete Fillable PDFs from entry.
	 *
	 * @since  2.0
	 *
	 * @param array $form  The current Form object.
	 * @param array $entry The current Entry object.
	 */
	public function erase_personal_data( $form, $entry ) {

		// Get PDFs for form.
		$pdf_ids = gform_get_meta( $entry['id'], 'fillablepdfs' );

		// If no PDFs were found for entry, continue.
		if ( ! $pdf_ids ) {
			return;
		}

		// Delete PDFs.
		foreach ( $pdf_ids as $pdf_id ) {

			// Get PDF meta.
			$pdf_meta = gform_get_meta( $entry['id'], 'fillablepdfs_' . $pdf_id );

			// Get file path.
			$file_path = $this->get_physical_file_path( $pdf_meta );

			// Delete file.
			if ( file_exists( $file_path ) ) {
				unlink( $file_path );
			}

			// Delete PDF meta.
			gform_delete_meta( $entry['id'], 'fillablepdfs_' . $pdf_id );

		}

		// Delete PDF meta for entry.
		gform_delete_meta( $entry['id'], 'fillablepdfs' );

	}





	// # INTEGRATIONS --------------------------------------------------------------------------------------------------

	/**
	 * Get available integrations for visual mapper.
	 *
	 * @since 3.0
	 *
	 * @return array
	 */
	public static function get_available_integrations() {

		$integrations = [];

		// GFChart.
		global $gfp_gfchart_image_charts;
		$gfchart_charts = self::get_gfchart_charts();

		if ( ! empty( $gfp_gfchart_image_charts ) && ! empty( $gfchart_charts ) ) {

			$integrations['gfchart'] = [
				'label'  => esc_html__( 'GFChart', 'forgravity_fillablepdfs' ),
				'charts' => $gfchart_charts,
			];

		}

		return $integrations;

	}

	/**
	 * Get available GFChart charts for mapper.
	 *
	 * @since 2.3
	 *
	 * @return array
	 */
	protected static function get_gfchart_charts() {

		// If GFCharts API does not exist, return.
		if ( ! class_exists( '\GFChart_API' ) ) {
			return [];
		}

		// Get charts.
		$charts = GFChart_API::get_charts();

		// If no charts exist, return.
		if ( empty( $charts ) ) {
			return $charts;
		}

		// Remove unnecessary data.
		$charts = array_map(
			function( $chart ) {
				return [ 'id' => rgobj( $chart, 'ID' ), 'title' => rgobj( $chart, 'post_title' ) ];
			},
			$charts
		);

		return $charts;

	}

	/**
	 * Get image URL for GFChart embed.
	 *
	 * @since 2.3
	 *
	 * @param int $chart_id Chart ID.
	 *
	 * @return string|null
	 */
	private static function get_gfchart_image_chart_url( $chart_id ) {

		global $gfp_gfchart_image_charts;

		// If GFChart Image Charts plugin is not active, return.
		if ( ! is_object( $gfp_gfchart_image_charts ) ) {
			fg_fillablepdfs()->log_debug( __METHOD__ . '(): GFChart Image Charts plugin is unavailable; skipping field.' );
			return null;
		}

		// Enable image chart generation.
		$gfp_gfchart_image_charts->_doing_notification_message = true;

		// Get shortcode response.
		$shortcode_response = do_shortcode( sprintf( '[gfchart id="%d"]', $chart_id ) );

		// If image tag was not found, return.
		if ( preg_match( '/<img src="(.*)" style="(.*)" \/>/', $shortcode_response, $matches ) !== 1 ) {
			return null;
		}

		return rgar( $matches, 1 );

	}






	// # HELPER METHODS ------------------------------------------------------------------------------------------------

	/**
	 * Initializes Fillable PDFs API if credentials are valid.
	 *
	 * @since     1.0
	 * @depecated 3.4 Use fg_pdfs_api().
	 *
	 * @return bool|null
	 */
	public function initialize_api() {

		$api = fg_pdfs_api();

		if ( is_object( $api ) ) {
			$this->api = $api;
			return true;
		}

		return $api;

	}

	/**
	 * Build public PDF URL.
	 *
	 * @since  1.0
	 *
	 * @param array $pdf_meta PDF metadata.
	 * @param bool  $token    Include token with URL.
	 *
	 * @return string
	 */
	public function build_pdf_url( $pdf_meta, $token = false ) {

		// Prepare query arguments.
		$args = [ 'fgpdf' => $pdf_meta['pdf_id'] ];

		// Include token.
		if ( rgar( $pdf_meta, 'access' ) === 'token' || ( rgar( $pdf_meta, 'access' ) !== 'token' && $token ) ) {
			$args['token'] = $pdf_meta['token'];
		}

		return add_query_arg(
			$args,
			home_url()
		);

	}

	/**
	 * Returns the physical path of the plugins root folder.
	 *
	 * @since 2.4
	 *
	 * @param string $full_path
	 *
	 * @return string
	 */
	public function get_base_path( $full_path = '' ) {

		return WP_PLUGIN_DIR . '/' . dirname( FG_FILLABLEPDFS_PLUGIN_BASENAME );

	}

	/**
	 * Returns the url of the root folder of the current Add-On.
	 *
 	 * @since 2.4
 	 *
	 * @param string $full_path Optional. The full path the the plugin file.
	 *
	 * @return string
	 */
	public function get_base_url( $full_path = '' ) {

		return plugins_url( null, FG_FILLABLEPDFS_PLUGIN_BASENAME );

	}

	/**
	 * Prevent the GFAddOn::update_path() method from running.
	 *
	 * @since 3.4
	 */
	public function update_path() {
	}

	/**
	 * Helper function to get current entry.
	 *
	 * @since  2.0
	 *
	 * @return array $entry
	 */
	public function get_current_entry() {

		if ( $this->is_gravityforms_supported( '2.0-beta-3' ) ) {

			if ( ! class_exists( '\GFEntryDetail' ) ) {
				require_once( GFCommon::get_base_path() . '/entry_detail.php' );
			}

			return GFEntryDetail::get_current_entry();

		} else {

			$entry_id = rgpost( 'entry_id' ) ? absint( rgpost( 'entry_id' ) ) : absint( rgget( 'lid' ) );

			if ( $entry_id > 0 ) {

				return GFAPI::get_entry( $entry_id );

			} else {

				$position = rgget( 'pos' ) ? rgget( 'pos' ) : 0;
				$paging   = [ 'offset' => $position, 'page_size' => 1 ];
				$entries  = GFAPI::get_entries( rgget( 'id' ), [], null, $paging );

				return $entries[0];

			}

		}

	}

	/**
	 * Get entry for PDF.
	 *
	 * @since  2.4
	 *
	 * @param string|array $pdf_id PDF ID or metadata.
	 *
	 * @return array|false
	 */
	public function get_entry_for_pdf( $pdf_id ) {

		global $wpdb;

		// Get PDF ID from metadata.
		if ( is_array( $pdf_id ) ) {
			$pdf_id = rgar( $pdf_id, 'pdf_id' );
		}

		// Get PDF meta based on Gravity Forms database version.
		if ( version_compare( fg_fillablepdfs()->get_gravityforms_db_version(), '2.3-dev-1', '<' ) ) {

			// Get entry meta table name.
			$table_name = GFFormsModel::get_lead_meta_table_name();

			// Get entry ID.
			$entry_id = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT lead_id FROM {$table_name} WHERE `meta_key` = '%s'",
					'fillablepdfs_' . $pdf_id
				)
			);

		} else {

			// Get entry meta table name.
			$table_name = GFFormsModel::get_entry_meta_table_name();

			// Get entry ID.
			$entry_id = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT entry_id FROM {$table_name} WHERE `meta_key` = '%s'",
					'fillablepdfs_' . $pdf_id
				)
			);

		}

		// If entry could not be found, return.
		if ( ! $entry_id ) {
			return false;
		}

		// Get entry.
		$entry = GFAPI::get_entry( $entry_id );

		return is_wp_error( $entry ) ? false : $entry;

	}

	/**
	 * Get entry meta options for form.
	 *
	 * @since  1.1
	 *
	 * @param array|bool $form Form object.
	 *
	 * @return array
	 */
	public function get_entry_meta_options( $form = false ) {

		// If form was not provided, get current form.
		if ( ! $form ) {
			$form = $this->get_current_form();
		}

		// Initialize meta array.
		$meta = [
			[
				'value' => 'id',
				'label' => esc_html__( 'Entry ID', 'forgravity_easypassthrough' ),
			],
			[
				'value' => 'date_created',
				'label' => esc_html__( 'Entry Date', 'forgravity_easypassthrough' ),
			],
			[
				'value' => 'ip',
				'label' => esc_html__( 'User IP', 'forgravity_easypassthrough' ),
			],
			[
				'value' => 'source_url',
				'label' => esc_html__( 'Source Url', 'forgravity_easypassthrough' ),
			],
			[
				'value' => 'form_title',
				'label' => esc_html__( 'Form Title', 'forgravity_easypassthrough' ),
			],
			[
				'value' => 'payment_status',
				'label' => esc_html__( 'Payment Status', 'forgravity_easypassthrough' ),
			],
			[
				'value' => 'transaction_id',
				'label' => esc_html__( 'Transaction Id', 'forgravity_easypassthrough' ),
			],
			[
				'value' => 'payment_date',
				'label' => esc_html__( 'Payment Date', 'forgravity_easypassthrough' ),
			],
			[
				'value' => 'payment_amount',
				'label' => esc_html__( 'Payment Amount', 'forgravity_easypassthrough' ),
			],
			[
				'value' => 'payment_gateway',
				'label' => esc_html__( 'Payment Gateway', 'forgravity_easypassthrough' ),
			],
		];

		// Get entry meta fields for form.
		$form_meta = GFFormsModel::get_entry_meta( $form['id'] );

		// Add entry meta to meta map.
		foreach ( $form_meta as $meta_key => $m ) {
			$meta[] = [
				'value' => $meta_key,
				'label' => rgars( $form_meta, "{$meta_key}/label" ),
			];
		}

		return $meta;

	}

	/**
	 * Returns a collection of PDF meta for the entry.
	 *
	 * @since 2.3
	 *
	 * @param array|int $entry_or_id Entry object or ID.
	 *
	 * @return array
	 */
	public function get_entry_pdfs( $entry_or_id = [] ) {

		$pdf_meta = [];
		$entry    = is_array( $entry_or_id ) ? $entry_or_id : GFAPI::get_entry( $entry_or_id );

		// If Entry ID was provided, get full entry.
		if ( ! $entry || is_wp_error( $entry ) ) {
			if ( is_wp_error( $entry ) ) {
				$this->log_error( __METHOD__ . '(): Unable to get the entry. Error: ' . $entry->get_error_message() );
			}

			return $pdf_meta;
		}

		// Get PDF meta IDs for entry.
		$ids = gform_get_meta( $entry['id'], 'fillablepdfs' );

		// If entry does not have any PDFs, return.
		if ( ! $ids ) {
			return $pdf_meta;
		}

		// Loop through PDF meta IDs, get PDF meta.
		foreach ( $ids as $id ) {

			// Get meta.
			$meta = gform_get_meta( $entry['id'], 'fillablepdfs_' . $id );

			// If meta was not found, skip.
			if ( ! $meta ) {
				continue;
			}

			// Add creation date.
			if ( ! rgar( $meta, 'date_created' ) ) {
				$meta['date_created'] = rgar( $entry, 'date_created' );
			}

			// Convert creation date to timestamp.
			$meta['date_created'] = strtotime( $meta['date_created'] );

			// Add entry, form IDs to meta.
			$meta['entry_id'] = $entry['id'];
			$meta['form_id']  = $entry['form_id'];

			// Add to PDF meta array.
			$pdf_meta[ $id ] = $meta;

		}

		return $pdf_meta;

	}

	/**
	 * Get PDF meta.
	 *
	 * @since 3.0 Moved from Server class.
	 *
	 * @param string $pdf_id PDF ID.
	 *
	 * @return array|null
	 */
	public static function get_pdf_meta( $pdf_id ) {

		global $wpdb;

		// Get PDF meta based on Gravity Forms database version.
		if ( version_compare( fg_fillablepdfs()->get_gravityforms_db_version(), '2.3-dev-1', '<' ) ) {

			// Get entry meta table name.
			$table_name = GFFormsModel::get_lead_meta_table_name();

		} else {

			// Get entry meta table name.
			$table_name = GFFormsModel::get_entry_meta_table_name();

		}

		// Get PDF meta row.
		$meta = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT meta_value FROM {$table_name} WHERE `meta_key` = '%s'",
				'fillablepdfs_' . $pdf_id
			)
		);

		return maybe_unserialize( $meta );

	}

	/**
	 * Get the physical file path for a generated PDF.
	 *
	 * @since 2.4
	 *
	 * @param array $pdf_meta PDF metadata.
	 *
	 * @return string|false
	 */
	public function get_physical_file_path( $pdf_meta ) {

		// If legacy file path is set, return.
		if ( rgar( $pdf_meta, 'file_path' ) ) {
			return $pdf_meta['file_path'];
		}

		// Get form ID, entry.
		$entry   = $this->get_entry_for_pdf( $pdf_meta );
		$form_id = rgar( $entry, 'form_id' );

		return $this->generate_physical_file_path( $form_id, $pdf_meta['physical_file_name'], $entry, false );

	}

	/**
	 * Get upload path for file.
	 *
	 * @since  1.0
	 *
	 * @param int    $form_id   Form ID.
	 * @param string $file_name File name.
	 * @param array  $entry     The current Entry object.
	 * @param bool   $unique    Use a unique file name.
	 *
	 * @return string|false
	 */
	public function generate_physical_file_path( $form_id, $file_name, $entry = [], $unique = true ) {

		if ( version_compare( phpversion(), '7.4', '<' ) && get_magic_quotes_gpc() ) {
			$file_name = stripslashes( $file_name );
		}

		// Generate target folder.
		$form_id     = absint( $form_id );
		$time        = ! empty( $entry ) ? date( 'Y-m-d H:i:s', strtotime( rgar( $entry, 'date_created' ) ) ) : current_time( 'mysql' );
		$y           = substr( $time, 0, 4 );
		$m           = substr( $time, 5, 2 );
		$base_path   = self::get_base_pdf_path();
		$form_path   = self::get_form_pdf_path( $form_id );
		$target_root = sprintf( '%s/%s/%s/', untrailingslashit( $form_path ), $y, $m );

		// Ensure base path has .htaccess file.
		if ( ! file_exists( $base_path . '.htaccess' ) ) {

			// Include GFExport.
			if ( ! class_exists( 'GFExport' ) ) {
				require_once( GFCommon::get_base_path() . '/export.php' );
			}

			// Add .htaccess file.
			GFExport::maybe_create_htaccess_file( $base_path );

		}

		// If target folder does not exist, create it.
		if ( ! is_dir( $target_root ) ) {

			// Log that we could not create the target folder.
			if ( ! wp_mkdir_p( $target_root ) ) {
				$this->log_error( __METHOD__ . '(): Unable to create folder "' . $target_root . '".' );
				return false;
			}

			// Adding index.html files to all sub-folders.
			if ( ! file_exists( $base_path . '/index.html' ) ) {
				GFCommon::recursive_add_index_file( $base_path );
			} else if ( ! file_exists( $form_path . 'index.html' ) ) {
				GFCommon::recursive_add_index_file( $form_path );
			} else if ( ! file_exists( $form_path . $y . '/index.html' ) ) {
				GFCommon::recursive_add_index_file( $form_path . $y );
			} else {
				GFCommon::recursive_add_index_file( $form_path . "$y/$m" );
			}

		}

		// Add the original filename to our target path.
		// Result is "uploads/filename.extension".
		$file_info = pathinfo( $file_name );
		$extension = rgar( $file_info, 'extension' );
		if ( ! empty( $extension ) ) {
			$extension = '.' . $extension;
		}
		$file_name = basename( $file_info['basename'], $extension );
		$file_name = sanitize_file_name( $file_name );

		$counter     = 1;
		$target_path = $target_root . $file_name . $extension;

		if ( $unique ) {
			while ( file_exists( $target_path ) ) {
				$target_path = $target_root . $file_name . "$counter" . $extension;
				$counter++;
			}
		}

		// Remove '.' from the end if file does not have a file extension
		$target_path = trim( $target_path, '.' );

		return $target_path;

	}

	/**
	 * Get upload path for form ID.
	 *
	 * @since  1.0
	 * @since  2.3 Renamed from get_upload_path()
	 *
	 * @param int $form_id Form ID.
	 *
	 * @return string
	 */
	public static function get_form_pdf_path( $form_id ) {

		// Get form folder name.
		$form_id          = absint( $form_id );
		$form_folder_name = sprintf( '%d-%s', $form_id, wp_hash( $form_id ) );

		// Get base path.
		$base_path = trailingslashit( self::get_base_pdf_path() );

		/**
		 * Modify the folder where generated PDFs are stored for a form.
		 *
		 * @since 2.3
		 *
		 * @param string $form_path Path to base folder.
		 * @param int    $form_id   The Form ID.
		 */
		$form_path = fg_pdfs_apply_filters( [ 'form_path', $form_id ], trailingslashit( $base_path . $form_folder_name ), $form_id );

		if ( ! is_dir( $form_path ) ) {
			wp_mkdir_p( $form_path );
		}

		return trailingslashit( $form_path );

	}

	/**
	 * The base folder path where generated PDFs are stored.
	 *
	 * @since 2.3
	 *
	 * @return string
	 */
	public static function get_base_pdf_path() {

		/**
		 * Modify the base folder where generated PDFs are stored.
		 *
		 * @since 2.3
		 *
		 * @param string $base_path Path to base folder.
		 */
		$base_path = fg_pdfs_apply_filters( 'base_path', trailingslashit( GFFormsModel::get_upload_root() . 'fillablepdfs' ) );

		if ( ! is_dir( $base_path ) ) {
			wp_mkdir_p( $base_path );
		}

		return trailingslashit( $base_path );

	}

	/**
	 * Get Gravity Forms database version number.
	 *
	 * @since  1.0.4
	 *
	 * @return string
	 */
	public static function get_gravityforms_db_version() {

		if ( method_exists( 'GFFormsModel', 'get_database_version' ) ) {
			$db_version = GFFormsModel::get_database_version();
		} else {
			$db_version = GFForms::$version;
		}

		return $db_version;

	}

	/**
	 * Remove field mapping when field is deleted.
	 *
	 * @since 2.0.5
	 *
	 * @param int    $form_id  Form ID.
	 * @param string $field_id Field being deleted.
	 */
	public function action_gform_after_delete_field( $form_id, $field_id ) {

		// Get Fillable PDFs feeds for form.
		$feeds = $this->get_feeds( $form_id );

		// If no feeds were found, exit.
		if ( ! $feeds ) {
			return;
		}

		// Loop through feeds, unmap field.
		foreach ( $feeds as $feed ) {

			// Initialize update meta flag.
			$update_meta = false;

			// Loop through field map, remove mapped field.
			foreach ( $feed['meta']['fieldMap'] as $i => $mapping ) {

				// If this is not the field being deleted, skip.
				if ( $field_id != $mapping['field'] ) {
					continue;
				}

				// Remove mapping.
				unset( $feed['meta']['fieldMap'][ $i ] );
				$update_meta = true;

			}

			// Update feed meta.
			if ( $update_meta ) {
				$this->update_feed_meta( $feed['id'], $feed['meta'] );
			}

		}

	}

	/**
	 * Returns the URL for the Fillable PDFs plugin settings page.
	 *
	 * @since 4.0
	 *
	 * @return string
	 */
	public function get_plugin_settings_url() {

		return admin_url( 'admin.php?page=' . $this->get_slug() );

	}

	/**
	 * Returns the authentication state.
	 *
	 * @since 4.0
	 *
	 * @return string
	 */
	public function get_authentication_state() {

		if ( ! GFCommon::current_user_can_any( $this->get_capabilities( 'settings_page' ) ) ) {
			return '';
		}

		$action = $this->get_authentication_state_action();

		// If state exists, return it.
		if ( ( $nonce = get_transient( $action ) ) ) {
			return $nonce;
		}

		// Create the state and store it in a transient.
		$nonce = wp_create_nonce( $action );

		set_transient( $action, $nonce, 10 * MINUTE_IN_SECONDS );

		return $nonce;

	}

	/**
	 * Deletes the authentication state.
	 *
	 * @since 4.0
	 *
	 * @return bool
	 */
	public function delete_authentication_state() {

		return delete_transient( $this->get_authentication_state_action() );

	}

	/**
	 * Get action name for authentication state.
	 *
	 * @since 4.0
	 *
	 * @return string
	 */
	public function get_authentication_state_action() {

		return $this->get_slug() . '_authentication_state';

	}

	/**
	 * Returns the instance of the PDFs Server.
	 *
	 * @since 3.4
	 *
	 * @return Server
	 */
	public function get_server() {

		return $this->server;

	}




	// # IMPORT / EXPORT -----------------------------------------------------------------------------------------------

	/**
	 * Imports feeds attached to form object.
	 *
	 * @since 2.3
	 *
	 * @param array $forms The forms being imported.
	 */
	public function action_gform_forms_post_import( $forms ) {

		// Loop through forms, import feeds.
		foreach ( $forms as $form ) {

			// If no feeds are found for form, skip.
			if ( ! rgars( $form, 'feeds/' . $this->_slug ) ) {
				continue;
			}

			// Loop through feeds, import.
			foreach ( $form['feeds'][ $this->_slug ] as $feed ) {

				// Import feed.
				$old_feed_id = rgar( $feed, 'id' );
				$new_feed_id = GFAPI::add_feed( $form['id'], $feed['meta'], $this->_slug );

				// Disable feed, if necessary.
				if ( ! is_wp_error( $new_feed_id ) && ! $feed['is_active'] ) {
					$this->update_feed_active( $new_feed_id, false );
				}

				// Replace merge tags.
				if ( ! is_wp_error( $new_feed_id ) ) {
					$form['confirmations'] = $this->update_import_merge_tags( $form['confirmations'], $old_feed_id, $new_feed_id );
					$form['notifications'] = $this->update_import_merge_tags( $form['notifications'], $old_feed_id, $new_feed_id );
				}

			}

			// Remove Add-On feeds from form object.
			unset( $form['feeds'][ $this->_slug ] );

			// If form has no other feeds to import, remove feeds array.
			if ( empty( $form['feeds'] ) ) {
				unset( $form['feeds'] );
			}

			// Save form object.
			GFAPI::update_form( $form );

		}

	}

	/**
	 * Update feed IDs in merge tags when importing form.
	 *
	 * @since 2.3
	 *
	 * @param array $objects     Collection of confirmations or notifications.
	 * @param int   $old_feed_id Old feed ID.
	 * @param int   $new_feed_id New feed ID.
	 *
	 * @return array
	 */
	private function update_import_merge_tags( $objects, $old_feed_id, $new_feed_id ) {

		// Loop through objects, update message.
		foreach ( $objects as &$object ) {

			// Search for merge tags in text.
			preg_match_all( '/{[^{]*?:(\d+)(:([^:]*?))?(:([^:]*?))?(:url)?}/mi', $object['message'], $matches, PREG_SET_ORDER );

			// Loop through matches, replace merge tag.
			foreach ( $matches as $match ) {

				// Get parts.
				$merge_tag = $match[0];
				$feed_id   = rgar( $match, 1 );

				// If this is not a PDF merge tag, skip it.
				if ( strpos( strtolower( $merge_tag ), '{fillable pdfs:' ) !== 0 ) {
					continue;
				}

				// If this is not the feed being imported, skip it.
				if ( intval( $feed_id ) !== intval( $old_feed_id ) ) {
					continue;
				}

				// Replace merge tag.
				$new_merge_tag     = str_replace( $old_feed_id, $new_feed_id, $merge_tag );
				$object['message'] = str_replace( $merge_tag, $new_merge_tag, $object['message'] );

			}

		}

		return $objects;

	}

	/**
	 * Add feeds to form object before exporting.
	 *
	 * @since 2.3
	 *
	 * @param array $form The form being exported.
	 *
	 * @return array
	 */
	public function filter_gform_export_form( $form ) {

		// Get feeds for form.
		$feeds = $this->get_feeds( $form['id'] );

		// If feeds array does not exist for object, add it.
		if ( ! isset( $form['feeds'] ) ) {
			$form['feeds'] = [];
		}

		// Add feeds to form.
		$form['feeds'][ $this->_slug ] = $feeds;

		return $form;

	}





	// # PUBLIC ACCESS DETECTION ---------------------------------------------------------------------------------------

	/**
	 * Determines whether the generated PDFs folder is accessible to the public.
	 *
	 * @since 2.3
	 *
	 * @return bool
	 */
	public function is_base_pdf_path_public() {

		// Get PDFs folder.
		$folder = self::get_base_pdf_path();

		// Generate test file.
		$file_name     = wp_hash( uniqid( $this->get_slug() ) ) . '.txt';
		$file_path     = trailingslashit( $folder ) . $file_name;
		$file_contents = wp_hash( uniqid( $this->get_slug() ) );
		file_put_contents( $file_path, $file_contents );

		// IF file does not exist, return.
		if ( ! is_file( $file_path ) ) {
			$this->log_error( __METHOD__ . '(): Unable to create test file.' );
			return false;
		}

		// Set public file flag.
		$public = false;

		// Get file URL.
		$file_url = self::convert_path_to_url( $file_path );

		// If file URL could be obtained, test it.
		if ( $file_url ) {

			// Attempt to get file.
			$request = wp_remote_get( $file_url );

			// If file could not be retrieved, log and set public flag.
			if ( ! is_wp_error( $request ) && wp_remote_retrieve_response_code( $request ) === 200 && wp_remote_retrieve_body( $request ) === $file_contents ) {
				$this->log_debug( __METHOD__ . '(): Generated PDF files are publicly accessible.' );
				$public = true;
			}

		}

		// Delete test file.
		@unlink( $file_path );

		return $public;

	}

	/**
	 * Attempt to convert a local path to a publicly accessible URL.
	 * (Credit: GravityPDF)
	 *
	 * @since 2.3
	 *
	 * @param string $path Path to file.
	 *
	 * @return bool|string
	 */
	private static function convert_path_to_url( $path ) {

		// Trim the path.
		$path = trim( $path );

		// If URL is not provided, return.
		if ( empty( $path ) ) {
			return false;
		}

		// Get the upload directory, prepare URL.
		$upload_dir = wp_upload_dir();
		$url        = str_replace( $upload_dir['basedir'], $upload_dir['baseurl'], $path );

		// If path was converted to URL, return.
		if ( $url !== $path && filter_var( $url, FILTER_VALIDATE_URL ) ) {
			return $url;
		}

		// Attempt to replace using defined constants.
		if ( defined( 'WP_CONTENT_DIR' ) && defined( 'WP_CONTENT_URL' ) ) {

			// Replace with defined constants.
			$url = str_replace( WP_CONTENT_DIR, WP_CONTENT_URL, $path );

			// If path was converted to URL, return.
			if ( $url !== $path && filter_var( $url, FILTER_VALIDATE_URL ) ) {
				return $url;
			}

		}

		/**
		 * Attempt to replace with get_home_path().
		 * Include the function first.
		 */
		if ( ! function_exists( 'get_home_path' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		// Replace with get_home_path().
		$url = str_replace( get_home_path(), home_url(), $path );

		// If path was converted to URL, return.
		if ( $url !== $path && filter_var( $url, FILTER_VALIDATE_URL ) ) {
			return $url;
		}

		// Attempt to replace with site_url().
		$url = str_replace( ABSPATH, site_url(), $path );

		// If path was converted to URL, return.
		if ( $url !== $path && filter_var( $url, FILTER_VALIDATE_URL ) ) {
			return $url;
		}

		return false;

	}

	/**
	 * Check if generated PDFs folder is accessible to public and display admin message.
	 *
	 * @since 2.3
	 */
	public function check_base_pdf_path_public() {

		// If base PDF path is not public, return.
		if ( ! $this->is_base_pdf_path_public() ) {
			return;
		}

		// If message has already been displayed and is not dismissed, exit.
		$dismissable_messages = get_option( 'gform_sticky_admin_messages', [] );
		if ( ! empty( $dismissable_messages ) ) {

			$dismissable_keys = array_filter( array_keys( $dismissable_messages ), function( $key ) {
				return strpos( $key, 'fillablepdfs_pdf_path_warning' ) !== false;
			} );

			if ( ! empty( $dismissable_keys ) ) {
				foreach ( $dismissable_keys as $key ) {

					// Get dismissed user meta.
					$db_key       = sprintf( 'gf_dismissed_%1$s', substr( md5( sanitize_key( $key ) ), 0, 40 ) );
					$is_dismissed = (bool) get_user_meta( get_current_user_id(), $db_key, true );

					if ( ! $is_dismissed ) {
						return;
					}

				}
			}

		}

		// Prepare message.
		$message = sprintf(
			esc_html__( 'Your generated PDFs folder is publicly accessible. This could allow anyone to view your PDFs. %sClick here to learn how to make this folder private.%s', 'forgravity_fillabepdfs' ),
			'<a href="https://forgravity.com/documentation/fillable-pdfs/protecting-your-pdfs/">',
			'</a>'
		);

		// Display message.
		GFCommon::add_dismissible_message(
			$message,
			'fillablepdfs_pdf_path_warning_' . date( 'Y' ) . date( 'z' ),
			'error',
			$this->_capabilities_settings_page,
			true
		);

	}

	/**
	 * Add weekly schedule to cron schedules.
	 *
	 * @since 2.3
	 *
	 * @param array $schedules An array of non-default cron schedules.
	 *
	 * @return array
	 */
	public function filter_cron_schedules( $schedules ) {

		$schedules['fg_fillablepdfs_weekly'] = [
			'display'  => __( 'Once Weekly', 'forgravity_fillablepdfs' ),
			'interval' => 604800,
		];

		return $schedules;

	}

	/**
	 * Clear scheduled events.
	 *
	 * @since 2.3
	 */
	public static function clear_scheduled_events() {

		// Clear scheduled action.
		if ( wp_next_scheduled( FG_FILLABLEPDFS_PATH_CHECK_ACTION ) ) {
			wp_clear_scheduled_hook( FG_FILLABLEPDFS_PATH_CHECK_ACTION );
		}

	}





	// # SYSTEM REPORT -------------------------------------------------------------------------------------------------

	/**
	 * Add Fillable PDFs section to Gravity Forms System Report.
	 *
	 * @since 2.3
	 *
	 * @param array $system_report An array of default sections displayed on the System Status page.
	 *
	 * @return array
	 */
	public function filter_gform_system_report( $system_report = [] ) {

		// Get title_export, target section key.
		$title_export   = wp_list_pluck( $system_report, 'title_export' );
		$gf_section_key = array_search( 'Gravity Forms Environment', $title_export );

		// Determine if base PDF path is public.
		$is_base_pdf_path_public = $this->is_base_pdf_path_public();

		// Add Fillable PDFs section.
		$system_report[ $gf_section_key ]['tables'][] = [
			'title'        => esc_html__( 'Fillable PDFs', 'forgravity_fillablepdfs' ),
			'title_export' => 'Fillable PDFs',
			'items'        => [
				[
					'label'        => esc_html__( 'Base PDFs Path Public', 'forgravity_fillablepdfs' ),
					'label_export' => 'Base PDFs Path Public',
					'value'        => $is_base_pdf_path_public ? __( 'Yes', 'forgravity_fillablepdfs' ) : __( 'No', 'forgravity_fillablepdfs' ),
					'value_export' => $is_base_pdf_path_public ? 'Yes' : 'No',
					'is_valid'     => ! $is_base_pdf_path_public,
				],
			],
		];

		return $system_report;

	}





	// # UPGRADE ROUTINES ----------------------------------------------------------------------------------------------

	/**
	 * Upgrade routines.
	 *
	 * @since  2.0
	 *
	 * @param string $previous_version Previously installed version number.
	 */
	public function upgrade( $previous_version ) {

		if ( is_null( $previous_version ) ) {
			return;
		}

		// Run meta upgrade.
		if ( version_compare( $previous_version, '2.0-dev-1', '<' ) ) {
			$this->upgrade_20();
		}

		// Run meta upgrade.
		if ( version_compare( $previous_version, '2.3-rc-3', '<' ) ) {
			$this->upgrade_23();
		}

		// Run security upgrade.
		if ( version_compare( $previous_version, '2.3-rc-3', '<' ) ) {

			// Get base path.
			$base_path = self::get_base_pdf_path();

			// Include GFExport.
			if ( ! class_exists( 'GFExport' ) ) {
				require_once( GFCommon::get_base_path() . '/export.php' );
			}

			// Add .htaccess file.
			GFExport::maybe_create_htaccess_file( $base_path );

		}

		// Run auto update upgrade.
		if ( version_compare( $previous_version, '3.1', '<' ) ) {

			$settings = $this->get_plugin_settings();
			if ( rgar( $settings, 'background_updates' ) ) {
				$this->update_wp_auto_updates( true );
			}

		}

	}

	/**
	 * Upgrade feeds to new field mapping.
	 *
	 * @since  2.0
	 */
	public function upgrade_20() {

	    // If API cannot be initialized, return.
        if ( ! fg_pdfs_api() ) {
            return;
        }

	    // Get feeds.
        $feeds = $this->get_feeds();

        // Loop through feeds.
        foreach ( $feeds as $feed ) {

            try {

                // Get template.
                $template = fg_pdfs_api()->get_template( $feed['meta']['templateID'] );

            } catch ( Exception $e ) {

                // Log that feed could not be migrated.
                $this->log_error( __METHOD__ . '(): Unable to migrate feed #' . $feed['id'] . ' becasuse template could not be retrieved.' );

                continue;

            }

			// Get field mapping.
            $current_mapping = rgars( $feed, 'meta/fieldMap' );

			// Initialize new field mapping array.
            $field_mapping = [];

			// Loop through old field mapping, convert to new format.
            foreach ( $current_mapping as $mapping ) {

				// Update mapping.
				$field_mapping[ $template['fields'][ $mapping['key'] ]['name'] ] = [
					'field'     => $mapping['value'],
					'value'     => '',
					'modifiers' => [],
				];

            }

            // Save new field mapping to feed.
            $feed['meta']['fieldMap'] = $field_mapping;

			// Save feed.
            $this->update_feed_meta( $feed['id'], $feed['meta'] );

		}

    }

	/**
	 * Upgrade to new security setting.
	 *
	 * @since  2.3
	 */
	public function upgrade_23() {

		// Get feeds.
		$feeds = $this->get_feeds();

		// Loop through feeds.
		foreach ( $feeds as $feed ) {

			// Set publicly accessible flag.
			if ( rgars( $feed, 'meta/downloadPermissions' ) === 'anyone' ) {
				$feed['meta']['publicAccess'] = '1';
			}

			// Remove old download permissions flag.
			unset( $feed['meta']['downloadPermissions'] );

			// Save feed.
			$this->update_feed_meta( $feed['id'], $feed['meta'] );

		}

	}





	// # PLUGIN SETTINGS -----------------------------------------------------------------------------------------------





	// # LICENSE METHODS -----------------------------------------------------------------------------------------------

	/**
	 * Activate a license key.
	 *
	 * @since  1.0
	 *
	 * @param string $license_key The license key.
	 *
	 * @return array
	 */
	public function activate_license( $license_key ) {

		// Activate license.
		$license = $this->process_license_request( 'activate_license', $license_key );

		// Clear update plugins transient.
		set_site_transient( 'update_plugins', null );

		// Delete plugin version info cache.
		$cache_key = md5( 'edd_plugin_' . sanitize_key( $this->_path ) . '_version_info' );
		delete_transient( $cache_key );

		return json_decode( wp_remote_retrieve_body( $license ) );

	}

	/**
	 * Check the status of a license key.
	 *
	 * @since  1.0
	 *
	 * @param string $license_key The license key.
	 *
	 * @return object
	 */
	public function check_license( $license_key = '' ) {

		// If license key is empty, get the plugin setting.
		if ( empty( $license_key ) ) {
			$license_key = $this->get_plugin_setting( 'license_key' );
		}

		// Perform a license check request.
		$license = $this->process_license_request( 'check_license', $license_key );

		return json_decode( wp_remote_retrieve_body( $license ) );

	}

	/**
	 * Process a request to the ForGravity store.
	 *
	 * @since  1.0
	 *
	 * @param string $action  The action to process.
	 * @param string $license The license key.
	 * @param int    $item_id The EDD item ID.
	 *
	 * @return array|WP_Error
	 */
	public function process_license_request( $action, $license, $item_id = FG_FILLABLEPDFS_EDD_ITEM_ID ) {

		// Prepare the request arguments.
		$args = [
			'method'    => 'POST',
			'timeout'   => 10,
			'sslverify' => false,
			'body'      => [
				'edd_action' => $action,
				'license'    => trim( $license ),
				'item_id'    => urlencode( $item_id ),
				'url'        => home_url(),
			],
		];

		return wp_remote_request( FG_EDD_STORE_URL, $args );

	}





	// # BACKGROUND UPDATES --------------------------------------------------------------------------------------------

	/**
	 * Display activate license message on Plugins list page.
	 *
	 * @since 3.4
	 *
	 * @param string $plugin_name The plugin filename.
	 */
	public function action_after_plugin_row( $plugin_name ) {

		// Get license key.
		$license_key = $this->get_plugin_setting( 'license_key' );

		// If no license key is installed, display message.
		if ( rgblank( $license_key ) ) {

			// Prepare message.
			$message = sprintf(
				esc_html__( '%2$sRegister your copy%4$s of %1$s to receive access to automatic upgrades and support. Need a license key? %3$sPurchase one now.%4$s', 'forgravity_fillablepdfs' ),
				esc_html( $this->_short_title ),
				'<a href="' . admin_url( 'admin.php?page=' . $this->_slug ) . '">',
				'<a href="' . esc_url( $this->_url ) . '" target="_blank">',
				'</a>'
			);

		} else {

			// Get license data.
			$license_data = $this->check_license( $license_key );

			// If license key is invalid, display message.
			if ( empty( $license_data ) || 'valid' !== $license_data->license ) {

				// Prepare message.
				$message = sprintf(
					esc_html__( 'Your license is invalid or expired. %1$sEnter a valid license key%3$s or %2$spurchase a new one.%3$s', 'forgravity_fillablepdfs' ),
					'<a href="' . admin_url( 'admin.php?page=' . $this->_slug ) . '">',
					'<a href="' . esc_url( $this->_url ) . '" target="_blank">',
					'</a>'
				);

			}

		}

		// If there is no message to display, exit.
		if ( ! isset( $message ) ) {
			return;
		}

		// Get active class.
		$active_class = ( is_network_admin() && is_plugin_active_for_network( $plugin_name ) ) || ( ! is_network_admin() && is_plugin_active( $plugin_name ) ) ? ' active' : '';

		// Display plugin message.
		printf(
			'<tr class="plugin-update-tr%3$s" id="%2$s-update" data-slug="%2$s" data-plugin="%1$s">
				<td colspan="99" class="plugin-update colspanchange">
					<div class="update-message notice inline notice-warning notice-alt">
						<p>%4$s</p>
					</div>
				</td>
			</tr>',
			esc_attr( $plugin_name ),
			esc_attr( $this->get_slug() ),
			esc_attr( $active_class ),
			$message // phpcs:ignore
		);

		// Hide border for plugin row.
		printf( '<script type="text/javascript">document.querySelector( \'tr[data-plugin="%s"]\' ).classList.add( \'update\' );</script>', esc_attr( $plugin_name ) );

	}

	/**
	 * Determines if automatic updating should be processed.
	 *
	 * @since  Unknown
	 *
	 * @param bool   $update Whether or not to update.
	 * @param object $item   The update offer object.
	 *
	 * @return bool
	 */
	public function filter_auto_update_plugin( $update, $item ) {

		if ( ! isset( $item->slug ) || 'fillablepdfs' !== $item->slug || is_null( $update ) ) {
			return $update;
		}

		if ( $this->is_auto_update_disabled( $update ) ) {
			$this->log_debug( __METHOD__ . '() - Aborting; auto updates disabled.' );
			return false;
		}

		return $this->can_auto_update_to_version( $item->new_version );

	}

	/**
	 * Determines if the new version can be auto-updated to.
	 *
	 * @since 4.0
	 *
	 * @param string $new_version     Version provided from Updater.
	 * @param string $current_version Current version of plugin.
	 *
	 * @return bool
	 */
	public function can_auto_update_to_version( $new_version, $current_version = false ) {

		if ( ! $current_version ) {
			$current_version = $this->_version;
		}

		$this->log_debug( __METHOD__ . '(): Skipping - not current branch.' );

		$current_major = implode( '.', array_slice( preg_split( '/[.-]/', $current_version ), 0, 1 ) );
		$new_major     = implode( '.', array_slice( preg_split( '/[.-]/', $new_version ), 0, 1 ) );

		return $current_major == $new_major;

	}

	/**
	 * Determine if automatic updates are disabled.
	 *
 	 * @since 3.1 Added the enabled param.
	 * @since 1.0
	 *
 	 * @param bool|null $enabled Indicates if auto updates are enabled.
	 *
	 * @return bool
	 */
	public function is_auto_update_disabled( $enabled = null ) {

		global $wp_version;

		if ( is_null( $enabled ) || version_compare( $wp_version, '5.5', '<' ) ) {
			$enabled = $this->get_plugin_setting( 'background_updates' );
		}

		$this->log_debug( __METHOD__ . ' - $enabled: ' . var_export( $enabled, true ) );

		return ! $enabled;

	}

	/**
	 * Updates the WordPress auto_update_plugins option to enable or disable automatic updates so the correct state is displayed on the plugins page.
	 *
	 * @since 3.1
	 *
	 * @param bool $is_enabled Indicates if background updates are enabled for Fillable PDFs in the plugin settings.
	 */
	public function update_wp_auto_updates( $is_enabled ) {

		$option       = 'auto_update_plugins';
		$auto_updates = (array) get_site_option( $option, [] );

		if ( $is_enabled ) {
			$auto_updates[] = FG_FILLABLEPDFS_PLUGIN_BASENAME;
			$auto_updates   = array_unique( $auto_updates );
		} else {
			$auto_updates = array_diff( $auto_updates, [ FG_FILLABLEPDFS_PLUGIN_BASENAME ] );
		}

		$callback = [ $this, 'action_update_site_option_auto_update_plugins' ];
		remove_action( 'update_site_option_auto_update_plugins', $callback );
		update_site_option( $option, $auto_updates );
		add_action( 'update_site_option_auto_update_plugins', $callback, 10, 3 );

	}

	/**
	 * Updates the background updates app setting when the WordPress auto_update_plugins option is changed.
	 *
	 * @since 3.1
	 *
	 * @param string $option    The name of the option.
	 * @param array  $value     The current value of the option.
	 * @param array  $old_value The previous value of the option.
	 */
	public function action_update_site_option_auto_update_plugins( $option, $value, $old_value ) {

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX && ! empty( $_POST['asset'] ) && ! empty( $_POST['state'] ) ) {
			// Option is being updated by the ajax request performed when using the enable/disable auto-updates links on the plugins page.
			$asset = sanitize_text_field( urldecode( $_POST['asset'] ) );

			if ( $asset !== FG_FILLABLEPDFS_PLUGIN_BASENAME ) {
				return;
			}

			$is_enabled = $_POST['state'] === 'enable';
		} else {
			// Option is being updated by some other means.
			$is_enabled  = in_array( FG_FILLABLEPDFS_PLUGIN_BASENAME, $value );
			$was_enabled = in_array( FG_FILLABLEPDFS_PLUGIN_BASENAME, $old_value );

			if ( $is_enabled === $was_enabled ) {
				return;
			}
		}

		$settings = $this->get_plugin_settings();

		if ( $settings['background_updates'] != $is_enabled ) {
			$settings['background_updates'] = $is_enabled;
			$this->update_plugin_settings( $settings );
		}

	}





	// # MEMBERS INTEGRATION -------------------------------------------------------------------------------------------

	/**
	 * Register the ForGravity capabilities group with the Members plugin.
	 *
	 * @since 5.0
	 */
	public function members_register_cap_group() {

		members_register_cap_group(
			'forgravity',
			[
				'label' => esc_html( 'ForGravity' ),
				'icon'  => 'dashicons-forgravity',
				'caps'  => [],
			]
		);

	}

	/**
	 * Register the capabilities and their human readable labels wit the Members plugin.
	 *
	 * @since 5.0
	 */
	public function members_register_caps() {

		$caps = [
			'forgravity_fillablepdfs'                     => esc_html__( 'Manage Settings', 'forgravity_fillablepdfs' ),
			'forgravity_fillablepdfs_uninstall'           => esc_html__( 'Uninstall', 'forgravity_fillablepdfs' ),
			'forgravity_fillablepdfs_view_generated_pdfs' => esc_html__( 'View Generated PDFs', 'forgravity_fillablepdfs' ),
		];

		foreach ( $caps as $cap => $label ) {
			members_register_cap(
				$cap,
				[
					'label' => sprintf( '%s: %s', $this->get_short_title(), $label ),
					'group' => 'forgravity',
				]
			);
		}

	}





	// # HELPER METHODS ------------------------------------------------------------------------------------------------

	/**
	 * Hook to gform_post_payment_action so we can run the PDF generation after payment is completed.
	 *
	 * @since 3.2
	 *
	 * @param array $entry  The entry object.
	 * @param array $action The action.
	 */
	public function action_gform_post_payment_action( $entry, $action ) {

		// If it's not a completed payment or subscription, do nothing.
		if ( ! in_array( rgar( $action, 'type' ), [ 'complete_payment', 'create_subscription' ] ) ) {
			return;
		}

		// Prepare variables.
		$form_id  = rgar( $entry, 'form_id' );
		$form     = GFAPI::get_form( $form_id );
		$entry_id = rgar( $entry, 'id' );

		// Get the processed feeds and target processed Fillable PDFs feeds.
		$processed_feeds = gform_get_meta( $entry_id, 'processed_feeds' );
		$processed_pdfs  = rgar( $processed_feeds, $this->get_slug() );
		if ( empty( $processed_pdfs ) ) {
			$processed_feeds[ $this->get_slug() ] = array();
		}

		// Get all Fillable PDFs feeds.
		$pdfs_feeds = $this->get_feeds( $form_id );

		// Run filters before processing feeds.
		$pdfs_feeds = $this->pre_process_feeds( $pdfs_feeds, $entry, $form );

		foreach ( $pdfs_feeds as $feed ) {

			// If the feed has been processed, move forward to the next one.
			if ( in_array( $feed['id'], $processed_feeds[ $this->get_slug() ] ) ) {
				$this->log_debug( __METHOD__ . "(): Feed (#{$feed['id']} - {$feed['meta']['feedName']}) has been processed for entry #{$entry['id']}." );
				continue;
			}

			// If this feed is inactive, log that it's not being processed and skip it.
			if ( ! $feed['is_active'] ) {
				$this->log_debug( __METHOD__ . "(): Feed is inactive, not processing feed (#{$feed['id']} - {$feed['meta']['feedName']}) for entry #{$entry['id']}." );
				continue;
			}

			// Process the feed when the condition is met and log error/debug message.
			if ( $this->is_feed_condition_met( $feed, $form, $entry ) ) {

				// Process the feed. Errors will be adding to the entry note.
				$this->process_feed( $feed, $entry, $form );

				/**
				 * Perform a custom action when a feed has been processed.
				 *
				 * @since 3.2
				 *
				 * @param array    $feed  The feed which was processed.
				 * @param array    $entry The current entry object, which may have been modified by the processed feed.
				 * @param array    $form  The current form object.
				 * @param \GFAddOn $addon The current instance of the GFAddOn object which extends GFFeedAddOn or GFPaymentAddOn (i.e. GFCoupons, GF_User_Registration, GFStripe).
				 */
				do_action( 'gform_post_process_feed', $feed, $entry, $form, $this );
				do_action( "gform_{$this->_slug}_post_process_feed", $feed, $entry, $form, $this );

				// Log that Add-On has been fulfilled.
				$this->log_debug( __METHOD__ . '(): Marking entry #' . $entry['id'] . ' as fulfilled for ' . $this->_slug );
				gform_update_meta( $entry['id'], "{$this->_slug}_is_fulfilled", true );

				// Store the feed ID to $processed_feeds for later.
				$processed_feeds[ $this->get_slug() ][] = $feed['id'];

			} else {

				// Log the feed condition not met scenario.
				$this->log_debug( __METHOD__ . "(): was trying to process the feed after payment completed, but feed (#{$feed['id']} - {$feed['meta']['feedName']}) condition was not met" );

			}

		}

		// Update the processed_feeds meta value.
		gform_update_meta( $entry_id, 'processed_feeds', $processed_feeds );

	}

	/**
	 * Plugin page header.
	 *
	 * This method output markups that has unenclosed HTML divs, we need to put it at the end of the file or it breaks PhpStorm's code formatting.
	 *
	 * @since  1.0
	 *
	 * @param string $title Page title.
	 */
	public function plugin_page_header( $title = '' ) {

		// Print admin styles.
		wp_print_styles( array( 'jquery-ui-styles', 'gform_admin', 'gform_settings' ) );

		// Get subviews.
		$subviews = $this->get_subviews();

		?>

		<div class="wrap <?php echo GFCommon::get_browser_class(); ?>">

			<header class="gform-settings-header gform-settings-header--fillablepdfs">
				<div class="gform-settings__wrapper">
					<img src="<?php echo $this->get_asset_url( '/dist/images/logo.svg' ); ?>" alt="Gravity Forms" height="60">
				</div>
			</header>

			<div class="gform-settings__wrapper">

				<nav class="gform-settings__navigation">
				<?php
				foreach ( $subviews as $view ) {

					// Initialize URL query params.
					$query = [ 'subview' => $view['name'] ];

					// Add subview query params, if set.
					if ( isset( $view['query'] ) ) {
						$query = array_merge( $query, $view['query'] );
					}

					// Prepare subview URL.
					$view_url = add_query_arg( $query, admin_url( 'admin.php?page=' . $this->get_slug() ) );

					// Get tab icon.
					$icon_markup = method_exists( 'GFCommon', 'get_icon_markup' ) ? GFCommon::get_icon_markup( $view, 'dashicons-admin-generic' ) : '';

					printf(
						'<a href="%s"%s><span class="icon">%s</span> <span class="label">%s</span></a>',
						esc_url( $view_url ),
						$this->get_current_subview() === $view['name'] ? ' class="active"' : '',
						$icon_markup,
						esc_html( $view['label'] )
					);
				}
				?>
			</nav>

				<div class="gform-settings__content" id="tab_<?php echo esc_attr( $this->get_current_subview() ); ?>">

		<?php
	}

	/**
	 * Helper function to get an asset URL.
	 *
	 * @since 3.3
	 *
	 * @param string $asset_path The asset path.
	 *
	 * @return string
	 */
	public function get_asset_url( $asset_path = '' ) {

		return File_Path::url( $asset_path );

	}

	/**
	 * Helper function to get an asset filetime.
	 *
	 * @since 3.3
	 *
	 * @param string $asset_path The asset path.
	 *
	 * @return int
	 */
	public function get_asset_filemtime( $asset_path ) {

		$file = File_Path::dir( $asset_path );

		return file_exists( $file ) ? filemtime( $file ) : false;

	}

	/**
	 * Helper function to get svg content.
	 *
	 * @since 3.3
	 *
	 * @param string $asset_path The asset path.
	 *
	 * @return string
	 */
	public function get_svg_content( $asset_path ) {

		$file = File_Path::dir( $asset_path );

		return file_exists( $file ) ? file_get_contents( $file ) : '';

	}

}
