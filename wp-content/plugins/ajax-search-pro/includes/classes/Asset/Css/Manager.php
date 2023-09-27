<?php
namespace WPDRMS\ASP\Asset\Css;

use WPDRMS\ASP\Asset\ManagerInterface;
use WPDRMS\ASP\Patterns\SingletonTrait;
use WPDRMS\ASP\Utils\Html;

/* Prevent direct access */
defined('ABSPATH') or die("You can't access this file directly.");

class Manager implements ManagerInterface {
	use SingletonTrait;

	private
		$method,	// file, optimized, inline
		$media_query,
		$minify;
	public
		$generator;

	function __construct() {
		$comp_settings = wd_asp()->o['asp_compatibility'];
		$this->method = $comp_settings['css_loading_method']; // optimized, inline, file
		$this->minify = $comp_settings['css_minify'];
		$this->media_query = get_site_option("asp_media_query", "defncss");
		$this->generator = new Generator( $this->minify );

		$this->adjustOptionsForCompatibility();

		if ( $this->method == 'optimized' || $this->method == 'file' ) {
			if ( !$this->generator->verifyFiles() ) {
				$this->generator->generate();
				if ( !$this->generator->verifyFiles() ) {
					// Swap to inline if the files were not generated
					$this->method = 'inline';
				}
			}
		}

		/**
		 * Call order:
		 *  wp_enqueue_scripts 			-> enqueue()
		 *  wp_head 					-> headerStartBuffer()  -> start buffer
		 *  shutdown				 	-> print()				-> end buffer trigger
		 */
	}

	/**
	 * Called at wp_enqueue_scripts
	 */
	function enqueue( $force = false ) {
		if ( $force || $this->method == 'file' ) {
			if ( !$this->generator->verifyFiles() ) {
				$this->generator->generate();
				if ( !$this->generator->verifyFiles() ) {
					$this->method = 'inline';
					return;
				}
			}
			wp_enqueue_style('asp-instances', $this->url('instances'), array(), $this->media_query);
		}
	}

	// asp_ob_end
	function injectToBuffer($buffer, $instances) {
		if ( $this->method != 'file' ) {
			$output = $this->getBasic();
			$output .= $this->getInstances( $instances );
			Html::inject($output, $buffer);
		}
		return $buffer;
	}

	/**
	 * Called at shutdown, after asp_ob_end, checks if the items were printed
	 */
	function printInline( $instances = array() ) {
		if ( $this->method != 'file' ) {
			echo $this->getBasic();
			echo $this->getInstances($instances);
		}
	}

	private function getBasic(): string {
		$output = '';

		if ( $this->method == 'optimized' ) {
			$output = '<link rel="stylesheet" id="asp-basic" href="' . $this->url('basic') . '?mq='.$this->media_query.'" media="all" />';
		} else if ( $this->method == 'inline' ) {
			$css = get_site_option('asp_css', array('basic' => '', 'instances' => array()));
			if ( $css['basic'] != '' ) {
				$output .= "<style id='asp-basic'>" . $css['basic'] . "</style>";
			}
		}
		return $output;
	}

	private function adjustOptionsForCompatibility() {
		if ( defined('SiteGround_Optimizer\VERSION') ) {
			// SiteGround Optimized CSS combine does not pick up the CSS files when injected
			if ( $this->method == 'optimized' ) {
				$this->method = 'inline';
			}
		}
		// Widgets screen
		if (
			wp_is_json_request() ||
			isset($_GET, $_GET['et_fb']) || // Divi frontend editor
			isset($_GET, $_GET['vcv-ajax']) || // Visual Composer Frontend editor
			isset($_GET, $_GET['fl_builder']) || // Beaver Builder Frontend editor
			isset($_GET, $_GET['elementor-preview']) // Elementor Frontend
		) {
			$this->method = 'file';
		}
	}

	private function getInstances( $instances ): string {
		$css = get_site_option('asp_css', array('basic' => '', 'instances' => array()));
		$output = '';
		foreach ($instances as $search_id) {
			if ( isset($css['instances'][$search_id]) && $css['instances'][$search_id] != '' ) {
				$output .= "<style id='asp-instance-$search_id'>" . $css['instances'][$search_id] . "</style>";
			}
		}
		return $output;
	}

	private function url( $handle ): string {
		if ( '' != $file = $this->generator->filename($handle) ) {
			return wd_asp()->cache_url . $file;
		} else {
			return '';
		}
	}
}