<?php

class pdfth_core_pdf_thumbnails {

	// Admin menu

	protected function get_options_menuname() {
		return 'pdfth_list_options';
	}

	protected function get_options_pagename() {
		return 'pdfth_options';
	}

	protected function get_options_name() {
		return 'pdfth';
	}

	public function pdfth_admin_menu() {
		if ($this->is_multisite_and_network_activated()) {
			add_submenu_page( 'settings.php', __('PDF Thumbnails settings', 'pdfth-thumbnails'), __('PDF Thumbnails', 'pdfth-thumbnails'),
				'manage_network_options', $this->get_options_menuname(),
				array($this, 'pdfth_options_do_page'));
		}
		else {
			add_options_page( __('PDF Thumbnails settings', 'pdfth-thumbnails'), __('PDF Thumbnails', 'pdfth-thumbnails'),
				'manage_options', $this->get_options_menuname(),
				array($this, 'pdfth_options_do_page'));
		}
	}

	public function pdfth_plugin_action_links( $links, $file ) {
		if ($file == $this->my_plugin_basename()) {
			$settings_link = '<a href="' . $this->get_settings_url() . '">' . __('Settings', 'pdfth-thumbnails') .'</a>';
			array_unshift($links, $settings_link);
		}

		return $links;
	}

	protected function get_settings_url() {
		return $this->is_multisite_and_network_activated()
			? network_admin_url( 'settings.php?page='.$this->get_options_menuname() )
			: admin_url( 'options-general.php?page='.$this->get_options_menuname() );
	}

	public function pdfth_options_do_page() {

		wp_enqueue_script( 'pdfth_admin_js', $this->my_plugin_url().'js/admin/pdfth-admin.js', array('jquery') );
		wp_enqueue_script( 'pdfth_generator_js', $this->my_plugin_url().'js/pdfth-generator.js', array('jquery') );
		wp_enqueue_style( 'pdfth_admin_css', $this->my_plugin_url().'css/pdfth-admin.css' );

		$submit_page = $this->is_multisite_and_network_activated() ? 'edit.php?action='.$this->get_options_menuname() : 'options.php';

		if ($this->is_multisite_and_network_activated()) {
			$this->pdfth_options_do_network_errors();
		}
		?>

		<div>

			<h2><?php esc_html_e('PDF Thumbnails setup', 'pdfth-thumbnails'); ?></h2>

			<div id="pdfth-tablewrapper">

				<div id="pdfth-tableleft" class="pdfth-tablecell">

					<h2 id="pdfth-tabs" class="nav-tab-wrapper">
						<a href="#thumbnails" id="thumbnails-tab" class="nav-tab nav-tab-active"><?php esc_html_e('Thumbnails', 'pdfth-thumbnails'); ?></a>
						<a href="#main" id="main-tab" class="nav-tab"><?php esc_html_e('Settings', 'pdfth-thumbnails'); ?></a>
						<a href="#license" id="license-tab" class="nav-tab"><?php esc_html_e('License', 'pdfth-thumbnails'); ?></a>
					</h2>

					<form action="<?php echo $submit_page; ?>" method="post" id="pdfth_form" enctype="multipart/form-data" >

						<?php

						settings_fields($this->get_options_pagename());


						echo '<div id="thumbnails-section" class="pdfthtab active">';
						$this->pdfth_thumbnailssection_text();
						echo '</div>';

						echo '<div id="main-section" class="pdfthtab">';
						$this->pdfth_mainsection_text();

						?>
						<p class="submit">
							<input type="submit" value="<?php esc_html_e('Save Changes', 'pdfth-thumbnails'); ?>" class="button button-primary" id="submit-main" name="submit">
						</p>
						<?php

						echo '</div>';


						echo '<div id="license-section" class="pdfthtab">';
						$this->pdfth_licensesection_text();
						?>

						<p class="submit">
							<input type="submit" value="<?php esc_html_e('Save Changes', 'pdfth-thumbnails'); ?>" class="button button-primary" id="submit-license" name="submit">
						</p>

						</div>


					</form>
				</div>


			</div>

		</div>  <?php
	}

	protected function pdfth_thumbnailssection_text() {
		?>
		<div>
				<h2><?php _e('Generate Thumbnails', 'pdfth-thumbnails'); ?></h2>
		<p>You can regenerate thumbnails for all existing PDF files:</p>
		<p><input type="checkbox" id='pdfemb-onlynew' class='checkbox' checked />
			<label for="pdfemb_onlynew">Only generate thumbnails for PDFs that don't already have one</label>
		</p>
		<br />
		<p><a href="#" id="pdfth-start-generate-all" class="button-secondary">Click here to start</a></p>
		</div>
		<?php
	}

