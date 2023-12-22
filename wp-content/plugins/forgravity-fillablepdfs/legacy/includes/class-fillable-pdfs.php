<?php

namespace ForGravity\Fillable_PDFs\Legacy;

use Exception;
use ForGravity\Fillable_PDFs\API;
use GFCommon;
use GFFeedAddOn;
use GFForms;

GFForms::include_feed_addon_framework();

/**
 * Fillable PDFs for Gravity Forms.
 *
 * @since     2.4
 * @package   FillablePDFs
 * @author    ForGravity
 * @copyright Copyright (c) 2020, ForGravity
 */
class Fillable_PDFs extends \ForGravity\Fillable_PDFs\Fillable_PDFs {

	/**
	 * Get instance of this class.
	 *
	 * @since  1.0
	 * @static
	 *
	 * @return self
	 */
	public static function get_instance() {

		if ( null === self::$_instance ) {
			self::$_instance = new self;
		}

		return self::$_instance;

	}

	/**
	 * Enqueue needed scripts.
	 *
	 * @since  1.0
	 *
	 * @return array
	 */
	public function scripts() {

		global $wp_version;

		// Get minification string.
		$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || isset( $_GET['gform_debug'] ) ? '' : '.min';

		$scripts = [
			[
				'handle'  => $this->get_slug() . '_vendor_react',
				'src'     => '//unpkg.com/react@16.6.0/umd/react.production.min.js',
				'version' => '16.6.0',
			],
			[
				'handle'  => $this->get_slug() . '_vendor_react-dom',
				'src'     => '//unpkg.com/react-dom@16.6.0/umd/react-dom.production.min.js',
				'version' => '16.6.0',
				'deps'    => [ $this->get_slug() . '_vendor_react' ],
			],
			[
				'handle'    => 'forgravity_fillablepdfs_admin',
				'src'       => $this->get_base_url() . '/legacy/js/admin.js',
				'version'   => $this->get_version(),
				'deps'      => [ 'jquery', 'gform_chosen', 'gform_tooltip_init' ],
				'in_footer' => true,
				'enqueue'   => [
					[
						'admin_page' => [ 'form_settings', 'plugin_page' ],
						'tab'        => $this->get_slug(),
					],
				],
				'strings'   => [
					'illegal_file_type' => esc_html__( 'Illegal file type.', 'forgravity_fillablepdfs' ),
				],
			],
			[
				'handle'    => 'forgravity_fillablepdfs_feed_settings',
				'src'       => $this->get_base_url() . "/legacy/js/feed_settings{$min}.js",
				'version'   => filemtime( $this->get_base_path() . "/legacy/js/feed_settings{$min}.js" ),
				'deps'      => [ 'jquery', version_compare( $wp_version, '5.0', '>=' ) ? 'wp-element' : $this->get_slug() . '_vendor_react-dom' ],
				'in_footer' => true,
				'enqueue'   => [
					[
						'admin_page' => [ 'form_settings' ],
						'tab'        => $this->get_slug(),
					],
				],
				'callback'  => [ $this, 'localize_feed_settings_script' ],
			],
			[
				'handle'    => 'forgravity_fillablepdfs_import',
				'src'       => $this->get_base_url() . '/legacy/js/import.js',
				'version'   => $this->get_version(),
				'deps'      => [ 'jquery', 'gform_chosen', 'thickbox' ],
				'in_footer' => true,
				'enqueue'   => [
					[ 'query' => 'page=' . $this->get_slug() . '&subview=import' ],
				],
				'strings'   => [
					'illegal_file_type' => esc_html__( 'Illegal file type.', 'forgravity_fillablepdfs' ),
					'modal_title'       => esc_html__( 'Define Field Choices', 'forgravity_fillablepdfs' ),
				],
			],
		];

		return array_merge( GFFeedAddOn::scripts(), $scripts );

	}

