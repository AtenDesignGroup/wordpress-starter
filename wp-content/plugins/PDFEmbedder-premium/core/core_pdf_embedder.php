<?php
/**
 * Core Embedder Class
 *
 * @package WP PDF Secure
 */

/**
 * Core Embedder
 */
class WP_PDF_Core {

	/**
	 * Plugin Version
	 *
	 * @var string
	 */
	protected $plugin_version;

	/**
	 * Plugin File
	 *
	 * @var string
	 */
	protected $file;

	/**
	 * Inserted Scripts
	 *
	 * @since 1.0.0
	 *
	 * @var boolean
	 */
	protected $inserted_scripts = false;

	/**
	 * PDF Options
	 *
	 * @var array
	 */
	protected $pdfemb_options = null;

	/**
	 * Class Constructor
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function __construct() {
		$this->init();
		register_activation_hook( $this->pdf_plugin_basename(), array( $this, 'pdfemb_activation_hook' ) );
	}

	/**
	 * Init Method
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function init() {

		add_action( 'plugins_loaded', array( $this, 'pdfemb_plugins_loaded' ) );

		add_action( 'init', array( $this, 'pdfemb_init' ) );

		add_action( 'wp_enqueue_scripts', array( $this, 'pdfemb_wp_enqueue_scripts' ), 5, 0 );

		add_action( 'init', array( $this, 'increment_download_view_counter' ) );

		/* add support for shortcodes, some of which was added into support docs */
		add_shortcode( 'pdf-embedder-counts', array( $this, 'pdf_embedder_counts' ) );
		add_shortcode( 'pdf-embedder-view-counts', array( $this, 'pdf_embedder_view_counts' ) );
		add_shortcode( 'pdf-embedder-download-counts', array( $this, 'pdf_embedder_download_counts' ) );

		if ( is_admin() ) {

			add_action( 'admin_init', array( $this, 'pdfemb_admin_init' ), 5, 0 );
			add_action( 'wp_ajax_pdfemb_install_partner', array( $this, 'install_partner' ) );
			add_action( 'wp_ajax_pdfemb_activate_partner', array( $this, 'activate_partner' ) );
			add_action( 'wp_ajax_pdfemb_deactivate_partner', array( $this, 'deactivate_partner' ) );
			add_action( $this->is_multisite_and_network_activated() ? 'network_admin_menu' : 'admin_menu', array( $this, 'admin_menu' ) );

			if ( $this->is_multisite_and_network_activated() ) {
				add_action( 'network_admin_edit_' . $this->get_options_menuname(), array( $this, 'pdfemb_save_network_options' ) );
			}

			add_filter( $this->is_multisite_and_network_activated() ? 'network_admin_plugin_action_links' : 'plugin_action_links', array( $this, 'pdfemb_plugin_action_links' ), 10, 2 );

		}
		add_filter( 'pdfemb_filter_shortcode_attrs', array( $this, 'pdfemb_filter_shortcode_gutenberg' ) );