	protected function pdfth_mainsection_text() {
		$options = $this->get_option_pdfth();
		?>
		<label for="input_pdfth_maxwidth" class="textinput"><?php _e('Maximum Width of generated images', 'pdfth-thumbnails'); ?></label>
		<input id='input_pdfth_maxwidth' class='textinput' name='<?php echo $this->get_options_name(); ?>[pdfth_maxwidth]' size='10' type='text' value='<?php echo esc_attr($options['pdfth_maxwidth']); ?>' />

		<br class="clear"/>

		<p class="desc big"><i><?php _e('Enter an integer number of pixels, or <b>0</b> for no maximum', 'pdf-embedder'); ?></i></p>

		<br class="clear"/>

		<label for="input_pdfth_imagetype" class="textinput"><?php _e('Image Format', 'pdfth-thumbnails'); ?></label>

		<select name='<?php echo $this->get_options_name(); ?>[pdfth_imagetype]' id='pdfth_imagetype' class='select'>
			<option value="png" <?php echo $options['pdfth_imagetype'] == 'png' ? 'selected' : ''; ?>><?php esc_html_e('PNG', 'pdf-thumbnails'); ?></option>
			<option value="jpg" <?php echo $options['pdfth_imagetype'] == 'jpg' ? 'selected' : ''; ?>><?php esc_html_e('JPEG', 'pdf-thumbnails'); ?></option>
		</select>

		<br class="clear"/>

		<p class="desc big"><i><?php _e('PNG has higher quality but JPEG may produce smaller images that load faster', 'pdf-embedder'); ?></i></p>

		<br class="clear"/>

		<?php
	}

	protected function pdfth_licensesection_text() {
		$options = $this->get_option_pdfth();
		?>
		<p><?php _e('You should have received a license key when you purchased PDF Thumbnails.', 'pdfth-thumbnails'); ?></p>
		<p><?php printf( __('Please enter it below to enable automatic updates, or <a href="%s">email us</a> if you do not have one.', 'pdfth-thumbnails'), 'mailto:contact@wp-pdf.com'); ?></p>

		<label for="input_pdfth_license_key" class="textinput big"><?php esc_html_e('License Key', 'pdfth-thumbnails'); ?></label>
		<input id='input_pdfth_license_key' name='<?php echo $this->get_options_name(); ?>[pdfth_license_key]' size='40' type='text' value='<?php echo esc_attr($options['pdfth_license_key']); ?>' class='textinput' />
		<br class="clear" />

		<?php

		// Display latest license status

		$license_status = get_site_option($this->get_eddsl_optname(), true);

		if (is_array($license_status) && isset($license_status['license_id']) && $license_status['license_id'] != '') {
			echo '<br class="clear" />';
			echo '<table>';
			echo '<tr><td>'.esc_html__('Current License', 'pdfth-thumbnails').': </td><td>'.htmlentities(isset($license_status['license_id']) ? $license_status['license_id'] : '').'</td></tr>';

			if (isset($license_status['status']) && $license_status['status'] != '') {
				echo '<tr><td>'.esc_html__('Status', 'pdfth-thumbnails').': </td><td>'.htmlentities(strtoupper($license_status['status'])).'</td></tr>';
			}

			if (isset($license_status['last_check_time']) && $license_status['last_check_time'] != '') {
				echo '<tr><td>'.esc_html__('Last Checked', 'pdfth-thumbnails').': </td><td>'.htmlentities(date("j M Y H:i:s",$license_status['last_check_time'])).'</td></tr>';
			}

			/* if (isset($license_status['first_check_time']) && $license_status['first_check_time'] != '') {
				echo '<p>Result First Seen: '.htmlentities(date("M j Y H:i:s",$license_status['first_check_time'])).'</p>';
			} */

			if (isset($license_status['expires_time'])) { // && $license_status['expires_time'] < time() + 24*60*60*30) {
				echo '<tr><td>'.esc_html__('License Expires', 'pdfth-thumbnails').': </td><td>'.htmlentities(date("j M Y H:i:s",$license_status['expires_time'])).'</td></tr>';
			}

			/* if (isset($license_status['result_cleared'])) {
				echo '<p>Result cleared: '.($license_status['result_cleared'] ? 'yes' : 'no').'</p>';
			}*/

			echo '</table>';

			if (isset($license_status['expires_time']) && $license_status['expires_time'] < time() + 24*60*60*60) {
				echo '<p>';
				if (isset($license_status['renewal_link']) && $license_status['renewal_link']) {
					printf(__('To renew your license, please <a href="%s" target="_blank">click here</a>.', 'pdfth-thumbnails'), esc_attr($license_status['renewal_link']));
				}
				echo ' ';
				esc_html_e('You will receive a 50% discount if you renew before your license expires.', 'pdfth-thumbnails');
				echo '</p>';
			}

			echo '<br class="clear" />';

			?>
            <span>
            <input type="checkbox" name='<?php echo $this->get_options_name(); ?>[pdfth_allowbeta]' id='pdfth_allowbeta' class='checkbox' <?php echo $options['pdfth_allowbeta'] == 'on' ? 'checked' : ''; ?> />
            <label for="pdfth_allowbeta" class="checkbox plain"><?php esc_html_e('Participate in future beta releases of the plugin', 'pdf-thumbnails'); ?></label>
            </span>

            <br class="clear" />

            <?php

			if (isset($license_status['download_link'])) {
				echo '<p>Download latest plugin ZIP <a href="'.$license_status['download_link'].'" target="_blank">here</a></p>';
				echo '<br class="clear" />';
			}

		}

	}

