<?php
/**
 * WP PDF Commercial Class
 *
 * @since 1.0.0
 *
 * @package WP-PDF-Secure
 */

if ( class_exists( 'WP_PDF_Core' ) ) {
	global $pdfemb_core_already_exists;
	$pdfemb_core_already_exists = true;
} else {
	require_once plugin_dir_path( __FILE__ ) . '/core_pdf_embedder.php';
}

/**
 * Commerical Class.
 */
class WP_PDF_Commercial extends WP_PDF_Core {

	/**
	 * Init Method
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function init() {
		parent::init();

		if ( is_admin() ) {

			add_action( 'add_meta_boxes_attachment', array( $this, 'setup_attachment_metaboxes' ) );
			add_action( 'edit_attachment', array( $this, 'save_attachment_meta_box_data' ) );

		}

		// Allow scripts to be injected into frame without call wp_head/wp_footer to prevent code injections.
		add_action( 'wp_pdf_viewer_head', 'wp_enqueue_scripts',1 );
		add_action( 'wp_pdf_viewer_head', 'wp_preload_resources', 1 );
		add_action( 'wp_pdf_viewer_head', 'wp_print_styles',1 );
		add_action( 'wp_pdf_viewer_head', 'wp_print_head_scripts',1 );
		add_action( 'wp_pdf_viewer_head', 'wp_custom_css_cb',1 );
		add_action( 'wp_pdf_viewer_head', 'wp_site_icon', 99 );
		add_action( 'wp_pdf_viewer_head', 'wp_maybe_inline_styles',1 );
		add_action( 'wp_pdf_viewer_head', 'wp_print_scripts', 20 );

		add_action( 'wp_pdf_viewer_footer', 'wp_print_footer_scripts',1 );
		add_action( 'wp_pdf_viewer_footer', 'wp_enqueue_global_styles',1 );
		add_action( 'wp_pdf_viewer_footer', 'wp_enqueue_stored_styles', 1 );
		add_action( 'wp_pdf_viewer_footer', 'wp_maybe_inline_styles', 1 );


		//Set the prior
		add_filter( 'template_include', array( $this, 'template_include' ), 20001, 1 );
		add_filter( 'show_admin_bar', array( $this, 'hide_viewer_admin_bar' ), 10, 1 );

		//Make sure theme scripts/styles dont load on the viewer.php
		add_action('wp_print_styles', array( $this, 'remove_styles_viewer' ), 99999 );
		add_action('wp_print_scripts', array( $this, 'remove_scripts_viewer' ), 99999 );
		add_filter( 'the_content', array( $this, 'pdfemb_the_content' ), 20, 1 );

	}

	/**
	 * Helper method to remove viewer styles.
	 *
	 * @since 5.1.2
	 *
	 * @return void
	 */
	public function remove_styles_viewer() {
		global $wp_styles, $template;

		if ( null === $template ) {
			return;
		}
		if ( 'wppdfemb-viewer.php' !== basename( $template ) ) {
			return;
		}

		// Runs through the queue styles
		foreach ($wp_styles->queue as $handle) :
			if( strpos( $handle, 'wppdfemb-') !== false ) {
				continue;
			}
			wp_dequeue_style($handle);
		endforeach;
	}

	/**
	 * Helper method to remove viewer scripts.
	 *
	 * @since 5.1.2
	 *
	 * @return void
	 */
	public function remove_scripts_viewer() {

		global $wp_scripts, $template;

		if ( null === $template ) {
			return;
		}
		if ( 'wppdfemb-viewer.php' !== basename( $template ) ) {
			return;
		}

		// Runs through the queue styles
		foreach ($wp_scripts->queue as $handle) :
			if( strpos( $handle, 'wppdfemb-') !== false ) {
				continue;
			}
			wp_dequeue_script($handle);
		endforeach;

	}

	/**
	 * Helper Method to Enqueue Scripts
	 *
	 * @return void
	 */
	public function pdfemb_wp_enqueue_scripts() {

		global $template;

		if ( null === $template ) {
			return;
		}
		if ( 'wppdfemb-viewer.php' !== basename( $template ) ) {
			return;
		}

		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'wppdfemb-worker-js', trailingslashit( WP_PDF_SECURE_URL ) . 'assets/js/min/pdf-worker-min.js', array( 'jquery' ), time(), true );
		wp_enqueue_script( 'wppdfemb-script-js', trailingslashit( WP_PDF_SECURE_URL ) . 'assets/js/min/pdf-viewer-min.js', array( 'jquery', 'wppdfemb-worker-js' ), time(), true );

		wp_add_inline_script(
			'wppdfemb-script-js',
			'var pdfemb_trans =' . wp_json_encode( $this->get_translation_array() ),
			'before'
		);

