<?php
namespace WPDRMS\ASP\Asset;

use WPDRMS\ASP\Misc\OutputBuffer;
use WPDRMS\ASP\Patterns\SingletonTrait;

/* Prevent direct access */
defined('ABSPATH') or die("You can't access this file directly.");

class Manager {
	use SingletonTrait;

	private
		$instances = array();

	/**
	 * hook: wp_enqueue_scripts
	 */
	function enqueue() {
		if ( $this->shouldLoadCss() ) {
			Css\Manager::_o()->enqueue();
		}
		if ( $this->shouldLoadJs() ) {
			Script\Manager::_o()->enqueue();
		}
	}

	/**
	 * hook: wp_print_footer_scripts, priority 6
	 * hook: admin_print_footer_scripts, priority 6
	 */
	function onPluginFooter() {
		// Needed to enqueue the internal (jquery, UI) requirements
		$this->getVisibleSearchIds();
		if ( count($this->instances) ) {
			if ( $this->shouldLoadJs() ) {
				Script\Manager::_o()->earlyFooterEnqueue($this->instances);
			}
		}
	}

	/**
	 * Classic script enqueue for the plugin backend
	 *
	 * hook: admin_print_footer_scripts, priority 7
	 */
	function onPluginBackendFooter() {
		Script\Manager::_o()->enqueue( true );
	}

	// asp_ob_end
	function injectToBuffer($buffer) {
		$this->getVisibleSearchIds($buffer);
		if ( count($this->instances) ) {
			if ( $this->shouldLoadCss() ) {
				$buffer = Css\Manager::_o()->injectToBuffer($buffer, $this->instances);
				$buffer = Font\Manager::_o()->injectToBuffer($buffer);
			}
			if ( $this->shouldLoadJs() ) {
				$buffer = Script\Manager::_o()->injectToBuffer($buffer, $this->instances);
			}
		}
		return $buffer;
	}

	/**
	 * Called at shutdown, after asp_ob_end, checks if the items were printed
	 */
	function printBackup() {
		$this->getVisibleSearchIds();
		if ( count($this->instances) && !OutputBuffer::getInstance()->obFound() ) {
			if ( $this->shouldLoadCss() ) {
				Css\Manager::_o()->printInline($this->instances);
				Font\Manager::_o()->printInline();
			}
			if ( $this->shouldLoadJs() ) {
				Script\Manager::_o()->printInline($this->instances);
			}
		}
	}

	public function shouldLoadCss(): bool {
		return
			wd_asp()->instances->exists() &&
			!apply_filters('asp_load_css_js', false) &&
			!apply_filters('asp_load_css', false);
	}

	public function shouldLoadJs(): bool {
		return
			wd_asp()->instances->exists() &&
			!apply_filters('asp_load_css_js', false) &&
			!apply_filters('asp_load_js', false);
	}

	function getVisibleSearchIds( $html = '' ): array {
		$this->instances = $this->getInstancesFromHtml($html);
		$this->instances = array_merge(
			$this->instances,
			wd_asp()->instances->getInstancesPrinted()
		);

		// Search results page && keyword highlighter
		if (
			isset($_GET['asp_highlight'], $_GET['p_asid']) && intval($_GET['p_asid']) > 0 &&
			wd_asp()->instances->exists($_GET['p_asid'])
		) {
			$instance = wd_asp()->instances->get(intval($_GET['p_asid']));
			if ( $instance['data']['single_highlight'] == 1 ) {
				$this->instances[] = $instance['id'];
			}
		}

		$this->instances = array_unique($this->instances);
		sort($this->instances);
		return $this->instances;
	}

	private function getInstancesFromHtml($out): array {
		if ( $out !== false && $out !== '' ) {
			if ( preg_match_all('/data-asp-id=["\'](\d+)[\'"]\s/', $out, $matches) > 0 ) {
				foreach ( $matches[1] as $search_id ) {
					$search_id = (int) $search_id;
					if ( $search_id !== 0 ) {
						$this->instances[] = $search_id;
					}
				}
			}
		}
		return $this->instances;
	}
}