<?php
namespace WPDRMS\ASP\Shortcodes;

use WPDRMS\ASP\Patterns\SingletonTrait;
use WPDRMS\ASP\Utils\MobileDetect;

if (!defined('ABSPATH')) die('-1');


class TwoColumn extends AbstractShortcode {
	use SingletonTrait;

	function handle( $atts ) {
		extract( shortcode_atts( array(
			'id' => '0',
			'element' => 'div',
			'search_width' => 50,
			'results_width' => 50,
			'invert' => 0,
			'display_on_mobile' => 1
		), $atts ) );
		if ($id == "") return;

		$mdetectObj = new MobileDetect();
		if ( $display_on_mobile == 0 && $mdetectObj->isMobile() ) return;

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

		$search_width -= 2;
		$results_width -= 2;
		$s_extra_style = "";
		$r_extra_style = "";

		if ($search_width != 45 || $results_width != 45) {
			$s_extra_style = " style='width:".$search_width."%'";
			$r_extra_style = " style='width:".$results_width."%'";
		}

		if ($invert != 0) {
			return "
			<div class='asp_two_column'>
				<div class='asp_two_column_first'$r_extra_style>".do_shortcode('[wpdreams_ajaxsearchpro_results id='.$id.']')."</div>
				<div class='asp_two_column_last'$s_extra_style>".do_shortcode('[wpdreams_ajaxsearchpro id='.$id.']')."</div>
				<div style='clear: both;'></div>
			</div>
			";
		} else {
			return "
			<div class='asp_two_column'>
				<div class='asp_two_column_first'$s_extra_style>".do_shortcode('[wpdreams_ajaxsearchpro id='.$id.']')."</div>
				<div class='asp_two_column_last'$r_extra_style>".do_shortcode('[wpdreams_ajaxsearchpro_results id='.$id.']')."</div>
				<div style='clear: both;'></div>
			</div>
			";
		}
	}

}