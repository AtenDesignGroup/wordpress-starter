<?php

namespace WPDRMS\ASP\Hooks\Filters;

if (!defined('ABSPATH')) die('-1');

class FormOverride extends AbstractFilter {
	public function handle( $form = "" ) {
		$asp_st_override = get_option("asp_st_override", -1);

		if ( $asp_st_override > -1 && wd_asp()->instances->exists( $asp_st_override ) ) {
			$new_form = do_shortcode("[wpdreams_ajaxsearchpro id=".$asp_st_override."]");
			if (strlen($new_form) > 100)
				return $new_form;
			else
				return $form;
		}

		return $form;
	}
}