	public function pdfth_options_validate($input) {
		$newinput = Array();

		$newinput['pdfth_maxwidth'] = isset($input['pdfth_maxwidth']) ? trim(strtolower($input['pdfth_maxwidth'])) : '2000';
		if (!is_numeric($newinput['pdfth_maxwidth']) || $newinput['pdfth_maxwidth'] < 0) {
			add_settings_error(
				'pdfth_maxwidth',
				'widtherror',
				self::get_error_string('pdfth_maxwidth|widtherror'),
				'error'
			);
		}

		$newinput['pdfth_imagetype'] = isset($input['pdfth_imagetype']) && in_array($input['pdfth_imagetype'], array('png', 'jpg')) ? $input['pdfth_imagetype'] : 'jpg';

		// License Key
		$newinput['pdfth_license_key'] = trim($input['pdfth_license_key']);
		if ($newinput['pdfth_license_key'] != '') {
			if(!preg_match('/^.{32}.*$/i', $newinput['pdfth_license_key'])) {
				add_settings_error(
					'pdfth_license_key',
					'tooshort_texterror',
					self::get_error_string('pdfth_license_key|tooshort_texterror'),
					'error'
				);
			}
			else {
				// There is a valid-looking license key present

				$checked_license_status = get_site_option($this->get_eddsl_optname(), true);

				// Only bother trying to activate if we have a new license key OR the same license key but it was invalid on last check.
				$existing_valid_license = '';
				if (is_array($checked_license_status) && isset($checked_license_status['license_id']) && $checked_license_status['license_id'] != ''
				    && isset($checked_license_status['status']) && $checked_license_status['status'] == 'valid') {
					$existing_valid_license = $checked_license_status['license_id'];
				}

				if ($existing_valid_license != $newinput['pdfth_license_key']) {

					$license_status = $this->edd_license_activate($newinput['pdfth_license_key']);
					if (isset($license_status['status']) && $license_status['status'] != 'valid') {
						add_settings_error(
							'pdfth_license_key',
							$license_status['status'],
							self::get_error_string('pdfth_license_key|'.$license_status['status']),
							'error'
						);
					}
				}
			}
		}

		$newinput['pdfth_allowbeta'] = isset($input['pdfth_allowbeta']) && $input['pdfth_allowbeta'];

		$newinput['pdfth_version'] = $this->PLUGIN_VERSION;
		return $newinput;
	}

	protected function get_error_string($fielderror) {
		$local_error_strings = Array(
			'pdfth_maxwidth|widtherror' => __('Max width must be 0 or greater', 'pdfth-thumbnails'),
			'pdfth_license_key|tooshort_texterror' => __('License key is too short', 'pdfth-thumbnails'),
			//	'valid', 'invalid', 'missing', 'item_name_mismatch', 'expired', 'site_inactive', 'inactive', 'disabled', 'empty'
			'pdfth_license_key|invalid' => __('License key failed to activate', 'pdfth-thumbnails'),
			'pdfth_license_key|missing' => __('License key does not exist in our system at all', 'pdfth-thumbnails'),
			'pdfth_license_key|item_name_mismatch' => __('License key entered is for the wrong product', 'pdfth-thumbnails'),
			'pdfth_license_key|expired' => __('License key has expired', 'pdfth-thumbnails'),
			'pdfth_license_key|site_inactive' => __('License key is not permitted for this website', 'pdfth-thumbnails'),
			'pdfth_license_key|inactive' => __('License key is not active for this website', 'pdfth-thumbnails'),
			'pdfth_license_key|disabled' => __('License key has been disabled', 'pdfth-thumbnails'),
			'pdfth_license_key|empty' => __('License key was not provided', 'pdfth-thumbnails')
		);
		if (isset($local_error_strings[$fielderror])) {
			return $local_error_strings[$fielderror];
		}

		return __('Unspecified error', 'pdfth-thumbnails');
	}