		if ( isset( $_GET['pdf-css'] ) && 'true' === sanitize_text_field( wp_unslash( $_GET['pdf-css'] ) ) ) {
			$this->css_stylesheet_general_main_settings();
			exit;
		}

	}

	/**
	 * Increase View counter
	 *
	 * @param bool $override_view Override view count.
	 * @param bool $override_download Override download count.
	 * @return void
	 */
	public function increment_download_view_counter( $override_view = false, $override_download = false ) {

		if ( ! isset( $_GET['pdffilepath'] ) || false === strpos( esc_url_raw( wp_unslash( $_GET['pdffilepath'] ) ), '.pdf' ) ) {
			return;
		}

		if ( $override_view || ( isset( $_GET['v'] ) || '' !== trim( sanitize_text_field( wp_unslash( $_GET['v'] ) ) ) ) ) {
			$this->increment_view_counter( esc_url_raw( wp_unslash( $_GET['pdffilepath'] ) ) );
		}

		if ( $override_download || ( isset( $_GET['d'] ) || '' !== trim( sanitize_text_field( wp_unslash( $_GET['d'] ) ) ) ) ) {
			$this->increment_download_counter( esc_url_raw( wp_unslash( $_GET['pdffilepath'] ) ) );
		}

		wp_safe_redirect( esc_url_raw( wp_unslash( $_GET['pdffilepath'] ) ) );
		exit;

	}

	/**
	 * Undocumented function
	 *
	 * @param string $url PDF url.
	 * @param int    $post_id Post ID.
	 * @return void
	 */
	public function increment_download_counter( $url = false, $post_id = false ) {

		$post_id = ( false === $post_id ) ? $this->pdfemb_get_attachment_id( $url ) : $post_id;
		if ( ! $post_id ) {
			$post_id = $this->pdfemb_get_attachment_id( $url, true );
		}
		if ( $post_id ) {
			$count_downloads = get_post_meta( $post_id, 'pdfemb-downloads', true );
			if ( false === $count_downloads ) {
				$count_downloads = 0;
			}
			$count_downloads++;
			update_post_meta( $post_id, 'pdfemb-downloads', $count_downloads );

		}

	}

	/**
	 * Undocumented function
	 *
	 * @param string $url PDF url.
	 * @param int    $post_id Post ID.
	 * @return void
	 */
	public function increment_view_counter( $url = false, $post_id = false ) {

		$post_id = ( false === $post_id ) ? $this->pdfemb_get_attachment_id( $url ) : $post_id;

		if ( ! $post_id ) {
			$post_id = $this->pdfemb_get_attachment_id( $url, true );
		}
		if ( $post_id ) {
			$count_views = get_post_meta( $post_id, 'pdfemb-views', true );
			if ( false === $count_views ) {
				$count_views = 0;
			}
			$count_views++;
			update_post_meta( $post_id, 'pdfemb-views', $count_views );
		}

	}

	/**
	 * Undocumented function
	 *
	 * @param string $url PDF Url.
	 * @param bool   $invertscheme Inverse Schema.
	 * @return mixed
	 */
	public function pdfemb_get_attachment_id( $url, $invertscheme = false ) {
		global $wpdb;

		if ( $invertscheme ) {
			$url = set_url_scheme( $url, preg_match( '#^https://#i', $url ) ? 'http' : 'https' );
		}

		if ( false !== strpos( $url, 'pdffilepath' ) ) {
			$parts = wp_parse_url( $url );
			parse_str( $parts['query'], $query );
			$url      = $query['pdffilepath'];
			$download = isset( $query['d'] ) ? $query['d'] : false;
		}

		$attachment = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE guid=%s;", $url ) );
		if ( is_array( $attachment ) && isset( $attachment[0] ) && $attachment[0] > 0 ) {
			if ( isset( $download ) && 'true' === $download ) {
				$this->increment_download_counter( false, $attachment[0] );
			}
			return $attachment[0];
		}
		return false;
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function css_stylesheet_general_main_settings() {

		header( 'Content-Type: text/css' );

		$options = $this->get_option_pdfemb();

		ob_start();

		$toolbar_bg_color = ( isset( $options['pdfemb_color_sidebar'] ) && '' !== $options['pdfemb_color_sidebar'] ) ? $options['pdfemb_color_sidebar'] : false;
		$button_color     = ( isset( $options['pdfemb_color_button'] ) && '' !== $options['pdfemb_color_button'] ) ? $options['pdfemb_color_button'] : false;
		$icon_color       = ( isset( $options['pdfemb_color_icon'] ) && '' !== $options['pdfemb_color_icon'] ) ? $options['pdfemb_color_icon'] : false;

		if ( $toolbar_bg_color ) :
			echo '
			#mainContainer #toolbarContainer {
				background-color: ' . esc_attr( $toolbar_bg_color ) . ';
			}';
		endif;

		if ( $button_color ) :
			echo '
			.site .button:not(:hover):not(:active):not(.has-background), button:not(:hover):not(:active):not(.has-background), input[type="submit"]:not(:hover):not(:active):not(.has-background), input[type="reset"]:not(:hover):not(:active):not(.has-background), .wp-block-search .wp-block-search__button:not(:hover):not(:active):not(.has-background), .wp-block-button .wp-block-button__link:not(:hover):not(:active):not(.has-background), .wp-block-file a.wp-block-file__button:not(:hover):not(:active):not(.has-background),
			input#pageNumber, select#scaleSelect {
				background-color: ' . esc_attr( trim( $button_color ) ) . ';
				border-color: ' . esc_attr( $button_color ) . ';
			}';
		endif;

		if ( $icon_color ) :
			echo '
			#mainContainer .toolbarButton::before, .secondaryToolbarButton::before, .dropdownToolbarButton::after, .treeItemToggler::before {
				background-color: ' . esc_attr( $icon_color ) . ';
				border-color: ' . esc_attr( $icon_color ) . ';
				border: 0;
			}
			input#pageNumber, select#scaleSelect, #numPages {
				color: ' . esc_attr( trim( $icon_color ) ) . ';

			}';
		endif;

		$form = ob_get_clean();
		echo $form; // @codingStandardsIgnoreLine

		exit;
	}

	/**
	 * Helper if script debug is enabled.
	 *
	 * @since 1.0.0
	 *
	 * @return boolean
	 */
	protected function useminified() {
		return ! defined( 'SCRIPT_DEBUG' ) || ! SCRIPT_DEBUG;
	}

	/**
	 * Placeholder for activation hook
	 *
	 * @since 1.0.0
	 *
	 * @param boolean $network_wide is network wide.
	 * @return void
	 */
	public function pdfemb_activation_hook( $network_wide ) {}

	/**
	 * Placeholder for enqueue scripts
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function pdfemb_wp_enqueue_scripts() {}

	/**
	 * Helper Method to get translation array.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	protected function get_translation_array() {
		return array(
			'worker_src' => $this->pdf_plugin_url() . 'assets/js/min/pdf.worker-min.js',
			'cmap_url'   => $this->pdf_plugin_url() . 'assets/js/min/cmaps/',
			'objectL10n' => array(
				'loading'            => esc_html__( 'Loading...', 'pdf-embedder' ),
				'page'               => esc_html__( 'Page', 'pdf-embedder' ),
				'zoom'               => esc_html__( 'Zoom', 'pdf-embedder' ),
				'prev'               => esc_html__( 'Previous page', 'pdf-embedder' ),
				'next'               => esc_html__( 'Next page', 'pdf-embedder' ),
				'zoomin'             => esc_html__( 'Zoom In', 'pdf-embedder' ),
				'zoomout'            => esc_html__( 'Zoom Out', 'pdf-embedder' ),
				'secure'             => esc_html__( 'Secure', 'pdf-embedder' ),
				'download'           => esc_html__( 'Download PDF', 'pdf-embedder' ),
				'fullscreen'         => esc_html__( 'Full Screen', 'pdf-embedder' ),
				'domainerror'        => esc_html__( 'Error: URL to the PDF file must be on exactly the same domain as the current web page.', 'pdf-embedder' ),
				'clickhereinfo'      => esc_html__( 'Click here for more info', 'pdf-embedder' ),
				'widthheightinvalid' => esc_html__( 'PDF page width or height are invalid', 'pdf-embedder' ),
				'viewinfullscreen'   => esc_html__( 'View in Full Screen', 'pdf-embedder' ),
			),
		);
	}

	/**
	 * Undocumented function
	 *
	 * @param array $existing_mimes Mime Types.
	 * @return array
	 */
	public function pdfemb_upload_mimes( $existing_mimes = array() ) {
		$existing_mimes['pdf'] = 'application/pdf';
		return $existing_mimes;
	}

	/**
	 * Undocumented function
	 *
	 * @param array $post_mime_types Mime Types.
	 * @return array
	 */
	public function pdfemb_post_mime_types( $post_mime_types ) {
		$post_mime_types['application/pdf'] = array( __( 'PDFs', 'pdf-embedder' ), __( 'Manage PDFs', 'pdf-embedder' ), _n_noop( 'PDF <span class="count">(%s)</span>', 'PDFs <span class="count">(%s)</span>', 'pdf-embedder' ) );
		return $post_mime_types;
	}

	/**
	 * Undocumented function
	 *
	 * @param string $html Markup.
	 * @param int    $id ID.
	 * @param array  $attachment Attachment.
	 * @return string
	 */
	public function pdfemb_media_send_to_editor( $html, $id, $attachment ) {
		$pdf_url = '';
		$title   = '';
		if ( isset( $attachment['url'] ) && preg_match( '/\.pdf$/i', $attachment['url'] ) ) {
			$pdf_url = $attachment['url'];
			$title   = isset( $attachment['post_title'] ) ? $attachment['post_title'] : '';
		} elseif ( $id > 0 ) {
			$post = get_post( $id );
			if ( $post && isset( $post->post_mime_type ) && 'application/pdf' === $post->post_mime_type ) {
				$pdf_url = wp_get_attachment_url( $id );
				$title   = get_the_title( $id );
			}
		}

		if ( '' !== $pdf_url ) {
			if ( '' !== $title ) {
				$title_from_url = $this->make_title_from_url( $pdf_url );
				if ( $title === $title_from_url || $this->make_title_from_url( '/' . $title ) === $title_from_url ) {
					// This would be the default title anyway based on URL
					// OR if you take .pdf off title it would match, so that's close enough - don't load up shortcode with title param.
					$title = '';
				} else {
					$title = ' title="' . esc_attr( $title ) . '"';
				}
			}

			return apply_filters( 'pdfemb_override_send_to_editor', '[pdf-embedder url="' . $pdf_url . '"' . $title . ']', $html, $id, $attachment );
		} else {
			return $html;
		}
	}

	/**
	 * Helper method to modify schema
	 *
	 * @param string $url PDF url.
	 * @return string
	 */
	protected function modify_pdfurl( $url ) {
		return set_url_scheme( $url );
	}

	/**
	 * Undocumented function
	 *
	 * @param array  $atts Shortcode atts.
	 * @param string $content Content.
	 * @return string
	 */
	public function pdfemb_shortcode_display_pdf( $atts, $content = null ) {

		$atts = apply_filters( 'pdfemb_filter_shortcode_attrs', $atts );

		if ( ! isset( $atts['url'] ) ) {
			return;
		}
		$url = $atts['url'];

		if ( is_admin() ) {
			return $content;
		}

		// Bail out if running an autosave, ajax, cron or revision.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		return $this->pdfemb_shortcode_display_pdf_noncanvas( $atts );

	}

	/**
	 * Undocumented function
	 *
	 * @param array $atts Shortcode atts.
	 * @return bool
	 */
	public function pdfemb_filter_shortcode_gutenberg( $atts = false ) {

		if ( ! isset( $atts['url'] ) ) {
			return false;
		}

	}

	/**
	 * Undocumented function
	 *
	 * @param array $atts Shortcode atts.
	 * @return bool
	 */
	public function pdfemb_shortcode_display_pdf_noncanvas( $atts = false ) {

		if ( ! isset( $atts['url'] ) ) {
			return false;
		}

	}

	/**
	 * Undocumented function
	 *
	 * @param string $url PDF URL.
	 * @return string
	 */
	protected function make_title_from_url( $url ) {
		if ( preg_match( '|/([^/]+?)(\.pdf(\?[^/]*)?)?$|i', $url, $matches ) ) {
			return $matches[1];
		}
		return $url;
	}

	/**
	 * Undocumented function
	 *
	 * @param array  $atts Shortcode atts.
	 * @param string $content Content.
	 * @return string
	 */
	protected function extra_shortcode_attrs( $atts, $content = null ) {
		return '';
	}

	/**
	 * Undocumented function
	 *
	 * @return string
	 */
	protected function get_options_menuname() {
		return 'pdfemb_list_options';
	}

	/**
	 * Undocumented function
	 *
	 * @return string
	 */
	protected function get_options_pagename() {
		return 'pdfemb_options';
	}

	/**
	 * Undocumented function
	 *
	 * @return bool
	 */
	protected function is_multisite_and_network_activated() {
		if ( ! is_multisite() ) {
			return false;
		}

		if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
			require_once ABSPATH . '/wp-admin/includes/plugin.php';
		}
		return is_plugin_active_for_network( $this->pdf_plugin_basename() );
	}

	/**
	 * Undocumented function
	 *
	 * @return string
	 */
	protected function get_settings_url() {
		return $this->is_multisite_and_network_activated()
		? network_admin_url( 'settings.php?page=' . $this->get_options_menuname() )
		: admin_url( 'options-general.php?page=' . $this->get_options_menuname() );
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function get_products() {

		$products = array(
			'soliloquy'             => array(
				'name'        => 'Slider by Soliloquy – Responsive Image Slider for WordPress',
				'description' => 'The best WordPress slider plugin. Drag & Drop responsive slider builder that helps you create a beautiful image slideshows with just a few clicks.',
				'icon'        => $this->pdf_plugin_url() . '/assets/img/partners/soliloquy.png',
				'url'         => 'https://downloads.wordpress.org/plugin/soliloquy-lite.zip',
				'basename'    => 'soliloquy-lite/soliloquy-lite.php',
			),
			'envira'                => array(
				'name'        => 'Envira Gallery',
				'description' => 'Envira Gallery is the fastest, easiest to use WordPress image gallery plugin. Lightbox with Drag & Drop builder that helps you create beautiful galleries.',
				'icon'        => $this->pdf_plugin_url() . '/assets/img/partners/envira-gallery-lite.png',
				'url'         => 'https://downloads.wordpress.org/plugin/envira-gallery-lite.zip',
				'basename'    => 'envira-gallery-lite/envira-gallery-lite.php',

			),
			'google_drive_embedder' => array(
				'name'        => 'Google Drive Embedder',
				'description' => 'Browse for Google Drive documents and embed directly in your posts/pages. This WordPress plugin extends the Google Apps Login plugin so no extra user …',
				'icon'        => $this->pdf_plugin_url() . '/assets/img/partners/google-drive.png',
				'url'         => 'https://downloads.wordpress.org/plugin/google-drive-embedder.zip',
				'basename'    => 'google-drive-embedder/google_drive_embedder.php',
			),
			'google_apps_login'     => array(
				'name'        => 'Google Apps Login',
				'description' => 'Simple secure login and user management through your Google Workspace for WordPress (uses secure OAuth2, and MFA if enabled)',
				'icon'        => $this->pdf_plugin_url() . '/assets/img/partners/google-apps.png',
				'url'         => 'https://downloads.wordpress.org/plugin/google-apps-login.zip',
				'basename'    => 'google-apps-login/google_apps_login.php',

			),
			'all_in_one'            => array(
				'name'        => 'All-In-One Intranet',
				'description' => 'Instantly turn your WordPress installation into a private corporate intranet',
				'icon'        => $this->pdf_plugin_url() . '/assets/img/partners/allinone.png',
				'url'         => 'https://downloads.wordpress.org/plugin/all-in-one-intranet.zip',
				'basename'    => 'all-in-one-intranet/basic_all_in_one_intranet.php',

			),
			'nextgen'               => array(
				'name'        => 'NextGEN Gallery',
				'description' => 'The most popular WordPress gallery plugin and one of the most popular plugins of all time with over 31 million downloads.',
				'icon'        => $this->pdf_plugin_url() . '/assets/img/partners/nextgen.png',
				'url'         => 'https://downloads.wordpress.org/plugin/nextgen-gallery.zip',
				'basename'    => 'nextgen-gallery/nggallery.php',

			),
		);
		return $products;
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function admin_menu() {
		if ( $this->is_multisite_and_network_activated() ) {
			add_submenu_page(
				'settings.php',
				__( 'PDF Embedder settings', 'pdf-embedder' ),
				__( 'PDF Embedder', 'pdf-embedder' ),
				'manage_network_options',
				$this->get_options_menuname(),
				array( $this, 'pdfemb_options_do_page' )
			);
		} else {
			add_options_page(
				__( 'PDF Embedder settings', 'pdf-embedder' ),
				__( 'PDF Embedder', 'pdf-embedder' ),
				'manage_options',
				$this->get_options_menuname(),
				array( $this, 'pdfemb_options_do_page' )
			);
		}
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function pdfemb_admin_menu() {
		if ( $this->is_multisite_and_network_activated() ) {
			add_submenu_page(
				'settings.php',
				__( 'PDF Embedder settings', 'pdf-embedder' ),
				__( 'PDF Embedder', 'pdf-embedder' ),
				'manage_network_options',
				$this->get_options_menuname(),
				array( $this, 'pdfemb_options_do_page' )
			);
		} else {
			add_options_page(
				__( 'PDF Embedder settings', 'pdf-embedder' ),
				__( 'PDF Embedder', 'pdf-embedder' ),
				'manage_options',
				$this->get_options_menuname(),
				array( $this, 'pdfemb_options_do_page' )
			);
		}
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function pdfemb_options_do_page() {

		wp_enqueue_script( 'pdfemb_admin_js', $this->pdf_plugin_url() . 'assets/js/admin/pdfemb-admin.js', array( 'jquery' ), $this->plugin_version );
		wp_enqueue_script( 'pdfemb-welcome-script', $this->pdf_plugin_url() . 'assets/js/admin/welcome.js', array( 'jquery' ), $this->plugin_version );
		wp_localize_script(
			'pdfemb_admin_js',
			'pdfemb_args',
			array(
				'activate_nonce'   => wp_create_nonce( 'pdfemb-activate-partner' ),
				'active'           => __( 'Status: Active', 'pdf-embedder' ),
				'activate'         => __( 'Activate', 'pdf-embedder' ),
				'get_addons_nonce' => wp_create_nonce( 'pdfemb-get-addons' ),
				'activating'       => __( 'Activating...', 'pdf-embedder' ),
				'ajax'             => admin_url( 'admin-ajax.php' ),
				'deactivate'       => __( 'Deactivate', 'pdf-embedder' ),
				'deactivate_nonce' => wp_create_nonce( 'pdfemb-deactivate-partner' ),
				'deactivating'     => __( 'Deactivating...', 'pdf-embedder' ),
				'inactive'         => __( 'Status: Inactive', 'pdf-embedder' ),
				'install'          => __( 'Install', 'pdf-embedder' ),
				'install_nonce'    => wp_create_nonce( 'pdfemb-install-partner' ),
				'installing'       => __( 'Installing...', 'pdf-embedder' ),
				'proceed'          => __( 'Proceed', 'pdf-embedder' ),
			)
		);
		wp_enqueue_style( 'pdfemb_admin_css', $this->pdf_plugin_url() . 'assets/css/pdfemb-admin.css', array(), $this->plugin_version );

		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );

		$submit_page = $this->is_multisite_and_network_activated() ? 'edit.php?action=' . $this->get_options_menuname() : 'options.php';

		if ( $this->is_multisite_and_network_activated() ) {
			$this->pdfemb_options_do_network_errors();
		}

		?>

		<div id="pdfemb-header">
			<div class="pdfemb-logo">
				<img src ="<?php echo esc_url( $this->pdf_plugin_url() ); ?><?php echo 'assets/img/pdf-embedder.png'; ?>" height="45px" />
				<h1>PDF Embedder</h1>
			</div>
		</div>
		<div>

		<h2 id="pdfemb-tabs" class="nav-tab-wrapper">
					<a href="#main" id="main-tab" class="nav-tab nav-tab-active"><?php esc_html_e( 'Main Settings', 'pdf-embedder' ); ?></a>
					<a href="#mobile" id="mobile-tab" class="nav-tab"><?php esc_html_e( 'Mobile', 'pdf-embedder' ); ?></a>
					<a href="#secure" id="secure-tab" class="nav-tab"><?php esc_html_e( 'Secure', 'pdf-embedder' ); ?></a>
					<a href="#about" id="about-tab" class="nav-tab"><?php esc_html_e( 'About', 'pdf-embedder' ); ?></a>
				<?php $this->draw_more_tabs(); ?>
		</h2>

			<div id="pdfemb-tablewrapper">

			<div id="pdfemb-tableleft" class="pdfemb-tablecell">

				<form action="<?php echo esc_attr( $submit_page ); ?>" method="post" id="pdfemb_form" enctype="multipart/form-data" >

		<?php

		echo '<div id="main-section" class="pdfembtab active">';
		$this->render_main_section();
		echo '</div>';

		echo '<div id="mobile-section" class="pdfembtab">';
		$this->pdfemb_mobilesection_text();
		echo '</div>';

		echo '<div id="secure-section" class="pdfembtab">';
		$this->pdfemb_securesection_text();
		echo '</div>';

		echo '<div id="about-section" class="pdfembtab">';
		$this->render_about_section();
		echo '</div>';

		$this->draw_extra_sections();

		settings_fields( $this->get_options_pagename() );

		?>

		<p class="submit">
			<input type="submit" value="<?php esc_html_e( 'Save Changes', 'pdf-embedder' ); ?>" class="button button-primary" id="submit" name="submit">
		</p>

		</form>
		</div>

		<?php $this->options_do_sidebar(); ?>

		</div>

		</div>
		<?php
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	protected function options_do_sidebar() {
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	protected function draw_more_tabs() {}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	protected function draw_extra_sections() {}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	protected function render_main_section() {
		$options = $this->get_option_pdfemb();
		?>

		<h2><?php esc_html_e( 'PDF Embedder setup', 'pdf-embedder' ); ?></h2>

		<p><?php esc_html_e( 'To use the plugin, just embed PDFs in the same way as you would normally embed images in your posts/pages - but try with a PDF file instead.', 'pdf-embedder' ); ?></p>
		<p>
		<?php
		esc_html_e(
			"From the post editor, click Add Media, and then drag-and-drop your PDF file into the media library.
		When you insert the PDF into your post, it will automatically embed using the plugin's viewer.",
			'pdf-embedder'
		);
		?>
			</p>

		<h2><?php esc_html_e( 'Default Viewer Settings', 'pdf-embedder' ); ?></h2>

		<label for="input_pdfemb_width" class="textinput"><?php esc_html_e( 'Width', 'pdf-embedder' ); ?></label>
		<input id='input_pdfemb_width' class='textinput' name='<?php echo esc_attr( $this->get_options_name() ); ?>[pdfemb_width]' size='10' type='text' value='<?php echo esc_attr( $options['pdfemb_width'] ); ?>' />
		<br class="clear"/>

		<label for="input_pdfemb_height" class="textinput"><?php esc_html_e( 'Height', 'pdf-embedder' ); ?></label>
		<input id='input_pdfemb_height' class='textinput' name='<?php echo esc_attr( $this->get_options_name() ); ?>[pdfemb_height]' size='10' type='text' value='<?php echo esc_attr( $options['pdfemb_height'] ); ?>' />
		<br class="clear"/>

		<p class="desc big"><i><?php _e( 'Enter <b>max</b> or an integer number of pixels', 'pdf-embedder' ); ?></i></p>

		<br class="clear"/>

		<label for="pdfemb_toolbar" class="textinput"><?php esc_html_e( 'Toolbar Location', 'pdf-embedder' ); ?></label>
		<select name='<?php echo esc_attr( $this->get_options_name() ); ?>[pdfemb_toolbar]' id='pdfemb_toolbar' class='select'>
			<option value="top" <?php echo 'top' === $options['pdfemb_toolbar'] ? 'selected' : ''; ?>><?php esc_html_e( 'Top', 'pdf-embedder' ); ?></option>
			<option value="bottom" <?php echo 'bottom' === $options['pdfemb_toolbar'] ? 'selected' : ''; ?>><?php esc_html_e( 'Bottom', 'pdf-embedder' ); ?></option>
			<option value="both" <?php echo 'both' === $options['pdfemb_toolbar'] ? 'selected' : ''; ?>><?php esc_html_e( 'Both', 'pdf-embedder' ); ?></option>
			<?php $this->no_toolbar_option( $options ); ?>
		</select>
		<br class="clear" />
		<br class="clear" />

		<label for="pdfemb_toolbarfixed" class="textinput"><?php esc_html_e( 'Toolbar Hover', 'pdf-embedder' ); ?></label>
		<span>
		<input type="radio" name='<?php echo esc_attr( $this->get_options_name() ); ?>[pdfemb_toolbarfixed]' id='pdfemb_toolbarfixed_off' class='radio' value="off" <?php echo 'off' === $options['pdfemb_toolbarfixed'] ? 'checked' : ''; ?> />
		<label for="pdfemb_toolbarfixed_off" class="radio"><?php esc_html_e( 'Toolbar appears only on hover over document', 'pdf-embedder' ); ?></label>
		</span>
		<br/>
		<span>
		<input type="radio" name='<?php echo esc_attr( $this->get_options_name() ); ?>[pdfemb_toolbarfixed]' id='pdfemb_toolbarfixed_on' class='radio' value="on" <?php echo 'on' === $options['pdfemb_toolbarfixed'] ? 'checked' : ''; ?> />
		<label for="pdfemb_toolbarfixed_on" class="radio"><?php esc_html_e( 'Toolbar always visible', 'pdf-embedder' ); ?></label>
		</span>
		<br/><br/>
		<label for="pdfemb_toolbarfixed" class="textinput"><?php esc_html_e( 'Display Credit', 'pdf-embedder' ); ?></label>
		<span>
		<input type='checkbox' name='<?php echo esc_attr( $this->get_options_name() ); ?>[poweredby]' id='poweredby' class='checkbox' <?php echo isset( $options['poweredby'] ) && 'on' === $options['poweredby'] ? 'checked' : ''; ?>  />

		<label for="poweredby" class="checkbox plain" style="margin-left: 10px;"><?php esc_html_e( 'Display "Powered by wp-pdf.com" on PDF Viewer with a link to our site. Spread the love!', 'pdf-embedder' ); ?></label>
		</span>
		<?php
			$this->pdfemb_mainsection_extra();
		?>

		<br class="clear" />
		<br class="clear" />

		<?php
	}

	/**
	 * Undocumented function
	 *
	 * Override in commercial.
	 *
	 * @param array $options Options.
	 * @return void
	 */
	protected function no_toolbar_option( $options ) {}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	protected function pdfemb_mainsection_extra() {}

	/**
	 * Undocumented function
	 *
	 * @return string
	 */
	protected function get_instructions_url() {
		return 'http://wp-pdf.com/free-instructions/?utm_source=PDF%20Settings%20Main&utm_medium=freemium&utm_campaign=Freemium';
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	protected function pdfemb_mobilesection_text() {
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	protected function pdfemb_securesection_text() {
		?>

		<h2><?php esc_html_e( 'Protect your PDFs using PDF Embedder Secure', 'pdf-embedder' ); ?></h2>
		<p>
		<?php
		_e(
			'Our <b>PDF Embedder Premium Secure</b> plugin provides the same simple but elegant viewer for your website visitors, with the added protection that
            it is difficult for users to download or print the original PDF document.',
			'pdf-embedder'
		);
		?>
			</p>

		<p><?php esc_html_e( 'This means that your PDF is unlikely to be shared outside your site where you have no control over who views, prints, or shares it.', 'pdf-embedder' ); ?></p>

		<p><?php esc_html_e( "Optionally add a watermark containing the user's name or email address to discourage sharing of screenshots.", 'pdf-embedder' ); ?></p>

		<p><?php printf( __( 'See our website <a href="%s">wp-pdf.com</a> for more details and purchase options.', 'pdf-embedder' ), 'http://wp-pdf.com/secure/?utm_source=PDF%20Settings%20Secure&utm_medium=freemium&utm_campaign=Freemium' ); ?>
		</p>

		<?php
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	protected function render_about_section() {
		?>

		<div class="lionsher-get-started-main">


			<div class="lionsher-get-started-panel">

				<div class="wraps upgrade-wrap">

					<h2 class="headline-title"><?php esc_html_e( 'See Our Partners!', 'pdf-embedder' ); ?></h2>

					<h4 class="headline-subtitle"><?php esc_html_e( 'We have partnered with these amazing companies for further enhancement to your PDF Embedder experience.', 'pdf-embedder' ); ?></h4>
					<div class="lionsher-partners-wrap">
					<?php
					foreach ( $this->get_products() as $products ) :

						$this->get_plugin_card( $products );

					endforeach;
					?>
					</div>
				</div>
			</div>
		</div>

		<?php

	}

	/**
	 * Undocumented function
	 *
	 * @param array $input Options array.
	 * @return array
	 */
	public function pdfemb_options_validate( $input ) {

		$newinput = array();

		$newinput['pdfemb_width'] = isset( $input['pdfemb_width'] ) ? trim( strtolower( $input['pdfemb_width'] ) ) : 'max';
		if ( ! is_numeric( $newinput['pdfemb_width'] ) && 'max' !== $newinput['pdfemb_width'] && 'auto' !== $newinput['pdfemb_width'] ) {
			add_settings_error(
				'pdfemb_width',
				'widtherror',
				self::get_error_string( 'pdfemb_width|widtherror' ),
				'error'
			);
		}

		$newinput['pdfemb_height'] = isset( $input['pdfemb_height'] ) ? trim( strtolower( $input['pdfemb_height'] ) ) : 'max';
		if ( ! is_numeric( $newinput['pdfemb_height'] ) && 'max' !== $newinput['pdfemb_height'] && 'auto' !== $newinput['pdfemb_height'] ) {
			add_settings_error(
				'pdfemb_height',
				'heighterror',
				self::get_error_string( 'pdfemb_height|heighterror' ),
				'error'
			);
		}

		if ( isset( $input['pdfemb_toolbar'] ) && in_array( $input['pdfemb_toolbar'], array( 'top', 'bottom', 'both', 'none' ), true ) ) {
			$newinput['pdfemb_toolbar'] = $input['pdfemb_toolbar'];
		} else {
			$newinput['pdfemb_toolbar'] = 'top';
		}

		if ( isset( $input['pdfemb_toolbarfixed'] ) && in_array( $input['pdfemb_toolbarfixed'], array( 'on', 'off' ), true ) ) {
			$newinput['pdfemb_toolbarfixed'] = $input['pdfemb_toolbarfixed'];
		}

		if ( isset( $input['poweredby'] ) && in_array( $input['poweredby'], array( 'on', 'off' ), true ) ) {
			$newinput['poweredby'] = $input['poweredby'];
		}

		$newinput['pdfemb_version'] = $this->plugin_version;
		return $newinput;
	}

	/**
	 * Undocumented function
	 *
	 * @param string $fielderror Field Error.
	 * @return string
	 */
	protected function get_error_string( $fielderror ) {
		$local_error_strings = array(
			'pdfemb_width|widtherror'   => __( 'Width must be "max" or an integer (number of pixels)', 'pdf-embedder' ),
			'pdfemb_height|heighterror' => __( 'Height must be "max" or an integer (number of pixels)', 'pdf-embedder' ),
		);
		if ( isset( $local_error_strings[ $fielderror ] ) ) {
			return $local_error_strings[ $fielderror ];
		}

		return __( 'Unspecified error', 'pdf-embedder' );
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function pdfemb_save_network_options() {
		check_admin_referer( $this->get_options_pagename() . '-options' );

		if ( isset( $_POST[ $this->get_options_name() ] ) && is_array( wp_unslash( $_POST[ $this->get_options_name() ] ) ) ) {

			$outoptions = $this->pdfemb_options_validate( wp_unslash( $_POST[ $this->get_options_name() ] ) ); // WPCS:XSS ok array sanitized when set in gdm_options_validate.

			$error_code    = array();
			$error_setting = array();
			foreach ( get_settings_errors() as $e ) {
				if ( is_array( $e ) && isset( $e['code'] ) && isset( $e['setting'] ) ) {
					$error_code[]    = $e['code'];
					$error_setting[] = $e['setting'];
				}
			}

			if ( $this->is_multisite_and_network_activated() ) {
				update_site_option( $this->get_options_name(), $outoptions );
			} else {
				update_option( $this->get_options_name(), $outoptions );
			}

			// redirect to settings page in network.
			wp_safe_redirect(
				add_query_arg(
					array(
						'page'          => $this->get_options_menuname(),
						'updated'       => true,
						'error_setting' => $error_setting,
						'error_code'    => $error_code,
					),
					network_admin_url( 'admin.php' )
				)
			);
			exit;
		}
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	protected function pdfemb_options_do_network_errors() {
		if ( isset( $_REQUEST['updated'] ) && sanitize_text_field( wp_unslash( $_REQUEST['updated'] ) ) ) {
			?>
					<div id="setting-error-settings_updated" class="updated settings-error">
					<p>
					<strong><?php esc_html_e( 'Settings saved', 'pdf-embedder' ); ?></strong>
					</p>
					</div>
				<?php
		}

		if ( isset( $_REQUEST['error_setting'] ) && is_array( $_REQUEST['error_setting'] )
				&& isset( $_REQUEST['error_code'] ) && is_array( $_REQUEST['error_code'] ) ) {

			if ( count( $_REQUEST['error_code'] ) > 0 && count( $_REQUEST['error_code'] ) === count( $_REQUEST['error_setting'] ) ) {
				$count = count( $_REQUEST['error_code'] );
				for ( $i = 0; $i < $count; ++$i ) {
					if ( ! isset( $_REQUEST['error_setting'][ $i ] ) || ! isset( $_REQUEST['error_setting'][ $i ] ) ) {
						return;
					}
					?>
					<div id="setting-error-settings_<?php echo esc_attr( $i ); ?>" class="error settings-error">
					<p>
					<strong><?php echo esc_html( $this->get_error_string( sanitize_text_field( wp_unslash( $_REQUEST['error_setting'][ $i ] ) ) . '|' . sanitize_text_field( wp_unslash( $_REQUEST['error_setting'][ $i ] ) ) ) ); ?></strong>
					</p>
					</div>
						<?php
				}
			}
		}
	}

	/**
	 * Undocumented function
	 *
	 * @return string
	 */
	protected function get_options_name() {
		return 'pdfemb';
	}

	/**
	 * Undocumented function
	 *
	 * @return string
	 */
	public static function self_option_name() {
		return 'pdfemb';
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	protected function get_default_options() {
		return array(
			'pdfemb_width'         => 'max',
			'pdfemb_height'        => 'max',
			'pdfemb_toolbar'       => 'top',
			'pdfemb_toolbarfixed'  => 'off',
			'pdfemb_poweredby'     => 'off',
			'pdfemb_version'       => $this->plugin_version,
			'pdfemb_color_sidebar' => '',
			'pdfemb_color_button'  => '',
			'pdfemb_color_icon'    => '',
		);
	}

	/**
	 * Undocumented function
	 *
	 * @param array $shortcode_atts Shortcode Atts.
	 * @return mixed
	 */
	public function get_option_pdfemb( $shortcode_atts = false ) {
		if ( null !== $this->pdfemb_options && false === $shortcode_atts ) {
			return $this->pdfemb_options;
		}

		if ( $this->is_multisite_and_network_activated() ) {
			$option = get_site_option( $this->get_options_name(), array() );
		} else {
			$option = get_option( $this->get_options_name(), array() );
		}

		// default options.
		$default_options = $this->get_default_options();
		foreach ( $default_options as $k => $v ) {
			if ( ! isset( $option[ $k ] ) ) {
				$option[ $k ] = $v;
			}
		}

		if ( ! empty( $shortcode_atts ) ) :

			foreach ( $shortcode_atts as $k => $v ) {
				// determine if a passed shortcode matches an established option?
				if ( array_key_exists( 'pdfemb_' . $k, $default_options ) ) {
					$default_options[ 'pdfemb_' . $k ] = $v;
					$option[ 'pdfemb_' . $k ]          = $v;
				} else {
					$option[ 'pdfemb_' . $k ] = $v;
				}
			}

		endif;

		// override any default option with the shortcode option, if exists.

		$this->pdfemb_options = $option;
		return $this->pdfemb_options;
	}

	/**
	 * Undocumented function
	 *
	 * @param string $option Option.
	 * @return void
	 */
	protected function save_option_pdfemb( $option ) {
		if ( $this->is_multisite_and_network_activated() ) {
			update_site_option( $this->get_options_name(), $option );
		} else {
			update_option( $this->get_options_name(), $option );
		}
		$this->pdfemb_options = $option;
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function pdfemb_admin_init() {
		// Add PDF as a supported upload type to Media Gallery.
		add_filter( 'upload_mimes', array( $this, 'pdfemb_upload_mimes' ) );

		// Filter for PDFs in Media Gallery.
		add_filter( 'post_mime_types', array( $this, 'pdfemb_post_mime_types' ) );

		// Embed PDF shortcode instead of link.
		add_filter( 'media_send_to_editor', array( $this, 'pdfemb_media_send_to_editor' ), 20, 3 );

		register_setting( $this->get_options_pagename(), $this->get_options_name(), array( $this, 'pdfemb_options_validate' ) );

		add_filter( 'attachment_fields_to_edit', array( $this, 'pdfemb_attachment_fields_to_edit' ), 10, 2 );

		wp_enqueue_style( 'pdfemb_admin_other_css', $this->pdf_plugin_url() . 'assets/css/pdfemb-admin-other.css', array(), $this->plugin_version );
		if ( is_admin() ) {
			add_action( 'enqueue_block_editor_assets', array( $this, 'gutenberg_enqueue_block_editor_assets' ) );
			add_action( 'admin_footer', array( $this, 'add_settings_js' ) );

		}

	}

	/**
	 * Outputs JS needed for some Envira admin screens.
	 *
	 * @since 1.7.0
	 */
	public function add_settings_js() {

        if ( ! empty( $_GET['page'] ) && 'pdfemb_list_options' === $_GET['page'] ) { // @codingStandardsIgnoreLine

			?>

		<script type="text/javascript">
		jQuery(document).ready(function($){
			$('.pdf-color-sidebar').wpColorPicker();
			$('.pdf-color-button').wpColorPicker();
			$('.pdf-color-icon').wpColorPicker();
		});
		</script>
			<?php
		}
	}

	/**
	 * Undocumented function
	 *
	 * @param string $form_fields Form Fields.
	 * @param object $post Post.
	 * @return string
	 */
	public function pdfemb_attachment_fields_to_edit( $form_fields, $post ) {
		return $form_fields;
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function pdfemb_init() {

		$GLOBALS['pdf_index'] = 1;

		add_shortcode( 'pdf-embedder', array( $this, 'pdfemb_shortcode_display_pdf' ) );

		// Gutenberg block.
		if ( function_exists( 'register_block_type' ) ) {
			register_block_type(
				'pdfemb/pdf-embedder-viewer',
				array(
					'render_callback' => array( $this, 'pdfemb_shortcode_display_pdf' ),
				)
			);
		}
		if ( is_admin() ) {
			add_action( 'enqueue_block_assets', array( $this, 'gutenberg_enqueue_block_assets' ) );
		}

	}

	/**
	 * Undocumented function
	 *
	 * @param string $links Action links.
	 * @param string $file Plugin file.
	 * @return string
	 */
	public function pdfemb_plugin_action_links( $links, $file ) {
		if ( $file === $this->pdf_plugin_basename() ) {
			$links = $this->extra_plugin_action_links( $links );

			$settings_link = '<a href="' . $this->get_settings_url() . '">' . __( 'Settings', 'pdf-embedder' ) . '</a>';
			array_unshift( $links, $settings_link );
		}

		return $links;
	}

	/**
	 * Undocumented function
	 *
	 * @param string $links Action Links.
	 * @return string
	 */
	protected function extra_plugin_action_links( $links ) {
		return $links;
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function pdfemb_plugins_loaded() {
		load_plugin_textdomain( 'pdf-embedder', false, dirname( $this->pdf_plugin_basename() ) . '/lang/' );
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	protected function add_actions() {

		add_action( 'plugins_loaded', array( $this, 'pdfemb_plugins_loaded' ) );

		add_action( 'init', array( $this, 'pdfemb_init' ) );

		add_action( 'wp_enqueue_scripts', array( $this, 'pdfemb_wp_enqueue_scripts' ), 5, 0 );

		if ( is_admin() ) {
			add_action( 'admin_init', array( $this, 'pdfemb_admin_init' ), 5, 0 );

			add_action( $this->is_multisite_and_network_activated() ? 'network_admin_menu' : 'admin_menu', array( $this, 'pdfemb_admin_menu' ) );

			if ( $this->is_multisite_and_network_activated() ) {
				add_action( 'network_admin_edit_' . $this->get_options_menuname(), array( $this, 'pdfemb_save_network_options' ) );
			}

			add_filter( $this->is_multisite_and_network_activated() ? 'network_admin_plugin_action_links' : 'plugin_action_links', array( $this, 'pdfemb_plugin_action_links' ), 10, 2 );
		}
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function gutenberg_enqueue_block_editor_assets() {
		wp_enqueue_script(
			'pdfemb-gutenberg-block-js', // Unique handle.
			$this->pdf_plugin_url() . 'assets/js/pdfemb-blocks.js',
			array( 'wp-blocks', 'wp-i18n', 'wp-element' ), // Dependencies, defined above.
			$this->plugin_version
		);

		wp_enqueue_style(
			'pdfemb-gutenberg-block-css', // Handle.
			$this->pdf_plugin_url() . 'assets/css/pdfemb-blocks.css', // editor.css: This file styles the block within the Gutenberg editor.
			array(),
			$this->plugin_version
		);
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function gutenberg_enqueue_block_assets() {
		wp_enqueue_style(
			'pdfemb-gutenberg-block-backend-js', // Handle.
			$this->pdf_plugin_url() . 'assets/css/pdfemb-blocks.css', // style.css: This file styles the block on the frontend.
			array(),
			$this->plugin_version
		);
	}

	/**
	 * Undocumented function
	 *
	 * @param bool $plugin Partner Plugin.
	 * @return void
	 */
	public function get_plugin_card( $plugin = false ) {

		if ( ! $plugin ) {
			return;
		}
		$this->installed_plugins = get_plugins();

		if ( ! isset( $this->installed_plugins[ $plugin['basename'] ] ) ) {
			?>
			<div class="lionsher-partners">
				<div class="lionsher-partners-main">
					<div>
						<img src="<?php echo esc_url( $plugin['icon'] ); ?>" width="64px" />
					</div>
					<div>
						<h3><?php echo esc_html( $plugin['name'] ); ?></h3>
						<p class="lionsher-partner-excerpt"><?php echo esc_html( $plugin['description'] ); ?></p>
					</div>
				</div>
					<div class="lionsher-partners-footer">
					<div class="lionsher-partner-status">Status:&nbsp;<span>Not Installed</span></div>
						<div class="lionsher-partners-install-wrap">
							<a href="#" target="_blank" class="button button-primary lionsher-partners-button lionsher-partners-install" data-url="<?php echo esc_url( $plugin['url'] ); ?>" data-basename="<?php echo esc_attr( $plugin['basename'] ); ?>">Install Plugin</a>
							<span class="spinner lionsher-gallery-spinner"></span>
						</div>
					</div>
				</div>
			<?php
		} else {
			if ( is_plugin_active( $plugin['basename'] ) ) {
				?>
							<div class="lionsher-partners">
							<div class="lionsher-partners-main">
								<div>
									<img src="<?php echo esc_attr( $plugin['icon'] ); ?>" width="64px" />
								</div>
								<div>
									<h3><?php echo esc_html( $plugin['name'] ); ?></h3>
								<p class="lionsher-partner-excerpt"><?php echo esc_html( $plugin['description'] ); ?></p>
								</div>
							</div>
								<div class="lionsher-partners-footer">
								<div class="lionsher-partner-status">Status:&nbsp;<span>Active</span></div>
									<div class="lionsher-partners-install-wrap">
							<a href="#" target="_blank" class="button button-primary lionsher-partners-button lionsher-partners-deactivate" data-url="<?php echo esc_url( $plugin['url'] ); ?>" data-basename="<?php echo esc_attr( $plugin['basename'] ); ?>">Deactivate</a>
							<span class="spinner lionsher-gallery-spinner"></span>
						</div>
				</div>
						</div>
			<?php } else { ?>
				<div class="lionsher-partners">
							<div class="lionsher-partners-main">
								<div>
									<img src="<?php echo esc_attr( $plugin['icon'] ); ?>" width="64px" />
								</div>
								<div>
									<h3><?php echo esc_html( $plugin['name'] ); ?></h3>
								<p class="lionsher-partner-excerpt"><?php echo esc_html( $plugin['description'] ); ?></p>
								</div>
							</div>
								<div class="lionsher-partners-footer">
									<div class="lionsher-partner-status">Status:&nbsp;<span>Inactive</span></div>
								<div class="lionsher-partners-install-wrap">
							<a href="#" target="_blank" class="button button-primary lionsher-partners-button lionsher-partners-activate" data-url="<?php echo esc_url( $plugin['url'] ); ?>" data-basename="<?php echo esc_attr( $plugin['basename'] ); ?>">Activate</a>
							<span class="spinner lionsher-gallery-spinner"></span>
						</div>
				</div>
						</div>
				<?php
			}
		}
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function activate_partner() {
		// Run a security check first.
		check_admin_referer( 'pdfemb-activate-partner', 'nonce' );

		// Activate the addon.
		if ( isset( $_POST['basename'] ) ) {
			$activate = activate_plugin( wp_unslash( $_POST['basename'] ) );  // @codingStandardsIgnoreLine

			if ( is_wp_error( $activate ) ) {
				echo wp_json_encode( array( 'error' => $activate->get_error_message() ) );
				die;
			}
		}

		echo wp_json_encode( true );
		die;

	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function deactivate_partner() {
		// Run a security check first.
		check_admin_referer( 'pdfemb-deactivate-partner', 'nonce' );

		// Deactivate the addon.
		if ( isset( $_POST['basename'] ) ) {
			$deactivate = deactivate_plugins( wp_unslash( $_POST['basename'] ) );  // @codingStandardsIgnoreLine
		}

		echo wp_json_encode( true );
		die;
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function install_partner() {

		check_admin_referer( 'pdfemb-install-partner', 'nonce' );
		// Install the addon.
		if ( isset( $_POST['download_url'] ) ) {

			$download_url = esc_url_raw( wp_unslash( $_POST['download_url'] ) );
			global $hook_suffix;

			// Set the current screen to avoid undefined notices.
			set_current_screen();

			// Prepare variables.
			$method = '';
			$url    = add_query_arg(
				array(
					'page' => 'pdfemb_list_options',
				),
				admin_url( 'options-general.php' )
			);
			$url    = esc_url( $url );

			// Start output bufferring to catch the filesystem form if credentials are needed.
			ob_start();
			$creds = request_filesystem_credentials( $url, $method, false, false, null );
			if ( false === $creds ) {
				$form = ob_get_clean();
				echo wp_json_encode( array( 'form' => $form ) );
				die;
			}

			// If we are not authenticated, make it happen now.
			if ( ! WP_Filesystem( $creds ) ) {
				ob_start();
				request_filesystem_credentials( $url, $method, true, false, null );
				$form = ob_get_clean();
				echo wp_json_encode( array( 'form' => $form ) );
				die;
			}

			// We do not need any extra credentials if we have gotten this far, so let's install the plugin.
			require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
			require_once plugin_dir_path( $this->file ) . 'core/install_skin.php';

			// Create the plugin upgrader with our custom skin.
			$skin      = new WPPDF_Skin();
			$installer = new Plugin_Upgrader( $skin );
			$installer->install( $download_url );

			// Flush the cache and return the newly installed plugin basename.
			wp_cache_flush();

			if ( $installer->plugin_info() ) {
				$plugin_basename = $installer->plugin_info();

				wp_send_json_success( array( 'plugin' => $plugin_basename ) );

				die();
			}
		}

		// Send back a response.
		echo wp_json_encode( true );
		die;

	}

	/**
	 * Helper Method to get Upgrade URL.
	 *
	 * @since 1.0.0
	 *
	 * @param boolean $url URL.
	 * @param string  $medium Location.
	 * @param string  $button Which button.
	 * @param boolean $append Add extras.
	 * @return string
	 */
	public function get_upgrade_link( $url = false, $medium = 'default', $button = 'default', $append = false ) {

		$source = apply_filters( 'pbfemb_tracking_src', 'liteplugin' );

		if ( defined( 'PDFEMB_TRACKING_SRC' ) ) {
			$source = PDFEMB_TRACKING_SRC;
		}

		if ( false === filter_var( $url, FILTER_VALIDATE_URL ) ) {
			// prevent a possible typo.
			$url = false;
		}

		$url = ( false !== $url ) ? trailingslashit( esc_url( $url ) ) : 'https://wp-pdf.com/';
		return $url . '?utm_source=' . $source . '&utm_medium=' . $medium . '&utm_campaign=' . $button . $append;

	}

	/**
	 * Undocumented function
	 *
	 * @param array  $atts Shortcode atts.
	 * @param string $content Content.
	 * @return void
	 */
	public function pdf_embedder_counts( $atts, $content = '' ) {}

	/**
	 * Undocumented function
	 *
	 * @param array  $atts Shortcode atts.
	 * @param string $content Content.
	 * @return void
	 */
	public function pdf_embedder_view_counts( $atts, $content = '' ) {}

	/**
	 * Undocumented function
	 *
	 * @param array  $atts Shortcode atts.
	 * @param string $content Content.
	 * @return void
	 */
	public function pdf_embedder_download_counts( $atts, $content = '' ) {}

	/**
	 * Helper Method to get plugin basename
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	protected function pdf_plugin_basename() {
		$basename = plugin_basename( __FILE__ );
		if ( __FILE__ === '/' . $basename ) { // Maybe due to symlink.
			$basename = basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ );
		}
		return $basename;
	}

	/**
	 * Helper method to get plugin url
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	protected function pdf_plugin_url() {
		$basename = plugin_basename( __FILE__ );
		if ( __FILE__ === '/' . $basename ) { // Maybe due to symlink.
			return plugins_url() . '/' . basename( dirname( __FILE__ ) ) . '/';
		}
		// Normal case (non symlink).
		return plugin_dir_url( __FILE__ );
	}

}
