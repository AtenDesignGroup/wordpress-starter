<?php

use WPDRMS\ASP\Media\RemoteService\License;

if (!class_exists("wd_MSLicenseActivator")) {

	class wd_MSLicenseActivator extends wpdreamsType {
		private $stats = array();
		public function getType() {
			parent::getType();
			$this->processData();
			?>
			<div class="wd_MSLicenseActivator">
				<div class="ms_license_active<?php echo $this->data['active'] == 1 ? '' : ' hiddend'; ?>">
					Your license key: <span class="ms_license_key"><?php echo esc_attr($this->data['license']);?></span>
					<?php if (  $this->data['active'] == 1 && $this->stats['free'] ): ?>
						&nbsp;|&nbsp;<span><?php echo __('FREE License tier', 'ajax-search-pro'); ?></span>
					<?php endif; ?>
					<button name="ms_license_deactivate" class="submit wd_button wd_button_red">
						<i class="fa"></i><?php echo __('Deactivate', 'ajax-search-pro'); ?>
					</button>
				</div>
				<div class="ms_license_inactive<?php echo $this->data['active'] == 1 ? ' hiddend' : ''; ?>">
					<label for="ms_license_key">
						<input type="text" name="ms_license_key" value="<?php echo esc_attr($this->data['license']);?>">
					</label>
					<button name="ms_license_activate" class="submit wd_button">
						<i class="fa"></i><?php echo __('Activate', 'ajax-search-pro'); ?>
					</button>
				</div>
				<div class="ms_license_log">
					<p class="successMsg hiddend"><?php echo __('License successfully activated!', 'ajax-search-pro'); ?></p>
					<p class="errorMsg<?php echo ( $this->data['active'] == 0 && $this->data['license'] != "" ) ? '' :' hiddend' ?>">
						<?php echo __('The license is no longer active, please renew your subscription.', 'ajax-search-pro'); ?>
					</p>
				</div>
				<?php if ( $this->data['active'] == 1 && count($this->stats) > 0 ): ?>
					<div class="ms_license_usage">
						<span><?php echo sprintf(__('Max. allowed file size: <strong>%sMB</strong>', 'ajax-search-pro'), $this->stats['max_filesize']); ?></span>&nbsp;|&nbsp;
						<span><?php echo __('Usage: ', 'ajax-search-pro'); ?></span>
						<span class="ms_license_usage_counter"><?php echo $this->stats['max_files_usage'] . "/" . $this->stats['max_files']; ?></span>
						<span><?php
							echo sprintf(__('(max files) for this period (renews at <strong>%s</strong>)', 'ajax-search-pro'), $this->stats['ends']);
						?></span>
					</div>
				<?php endif; ?>
				<input type="hidden" id="asp_mediaservice_request_nonce" value="<?php echo wp_create_nonce( 'asp_mediaservice_request_nonce' ); ?>">
				<input type="hidden" name="ms_license_active" value="<?php echo $this->data['active'] ? 1 : 0; ?>">
			</div>
			<?php
		}

		public function processData() {
			$this->data = License::getInstance()->getData();
			$this->stats = $this->data['stats'];
		}
	}
}

wp_register_script('wd_ms_license_activator-js', ASP_URL_NP . 'backend/settings/assets/wd_ms_license_activator/js/wd_ms_license_activator.js', array('jquery'), '1', true);
wp_enqueue_script('wd_ms_license_activator-js');
wp_register_style('wd_ms_license_activator-style', ASP_URL_NP . 'backend/settings/assets/wd_ms_license_activator/css/wd_ms_license_activator.css', false, 1);
wp_enqueue_style('wd_ms_license_activator-style');