<?php
defined('ABSPATH') or die("You can't access this file directly.");

if (!class_exists("wd_MimeTypeSelect")) {
	/**
	 * Class wd_MimeTypeSelect
	 *
	 * Selection field for mime types
	 *
	 * @package  WPDreams/OptionsFramework/Classes
	 * @category Class
	 * @author Ernest Marcinko <ernest.marcinko@wp-dreams.com>
	 * @link http://codecanyon.net/user/wpdreams/portfolio
	 * @copyright Copyright (c) 2022, Ernest Marcinko
	 */
	class wd_MimeTypeSelect extends wpdreamsType {
		public function getType() {
			parent::getType();
			$this->processData();
			?>
			<div class="wd_MimeTypeSelect">
				<div class="file_mime_types_input hiddend">
					<label class='wd_textarea_expandable'
						   for='wd_textareae_<?php echo self::$_instancenumber; ?>'><?php echo __($this->label, 'ajax-search-pro'); ?>
					<textarea rows='1' data-min-rows='1'
							  class='wd_textarea_expandable'
							  id='wd_textareae_<?php echo self::$_instancenumber; ?>'
							  name='<?php echo $this->name; ?>'><?php echo stripslashes(esc_html($this->data)); ?></textarea>
					</label>
					<span class="mime_input_hide"><?php echo __('>> Simplified view <<', 'ajax-search-pro'); ?></span>
				</div>
				<div class="file_mime_types_list">
					<label>
						<?php echo __($this->label, 'ajax-search-pro'); ?>
						<select multiple attr="multi_attachment_mime_types_<?php echo self::$_instancenumber; ?>"
								id="multi_attachment_mime_types">
							<option value="pdf">PDF</option>
							<option value="text">Text</option>
							<option value="richtext">Rich Text (rtf etc..)</option>
							<option value="mso_word">Office Word</option>
							<option value="mso_excel">Office Excel</option>
							<option value="mso_powerpoint">Office PowerPoint</option>
							<option value="image">Image</option>
							<option value="video">Video</option>
							<option value="audio">Audio</option>
						</select>
					</label>
					<span class="mime_list_hide"><?php echo __('>> Enter manually <<', 'ajax-search-pro'); ?></span>
				</div>
			</div>
			<?php
		}

		function processData() {
			if ( $this->isBase64Encoded($this->data) ) {
				$this->data = base64_decode($this->data);
			}
		}

		function isBase64Encoded( $s ){
			if ((bool) preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $s) === false) {
				return false;
			}
			$decoded = base64_decode($s, true);
			if ($decoded === false) {
				return false;
			}
			if ( function_exists('mb_detect_encoding') ) {
				$encoding = mb_detect_encoding($decoded);
				if ( !in_array($encoding, ['UTF-8', 'ASCII'], true) ) {
					return false;
				}
			}
			return $decoded !== false && base64_encode($decoded) === $s;
		}
	}
}

wp_register_script('wd_mime_select-js', ASP_URL_NP . 'backend/settings/assets/wd_mime_select/js/wd_mime_select.js', array('asp-backend-jquery-select2'), '1', true);
wp_enqueue_script('wd_mime_select-js');
wp_register_style('wd_mime_select-style', ASP_URL_NP . 'backend/settings/assets/wd_mime_select/css/wd_mime_select.css', false, 1);
wp_enqueue_style('wd_mime_select-style');