	public function pdfth_save_network_options() {
		check_admin_referer( $this->get_options_pagename().'-options' );

		if (isset($_POST[$this->get_options_name()]) && is_array($_POST[$this->get_options_name()])) {
			$inoptions = $_POST[$this->get_options_name()];

			$outoptions = $this->pdfth_options_validate($inoptions);

			$error_code = Array();
			$error_setting = Array();
			foreach (get_settings_errors() as $e) {
				if (is_array($e) && isset($e['code']) && isset($e['setting'])) {
					$error_code[] = $e['code'];
					$error_setting[] = $e['setting'];
				}
			}

			if ($this->is_multisite_and_network_activated()) {
				update_site_option( $this->get_options_name(), $outoptions );
			}
			else {
				update_option( $this->get_options_name(), $outoptions );
			}

			// redirect to settings page in network
			wp_redirect(
				add_query_arg(
					array( 'page' => $this->get_options_menuname(),
						'updated' => true,
						'error_setting' => $error_setting,
						'error_code' => $error_code ),
					network_admin_url( 'admin.php' )
				)
			);
			exit;
		}
	}

	protected function pdfth_options_do_network_errors() {
		if (isset($_REQUEST['updated']) && $_REQUEST['updated']) {
			?>
			<div id="setting-error-settings_updated" class="updated settings-error">
				<p>
					<strong><?php esc_html_e('Settings saved', 'pdfth-thumbnails'); ?></strong>
				</p>
			</div>
			<?php
		}

		if (isset($_REQUEST['error_setting']) && is_array($_REQUEST['error_setting'])
		    && isset($_REQUEST['error_code']) && is_array($_REQUEST['error_code'])) {
			$error_code = $_REQUEST['error_code'];
			$error_setting = $_REQUEST['error_setting'];
			if (count($error_code) > 0 && count($error_code) == count($error_setting)) {
				for ($i=0; $i<count($error_code) ; ++$i) {
					?>
					<div id="setting-error-settings_<?php echo $i; ?>" class="error settings-error">
						<p>
							<strong><?php echo htmlentities2($this->get_error_string($error_setting[$i].'|'.$error_code[$i])); ?></strong>
						</p>
					</div>
					<?php
				}
			}
		}
	}

	protected $pdfth_options = null;
	protected function get_option_pdfth() {
		if ($this->pdfth_options != null) {
			return $this->pdfth_options;
		}

		if ($this->is_multisite_and_network_activated()) {
			$option = get_site_option( $this->get_options_name(), Array() );
		}
		else {
			$option = get_option( $this->get_options_name(), Array() );
		}

		$default_options = $this->get_default_options();
		foreach ($default_options as $k => $v) {
			if (!isset($option[$k])) {
				$option[$k] = $v;
			}
		}

		$this->pdfth_options = $option;
		return $this->pdfth_options;
	}

	protected function save_option_pdfth($option) {
		if ($this->is_multisite_and_network_activated()) {
			update_site_option( $this->get_options_name(), $option );
		}
		else {
			update_option( $this->get_options_name(), $option );
		}
		$this->pdfth_options = $option;
	}

	protected function get_default_options() {
		return Array(
			'pdfth_maxwidth' => '2000',
			'pdfth_imagetype' => 'jpg',
			'pdfth_license_key' => '',
			'pdfth_version' => $this->PLUGIN_VERSION,
            'pdfth_allowbeta' => false
		);
	}

	protected function is_multisite_and_network_activated() {
		if (!is_multisite()) {
			return false;
		}

		if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
			require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
		}
		return is_plugin_active_for_network($this->my_plugin_basename());
	}

	// EDD License

	protected function edd_license_activate($license_key) {
		$edd_updater = $this->edd_plugin_updater($license_key);
		return $edd_updater->edd_license_activate();
	}

	protected function get_eddsl_optname() {
		return 'eddsl_pdfth_ls';
	}

	protected function edd_plugin_updater($license_key=null) {
		if (is_null($license_key)) {
			$options = $this->get_option_pdfth();
			$license_key = $options['pdfth_license_key'];
		}

		if( !class_exists( 'EDD_SL_Plugin_Updater13' ) ) {
			// load our custom updater
			include( dirname( __FILE__ ) . '/EDD_SL_Plugin_Updater.php' );
		}

		// setup the updater
		$edd_updater = new EDD_SL_Plugin_Updater13( $this->WPPDF_STORE_URL, $this->my_plugin_basename(),
			array(
				'version' 	=> $this->PLUGIN_VERSION,
				'license' 	=> $license_key,
				'item_name' => $this->WPPDF_ITEM_NAME,
				'item_id' => $this->WPPDF_ITEM_ID,
				'author' 	=> 'Dan Lester',
				'beta'      => $options['pdfth_allowbeta']
			),
			$this->get_eddsl_optname(),
			$this->get_settings_url()."#license",
			false // Don't display admin panel warnings
		);

		return $edd_updater;
	}

}

