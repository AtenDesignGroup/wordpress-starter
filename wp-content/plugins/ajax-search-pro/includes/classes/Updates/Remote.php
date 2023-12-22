<?php /** @noinspection PhpWrongStringConcatenationInspection */

namespace WPDRMS\ASP\Updates;

use WPDRMS\ASP\Patterns\SingletonTrait;

class Remote {
	use SingletonTrait;

	private $url = "https://update.wp-dreams.com/version/asp.txt";

	// 3 seconds of timeout, no need to hold up the back-end
	private $timeout = 3;

	private $interval = 1800;

	private $option_name = "asp_updates";

	private $data = false;

	private $version, $version_string, $requires_version, $tested_version, $downloaded_count, $last_updated;

	// -------------------------------------------- Auto Updater Stuff here---------------------------------------------
	public $title = "Ajax Search Pro";

	function __construct() {
		$this->initDefaults();

		if (
			defined('ASP_BLOCK_EXTERNAL') ||
			( defined('WP_HTTP_BLOCK_EXTERNAL') && WP_HTTP_BLOCK_EXTERNAL )
		)
			return false;

		$this->getData();
		$this->processData();

		return true;
	}

	function initDefaults() {
		global $wp_version;
		$this->version = ASP_CURR_VER;
		$this->version_string = ASP_CURR_VER_STRING;
		$this->requires_version = '4.0';
		$this->tested_version = $wp_version;
		$this->downloaded_count = '10000';
		$this->last_updated = date('Y-m-d');
	}

	function getData($force_update = false) {
		// Redundant: Let's make sure, that the version check is not executed during Ajax requests, by any chance
		if (  !( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			$last_checked = get_option($this->option_name . "_lc", time() - $this->interval - 500);

			if ($this->data != "" && $force_update != true) return;

			if (
				((time() - $this->interval) > $last_checked) ||
				$force_update
			) {
				$response = wp_remote_get( $this->url . "?t=" . time(), array( 'timeout' => $this->timeout ) );
				if ( is_wp_error( $response ) ) {
					$this->data = get_option($this->option_name, false);
				} else {
					$this->data = $response['body'];
					update_option($this->option_name, $this->data);
				}
				/**
				 * Any case, success or failure, the last checked timer should be updated, otherwise if the remote server
				 * is offline, it will block each back-end page load every time for 'timeout' seconds
				 */
				update_option($this->option_name . "_lc", time());
			} else {
				$this->data = get_option($this->option_name, false);
			}
		} else {
			$this->data = get_option($this->option_name, false);
		}
	}

	function processData(): bool {
		if ($this->data === false) return false;

		// Version
		preg_match("/VERSION:(.*?)[\r\n]/s", $this->data, $m);
		$this->version = isset($m[1]) ? (trim($m[1]) + 0) : $this->version;

		// Version string
		preg_match("/VERSION_STRING:(.*?)[\r\n]/s", $this->data, $m);
		$this->version_string = isset($m[1]) ? trim($m[1]) : $this->version_string;

		// Requires version string
		preg_match("/REQUIRES:(.*?)[\r\n]/s", $this->data, $m);
		$this->requires_version = isset($m[1]) ? trim($m[1]) : $this->requires_version;

		// Tested version string
		preg_match("/TESTED:(.*?)[\r\n]/s", $this->data, $m);
		$this->tested_version = isset($m[1]) ? trim($m[1]) : $this->tested_version;

		// Downloaded count
		preg_match("/DOWNLOADED:(.*?)[\r\n]/s", $this->data, $m);
		$this->downloaded_count = isset($m[1]) ? trim($m[1]) : $this->downloaded_count;

		// Last updated date
		preg_match("/LAST_UPDATED:(.*?)$/s", $this->data, $m);
		$this->last_updated = isset($m[1]) ? trim($m[1]) : $this->last_updated;

		return true;
	}

	public function refresh() {
		$this->getData(true );
		$this->processData();
	}

	public function getVersion() {
		return $this->version;
	}

	public function getVersionString() {
		return $this->version_string;
	}

	public function needsUpdate( $refresh = false ) {
		if ( $refresh )
			$this->refresh();

		if ($this->version != "")
			if ($this->version > ASP_CURR_VER)
				return true;

		return false;
	}

	public function printUpdateMessage() {
		?>
		<p class='infoMsgBox'>
			<?php echo sprintf( __('Ajax Search Pro version <strong>%s</strong> is available.', 'ajax-search-pro'),
				$this->getVersionString() ); ?>
			<a target="_blank" href="https://documentation.ajaxsearchpro.com/plugin-updates">
				<?php echo __('How to update?', 'ajax-search-pro'); ?>
			</a>
		</p>
		<?php
	}

	public function getRequiresVersion() {
		return $this->requires_version;
	}

	public function getTestedVersion() {
		return $this->tested_version;
	}

	public function getDownloadedCount() {
		return $this->downloaded_count;
	}

	public function getLastUpdated() {
		return $this->last_updated;
	}
}