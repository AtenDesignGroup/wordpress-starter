<?php
namespace WPDRMS\ASP\Shortcodes;

use WPDRMS\ASP\Patterns\SingletonTrait;
use WPDRMS\ASP\Utils\MobileDetect;

if (!defined('ABSPATH')) die('-1');


class Results extends AbstractShortcode {
	use SingletonTrait;

	public function handle( $atts ) {
		extract( shortcode_atts( array(
			'id' => '0',
			'element' => 'div',
			'display_on_mobile' => 1
		), $atts ) );

		$mdetectObj = new MobileDetect();
		if ( $display_on_mobile == 0 && $mdetectObj->isMobile() ) return;

		if ($id == "") return;

		// Disable back-end display on taxonomy list pages
		if ( is_admin() && isset($_GET['taxonomy']) ) return;

		// Visual composer bug, get the first instance ID
		if ($id == 99999) {
			$_instances = wd_asp()->instances->get();
			if ( empty($_instances) )
				return "";

			$search = reset($_instances);
			$id = $search['id'];
		}

		return "<".$element." id='wpdreams_asp_results_".$id."'></".$element.">";
	}
}