	/**
	 * Localize feed settings script.
	 *
	 * @since  2.0
	 *
	 * @param string|array $form    The current Form object.
	 * @param bool         $is_ajax If form is being loaded via AJAX.
	 */
	public function localize_feed_settings_script( $form = '', $is_ajax = false ) {

		global $gfp_gfchart_image_charts;

		// Initialize API.
		if ( ! $this->initialize_api() ) {
			return;
		}

		// Get settings.
		if ( $this->is_postback() ) {

			// Get posted settings.
			$settings = $this->get_posted_settings();

		} else {

			// Get saved settings.
			$settings = $this->get_current_feed();
			$settings = rgar( $settings, 'meta' );

		}

		try {

			// Get template.
			$template = rgar( $settings, 'templateID' ) ? $this->api->get_template( $settings['templateID'] ) : [];

		} catch ( Exception $e ) {

			// Log that template could not be retrieved.
			$this->log_error( __METHOD__ . '(): Unable to localize script because template could not be retrieved; ' . $e->getMessage() );

			// Set template to empty array.
			$template = [];

		}

		// Get available GFChart charts.
		$gfchart_charts = self::get_gfchart_charts();

		// Localize script.
		wp_localize_script(
			'forgravity_fillablepdfs_feed_settings',
			'fg_fillablepdfs',
			[
				'api_base'     => FG_FILLABLEPDFS_API_URL,
				'entry_meta'   => $this->get_entry_meta_options(),
				'template'     => $template,
				'integrations' => [
					'gfchart' => [
						'label'   => esc_html__( 'GFChart', 'forgravity_fillablepdfs' ),
						'enabled' => ! empty( $gfp_gfchart_image_charts ) && ! empty( $gfchart_charts ),
						'charts'  => $gfchart_charts,
					],
				],
			]
		);

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
				'handle'  => $this->get_slug() . '_admin',
				'src'     => $this->get_base_url() . '/legacy/css/admin.css',
				'version' => $this->get_version(),
				'deps'    => [ 'gform_tooltip' ],
				'enqueue' => [
					[
						'admin_page' => [ 'form_settings', 'plugin_page' ],
						'tab'        => $this->get_slug(),
					],
					[ 'query' => 'page=gf_export&view=import_pdf' ],
				],
			],
			[
				'handle'  => $this->get_slug() . '_feed_settings',
				'src'     => $this->get_base_url() . "/legacy/css/feed_settings{$min}.css",
				'version' => filemtime( $this->get_base_path() . '/legacy/css/feed_settings.css' ),
				'deps'    => [ 'gform_chosen' ],
				'enqueue' => [
					[
						'admin_page' => [ 'form_settings' ],
						'tab'        => $this->get_slug(),
					],
				],
			],
			[
				'handle'  => $this->get_slug() . '_import',
				'src'     => $this->get_base_url() . '/legacy/css/import.css',
				'version' => $this->get_version(),
				'deps'    => [ 'thickbox', 'gform_chosen' ],
				'enqueue' => [
					[ 'query' => 'page=' . $this->get_slug() . '&subview=import' ],
				],
			],
		];

		// Add Chosen stylesheet.
		if ( ! wp_style_is( 'gform_chosen', 'registered' ) ) {

			// Add Chosen.
			$styles[] = [
				'handle'  => 'gform_chosen',
				'src'     => GFCommon::get_base_url() . "/css/chosen{$min}.css",
				'version' => GFCommon::$version,
				'enqueue' => [],
			];

		}

		return array_merge( GFFeedAddOn::styles(), $styles );

	}





	// # PLUGIN PAGE ---------------------------------------------------------------------------------------------------

	/**
	 * Prevent plugin settings page from appearing on Gravity Forms settings page.
	 *
	 * @since  1.0
	 */
	public function plugin_settings_init() {
	}

	/**
	 * Plugin page container.
	 *
	 * @since 2.4
	 */
	public function plugin_page_container() {

		?>
		<div class="wrap">
			<?php

			$icon = $this->plugin_page_icon();
			if ( ! empty( $icon ) ) {
				?>
				<img alt="<?php echo $this->get_short_title() ?>" style="margin: 15px 7px 0pt 0pt; float: left;"
					 src="<?php echo $icon ?>"/>
				<?php

			}
			?>

			<h2 class="gf_admin_page_title"><?php echo $this->plugin_page_title() ?></h2>
			<?php

			$this->plugin_page();
			?>
		</div>
		<?php

	}

	/**
	 * Plugin page header.
	 *
	 * @since  1.0
	 *
	 * @param string $title Page title.
	 */
	public function plugin_page_header( $title = '' ) {

		// Register needed styles.
		wp_register_style( 'gform_admin', GFCommon::get_base_url() . '/css/admin.css' );
		wp_print_styles( [ 'jquery-ui-styles', 'gform_admin', 'wp-pointer' ] );

		// Get subviews.
		$subviews = $this->get_subviews();

		?>

		<div class="wrap <?php echo esc_attr( GFCommon::get_browser_class() ); ?>">

			<?php GFCommon::display_admin_message(); ?>

			<div id="gform_tab_group" class="gform_tab_group vertical_tabs">

				<ul id="gform_tabs" class="gform_tabs">
					<?php
					foreach ( $subviews as $view ) {

						// Initialize URL query params.
						$query = [ 'subview' => $view['name'] ];

						// Add subview query params, if set.
						if ( isset( $view['query'] ) ) {
							$query = array_merge( $query, $view['query'] );
						}

						// Prepare subview URL.
						$view_url = add_query_arg( $query );

						// Remove unneeded query params.
						$view_url = remove_query_arg( [ 'id', 'action' ], $view_url );

						?>
						<li <?php echo $this->get_current_subview() === $view['name'] ? 'class="active"' : ''; ?>>
							<a href="<?php echo esc_attr( $view_url ); ?>"><?php echo esc_html( $view['label'] ); ?></a>
						</li>
					<?php } ?>
				</ul>

				<div id="gform_tab_container" class="gform_tab_container">
					<div class="gform_tab_content" id="tab_<?php echo esc_attr( $this->get_current_subview() ); ?>">
	<?php
	}

	/**
	 * Plugin page footer.
	 *
	 * @since  1.0
	 */
	public function plugin_page_footer() {

	?>

					</div> <!-- / gform_tab_content -->
				</div> <!-- / gform_tab_container -->
			</div> <!-- / gform_tab_group -->

			<br class="clear" style="clear: both;"/>

		</div> <!-- / wrap -->

		<script type="text/javascript">
			jQuery( document ).ready( function ( $ ) {
				$( '.gform_tab_container' ).css( 'minHeight', jQuery( '#gform_tabs' ).height() + 100 );
			} );
		</script>

		<?php

	}

	/**
	 * Get plugin page subviews.
	 *
	 * @since  1.0
	 *
	 * @return array
	 */
	public function get_subviews() {

		// Initialize subviews.
		$subviews = [
			[
				'name'     => 'settings',
				'label'    => esc_html__( 'Settings', 'forgravity_fillablepdfs' ),
				'callback' => [ $this, 'settings_page' ],
			],
		];

		// If API is not initialized, return.
		if ( ! $this->initialize_api() ) {
			return $subviews;
		}

		// Add additional subviews.
		$subviews[] = [
			'name'     => 'templates',
			'label'    => esc_html__( 'Templates', 'forgravity_fillablepdfs' ),
			'callback' => [ fg_fillablepdfs_templates(), 'templates_page' ],
		];

		try {

			// Get license info.
			$license = $this->api->get_license_info();

		} catch ( Exception $e ) {

			// Log that license info could not be retrieved.
			$this->log_error( __METHOD__ . '(): Unable to get license info; ' . $e->getMessage() );

			return $subviews;

		}

		// If license has access to Import feature, add tab.
		if ( $license['supports']['import'] ) {
			$subviews[] = [
				'name'     => 'import',
				'label'    => esc_html__( 'Import PDFs', 'forgravity_fillablepdfs' ),
				'callback' => [ fg_fillablepdfs_import(), 'import_page' ],
			];
		}

		return $subviews;

	}





	// # PLUGIN SETTINGS -----------------------------------------------------------------------------------------------

	/**
	 * Get the plugin settings fields. Fallback for Gravity Forms 2.4
	 *
	 * @since 1.0
	 *
	 * @return array
	 */
	public function plugin_settings_fields() {

		return [
			[
				'fields' => [
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
						'name'                => 'license_key',
						'label'               => esc_html__( 'License Key', 'forgravity_fillablepdfs' ),
						'type'                => 'text',
						'class'               => 'medium',
						'default_value'       => '',
						'error_message'       => esc_html__( 'Invalid License', 'forgravity_fillablepdfs' ),
						'feedback_callback'   => [ $this, 'license_feedback' ],
						'validation_callback' => [ $this, 'license_validation' ],
						'description'         => $this->get_license_info(),
					],
				],
			],
		];

	}

	/**
	 * Title for plugin settings page.
	 *
	 * @since  1.0
	 *
	 * @return string
	 */
	public function plugin_settings_title() {

		return esc_html__( 'Settings', 'forgravity_fillablepdfs' );

	}

	/**
	 * Get license info for plugin settings page.
	 *
	 * @since  1.0
	 *
	 * @return string
	 */
	public function get_license_info() {

		// Initialize HTML string.
		$html = '';

		// If API is not initialized, return.
		if ( ! $this->initialize_api() ) {
			return $html;
		}

		try {

			// Get license info.
			$license = $this->api->get_license_info();

		} catch ( Exception $e ) {

			// Log that license info could not be retrieved.
			$this->log_error( __METHOD__ . '(): Unable to retrieve license info; ' . $e->getMessage() );

			return $html;

		}

		// Add legacy license info.
		if ( $license['plan_id'] < 10 ) {

			// Display monthly usage data.
			$html .= sprintf(
				'%s: %d / %d<br />',
				esc_html__( 'Monthly Usage', 'forgravity_fillablepdfs' ),
				$license['usage']['used'] + rgars( $license, 'usage/overage/used' ),
				$license['usage']['limit']
			);

			// Display overage data.
			$html .= sprintf(
				'%s: %s<br />',
				esc_html__( 'Overage Status', 'forgravity_fillablepdfs' ),
				rgars( $license, 'usage/overage/enabled' ) ? esc_html__( 'Enabled', 'forgravity_fillablepdfs' ) : esc_html__( 'Disabled', 'forgravity_fillablepdfs' )
			);

		} else {

			// Display template count.
			$html .= sprintf(
				'%s: %d / %d<br />',
				esc_html__( 'Templates Created', 'forgravity_fillablepdfs' ),
				rgars( $license, 'templates/created' ),
				rgars( $license, 'templates/limit' )
			);

		}

		// Get renewal date.
		if ( rgar( $license, 'reset_date' ) && is_string( $license['reset_date'] ) ) {
			$renewal = strtotime( $license['reset_date'] );
			$renewal = date( get_option( 'date_format' ), $renewal );
		} else if ( is_numeric( $license['expires'] ) ) {
			$renewal = date( get_option( 'date_format' ), $license['expires'] );
		} else {
			$renewal = ucwords( $license['expires'] );
		}

		// Display subscription renewal date.
		$html .= sprintf(
			'%s: %s',
			esc_html__( 'Subscription Renewal Date', 'forgravity_fillablepdfs' ),
			esc_html( $renewal )
		);

		return $html;

	}





	// # FEED SETTINGS -------------------------------------------------------------------------------------------------

	/**
	 * Renders the UI of all settings page based on the specified configuration array $sections.
	 *    Forked to display section tabs.
	 *
	 * @since  1.0
	 *
	 * @param  array $sections Configuration array containing all fields to be rendered grouped into sections.
	 */
	public function render_settings( $sections ) {

		// Add default save button if not defined.
		if ( ! $this->has_setting_field_type( 'save', $sections ) ) {
			$sections = $this->add_default_save_button( $sections );
		}

		// Initialize tabs.
		$tabs = [];

		// Get tabs.
		foreach ( $sections as $section ) {

			// If no tab is defined, skip it.
			if ( ! rgar( $section, 'tab' ) ) {
				continue;
			}

			// If section doesn't meet dependency, skip it.
			if ( ! $this->setting_dependency_met( rgar( $section, 'dependency' ) ) ) {
				continue;
			}

			// Add tab.
			$tabs[ rgar( $section, 'id' ) ] = [
				'label' => rgars( $section, 'tab/label' ),
				'icon'  => rgars( $section, 'tab/icon' ),
			];

		}

		?>

		<form id="gform-settings" action="" enctype="multipart/form-data" method="post">
			<?php if ( ! empty( $tabs ) ) { ?>
				<div class="wp-filter fillablepdfs-tabs">
					<ul class="filter-links">
						<?php
						$is_first = true;
						foreach ( $tabs as $id => $tab ) {
							echo '<li id="' . esc_attr( $id ) . '-nav">';
							echo '<a href="#' . esc_attr( $id ) . '"' . ( $is_first ? ' class="current"' : '' ) . '>';
							echo $tab['icon'] ? '<i class="fa ' . esc_attr( $tab['icon'] ) . '"></i> ' : null;
							echo esc_html( $tab['label'] );
							echo '</a>';
							echo '</li>';
							$is_first = false;
						}
						?>
					</ul>
				</div>
				<?php
			}
			wp_nonce_field( $this->_slug . '_save_settings', '_' . $this->_slug . '_save_settings_nonce' );
			$this->settings( $sections );
			?>
		</form>

		<?php
	}

	/**
	 * Prepare title for Feed Settings page.
	 *
	 * @since  1.0
	 *
	 * @return string
	 */
	public function feed_settings_title() {

		return sprintf(
			'<i class="fa fa-file-pdf-o" aria-hidden="true"></i> &nbsp;%s',
			esc_html__( 'Feed Settings', 'gravityforms' )
		);

	}

	/**
	 * Setup fields for feed settings.
	 *
	 * @since  1.0
	 *
	 * @return array
	 */
	public function feed_settings_fields() {

		return [
			[
				'id'     => 'section-general',
				'class'  => 'fillablepdfs-feed-section',
				'tab'    => [ 'label' => esc_html__( 'General', 'forgravity_fillablepdfs' ), 'icon' => 'fa-cog' ],
				'fields' => [
					[
						'label'    => esc_html__( 'Name', 'forgravity_fillablepdfs' ),
						'name'     => 'feedName',
						'type'     => 'text',
						'class'    => 'medium',
						'required' => true,
					],
					[
						'label'            => esc_html__( 'Template', 'forgravity_fillablepdfs' ),
						'name'             => 'templateID',
						'type'             => 'template',
						'required'         => true,
						'onchange'         => "jQuery(this).parents('form').submit();",
						'choices'          => $this->get_templates_as_choices(),
						'data-placeholder' => esc_html__( 'Select a Template', 'forgravity_fillablepdfs' ),
						'no_choices' => sprintf(
							'<p>%s</p>',
							sprintf(
								esc_html__( 'You must have %sat least one template%s to be able to create a Fillable PDFs feed.', 'forgravity_fillablepdfs' ),
								'<a href="' . esc_url( add_query_arg( [
									'page'    => $this->get_slug(),
									'subview' => 'templates',
								], admin_url( 'admin.php' ) ) ) . '">',
								'</a>'
							)
						),
					],
					[
						'label'         => esc_html__( 'File Name', 'forgravity_fillablepdfs' ),
						'name'          => 'fileName',
						'type'          => 'text',
						'required'      => true,
						'class'         => 'medium merge-tag-support mt-position-right mt-hide_all_fields',
						'dependency'    => 'templateID',
						'default_value' => $this->get_default_file_name(),
					],
					[
						'label'            => esc_html__( 'Notifications', 'forgravity_fillablepdfs' ),
						'name'             => 'notifications[]',
						'type'             => 'select',
						'multiple'         => true,
						'choices'          => $this->get_notifications_as_choices(),
						'data-placeholder' => esc_html__( 'Select Notifications', 'forgravity_fillablepdfs' ),
						'dependency'       => 'templateID',
						'description'      => esc_html__( 'Select what notifications this generated PDF will be attached to', 'forgravity_fillablepdfs' ),
					],
					[
						'label'          => esc_html__( 'Conditional Logic', 'forgravity_fillablepdfs' ),
						'name'           => 'feed_condition',
						'type'           => 'feed_condition',
						'checkbox_label' => esc_html__( 'Enable', 'forgravity_fillablepdfs' ),
						'instructions'   => esc_html__( 'Export to PDF if', 'forgravity_fillablepdfs' ),
						'dependency'     => 'templateID',
					],
				],
			],
			[
				'id'         => 'section-advanced',
				'class'      => 'fillablepdfs-feed-section',
				'tab'        => [
					'label' => esc_html__( 'Advanced', 'forgravity_fillablepdfs' ),
					'icon'  => 'fa-cogs',
				],
				'dependency' => 'templateID',
				'fields'     => [
					[
						'name'        => 'userPassword',
						'label'       => esc_html__( 'User Password', 'forgravity_fillablepdfs' ),
						'type'        => 'text',
						'class'       => 'medium merge-tag-support mt-position-right mt-hide_all_fields',
						'description' => esc_html__( 'Set password required to view PDF', 'forgravity_fillablepdfs' ),
					],
					[
						'name'        => 'password',
						'label'       => esc_html__( 'Owner Password', 'forgravity_fillablepdfs' ),
						'type'        => 'text',
						'class'       => 'medium merge-tag-support mt-position-right mt-hide_all_fields',
						'description' => esc_html__( 'Set password to allow PDF permissions to be changed', 'forgravity_fillablepdfs' ),
					],
					[
						'name'          => 'filePermissions[]',
						'label'         => esc_html__( 'File Permissions', 'forgravity_fillablepdfs' ),
						'type'          => 'select',
						'multiple'      => true,
						'default_value' => [
							'ModifyAnnotations',
							'Printing',
							'DegradedPrinting',
							'ModifyContents',
							'Assembly',
							'CopyContents',
							'ScreenReaders',
							'FillIn',
						],
						'description'   => esc_html__( 'Select what users are allowed to do with the generated PDF', 'forgravity_fillablepdfs' ),
						'choices'       => [
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
								'label' => esc_html__( 'Yes', 'forgravity_fillablepdfs' ),
								'value' => '1',
							],
							[
								'label' => esc_html__( 'No', 'forgravity_fillablepdfs' ),
								'value' => '0',
							],
						],
					],
					[
						'name'    => 'options',
						'label'   => esc_html__( 'Additional Options', 'forgravity_fillablepdfs' ),
						'type'    => 'checkbox',
						'choices' => [
							[
								'label' => esc_html__( 'Remove interactive form fields', 'forgravity_fillablepdfs' ),
								'name'  => 'flatten',
							],
							[
								'label' => esc_html__( 'Regenerate PDF when entry is edited', 'forgravity_fillablepdfs' ),
								'name'  => 'regenerateOnEdit',
							],
						],
					],
				],
			],
			[
				'fields' => [ [ 'type' => 'save' ] ],
			],
		];

	}

	/**
	 * Renders and initializes a template selection field based on the $field array
	 *
	 * @since  1.0
	 *
	 * @param array $field Field array containing the configuration options of this field.
	 * @param bool  $echo  Echo the output to the screen.
	 *
	 * @return string
	 */
	public function settings_template( $field, $echo = true ) {

		// Get template setting.
		$template_id = $this->get_setting( 'templateID' );

		// Get select field.
		$html = '<div class="fillablepdfs__template-controls">';
		$html .= $this->settings_select( $field, false );

		// If template is selected, display field map field.
		if ( $template_id ) {

			try {

				// Get template.
				$this->api->get_template( $template_id );

				// Add open mapper button.
				$html .= sprintf(
					' <a class="button" onclick="javascript:openTemplateMapper();">%s</a>',
					esc_html__( 'Open Mapper', 'forgravity_fillablepdfs' )
				);
				$html .= '</div>';

				// Prepare field map field.
				$field_map = [
					'name'          => 'fieldMap',
					'type'          => 'hidden',
					'default_value' => json_encode( [] ),
				];

				// Get hidden field.
				$html .= $this->settings_hidden( $field_map, false );

				// Display template thumbnail.
				$html .= sprintf(
					'<p><img src="%s" alt="%s" class="fillablepdfs-template-thumbnail" /></p>',
					esc_url( API::$api_url . 'templates/' . $template_id . '/image' ),
					esc_attr( $this->get_setting( 'feedName' ) )
				);

			} catch ( Exception $e ) {
			}

		} else {

			$html .= '</div>';

		}

		if ( $echo ) {
			echo $html;
		}

		return $html;

	}





	// # FEED LIST -------------------------------------------------------------------------------------------------

	/**
	 * Add PDF icon to feed list title.
	 *
	 * @since  1.0
	 *
	 * @return string
	 */
	public function feed_list_title() {

		return sprintf( '<i class="fa fa-file-pdf-o" aria-hidden="true"></i> %s', parent::feed_list_title() );

	}





	// # ENTRY DETAILS -------------------------------------------------------------------------------------------------

	/**
	 * Display generated PDFs in Fillable PDFs entry meta box.
	 *
	 * @since  3.0
	 *
	 * @param array $args An array containing the Form and Entry objects.
	 */
	public function render_metabox( $args ) {

		// Get PDF IDs entry meta.
		$pdf_ids = gform_get_meta( $args['entry']['id'], 'fillablepdfs' );

		// Display generated PDFs.
		if ( ! empty( $pdf_ids ) ) {

			echo '<ul>';

			// Loop through PDF IDs.
			foreach ( $pdf_ids as $feed_id => $pdf_id ) {

				// Get PDF meta.
				$pdf_meta = gform_get_meta( $args['entry']['id'], 'fillablepdfs_' . $pdf_id );

				// Display link.
				printf(
					'<li><a href="%s">%s</a></li>',
					esc_url( $this->build_pdf_url( $pdf_meta, false ) ),
					esc_html( $pdf_meta['file_name'] )
				);

			}

			echo '</ul>';

		}

		// Prepare regenerate PDFs URL.
		$url = add_query_arg( [ 'fillablepdfs' => 'regenerate', 'lid' => $args['entry']['id'] ] );

		// Display button.
		printf(
			'<p><a href="%s" class="button">%s</a></p>',
			esc_url( $url ),
			esc_html__( 'Regenerate PDFs', 'forgravity_fillablepdfs' )
		);

	}

}