		wp_add_inline_script(
			'wppdfemb-script-js',
			'const pdf_embed_global = ' . wp_json_encode(
				array(
					'pathToAssets' => trailingslashit( WP_PDF_SECURE_URL ) . 'assets/',
				)
			),
			'before'
		);
		wp_enqueue_style( 'wppdfemb-styles', trailingslashit( WP_PDF_SECURE_URL ) . 'assets/css/pdf-embedder.css', null, WP_PDF_VERSION, 'all' );
		wp_enqueue_style( 'wppdfemb-print-styles', trailingslashit( WP_PDF_SECURE_URL ) . 'assets/css/pdf-embedder-print.css', null, WP_PDF_VERSION, 'print' );


	}

	/**
	 * Hide Viewer Admin Bar
	 *
	 * @since 5.1.0
	 *
	 * @return void
	 */
	public function hide_viewer_admin_bar( $show ) {

		global $template;

		if ( null === $template ) {
			return $show;
		}

		if ( 'wppdfemb-viewer.php' === basename( $template ) ) {
			return false;
		}

		return $show;

	}

	/**
	 * Helper Method to include template
	 *
	 * @since 5.1.0
	 *
	 * @param string $template Current Template.
	 * @return string
	 */
	public function template_include( $template ) {

		if ( isset( $_GET['pdfID'] ) ) {
			$args     = implode( '&', $_GET );
			$template = plugin_dir_path( __FILE__ ) . 'views/wppdfemb-viewer.php';
		}

		return $template;

	}

	/**
	 * Helper method to add actions
	 *
	 * @since 5.1.0
	 *
	 * @return void
	 */
	protected function add_actions() {
		parent::add_actions();

		// When viewing attachment page, embded document instead of link.
		add_filter( 'the_content', array( $this, 'pdfemb_the_content' ), 20, 1 );
		add_action( 'wp_pdf_viewer_head', array( $this, 'pdfemb_wp_head' ) );

	}

	/**
	 * Add Metaboxes
	 *
	 * @return void
	 */
	public function setup_attachment_metaboxes() {
		add_meta_box( 'attachment_meta_box', 'PDF Embed Settings', array( $this, 'attachment_data_meta_box' ), 'attachment', 'side' );
	}

	/**
	 * Add Attachment Metabox
	 *
	 * @param object $post Post Object.
	 * @return void
	 */
	public function attachment_data_meta_box( $post ) {

		// Add a nonce field so we can check for it later.
		wp_nonce_field( 'pdfemb_attachment_meta_box', 'pdfemb_attachment_meta_box_nonce' );

		$post_id         = isset( $post->ID ) ? intval( $post->ID ) : false;
		$count_downloads = get_post_meta( $post_id, 'pdfemb-downloads', true );
		if ( false === $count_downloads ) {
			$count_downloads = 0;
		}
		$count_views = get_post_meta( $post_id, 'pdfemb-views', true );
		if ( false === $count_views ) {
			$count_views = 0;
		}

		echo '<div class="attachment_field_containers">';
		echo '<p>Downloads: ' . intval( $count_downloads ) . '</p>';
		echo '<a href="' . esc_url( admin_url( 'post.php?post=' . intval( wp_unslash( $_GET['post'] ) ) ) . '&action=' . esc_attr( wp_unslash( $_GET['action'] ) ) . '&pdfemb=reset-downloads' ) . '">
			 <button type="button" class="button copy-attachment-url edit-media">Reset Download Counter</button>
		  </a>';
		echo '<p>Views: ' . intval( $count_views ) . '</p>';
		echo '<a href="' . esc_url( admin_url( 'post.php?post=' . intval( wp_unslash( $_GET['post'] ) ) ) . '&action=' . esc_attr( wp_unslash( $_GET['action'] ) ) . '&pdfemb=reset-views' ) . '">
			 <button type="button" class="button copy-attachment-url edit-media">Reset View Counter</button>
		  </span>';
		echo '</a>';

	}

	/**
	 * Save Metabox
	 *
	 * @param int $post_id Post ID.
	 * @return void
	 */
	public function save_attachment_meta_box_data( $post_id ) {
		/*
		 * We need to verify this came from our screen and with proper authorization,
		 * because the save_post action can be triggered at other times.
		 */

		// Check if our nonce is set.
		if ( ! isset( $_POST[ $this->plugin_name . '_attachment_meta_box_nonce' ] ) ) {
			return;
		}

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $_POST['pdfemb_attachment_meta_box_nonce'], array( 'pdfemb_attachment_meta_box' ) ) ) {
			return;
		}

		// If this is an autosave, our form has not been submitted, so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check the user's permissions.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Make sure that it is set.
		if ( ! isset( $_GET['pdfemb'] ) ) {
			return;
		}

		if ( 'reset-downloads' === esc_html( $_GET['pdfemb'] ) ) {
			update_post_meta( $post_id, 'pdfemb-downloads', false );
		}
		if ( 'reset-views' === esc_html( $_GET['pdfemb'] ) ) {
			update_post_meta( $post_id, 'pdfemb-downloads', false );
		}

	}

	/**
	 * PDF Embed Head Helper
	 *
	 * @return void
	 */
	public function pdfemb_wp_head() {
		$options = $this->get_option_pdfemb();
		if ( $options['pdfemb_resetviewport'] ) {
			echo '<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0" />';
		}
	}

	/**
	 * Actrivation Hook
	 *
	 * @param boolean $network_wide Network wide.
	 * @return void
	 */
	public function pdfemb_activation_hook( $network_wide ) {
		global $pdfemb_core_already_exists;
		if ( $pdfemb_core_already_exists ) {
			deactivate_plugins( $this->pdf_plugin_basename() );
			echo( 'Please Deactivate the free version of PDF Embedder before you activate the new Premium version.' );
			exit;
		}
		parent::pdfemb_activation_hook( $network_wide );
	}

	/**
	 * Helper Method to add more tabs.
	 *
	 * @return void
	 */
	protected function draw_more_tabs() {
		?>

		<a href="#license" id="license-tab" class="nav-tab"><?php esc_html_e( 'License', 'pdf-embedder' ); ?></a>

		<?php
	}

	/**
	 * Helper Method to render extra main section.
	 *
	 * @return void
	 */
	protected function pdfemb_mainsection_extra() {
		$options = $this->get_option_pdfemb();

		?>
		<br class="clear" />
		<br class="clear" />

		<label for="pdfemb_scrollbar" class="textinput"><?php esc_html_e( 'Display Scrollbars', 'pdf-embedder' ); ?></label>
		<span>

			<select name='<?php echo esc_attr( $this->get_options_name() ); ?>[pdfemb_scrollbar]' id='pdfemb_scrollbar' class='select'>
				<option value="none" <?php echo 'none' === $options['pdfemb_scrollbar'] ? 'selected' : ''; ?>><?php esc_html_e( 'None', 'pdf-embedder' ); ?></option>
				<option value="vertical" <?php echo 'vertical' === $options['pdfemb_scrollbar'] ? 'selected' : ''; ?>><?php esc_html_e( 'Vertical', 'pdf-embedder' ); ?></option>
				<option value="horizontal" <?php echo 'horizontal' === $options['pdfemb_scrollbar'] ? 'selected' : ''; ?>><?php esc_html_e( 'Horizontal', 'pdf-embedder' ); ?></option>
				<option value="both" <?php echo 'both' === $options['pdfemb_scrollbar'] ? 'selected' : ''; ?>><?php esc_html_e( 'Both', 'pdf-embedder' ); ?></option>
			</select>

			<br class="clear"/>

		<p class="desc big"><i><?php esc_html_e( 'User can still use mouse if scrollbars not visible', 'pdf-embedder' ); ?></i></p>

		</span>


		<label for="pdfemb_continousscroll" class="textinput"><?php esc_html_e( 'Continous Page Scrolling', 'pdf-embedder' ); ?></label>
		<span>
		<input type="checkbox" name='<?php echo esc_attr( $this->get_options_name() ); ?>[pdfemb_continousscroll]' id='pdfemb_continousscroll' class='checkbox' <?php echo $options['pdfemb_continousscroll'] ? 'checked' : ''; ?> />
		<label for="pdfemb_continousscroll" class="checkbox plain"><?php esc_html_e( 'Allow user to scroll up/down between all pages in the PDF (if unchecked, user must click next/prev buttons to change page)', 'pdf-embedder' ); ?></label>
		</span>

		<br class="clear" />
		<br class="clear" />

		<label for="pdfemb_download" class="textinput"><?php esc_html_e( 'Download Button', 'pdf-embedder' ); ?></label>
		<span>
		<input type="checkbox" name='<?php echo esc_attr( $this->get_options_name() ); ?>[pdfemb_download]' id='pdfemb_download' class='checkbox' <?php echo 'on' === $options['pdfemb_download'] ? 'checked' : ''; ?> />
		<label for="pdfemb_download" class="checkbox plain"><?php esc_html_e( 'Provide PDF download button in toolbar', 'pdf-embedder' ); ?></label>
		</span>

		<br class="clear" />
		<br class="clear" />

		<label for="pdfemb_tracking" class="textinput"><?php esc_html_e( 'Track Views/Downloads', 'pdf-embedder' ); ?></label>
		<span>
		<input type="checkbox" name='<?php echo esc_attr( $this->get_options_name() ); ?>[pdfemb_tracking]' id='pdfemb_tracking' class='checkbox' <?php echo 'on' === $options['pdfemb_tracking'] ? 'checked' : ''; ?> />
		<label for="pdfemb_tracking" class="checkbox plain"><?php printf( __( 'Count number of views and downloads (figures will be shown in <a href="%s">Media Library</a>)', 'pdf-embedder' ), esc_url( admin_url( 'upload.php' ) ) ); ?></label>
		</span>

		<br class="clear" />
		<br class="clear" />

		<label for="pdfemb_newwindow" class="textinput"><?php esc_html_e( 'External Links', 'pdf-embedder' ); ?></label>
		<span>
		<input type="checkbox" name='<?php echo esc_attr( $this->get_options_name() ); ?>[pdfemb_newwindow]' id='pdfemb_newwindow' class='checkbox' <?php echo 'on' === $options['pdfemb_newwindow'] ? 'checked' : ''; ?> />
		<label for="pdfemb_newwindow" class="checkbox plain"><?php esc_html_e( 'Open links in a new browser tab/window', 'pdf-embedder' ); ?></label>
		</span>

		<br class="clear" />
		<br class="clear" />

		<label for="pdfemb_scrolltotop" class="textinput"><?php esc_html_e( 'Scroll to Top', 'pdf-embedder' ); ?></label>
		<span>
		<input type="checkbox" name='<?php echo esc_attr( $this->get_options_name() ); ?>[pdfemb_scrolltotop]' id='pdfemb_scrolltotop' class='checkbox' <?php echo 'on' === $options['pdfemb_scrolltotop'] ? 'checked' : ''; ?> />
		<label for="pdfemb_scrolltotop" class="checkbox plain"><?php esc_html_e( 'Scroll to top of page when user clicks next/prev', 'pdf-embedder' ); ?></label>


		<br class="clear" />
		<br class="clear" />

		<label for="pdfemb_search" class="textinput"><?php esc_html_e( 'Search Button', 'pdf-embedder' ); ?></label>
		<span>
		<input type="checkbox" name='<?php echo esc_attr( $this->get_options_name() ); ?>[pdfemb_search]' id='pdfemb_search' class='checkbox' <?php echo isset( $options['pdfemb_search'] ) && 'on' === $options['pdfemb_search'] ? 'checked' : ''; ?> />
		<label for="pdfemb_search" class="checkbox plain"><?php esc_html_e( 'Provides PDF search/find button in toolbar', 'pdf-embedder' ); ?></label>

		<br class="clear" />
		<br class="clear" />

		<?php do_action( 'pdfemb_additional_general_settings' ); ?>

		</span>

		<?php
	}

	/**
	 * Helper method for tool bar option.
	 *
	 * @param array $options Options array.
	 * @return void
	 */
	protected function no_toolbar_option( $options ) {
		?>
		<option value="none" <?php echo 'none' === $options['pdfemb_toolbar'] ? 'selected' : ''; ?>><?php esc_html_e( 'No Toolbar', 'pdf-embedder' ); ?></option>
		<?php
	}

	/**
	 * Helper Method to render mobile section.
	 *
	 * @return void
	 */
	protected function pdfemb_mobilesection_text() {
		$options = $this->get_option_pdfemb();

		?>
		<h2><?php esc_html_e( 'Default Mobile Settings', 'pdf-embedder' ); ?></h2>

		<p><?php esc_html_e( "When the document is smaller than the width specified below, the document displays only as a 'thumbnail' with a large 'View in Full Screen' button for the user to click to open.", 'pdf-embedder' ); ?></p>

		<label for="input_pdfemb_mobilewidth" class="textinput"><?php esc_html_e( 'Mobile Width', 'pdf-embedder' ); ?></label>
		<input id='input_pdfemb_mobilewidth' class='textinput' name='<?php echo esc_attr( $this->get_options_name() ); ?>[pdfemb_mobilewidth]' size='10' type='text' value='<?php echo esc_attr( $options['pdfemb_mobilewidth'] ); ?>' />
		<br class="clear"/>

		<p class="desc big"><i><?php esc_html_e( 'Enter an integer number of pixels, or 0 to disable automatic full-screen', 'pdf-embedder' ); ?></i></p>

		<br class="clear"/>

		<label for="input_mobilewidth_button_text" class="textinput"><?php esc_html_e( 'Mobile Width Button Text', 'pdf-embedder' ); ?></label>
		<input id='input_mobilewidth_button_text' class='textinput' name='<?php echo esc_attr( $this->get_options_name() ); ?>[pdfemb_mobilewidth_button_text]' size='50' type='text' value='<?php echo esc_attr( $options['pdfemb_mobilewidth_button_text'] ); ?>' />
		<br class="clear"/>

		<p class="desc big"><i><?php esc_html_e( 'Enter a short string of text for the button.', 'pdf-embedder' ); ?></i></p>

		<br class="clear"/>

		<label for="pdfemb_resetviewport" class="textinput"><?php esc_html_e( 'Disable Device Zoom', 'pdf-embedder' ); ?></label>
		<span>
		<input type="checkbox" name='<?php echo esc_attr( $this->get_options_name() ); ?>[pdfemb_resetviewport]' id='pdfemb_resetviewport' class='checkbox' <?php echo $options['pdfemb_resetviewport'] ? 'checked' : ''; ?> />
		<label for="pdfemb_resetviewport" class="checkbox plain"><?php esc_html_e( 'Enable if you are experiencing quality issues on mobiles', 'pdf-embedder' ); ?></label>
		</span>

		<br class="clear"/>

		<p class="desc big"><i><?php esc_html_e( 'Some mobile browsers will use their own zoom, causing the PDF Embedder to render at a lower resolution than it should, or lose the toolbar off screen.', 'pdf-embedder' ); ?>
				<?php esc_html_e( 'Enabling this option may help, but could potentially affect appearance in the rest of your site.', 'pdf-embedder' ); ?>
				<?php
				printf(
					__( 'See <a href="%s" target="_blank">documentation</a> for details.', 'pdf-embedder' ),
					'https://wp-pdf.com/troubleshooting/?utm_source=Premium%20ResetViewport&utm_medium=premium&utm_campaign=Premium#resetviewport'
				);
				?>
			</i></p>

		<?php
	}

	/**
	 * Helper method to render extra sections.
	 *
	 * @return void
	 */
	protected function draw_extra_sections() {
		$options = $this->get_option_pdfemb();
		?>
		<div id="license-section" class="pdfembtab">
		<p><?php esc_html_e( 'You should have received a license key when you purchased this premium version of PDF Embedder.', 'pdf-embedder' ); ?></p>
		<p><?php printf( __( 'Please enter it below to enable automatic updates, or <a href="%s">email us</a> if you do not have one.', 'pdf-embedder' ), 'mailto:contact@wp-pdf.com' ); ?></p>

		<label for="input_pdfemb_license_key" class="textinput big"><?php esc_html_e( 'License Key', 'pdf-embedder' ); ?></label>
		<input id='input_pdfemb_license_key' name='<?php echo esc_attr( $this->get_options_name() ); ?>[pdfemb_license_key]' size='40' type='text' value='<?php echo esc_attr( $options['pdfemb_license_key'] ); ?>' class='textinput' />
		<br class="clear" />

		<?php

		// Display latest license status.
		$license_status = get_site_option( $this->get_eddsl_optname(), true );

		if ( is_array( $license_status ) && isset( $license_status['license_id'] ) && '' !== $license_status['license_id'] ) {
			echo '<br class="clear" />';
			echo '<table>';
			echo '<tr><td>' . esc_html__( 'Current License', 'pdf-embedder' ) . ': </td><td>' . esc_html( isset( $license_status['license_id'] ) ? $license_status['license_id'] : '' ) . '</td></tr>';

			if ( isset( $license_status['status'] ) && '' !== $license_status['status'] ) {
				echo '<tr><td>' . esc_html__( 'Status', 'pdf-embedder' ) . ': </td><td>' . esc_html( strtoupper( $license_status['status'] ) ) . '</td></tr>';
			}

			if ( isset( $license_status['last_check_time'] ) && '' !== $license_status['last_check_time'] ) {
				echo '<tr><td>' . esc_html__( 'Last Checked', 'pdf-embedder' ) . ': </td><td>' . esc_html( date( 'j M Y H:i:s', $license_status['last_check_time'] ) ) . '</td></tr>';
			}

			if ( isset( $license_status['expires_time'] ) ) {
				echo '<tr><td>' . esc_html__( 'License Expires', 'pdf-embedder' ) . ': </td><td>' . esc_html( date( 'j M Y H:i:s', $license_status['expires_time'] ) ) . '</td></tr>';
			}

			echo '</table>';

			if ( isset( $license_status['expires_time'] ) && $license_status['expires_time'] < time() + 24 * 60 * 60 * 60 ) {
				echo '<p>';
				if ( isset( $license_status['renewal_link'] ) && $license_status['renewal_link'] ) {
					printf( __( 'To renew your license, please <a href="%s" target="_blank">click here</a>.', 'pdf-embedder' ), esc_attr( $license_status['renewal_link'] ) );
				}
				echo ' ';
				echo '</p>';
			}

			echo '<br class="clear" />';

			?>
			<span>
			<input type="checkbox" name='<?php echo esc_attr( $this->get_options_name() ); ?>[pdfemb_allowbeta]' id='pdfemb_allowbeta' class='checkbox' <?php echo 'on' === $options['pdfemb_allowbeta'] ? 'checked' : ''; ?> />
			<label for="pdfemb_allowbeta" class="checkbox plain"><?php esc_html_e( 'Participate in future beta releases of the plugin', 'pdf-embedder' ); ?></label>
			</span>

			<br class="clear" />

			<?php

			if ( isset( $license_status['download_link'] ) ) {
				echo '<p>Download latest plugin ZIP <a href="' . esc_url( $license_status['download_link'] ) . '" target="_blank">here</a></p>';
				echo '<br class="clear" />';
			}
		}

		echo '</div>';

	}

	/**
	 * Helper Method for EDD updated
	 *
	 * @param string $license_key License Key.
	 * @return object
	 */
	protected function edd_plugin_updater( $license_key = null ) {
		$options = $this->get_option_pdfemb();
		if ( is_null( $license_key ) ) {
			$license_key = $options['pdfemb_license_key'];
		}

		if ( ! class_exists( 'EDD_SL_Plugin_Updater13' ) ) {
			// load our custom updater.
			include dirname( __FILE__ ) . '/EDD_SL_Plugin_Updater.php';
		}

		// setup the updater.
		$edd_updater = new EDD_SL_Plugin_Updater13(
			$this->wppdf_store_url,
			$this->pdf_plugin_basename(),
			array(
				'version'   => $this->plugin_version,
				'license'   => $license_key,
				'item_name' => $this->wppdf_item_name,
				'item_id'   => $this->wppdf_item_id,
				'author'    => 'WP PDF Team',
				'beta'      => $options['pdfemb_allowbeta'],
			),
			$this->get_eddsl_optname(),
			$this->get_settings_url() . '#license',
			false // Don't display admin panel warnings.
		);

		return $edd_updater;
	}

	/**
	 * EDD Opt Name
	 *
	 * @return null
	 */
	protected function get_eddsl_optname() {
		return null;
	}

	/**
	 * Helper Method to check active licesnse
	 *
	 * @param string $license_key License Key.
	 * @return boolean
	 */
	protected function edd_license_activate( $license_key ) {
		$edd_updater = $this->edd_plugin_updater( $license_key );
		return $edd_updater->edd_license_activate();
	}

	/**
	 * Admin init
	 *
	 * @return void
	 */
	public function pdfemb_admin_init() {
		$edd_updater = $this->edd_plugin_updater();
		$edd_updater->setup_hooks();

		$options = $this->get_option_pdfemb();
		if ( 'on' === $options['pdfemb_tracking'] ) {
			add_action( 'wp_ajax_pdfemb_count_download', array( $this, 'ajax_pdfemb_count_download' ) );
		}

		parent::pdfemb_admin_init();
	}

	/**
	 * Helper to get Instructions Url
	 *
	 * @return string
	 */
	protected function get_instructions_url() {
		return 'http://wp-pdf.com/premium-instructions/?utm_source=PDF%20Settings%20Main&utm_medium=premium&utm_campaign=Premium';
	}

	/**
	 * Validate PDf Opts
	 *
	 * @param array $input Options input.
	 * @return array
	 */
	public function pdfemb_options_validate( $input ) {

		$newinput = parent::pdfemb_options_validate( $input );

		if ( isset( $input['pdfemb_scrollbar'] ) && in_array( $input['pdfemb_scrollbar'], array( 'vertical', 'horizontal', 'both', 'none' ), true ) ) {
			$newinput['pdfemb_scrollbar'] = $input['pdfemb_scrollbar'];
		} else {
			$newinput['pdfemb_scrollbar'] = 'none';
		}
		$newinput['pdfemb_continousscroll'] = isset( $input['pdfemb_continousscroll'] ) && $input['pdfemb_continousscroll'];

		$newinput['pdfemb_sidebar']       = isset( $input['pdfemb_sidebar'] ) && ( true === $input['pdfemb_sidebar'] || 'on' === $input['pdfemb_sidebar'] ) ? 'on' : 'off';
		$newinput['pdfemb_download']      = isset( $input['pdfemb_download'] ) && ( true === $input['pdfemb_download'] || 'on' === $input['pdfemb_download'] ) ? 'on' : 'off';
		$newinput['pdfemb_tracking']      = isset( $input['pdfemb_tracking'] ) && ( true === $input['pdfemb_tracking'] || 'on' === $input['pdfemb_tracking'] ) ? 'on' : 'off';
		$newinput['pdfemb_newwindow']     = isset( $input['pdfemb_newwindow'] ) && ( true === $input['pdfemb_newwindow'] || 'on' === $input['pdfemb_newwindow'] ) ? 'on' : 'off';
		$newinput['pdfemb_scrolltotop']   = isset( $input['pdfemb_scrolltotop'] ) && ( true === $input['pdfemb_scrolltotop'] || 'on' === $input['pdfemb_scrolltotop'] ) ? 'on' : 'off';
		$newinput['pdfemb_resetviewport'] = isset( $input['pdfemb_resetviewport'] ) && ( true === $input['pdfemb_resetviewport'] || 'on' === $input['pdfemb_resetviewport'] );
		$newinput['pdfemb_search']        = isset( $input['pdfemb_search'] ) && ( true === $input['pdfemb_search'] || 'on' === $input['pdfemb_search'] ) ? 'on' : 'off';

		$newinput['pdfemb_download_view_counter'] = isset( $input['pdfemb_download_view_counter'] ) && ( true === $input['pdfemb_download_view_counter'] || 'on' === $input['pdfemb_download_view_counter'] ) ? 'on' : 'off';
		$newinput['pdfemb_view_counter']          = isset( $input['pdfemb_view_counter'] ) && ( true === $input['pdfemb_view_counter'] || 'on' === $input['pdfemb_view_counter'] ) ? 'on' : 'off';
		$newinput['pdfemb_download_counter']      = isset( $input['pdfemb_download_counter'] ) && ( true === $input['pdfemb_download_counter'] || 'on' === $input['pdfemb_download_counter'] ) ? 'on' : 'off';

		$newinput['pdfemb_mobilewidth']             = $input['pdfemb_mobilewidth'];
		$newinput['pdfemb_mobilewidth_button_text'] = isset( $input['pdfemb_mobilewidth_button_text'] ) && ( '' !== trim( $input['pdfemb_mobilewidth_button_text'] ) ) ? esc_html( $input['pdfemb_mobilewidth_button_text'] ) : false;

		if ( ! isset( $input['pdfemb_mobilewidth'] ) || ! is_numeric( $input['pdfemb_mobilewidth'] ) ) {
			add_settings_error(
				'pdfemb_mobilewidth',
				'widtherror',
				self::get_error_string( 'pdfemb_mobilewidth|widtherror' ),
				'error'
			);
		}

		// License Key.
		$newinput['pdfemb_license_key'] = trim( $input['pdfemb_license_key'] );
		if ( '' !== $newinput['pdfemb_license_key'] ) {
			if ( ! preg_match( '/^.{32}.*$/i', $newinput['pdfemb_license_key'] ) ) {
				add_settings_error(
					'pdfemb_license_key',
					'tooshort_texterror',
					self::get_error_string( 'pdfemb_license_key|tooshort_texterror' ),
					'error'
				);
			} else {
				// There is a valid-looking license key present.
				$checked_license_status = get_site_option( $this->get_eddsl_optname(), true );

				// Only bother trying to activate if we have a new license key OR the same license key but it was invalid on last check.
				$existing_valid_license = '';
				if ( is_array( $checked_license_status ) && isset( $checked_license_status['license_id'] ) && '' !== $checked_license_status['license_id']
					&& isset( $checked_license_status['status'] ) && 'valid' === $checked_license_status['status'] ) {
					$existing_valid_license = $checked_license_status['license_id'];
				}

				if ( $existing_valid_license !== $newinput['pdfemb_license_key'] ) {

					$license_status = $this->edd_license_activate( $newinput['pdfemb_license_key'] );
					if ( isset( $license_status['status'] ) && 'valid' !== $license_status['status'] ) {
						add_settings_error(
							'pdfemb_license_key',
							$license_status['status'],
							self::get_error_string( 'pdfemb_license_key|' . $license_status['status'] ),
							'error'
						);
					}
				}
			}
		}

		$newinput['pdfemb_allowbeta'] = isset( $input['pdfemb_allowbeta'] ) && $input['pdfemb_allowbeta'];

		return $newinput;
	}

	/**
	 * Helper Method to get error string
	 *
	 * @param string $fielderror Field Error.
	 * @return string
	 */
	protected function get_error_string( $fielderror ) {
		$premium_local_error_strings = array(
			'pdfemb_mobilewidth|widtherror'         => esc_html__( 'Mobile width should be an integer number of pixels, or 0 to turn off', 'pdf-embedder' ),
			'pdfemb_license_key|tooshort_texterror' => esc_html__( 'License key is too short', 'pdf-embedder' ),
			'pdfemb_license_key|invalid'            => esc_html__( 'License key failed to activate', 'pdf-embedder' ),
			'pdfemb_license_key|missing'            => esc_html__( 'License key does not exist in our system at all', 'pdf-embedder' ),
			'pdfemb_license_key|item_name_mismatch' => esc_html__( 'License key entered is for the wrong product', 'pdf-embedder' ),
			'pdfemb_license_key|expired'            => esc_html__( 'License key has expired', 'pdf-embedder' ),
			'pdfemb_license_key|site_inactive'      => esc_html__( 'License key is not permitted for this website', 'pdf-embedder' ),
			'pdfemb_license_key|inactive'           => esc_html__( 'License key is not active for this website', 'pdf-embedder' ),
			'pdfemb_license_key|disabled'           => esc_html__( 'License key has been disabled', 'pdf-embedder' ),
			'pdfemb_license_key|empty'              => esc_html__( 'License key was not provided', 'pdf-embedder' ),
		);
		if ( isset( $premium_local_error_strings[ $fielderror ] ) ) {
			return $premium_local_error_strings[ $fielderror ];
		}
		return parent::get_error_string( $fielderror );
	}

	/**
	 * Helper Method to get default options.
	 *
	 * @return array
	 */
	protected function get_default_options() {
		return array_merge(
			parent::get_default_options(),
			array(
				'pdfemb_continousscroll'         => true,
				'pdfemb_scrollbar'               => 'none',
				'pdfemb_mobilewidth'             => '500',
				'pdfemb_mobilewidth_button_text' => 'View In Full Screen',
				'pdfemb_license_key'             => '',
				'pdfemb_tracking'                => 'on',
				'pdfemb_newwindow'               => 'on',
				'pdfemb_scrolltotop'             => 'off',
				'pdfemb_resetviewport'           => false,
				'pdfemb_allowbeta'               => false,
			)
		);
	}

	/**
	 * Helper Method to get Translations Array
	 *
	 * @return array
	 */
	protected function get_translation_array() {
		$options = $this->get_option_pdfemb();
		return array_merge(
			parent::get_translation_array(),
			array(
				'continousscroll' => $options['pdfemb_continousscroll'],
				'poweredby'       => false,
				'ajaxurl'         => admin_url( 'admin-ajax.php' ),
			)
		);
	}

	/**
	 * Helper Method to get PDF Fields.
	 *
	 * @param array  $form_fields Form Fields.
	 * @param object $post Post object.
	 * @return array
	 */
	public function pdfemb_attachment_fields_to_edit( $form_fields, $post ) {
		if ( 'application/pdf' === $post->post_mime_type ) {
			$options = $this->get_option_pdfemb();
			if ( 'on' === $options['pdfemb_tracking'] ) {

				$downloads = get_post_meta( $post->ID, 'pdfemb-downloads', true );
				if ( ! is_numeric( $downloads ) ) {
					$downloads = 'None';
				}
				$views = get_post_meta( $post->ID, 'pdfemb-views', true );
				if ( ! is_numeric( $views ) ) {
					$views = 'None';
				}
				$form_fields['pdfemb-downloads'] = array(
					'value' => $downloads,
					'input' => 'value',
					'label' => __( 'Downloads' ),
				);
				$form_fields['pdfemb-views']     = array(
					'value' => $views,
					'input' => 'value',
					'label' => __( 'Views' ),
				);

			}
		}
		return $form_fields;
	}

	/**
	 * PDF Init
	 *
	 * @return void
	 */
	public function pdfemb_init() {
		$options = $this->get_option_pdfemb();
		if ( 'on' === $options['pdfemb_tracking'] ) {
			add_action( 'wp_ajax_nopriv_pdfemb_count_download', array( $this, 'ajax_pdfemb_count_download' ) );
		}
		parent::pdfemb_init();
	}

	/**
	 * PDF Embed content
	 *
	 * @param string $content Content.
	 * @return string
	 */
	public function pdfemb_the_content( $content ) {
		global $post;
		if ( isset( $post ) && 'attachment' === $post->post_type && is_singular() && isset( $post->post_mime_type ) && 'application/pdf' === $post->post_mime_type ) {
			$pdfurl = wp_get_attachment_url( $post->ID );
			if ( ! empty( $pdfurl ) ) {
				$content = $this->output_the_content( $pdfurl );
			}
		}
		return $content;
	}

	/**
	 * Helper MEthod to output the content
	 *
	 * @param string $pdfurl PDF Url.
	 * @return string
	 */
	protected function output_the_content( $pdfurl ) {
		return $this->pdfemb_shortcode_display_pdf_noncanvas( array( 'url' => $pdfurl ) );
	}

	/**
	 * Helper Method to toggle data.
	 *
	 * @param string $keyname Atts keyname.
	 * @param string $default Default.
	 * @param array  $atts Shortcode atts.
	 * @param array  $options Options.
	 * @return string
	 */
	private function shortcode_attr_data_on_off( $keyname, $default, $atts, $options ) {
		$v = isset( $atts[ $keyname ] ) ? $atts[ $keyname ] : ( isset( $options[ 'pdfemb_' . $keyname ] ) && 'on' === $options[ 'pdfemb_' . $keyname ] ? 'on' : 'off' );
		if ( ! in_array( $v, array( 'on', 'off' ), true ) ) {
			$v = $default;
		}
		return ' data-' . $keyname . '="' . $v . '"';
	}

	/**
	 * Undocumented function
	 *
	 * @param array  $atts Shortcode Atts.
	 * @param string $content Content.
	 * @return string
	 */
	protected function extra_shortcode_attrs( $atts, $content = null ) {
		$options = $this->get_option_pdfemb();

		$extraparams = '';

		$scrollbar = isset( $atts['scrollbar'] ) && in_array( $atts['scrollbar'], array( 'vertical', 'horizontal', 'both', 'none' ), true ) ? $atts['scrollbar'] : $options['pdfemb_scrollbar'];
		if ( ! in_array( $scrollbar, array( 'vertical', 'horizontal', 'both', 'none' ), true ) ) {
			$scrollbar = 'none';
		}

		$extraparams .= ' data-scrollbar="' . $scrollbar . '"';

		$extraparams .= $this->shortcode_attr_data_on_off( 'download', 'off', $atts, $options );

		if ( isset( $atts['page'] ) && preg_match( '/^[0-9]+$/', $atts['page'] ) && $atts['page'] > 0 ) {
			$extraparams .= ' data-pagenum="' . esc_attr( $atts['page'] ) . '"';
		}

		$mobilewidth = '500';
		if ( isset( $atts['mobilewidth'] ) && is_numeric( $atts['mobilewidth'] ) ) {
			$mobilewidth = $atts['mobilewidth'];
		} elseif ( isset( $options['pdfemb_mobilewidth'] ) && is_numeric( $options['pdfemb_mobilewidth'] ) ) {
			$mobilewidth = $options['pdfemb_mobilewidth'];
		}

		// Record views if tracking enabled.
		if ( 'on' === $options['pdfemb_tracking'] ) {
			$this->count_views_or_downloads( $atts['url'], 'views' );
			$extraparams .= ' data-tracking="on"';
		}

		$extraparams .= $this->shortcode_attr_data_on_off( 'newwindow', 'on', $atts, $options );

		$pagetextbox  = isset( $atts['pagetextbox'] ) && 'on' === $atts['pagetextbox'] ? 'on' : 'off';
		$extraparams .= ' data-pagetextbox="' . $pagetextbox . '"';

		$extraparams .= $this->shortcode_attr_data_on_off( 'scrolltotop', 'off', $atts, $options );

		$zoom = isset( $atts['zoom'] ) ? $atts['zoom'] : '100';
		if ( ! is_numeric( $zoom ) || $zoom < 20 || $zoom > 800 ) {
			$zoom = '100';
		}
		$extraparams .= ' data-startzoom="' . $zoom . '"';

		$fpzoom = isset( $atts['fpzoom'] ) ? $atts['fpzoom'] : $zoom;
		if ( ! is_numeric( $fpzoom ) || $fpzoom < 20 || $fpzoom > 800 ) {
			$fpzoom = $zoom;
		}
		$extraparams .= ' data-startfpzoom="' . $fpzoom . '"';

		return 'data-mobile-width="' . esc_attr( $mobilewidth ) . '" ' . $extraparams;
	}

	/**
	 * Helper to count views or downloads
	 *
	 * @param string $url URL.
	 * @param string $type Type ( views, downloads ).
	 * @return int
	 */
	protected function count_views_or_downloads( $url, $type = 'views' ) {
		$count   = 'N/A';
		$post_id = $this->get_attachment_id( $url );
		if ( ! $post_id ) {
			$post_id = $this->get_attachment_id( $url, true );
		}
		if ( $post_id ) {
			$meta_name = 'pdfemb-' . $type;
			$count     = get_post_meta( $post_id, $meta_name, true );
			if ( ! is_numeric( $count ) ) {
				$count = 0;
			}
			++$count;
			update_post_meta( $post_id, $meta_name, $count );
		}
		return $count;
	}

	/**
	 * Helper to get attachment ID
	 *
	 * @param string $url URL.
	 * @param bool   $invertscheme Toggle between scheme.
	 * @return mixed
	 */
	protected function get_attachment_id( $url, $invertscheme = false ) {
		global $wpdb;

		if ( $invertscheme ) {
			$url = set_url_scheme( $url, preg_match( '#^https://#i', $url ) ? 'http' : 'https' );
		}

		$attachment = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE guid='%s';", $url ) ); // @codingStandardsIgnoreLine
		if ( is_array( $attachment ) && isset( $attachment[0] ) && $attachment[0] > 0 ) {
			return $attachment[0];
		}
		return false;
	}

	/**
	 * Helper Method for ajax count downloads
	 *
	 * @return void
	 */
	public function ajax_pdfemb_count_download() {
		$newcount = 'No pdf';
		if ( isset( $_POST['pdf_url'] ) ) {
			$url     = $_POST['pdf_url'];
			$matches = array();
			if ( preg_match( '#/\?pdfemb-serveurl\=([^&]+)#', $url, $matches ) ) {
				// Correct for Secure URLs.
				$url = urldecode( $matches[1] );
			}
			$newcount = $this->count_views_or_downloads( $url, 'downloads' );
		}

		wp_die( $newcount ); // @codingStandardsIgnoreLine
	}

	/**
	 * Helper Method for embedder counts
	 *
	 * @param array  $atts Shortcode atts.
	 * @param string $content content.
	 * @return string
	 */
	public function pdf_embedder_counts( $atts, $content = '' ) {
		if ( ! isset( $atts['url'] ) ) {
			return '<b>PDF Embedder counts requires a url attribute</b>';
		}
		$url     = $atts['url'];
		$post_id = $this->pdfemb_get_attachment_id( $url );
		if ( ! $post_id ) {
			$post_id = $this->pdfemb_get_attachment_id( $url, true );
		}
		if ( $post_id ) {
			$count_views     = get_post_meta( $post_id, 'pdfemb-views', true );
			$count_downloads = get_post_meta( $post_id, 'pdfemb-downloads', true );
			if ( is_numeric( $count_views ) ) {
				$content .= 'Views: ' . $count_views;
			} else {
				$content .= 'Views: 0';
			}
			if ( is_numeric( $count_downloads ) ) {
				$content .= ' Downloads: ' . $count_downloads;
			} else {
				$content .= ' Downloads: 0';
			}
		}
		return $content;
	}

	/**
	 * Helper method for view count.
	 *
	 * @param array  $atts Shortcode atts.
	 * @param string $content content.
	 * @return string
	 */
	public function pdf_embedder_view_counts( $atts, $content = '' ) {
		if ( ! isset( $atts['url'] ) ) {
			return '<b>PDF Embedder counts requires a url attribute</b>';
		}
		$url     = $atts['url'];
		$post_id = $this->pdfemb_get_attachment_id( $url );
		if ( ! $post_id ) {
			$post_id = $this->pdfemb_get_attachment_id( $url, true );
		}
		if ( $post_id ) {
			$count_views = get_post_meta( $post_id, 'pdfemb-views', true );
			if ( is_numeric( $count_views ) ) {
				$content .= '<p class="pdf-download-counter">Views: ' . $count_views . '</p>';
			} else {
				$content .= '<p class="pdf-download-counter no-views">Views: 0</p>';
			}
		}
		return $content;
	}

	/**
	 * Undocumented function
	 *
	 * @param array  $atts Shortcode Atts.
	 * @param string $content Content.
	 * @return string
	 */
	public function pdf_embedder_download_counts( $atts, $content = '' ) {
		if ( ! isset( $atts['url'] ) ) {
			return '<b>PDF Embedder counts requires a url attribute</b>';
		}
		$url     = $atts['url'];
		$post_id = $this->pdfemb_get_attachment_id( $url );
		if ( ! $post_id ) {
			$post_id = $this->pdfemb_get_attachment_id( $url, true );
		}
		if ( $post_id ) {
			$count_downloads = get_post_meta( $post_id, 'pdfemb-downloads', true );
			if ( is_numeric( $count_downloads ) ) {
				$content .= '<p class="pdf-download-counter">Downloads: ' . $count_downloads . '</p>';
			} else {
				$content .= '<p class="pdf-download-counter no-downloads">Downloads: 0</p>';
			}
		}
		return $content;
	}

	/**
	 * Undocumented function
	 *
	 * @param array $atts Shortcode Atts.
	 * @return array
	 */
	public function pdfemb_filter_shortcode_gutenberg( $atts = false ) {

		if ( ! isset( $atts['url'] ) ) {
			return false;
		}

		// map Gutenberg values to the PDF ones (print_button = print, etc.
		$new_atts = array();

		foreach ( $atts as $key => $value ) {

			switch ( $key ) {
				case 'externalButton':
					$new_value          = ( true === $value ) ? 'blank' : false;
					$new_atts['target'] = $new_value;
					break;
				case 'downloadButton':
					$new_value            = ( true === $value ) ? 'on' : 'off';
					$new_atts['download'] = $new_value;
					break;
				case 'printButton':
					$new_value         = ( true === $value ) ? 'on' : 'off';
					$new_atts['print'] = $new_value;
					break;
				case 'bookmarkButton':
					$new_value            = ( true === $value ) ? 'on' : 'off';
					$new_atts['bookmark'] = $new_value;
					break;
				case 'presentationButton':
					$new_value                = ( true === $value ) ? 'on' : 'off';
					$new_atts['presentation'] = $new_value;
					break;
				case 'searchButton':
					$new_value          = ( true === $value ) ? 'on' : 'off';
					$new_atts['search'] = $new_value;
					break;
				default:
					$new_atts[ $key ] = $value;
					break;
			}
		}

		return $new_atts;

	}

	/**
	 * Undocumented function
	 *
	 * @param array $atts Shortcode Atts.
	 *
	 * @return string
	 */
	public function pdfemb_shortcode_display_pdf_noncanvas( $atts = false ) {

		$GLOBALS['pdf_index'] = isset( $GLOBALS['pdf_index'] ) ? $GLOBALS['pdf_index'] + 1 : 1;
		$index                = $GLOBALS['pdf_index'];

		// get the main options.
		$options = $this->get_option_pdfemb( $atts );
		$height  = ( isset( $atts['height'] ) ) ? esc_html( $atts['height'] ) : '1000px'; // get the default instead todo.
		$height  = ( is_numeric( $height ) ) ? $height . 'px' : $height; // make sure to add px to the end if it's a number.
		$width   = ( isset( $atts['width'] ) ) ? esc_html( $atts['width'] ) : '100%'; // get the default instead todo.
		$width   = ( is_numeric( $width ) ) ? $width . 'px' : $width; // make sure to add px to the end if it's a number.
		$pdf_id  = ( isset( $atts['pdfID'] ) ) ? intval( $atts['pdfID'] ) : $index;

		$atts['pdfID'] = $pdf_id;

		$atts['index'] = $index;

		$ssl = is_ssl() ? 'https' : 'http';

		$atts['url'] = str_replace( array( 'http://','https://' ), $ssl . '://', $atts['url'] );
		$secure_url = strpos( $atts['url'], 'securepdfs/' ) !== false ? true : false;

		if ( $secure_url && class_exists( 'WP_PDF_Secure' ) ) {
			$atts['url'] = add_query_arg(
				array(
					'pdfemb-serveurl' => urlencode( $atts['url'] ),
					'pdfemb-nonce' => wp_create_nonce( 'pdfemb-secure-download-' . $atts['url'] ),
				),
				parse_url( home_url( '/', $ssl ), PHP_URL_PATH )
			);

		}

		$args = http_build_query( $atts );

		// Note: we are making the iframe point to home_url() to we do not point to the viewer php file directly, which causes some issues with some hosts like Cloudways, security, etc.
		wp_enqueue_style( 'pdf-fullscreen', trailingslashit( WP_PDF_SECURE_URL ) . 'assets/css/pdf-embedder-fullscreen.css', null, WP_PDF_VERSION );

		$content = '<div id="wppdfemb-frame-container-' .esc_attr( $pdf_id ) . '"><iframe id="wppdf-emb-iframe-' .esc_attr( $pdf_id ) . '" scrolling="no" data-pdf-index="' . esc_attr( $index ) . '" class="pdfembed-iframe nonfullscreen" style="border: none; width:100%; max-width: ' . $width . '; min-height: ' . $height . ';" src="' . home_url('', $ssl) . '?' . $args . '" ></iframe></div>';

		return $content;

	}

	/**
	 * Undocumented function
	 *
	 * @param bool $atts Shortcode attributes.
	 * @return string
	 */
	public function pdfemb_shortcode_display_pdf_noncanvas_process( $atts = false ) {

		if ( ! isset( $atts['url'] ) ) {
			return false;
		}

		global $post;

		// get the main options.
		$options = $this->get_option_pdfemb( $atts );
		$default_options = $this->get_default_options();
		$post_id = isset( $post->ID ) ? intval( $post->ID ) : false; // this is the backup if an ID is not supplied.
		$pdf_url = esc_url_raw( $atts['url'] );

		// If there's no url then no sense generating the viewer.
		if ( false === $pdf_url ) {
			return false;
		}

		// is this a secure PDF? we should check, and by checking 'securepdfs' in the url.
		$secure_url = strpos( $pdf_url, '/?pdfemb-serveurl=' ) !== false ? true : false;

		$pdf_embed_index = ( isset( $atts['index'] ) ) ? esc_html( intval( $atts['index'] ) ) : 1;

		$height       = ( isset( $atts['height'] ) ) ? esc_html( $atts['height'] ) : '1000px';
		$height       = ( is_numeric( $height ) ) ? $height . 'px' : $height;
		$width        = ( isset( $atts['width'] ) ) ? esc_html( $atts['width'] ) : '640px';
		$pdf_id       = ( isset( $atts['pdfID'] ) ) ? esc_html( $atts['pdfID'] ) : intval( $post_id );
		$toolbar      = ( isset( $options['pdfemb_toolbar'] ) ) ? esc_attr( $options['pdfemb_toolbar'] ) : esc_attr( $default_options['pdfemb_toolbar'] );
		$toolbarfixed = ( isset( $options['pdfemb_toolbarfixed'] ) ) ? esc_attr( $options['pdfemb_toolbarfixed'] ) : esc_attr( $default_options['pdfemb_toolbarfixed'] );
		$start_page   = ( isset( $atts['page'] ) ) ? intval( $atts['page'] ) : false;
		$scrollbar    = ( isset( $atts['scrollbar'] ) && in_array( esc_html( $atts['scrollbar'] ), array( 'vertical', 'horizontal', 'both', 'none' ) ) ) ? esc_html( $atts['scrollbar'] ) : $options['pdfemb_scrollbar'];

		// get the default instead todo.
		$tb_location       = ( isset( $atts['toolbar'] ) ) ? esc_html( $atts['toolbar'] ) : esc_attr( $default_options['pdfemb_toolbar'] );
		$poweredby         = ( isset( $atts['poweredby'] ) ) ? esc_html( $atts['poweredby'] ) : false;
		$poweredby         = ( false === $poweredby && isset( $options['poweredby'] ) ) ? $options['poweredby'] : $poweredby;
		$target            = ( isset( $atts['target'] ) ) ? esc_html( $atts['target'] ) : false;
		$target            = ( false === $target && isset( $options['newsindow'] ) ) ? $options['newsindow'] : $target;
		$counter           = ( isset( $options['pdfemb_download_view_counter'] ) ) ? esc_html( $options['pdfemb_download_view_counter'] ) : false;
		$counter           = ( isset( $atts['download_view_counter'] ) ) ? esc_html( $atts['download_view_counter'] ) : $counter;
		$view_counter      = ( isset( $options['pdfemb_view_counter'] ) ) ? esc_html( $options['pdfemb_view_counter'] ) : false;
		$view_counter      = ( isset( $atts['view_counter'] ) ) ? esc_html( $atts['view_counter'] ) : $view_counter;
		$download_counter  = ( isset( $options['pdfemb_download_counter'] ) ) ? esc_html( $options['pdfemb_download_counter'] ) : false;
		$download_counter  = ( isset( $atts['download_counter'] ) ) ? esc_html( $atts['download_counter'] ) : $download_counter;
		$download_base_url = home_url( '?d=true' );
		$continousscroll   = ( isset( $atts['continousscroll'] ) ) ? esc_html( $atts['continousscroll'] ) : $options['pdfemb_continousscroll'];
		$continousscroll   = ( false === $continousscroll ) ? 'off' : $continousscroll;

		// color overrides.
		$sidebar_color = ( isset( $atts['sidebarcolor'] ) ) ? esc_html( $atts['sidebarcolor'] ) : false;
		$sidebar_color = ( false === $sidebar_color && isset( $options['pdfemb_sidebarColor'] ) ) ? $options['pdfemb_sidebarColor'] : $sidebar_color;
		$icon_color    = ( isset( $atts['iconcolor'] ) ) ? esc_html( $atts['iconcolor'] ) : false;
		$icon_color    = ( false === $icon_color && isset( $options['pdfemb_iconColor'] ) ) ? $options['pdfemb_iconColor'] : $icon_color;
		$button_color  = ( isset( $atts['buttoncolor'] ) ) ? esc_html( $atts['buttoncolor'] ) : false;
		$button_color  = ( false === $button_color && isset( $options['pdfemb_buttonColor'] ) ) ? $options['pdfemb_buttonColor'] : $button_color;

		// mobile.
		$mobile_width       = ( isset( $atts['mobilewidth'] ) ) ? intval( $atts['pdfemb_mobilewidth'] ) : $options['pdfemb_mobilewidth'];
		$mobile_width_data  = ( 0 === intval( $mobile_width ) ) ? false : 'data-mobile-width="' . intval( $mobile_width ) . '"';
		$mobile_button_text = ( isset( $atts['mobilewidth_button_text'] ) ) ? esc_html( $atts['mobilewidth_button_text'] ) : false;
		$mobile_button_text = ( false === $mobile_button_text && isset( $options['pdfemb_mobilewidth_button_text'] ) ) ? esc_html( $options['pdfemb_mobilewidth_button_text'] ) : false;
		$mobile_button_text = ( false === $mobile_button_text ) ? 'View In Full Screen' : $mobile_button_text;

		// secure.
		$secure      = ( isset( $atts['secure'] ) ) ? esc_html( $atts['secure'] ) : false;
		$secure      = ( false === $secure && isset( $options['pdfemb_secure'] ) ) ? esc_html( $options['pdfemb_secure'] ) : $secure;
		$secure_data = ( false === $secure || '' === trim( $secure ) ) ? false : 'data-secure="' . esc_html( $secure ) . '"';

		$disable_right_click      = ( isset( $atts['disablerightclick'] ) ) ? intval( $atts['disablerightclick'] ) : false;
		$disable_right_click      = ( false === $disable_right_click && isset( $options['pdfemb_disablerightclick'] ) ) ? intval( $options['pdfemb_disablerightclick'] ) : $disable_right_click;
		$disable_right_click_data = ( 0 === intval( $disable_right_click ) ) ? false : 'data-disablerightclick="' . intval( $disable_right_click ) . '"';

		// watermarking placeholders, filters.
		$watermark_text      = ( isset( $atts['wm_text'] ) ) ? esc_html( $atts['wm_text'] ) : false;
		$watermark_text      = ( false === $watermark_text && isset( $options['pdfemb_wm_text'] ) ) ? esc_html( $options['pdfemb_wm_text'] ) : $watermark_text;
		$watermark_text_data = ( false === $watermark_text ) ? false : 'data-watermark-text="' . esc_html( $watermark_text ) . '"';

		// if the pdf isn't truly secure then we don't do the watermark.
		$watermark_text      = ( false === $secure_url || ( isset( $atts['watermark'] ) && 'no' === strtolower( $atts['watermark'] ) ) ) ? false : $watermark_text;
		$watermark_text_data = ( false === $secure_url || ( isset( $atts['watermark'] ) && 'no' === strtolower( $atts['watermark'] ) ) ) ? 'data-watermark-text=""' : $watermark_text_data;

		// if there is truly a watermark, find and replace placeholders (see settings page).
		if ( false !== $watermark_text ) {
			if ( is_user_logged_in() ) {
				$current_user        = wp_get_current_user();
				$watermark_text      = isset( $current_user->data->user_login ) ? str_replace( '{username}', $current_user->data->user_login, $watermark_text ) : str_replace( '{username}', false, $watermark_text );
				$watermark_text      = isset( $current_user->data->display_name ) ? str_replace( '{fullname}', $current_user->data->display_name, $watermark_text ) : str_replace( '{fullname}', false, $watermark_text );
				$watermark_text      = isset( $current_user->data->user_email ) ? str_replace( '{email}', $current_user->data->user_email, $watermark_text ) : str_replace( '{username}', false, $watermark_text );
				$watermark_text_data = ( false === $watermark_text ) ? false : 'data-watermark-text="' . esc_html( $watermark_text ) . '"';
			} else {
				$watermark_text      = str_replace( '{username}', '', $watermark_text );
				$watermark_text      = str_replace( '{fullname}', '', $watermark_text );
				$watermark_text      = str_replace( '{email}', '', $watermark_text );
				$watermark_text_data = ( false === $watermark_text ) ? false : 'data-watermark-text="' . esc_html( $watermark_text ) . '"';
			}
		}

		$watermark_text      = apply_filters( 'pdf_embedder_watermark_text', $watermark_text, $options, $atts );
		$watermark_text_data = apply_filters( 'pdf_embedder_watermark_text_data', $watermark_text_data, $watermark_text, $options, $atts );

		$watermark_align      = ( isset( $atts['wm_halign'] ) ) ? esc_html( $atts['wm_halign'] ) : false;
		$watermark_align      = ( false === $watermark_align && isset( $options['pdfemb_wm_halign'] ) ) ? esc_html( $options['pdfemb_wm_halign'] ) : $watermark_align;
		$watermark_align_data = ( false === $watermark_align ) ? false : 'data-watermark-align="' . esc_html( $watermark_align ) . '"';

		$watermark_v_offset      = ( isset( $atts['wm_voffset'] ) ) ? esc_html( $atts['wm_voffset'] ) : false;
		$watermark_v_offset      = ( false === $watermark_v_offset && isset( $options['pdfemb_wm_voffset'] ) ) ? esc_html( $options['pdfemb_wm_voffset'] ) : $watermark_v_offset;
		$watermark_v_offset_data = ( false === $watermark_v_offset ) ? false : 'data-watermark-voffset="' . esc_html( $watermark_v_offset ) . '"';

		$watermark_font_size      = ( isset( $atts['wm_fontsize'] ) ) ? esc_html( $atts['wm_fontsize'] ) : false;
		$watermark_font_size      = ( false === $watermark_font_size && isset( $options['pdfemb_wm_fontsize'] ) ) ? esc_html( $options['pdfemb_wm_fontsize'] ) : $watermark_font_size;
		$watermark_font_size_data = ( false === $watermark_font_size ) ? false : 'data-watermark-fontsize="' . esc_html( $watermark_font_size ) . '"';

		$watermark_font_opacity      = ( isset( $atts['wm_opacity'] ) ) ? esc_html( $atts['wm_opacity'] ) : false;
		$watermark_font_opacity      = ( false === $watermark_font_opacity && isset( $options['pdfemb_wm_opacity'] ) ) ? esc_html( $options['pdfemb_wm_opacity'] ) : $watermark_font_opacity;
		$watermark_font_opacity_data = ( false === $watermark_font_opacity ) ? false : 'data-watermark-opacity="' . esc_html( $watermark_font_opacity ) . '"';

		$watermark_font_rotate      = ( isset( $atts['wm_rotate'] ) ) ? esc_html( $atts['wm_rotate'] ) : false;
		$watermark_font_rotate      = ( false === $watermark_font_rotate && isset( $options['pdfemb_wm_rotate'] ) ) ? esc_html( $options['pdfemb_wm_rotate'] ) : $watermark_font_rotate;
		$watermark_font_rotate_data = ( false === $watermark_font_rotate ) ? false : 'data-watermark-rotate="' . esc_html( $watermark_font_rotate ) . '"';

		ob_start();

		$height = '100%';
		$width  = '100%';

		?>

		<div id="pdf-data" data-index="<?php echo esc_attr( $pdf_embed_index ); ?>" class="pdf-embed pdf-embed-<?php echo esc_attr( $pdf_embed_index ); ?> pdf-embed-container <?php echo ( false !== $mobile_width_data ) ? 'mobile-view-active mobile-view-' . esc_attr( $mobile_width ) : false; ?>" id="pdf-embed" data-file="<?php echo esc_url( $pdf_url ); ?>"
		<?php
		if ( false !== $start_page ) {
			?>
			data-page="<?php echo esc_attr( $start_page ); ?>"<?php } ?> <?php
			if ( false !== $target && '' !== trim( $target ) ) {
				?>
			data-target="<?php echo esc_attr( $target ); ?>"<?php } ?>  <?php
			if ( false !== $download_base_url ) {
				?>
			data-downloadbaseurl="<?php echo esc_url( $download_base_url ); ?>"<?php } ?> data-height="<?php echo esc_attr( $height ); ?>" style="height: <?php echo esc_attr( $height ); ?>;" <?php echo esc_attr( $mobile_width_data ); ?> <?php echo esc_attr( $disable_right_click_data ); ?> data-s="securepdfs">

		<div id="outerContainer" class="pdf-embed-<?php echo esc_attr( $pdf_id ); ?>">

		<?php if ( false !== $mobile_width ) { ?>
			<div class="pdfemb-inner-div-wantmobile-fswrap pdfemb-wantmobile">
			</div>
		<?php } ?>

		<?php

		$toolbar_bg_color = ( isset( $options['pdfemb_color_sidebar'] ) && '' !== $options['pdfemb_color_sidebar'] ) ? 'style="background-color: ' . $options['pdfemb_color_sidebar'] . '"' : false;
		$button_color     = ( isset( $options['pdfemb_color_button'] ) && '' !== $options['pdfemb_color_button'] ) ? 'style="background-color: ' . $options['pdfemb_color_button'] . '"' : false;
		$icon_color       = ( isset( $options['pdfemb_color_icon'] ) && '' !== $options['pdfemb_color_icon'] ) ? 'style="background-color: ' . $options['pdfemb_color_icon'] . '"' : false;

		$this->increment_view_counter( $pdf_url );

		?>

<div id="mainContainer">

	<div class="findbar hidden doorHanger findbar-<?php echo esc_attr( $tb_location ); ?>" id="findbar">
		<div id="findbarInputContainer">
			<input id="findInput" class="findInput toolbarField" title="Find" placeholder="Find in document…" tabindex="91" data-l10n-id="find_input" aria-invalid="false">
			<div class="splitToolbarButton">
				<button id="findPrevious" class="findPrevious toolbarButton" title="Find the previous occurrence of the phrase" tabindex="92" data-l10n-id="find_previous">
					<span data-l10n-id="find_previous_label"><?php esc_html_e('Previous', 'pdf-embedder'); ?></span>
				</button>
				<button id="findNext" class="findNext toolbarButton" title="Find the next occurrence of the phrase" tabindex="93" data-l10n-id="find_next">
					<span data-l10n-id="find_next_label"><?php esc_html_e('Next', 'pdf-embedder'); ?></span>
				</button>
			</div>
		</div>

		<div id="findbarOptionsOneContainer">
			<input type="checkbox" id="findHighlightAll" class="findHighlightAll toolbarField" tabindex="94">
			<label for="findHighlightAll" class="toolbarLabel" data-l10n-id="find_highlight"><?php esc_html_e('Highlight All', 'pdf-embedder'); ?></label>
			<input type="checkbox" id="findMatchCase" class="findMatchCase toolbarField" tabindex="95">
			<label for="findMatchCase" class="toolbarLabel" data-l10n-id="find_match_case_label"><?php esc_html_e('Match Case', 'pdf-embedder'); ?></label>
		</div>
		<div id="findbarOptionsTwoContainer">
			<input type="checkbox" id="findMatchDiacritics" class="findMatchDiacritics toolbarField" tabindex="96">
			<label for="findMatchDiacritics" class="toolbarLabel" data-l10n-id="find_match_diacritics_label"><?php esc_html_e('Match Diacritics', 'pdf-embedder'); ?></label>
			<input type="checkbox" id="findEntireWord" class="findEntireWord toolbarField" tabindex="97">
			<label for="findEntireWord" class="toolbarLabel" data-l10n-id="find_entire_word_label"><?php esc_html_e('Whole Words', 'pdf-embedder'); ?></label>
		</div>

		<div id="findbarMessageContainer" class="findbarMessageContainer" aria-live="polite">
			<span id="findResultsCount" class="findResultsCount toolbarLabel"></span>
			<span id="findMsg" class="findMsg toolbarLabel"></span>
		</div>
	</div>  <!-- findbar -->
			<!-- findbar -->

			<div class="toolbar pdfemb-toolbar-always-visible-<?php echo esc_attr( $toolbarfixed ); ?> pdfemb-toolbar-display-<?php echo esc_attr( $tb_location ); ?>">

<div id="toolbarContainer" class="toolbarContainer">
	<div id="toolbarViewer" class="toolbarViewer">
		<div id="toolbarViewerLeft" class="toolbarViewerLeft">
		<?php $fb_hidden = isset( $options['pdfemb_search'] ) && 'on' === strtolower( $options['pdfemb_search'] ) ? 'findbarOn' : 'findbarHidden'; ?>

			<button id="viewFind" class="viewFind toolbarButton <?php echo esc_attr( $fb_hidden ); ?>" title="Find in Document" tabindex="12" data-l10n-id="findbar" aria-expanded="false" aria-controls="findbar">
				<span data-l10n-id="findbar_label"><?php esc_html_e('Find', 'pdf-embedder'); ?></span>
			</button>

			<div class="splitToolbarButton">
				<button class="toolbarButton previousButton" title="Previous Page" id="previous" tabindex="13" data-l10n-id="previous">
					<span data-l10n-id="previous_label"><?php esc_html_e('Previous', 'pdf-embedder'); ?></span>
				</button>

					<button class="toolbarButton nextButton next-bottom" title="Next Page" id="next" tabindex="14" data-l10n-id="next">
						<span data-l10n-id="next_label"><?php esc_html_e('Next', 'pdf-embedder'); ?></span>
					</button>
				</div>
				<span class="numPageStart"><?php esc_html_e('Page', 'pdf-embedder'); ?></span>
				<input type="number" id="pageNumber" class="pageNumber toolbarField" title="Page" value="1" size="4" min="1" tabindex="15" data-l10n-id="page" autocomplete="off">
				<span class="numbPagesDivider">/</span>
				<span id="numPages" class="numPages toolbarLabel"></span>
			</div>
			<div id="toolbarViewerRight" class="toolbarViewerRight">

			<?php $print_button_class = isset( $options['pdfemb_print'] ) && 'on' === strtolower( $options['pdfemb_print'] ) ? 'printButtonOn' : 'printButtonHidden'; ?>

<button id="print" class="toolbarButton <?php echo esc_attr( $print_button_class ); ?>" title="Print" tabindex="33" data-l10n-id="print">
	<span data-l10n-id="print_label"><?php esc_html_e('Print', 'pdf-embedder'); ?></span>
</button>

<?php $dl_button_class = isset( $options['pdfemb_download'] ) && 'on' === strtolower( $options['pdfemb_download'] ) ? 'dlButtonOn' : 'dlButtonHidden'; ?>

<button id="download" class="download toolbarButton <?php echo esc_attr( $dl_button_class ); ?>" title="Download" tabindex="34" data-l10n-id="download">
	<span data-l10n-id="download_label"><?php esc_html_e('Download', 'pdf-embedder'); ?></span>
</button>
<?php $fs_button_class = isset( $options['pdfemb_fullscreen'] ) && 'on' === strtolower( $options['pdfemb_fullscreen'] ) ? 'fsButtonOn' : 'fsButtonHidden'; ?>

<button id="fullscreen" data-pdf-id="<?php echo esc_attr( $pdf_id );?>" class="toolbarButton wppdf-fullscreen-button wppdf-embed-fullscreen-<?php echo esc_attr( $pdf_id );?> <?php echo esc_attr( $fs_button_class ); ?>" title="Fullscreen" tabindex="35" data-l10n-id="fullscreen" data-popup="#pdf-embed">
	<span data-l10n-id="fullscreen_label"><?php esc_html_e( 'Fullscreen', 'pdf-embedder' ); ?></span>
</button>

<!-- Should be visible when the "editorModeButtons" are visible. -->
<div id="editorModeSeparator" class="editorModeSeparator verticalToolbarSeparator hidden"></div>

</div>
<div id="toolbarViewerMiddle" class="toolbarViewerMiddle">
<div class="splitToolbarButton">
	<button id="zoomOut" class="zoomOut toolbarButton" title="Zoom Out" tabindex="21" data-l10n-id="zoom_out">
		<span data-l10n-id="zoom_out_label"><?php esc_html_e( 'Zoom Out', 'pdf-embedder' ); ?></span>
	</button>
	<button id="zoomIn" class="zoomIn toolbarButton" title="Zoom In" tabindex="22" data-l10n-id="zoom_in">
		<span data-l10n-id="zoom_in_label"><?php esc_html_e( 'Zoom In', 'pdf-embedder' ); ?></span>
	</button>
</div>
<span id="scaleSelectContainer" class="scaleSelectContainer dropdownToolbarButton">
	<select id="scaleSelect" class="scaleSelect" title="Zoom" tabindex="23" data-l10n-id="zoom">
		<option id="pageAutoOption" title="" value="auto"  data-l10n-id="page_scale_auto"><?php esc_html_e( 'Automatic Zoom', 'pdf-embedder' ); ?></option>
		<option id="pageActualOption" title="" value="page-actual"  data-l10n-id="page_scale_actual"><?php esc_html_e( 'Actual Size', 'pdf-embedder' ); ?></option>
		<option id="pageFitOption" title="" value="page-fit" selected="selected" data-l10n-id="page_scale_fit"><?php esc_html_e( 'Page Fit', 'pdf-embedder' ); ?></option>
		<option id="pageWidthOption" title="" value="page-width" data-l10n-id="page_scale_width"><?php esc_html_e( 'Page Width', 'pdf-embedder' ); ?></option>
		<option id="customScaleOption" title="" value="custom" disabled="disabled" hidden="true"></option>
		<option title="" value="0.5" data-l10n-id="page_scale_percent" data-l10n-args='{ "scale": 50 }'><?php esc_html_e( '50%', 'pdf-embedder' ); ?></option>
		<option title="" value="0.75" data-l10n-id="page_scale_percent" data-l10n-args='{ "scale": 75 }'><?php esc_html_e( '75%', 'pdf-embedder' ); ?></option>
		<option title="" value="1" data-l10n-id="page_scale_percent" data-l10n-args='{ "scale": 100 }'><?php esc_html_e( '100%', 'pdf-embedder' ); ?></option>
		<option title="" value="1.25" data-l10n-id="page_scale_percent" data-l10n-args='{ "scale": 125 }'><?php esc_html_e( '125%', 'pdf-embedder' ); ?></option>
		<option title="" value="1.5" data-l10n-id="page_scale_percent" data-l10n-args='{ "scale": 150 }'><?php esc_html_e( '150%', 'pdf-embedder' ); ?></option>
		<option title="" value="2" data-l10n-id="page_scale_percent" data-l10n-args='{ "scale": 200 }'><?php esc_html_e( '200%', 'pdf-embedder' ); ?></option>
		<option title="" value="3" data-l10n-id="page_scale_percent" data-l10n-args='{ "scale": 300 }'><?php esc_html_e( '300%', 'pdf-embedder' ); ?></option>
		<option title="" value="4" data-l10n-id="page_scale_percent" data-l10n-args='{ "scale": 400 }'><?php esc_html_e( '400%', 'pdf-embedder' ); ?></option>
	</select>
</span>
</div>
</div>
<div id="loadingBar" class="loadingBar">
<div class="progress">
	<div class="glimmer"></div>
</div>
</div>
</div>
</div>
<?php if( $secure_url ) : ?>
			<div style="width:100%;z-index:1000;position:fixed;font-size:<?php echo esc_attr($watermark_font_size); ?>pt;opacity:<?php echo esc_attr($watermark_font_opacity);?>%;top:<?php echo esc_attr( $watermark_v_offset ); ?>%;text-align:<?php echo esc_attr($watermark_align); ?>;"><span class="watermark" style="position:absolute;transform: rotate(<?php echo esc_attr( $watermark_font_rotate ); ?>deg);"><?php esc_html_e($watermark_text); ?></span></div>
		<?php endif; ?>
<div id="viewerContainer" tabindex="0" <?php echo $watermark_text_data; ?> <?php echo $watermark_align_data; ?> <?php echo $watermark_v_offset_data; ?> <?php echo $watermark_font_size_data; ?> <?php echo $watermark_font_opacity_data; ?> <?php echo $watermark_font_rotate_data; ?> class="scrollbar-<?php echo $scrollbar; ?> continousscroll-<?php echo $continousscroll; ?>">


<div id="viewer" class="pdfViewer"></div>
</div>


<div id="errorWrapper" hidden='true'>
	<div id="errorMessageLeft">
		<span id="errorMessage"></span>
		<button id="errorShowMore" data-l10n-id="error_more_info">
		<?php esc_html_e( 'More Information', 'pdf-embedder' ); ?>
		</button>
		<button id="errorShowLess" data-l10n-id="error_less_info" hidden='true'>
		<?php esc_html_e( 'Less Information', 'pdf-embedder' ); ?>
		</button>
	</div>
	<div id="errorMessageRight">
		<button id="errorClose" data-l10n-id="error_close">
		<?php esc_html_e( 'Close', 'pdf-embedder' ); ?>
		</button>
	</div>
	<div id="errorSpacer"></div>
	<textarea id="errorMoreInfo" hidden='true' readonly="readonly"></textarea>
	</div>
</div> <!-- mainContainer -->

<div id="dialogContainer">
	<dialog id="passwordDialog">
		<div class="row">
			<label for="password" id="passwordText" data-l10n-id="password_label"><?php esc_html_e( 'Enter the password to open this PDF file:', 'pdf-embedder' ); ?></label>
		</div>
		<div class="row">
			<input type="password" id="password" class="toolbarField">
		</div>
		<div class="buttonRow">
			<button id="passwordCancel" class="dialogButton"><span data-l10n-id="password_cancel"><?php esc_html_e( 'Cancel', 'pdf-embedder' ); ?></span></button>
			<button id="passwordSubmit" class="dialogButton"><span data-l10n-id="password_ok"><?php esc_html_e( 'OK', 'pdf-embedder' ); ?></span></button>
		</div>
	</dialog>
	<dialog id="documentPropertiesDialog">
		<div class="row">
			<span id="fileNameLabel" data-l10n-id="document_properties_file_name"><?php esc_html_e( 'File name:', 'pdf-embedder' ); ?></span>
		<p id="fileNameField" aria-labelledby="fileNameLabel">-</p>
	</div>
	<div class="row">
		<span id="fileSizeLabel" data-l10n-id="document_properties_file_size"><?php esc_html_e( 'Close', 'pdf-embedder' ); ?>File size:</span>
		<p id="fileSizeField" aria-labelledby="fileSizeLabel">-</p>
	</div>
	<div class="separator"></div>
	<div class="row">
		<span id="titleLabel" data-l10n-id="document_properties_title"><?php esc_html_e( 'Title', 'pdf-embedder' ); ?>:</span>
		<p id="titleField" aria-labelledby="titleLabel">-</p>
	</div>
	<div class="row">
		<span id="authorLabel" data-l10n-id="document_properties_author"><?php esc_html_e( 'Author', 'pdf-embedder' ); ?>:</span>
		<p id="authorField" aria-labelledby="authorLabel">-</p>
	</div>
	<div class="row">
		<span id="subjectLabel" data-l10n-id="document_properties_subject"><?php esc_html_e( 'Subject', 'pdf-embedder' ); ?>:</span>
		<p id="subjectField" aria-labelledby="subjectLabel">-</p>
	</div>
	<div class="row">
		<span id="keywordsLabel" data-l10n-id="document_properties_keywords"><?php esc_html_e( 'Keywords', 'pdf-embedder' ); ?>:</span>
		<p id="keywordsField" aria-labelledby="keywordsLabel">-</p>
	</div>
	<div class="row">
		<span id="creationDateLabel" data-l10n-id="document_properties_creation_date">Creation Date:</span>
		<p id="creationDateField" aria-labelledby="creationDateLabel">-</p>
	</div>
	<div class="row">
		<span id="modificationDateLabel" data-l10n-id="document_properties_modification_date">Modification Date:</span>
		<p id="modificationDateField" aria-labelledby="modificationDateLabel">-</p>
	</div>
	<div class="row">
		<span id="creatorLabel" data-l10n-id="document_properties_creator">Creator:</span>
		<p id="creatorField" aria-labelledby="creatorLabel">-</p>
	</div>
	<div class="separator"></div>
	<div class="row">
		<span id="producerLabel" data-l10n-id="document_properties_producer">PDF Producer:</span>
		<p id="producerField" aria-labelledby="producerLabel">-</p>
	</div>
	<div class="row">
		<span id="versionLabel" data-l10n-id="document_properties_version">PDF Version:</span>
		<p id="versionField" aria-labelledby="versionLabel">-</p>
	</div>
	<div class="row">
		<span id="pageCountLabel" data-l10n-id="document_properties_page_count">Page Count:</span>
		<p id="pageCountField" aria-labelledby="pageCountLabel">-</p>
	</div>
	<div class="row">
		<span id="pageSizeLabel" data-l10n-id="document_properties_page_size">Page Size:</span>
		<p id="pageSizeField" aria-labelledby="pageSizeLabel">-</p>
	</div>
	<div class="separator"></div>
	<div class="row">
		<span id="linearizedLabel" data-l10n-id="document_properties_linearized">Fast Web View:</span>
		<p id="linearizedField" aria-labelledby="linearizedLabel">-</p>
	</div>
	<div class="buttonRow">
		<button id="documentPropertiesClose" class="dialogButton"><span data-l10n-id="document_properties_close">Close</span></button>
	</div>
	</dialog>
	<dialog id="printServiceDialog" style="min-width: 200px;">
	<div class="row">
		<span data-l10n-id="print_progress_message">Preparing document for printing…</span>
	</div>
	<div class="row">
		<progress value="0" max="100"></progress>
		<span data-l10n-id="print_progress_percent" data-l10n-args='{ "progress": 0 }' class="relative-progress">0%</span>
	</div>
	<div class="buttonRow">
	<button id="printCancel" class="dialogButton"><span data-l10n-id="print_progress_close">Cancel</span></button>
	</div>
	</dialog>
</div>  <!-- dialogContainer -->

<div id="printContainer"></div>
<input type="file" id="fileInput" class="hidden">

		<div class="pdfmedia">

		<?php

		if ( isset( $poweredby ) && 'on' === strtolower( $poweredby ) ) :

			echo '<div><a href="https://wp-pdf.com/?utm_source=Poweredby&utm_medium=freemium&utm_campaign=Freemium" target="_blank">wp-pdf.com</a></div>';

		endif;

		?>

		<?php

		if ( isset( $view_counter ) && 'on' === strtolower( $view_counter ) ) :

			echo $this->pdf_embedder_view_counts( array( 'url' => $pdf_url ), false );  // @codingStandardsIgnoreLine

		endif;
		?>

		<?php

		if ( isset( $download_counter ) && 'on' === strtolower( $download_counter ) ) :

			echo $this->pdf_embedder_download_counts( array( 'url' => $pdf_url ), false ); // @codingStandardsIgnoreLine

endif;

		?>

<?php if('both' === $tb_location ) : ?>

<div class="findbar hidden doorHanger findbar-bottom" id="findbar-bottom">
	<div id="findbarInputContainer">
		<input id="findInput-bottom" class="findInput toolbarField" title="Find" placeholder="Find in document…" tabindex="91" data-l10n-id="find_input" aria-invalid="false">
		<div class="splitToolbarButton">
			<button id="findPrevious" class="findPrevious toolbarButton" title="Find the previous occurrence of the phrase" tabindex="92" data-l10n-id="find_previous">
				<span data-l10n-id="find_previous_label"><?php esc_html_e('Previous', 'pdf-embedder'); ?></span>
			</button>
			<button id="findNext-bottom" class="findNext toolbarButton" title="Find the next occurrence of the phrase" tabindex="93" data-l10n-id="find_next">
				<span data-l10n-id="find_next_label"><?php esc_html_e('Next', 'pdf-embedder'); ?></span>
			</button>
		</div>
	</div>

	<div id="findbarOptionsOneContainer">
		<input type="checkbox" id="findHighlightAll-bottom" class="findHighlightAll toolbarField" tabindex="94">
		<label for="findHighlightAll-bottom" class="toolbarLabel" data-l10n-id="find_highlight"><?php esc_html_e('Highlight All', 'pdf-embedder'); ?></label>
		<input type="checkbox" id="findMatchCase-bottom" class="findMatchCase toolbarField" tabindex="95">
		<label for="findMatchCase-bottom" class="toolbarLabel" data-l10n-id="find_match_case_label"><?php esc_html_e('Match Case', 'pdf-embedder'); ?></label>
	</div>
	<div id="findbarOptionsTwoContainer">
		<input type="checkbox" id="findMatchDiacritics-bottom" class="findMatchDiacritics toolbarField" tabindex="96">
		<label for="findMatchDiacritics-bottom" class="toolbarLabel" data-l10n-id="find_match_diacritics_label"><?php esc_html_e('Match Diacritics', 'pdf-embedder'); ?></label>
		<input type="checkbox" id="findEntireWord-bottom" class="findEntireWord toolbarField" tabindex="97">
		<label for="findEntireWord" class="toolbarLabel" data-l10n-id="find_entire_word_label"><?php esc_html_e('Whole Words', 'pdf-embedder'); ?></label>
	</div>

	<div id="findbarMessageContainer-bottom" class="findbarMessageContainer" aria-live="polite">
		<span id="findResultsCount-bottom" class="findResultsCount toolbarLabel"></span>
		<span id="findMsg-bottom" class="findMsg toolbarLabel"></span>
	</div>
</div>  <!-- findbar -->
		<!-- findbar -->

<div id="toolbar-bottom" class="toolbar pdfemb-toolbar-always-visible-<?php echo esc_attr( $toolbarfixed ); ?> pdfemb-toolbar-display-bottom">

	<div id="toolbarContainer-bottom" class="toolbarContainer">
		<div id="toolbarViewer-bottom" class="toolbarViewer">
			<div id="toolbarViewerLeft-bottom" class="toolbarViewerLeft">
			<?php $fb_hidden = isset( $options['pdfemb_search'] ) && 'on' === strtolower( $options['pdfemb_search'] ) ? 'findbarOn' : 'findbarHidden'; ?>

				<button id="viewFind-bottom" class="viewFind viewfind-bottom toolbarButton <?php echo esc_attr( $fb_hidden ); ?>" title="Find in Document" tabindex="12" data-l10n-id="findbar" aria-expanded="false" aria-controls="findbar">
					<span data-l10n-id="findbar_label"><?php esc_html_e('Find', 'pdf-embedder'); ?></span>
				</button>

				<div class="splitToolbarButton">
					<button class="toolbarButton previousButton previousBottom" title="Previous Page" id="previous-bottom" tabindex="13" data-l10n-id="previous">
						<span data-l10n-id="previous_label"><?php esc_html_e('Previous', 'pdf-embedder'); ?></span>
					</button>
					<div class="splitToolbarButtonSeparator"></div>
						<button class="toolbarButton nextButton next-bottom" title="Next Page" id="next-bottom" tabindex="14" data-l10n-id="next">
							<span data-l10n-id="next_label"><?php esc_html_e('Next', 'pdf-embedder'); ?></span>
						</button>
					</div>
					<span class="numPageStart"><?php esc_html_e('Page', 'pdf-embedder'); ?></span>
					<input type="number" id="pageNumber-bottom" class="pageNumber toolbarField" title="Page" value="1" size="4" min="1" tabindex="15" data-l10n-id="page" autocomplete="off">
					<span class="numbPagesDivider">/</span>
					<span id="numPages-bottom" class="numPages toolbarLabel"></span>
				</div>
				<div id="toolbarViewerRight" class="toolbarViewerRight">

				<?php $print_button_class = isset( $options['pdfemb_print'] ) && 'on' === strtolower( $options['pdfemb_print'] ) ? 'printButtonOn' : 'printButtonHidden'; ?>

	<button id="print-bottom" class="toolbarButton <?php echo esc_attr( $print_button_class ); ?>" title="Print" tabindex="33" data-l10n-id="print">
		<span data-l10n-id="print_label"><?php esc_html_e('Print', 'pdf-embedder'); ?></span>
	</button>

	<?php $dl_button_class = isset( $options['pdfemb_download'] ) && 'on' === strtolower( $options['pdfemb_download'] ) ? 'dlButtonOn' : 'dlButtonHidden'; ?>

	<button id="download-bottom" class="download toolbarButton <?php echo esc_attr( $dl_button_class ); ?>" title="Download" tabindex="34" data-l10n-id="download">
		<span data-l10n-id="download_label"><?php esc_html_e('Download', 'pdf-embedder'); ?></span>
	</button>
	<?php $fs_button_class = isset( $options['pdfemb_fullscreen'] ) && 'on' === strtolower( $options['pdfemb_fullscreen'] ) ? 'fsButtonOn' : 'fsButtonHidden'; ?>

	<button id="fullscreen-bottom" data-pdf-id="<?php echo esc_attr( $pdf_id );?>" class="toolbarButton wppdf-fullscreen-button wppdf-embed-fullscreen-<?php echo esc_attr( $pdf_id );?> <?php echo esc_attr( $fs_button_class ); ?>" title="Fullscreen" tabindex="35" data-l10n-id="fullscreen" data-popup="#pdf-embed">
		<span data-l10n-id="fullscreen_label"><?php esc_html_e( 'Fullscreen', 'pdf-embedder' ); ?></span>
	</button>

	</div>
	<div id="toolbarViewerMiddle-bottom" class="toolbarViewerMiddle">
	<div class="splitToolbarButton">
		<button id="zoomOut-bottom" class="zoomOut toolbarButton" title="Zoom Out" tabindex="21" data-l10n-id="zoom_out">
			<span data-l10n-id="zoom_out_label"><?php esc_html_e( 'Zoom Out', 'pdf-embedder' ); ?></span>
		</button>
		<button id="zoomIn-bottom" class="zoomIn toolbarButton" title="Zoom In" tabindex="22" data-l10n-id="zoom_in">
			<span data-l10n-id="zoom_in_label"><?php esc_html_e( 'Zoom In', 'pdf-embedder' ); ?></span>
		</button>
	</div>
	<span id="scaleSelectContainer" class="dropdownToolbarButton">
		<select id="scaleSelect-bottom" class="scaleSelect title="Zoom" tabindex="23" data-l10n-id="zoom">
			<option id="pageAutoOption-bottom" title="" value="auto"  data-l10n-id="page_scale_auto"><?php esc_html_e( 'Automatic Zoom', 'pdf-embedder' ); ?></option>
			<option id="pageActualOption-bottom" title="" value="page-actual"  data-l10n-id="page_scale_actual"><?php esc_html_e( 'Actual Size', 'pdf-embedder' ); ?></option>
			<option id="pageFitOption-bottom" title="" value="page-fit" selected="selected" data-l10n-id="page_scale_fit"><?php esc_html_e( 'Page Fit', 'pdf-embedder' ); ?></option>
			<option id="pageWidthOption-bottom" title="" value="page-width" data-l10n-id="page_scale_width"><?php esc_html_e( 'Page Width', 'pdf-embedder' ); ?></option>
			<option id="customScaleOption-bottom" title="" value="custom" disabled="disabled" hidden="true"></option>
			<option title="" value="0.5" data-l10n-id="page_scale_percent" data-l10n-args='{ "scale": 50 }'><?php esc_html_e( '50%', 'pdf-embedder' ); ?></option>
			<option title="" value="0.75" data-l10n-id="page_scale_percent" data-l10n-args='{ "scale": 75 }'><?php esc_html_e( '75%', 'pdf-embedder' ); ?></option>
			<option title="" value="1" data-l10n-id="page_scale_percent" data-l10n-args='{ "scale": 100 }'><?php esc_html_e( '100%', 'pdf-embedder' ); ?></option>
			<option title="" value="1.25" data-l10n-id="page_scale_percent" data-l10n-args='{ "scale": 125 }'><?php esc_html_e( '125%', 'pdf-embedder' ); ?></option>
			<option title="" value="1.5" data-l10n-id="page_scale_percent" data-l10n-args='{ "scale": 150 }'><?php esc_html_e( '150%', 'pdf-embedder' ); ?></option>
			<option title="" value="2" data-l10n-id="page_scale_percent" data-l10n-args='{ "scale": 200 }'><?php esc_html_e( '200%', 'pdf-embedder' ); ?></option>
			<option title="" value="3" data-l10n-id="page_scale_percent" data-l10n-args='{ "scale": 300 }'><?php esc_html_e( '300%', 'pdf-embedder' ); ?></option>
			<option title="" value="4" data-l10n-id="page_scale_percent" data-l10n-args='{ "scale": 400 }'><?php esc_html_e( '400%', 'pdf-embedder' ); ?></option>
		</select>
	</span>
	</div>
</div>
<div id="loadingBar-bottom" class="loadingBar">
	<div class="progress">
		<div class="glimmer"></div>
	</div>
	</div>
</div>
<?php endif; ?>
</div>


		<style>
		<?php if ( $icon_color ) : ?>
			#pdf-embed .toolbarButton::before { background-color: <?php echo esc_attr( $icon_color ); ?>; }
			#pdf-embed .toolbarButton::before { background-color: <?php echo esc_attr( $icon_color ); ?>; }
			<?php endif; ?>
		<?php if ( $button_color ) : ?>
			#pdf-embed .dropdownToolbarButton select { background-color: <?php echo esc_attr( $button_color ); ?>; }
			#pdf-embed .toolbarField { background-color: <?php echo esc_attr( $button_color ); ?>; }
			#pdf-embed button:not(:hover):not(:active):not(.has-background) { background-color: <?php echo esc_attr( $button_color ); ?> !important; }
			<?php endif; ?>
		</style>
		<?php
			$content = ob_get_clean();

			return $content;

	}


}

?>
