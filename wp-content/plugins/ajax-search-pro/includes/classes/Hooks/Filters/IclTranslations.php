<?php /** @noinspection PhpMissingReturnTypeInspection */

namespace WPDRMS\ASP\Hooks\Filters;

use WPDRMS\ASP\Utils\Polylang\StringTranslations as PolylangStringTranslations;

if (!defined('ABSPATH')) die('-1');

class IclTranslations extends AbstractFilter {
	function handle() {}

	/**
	 * Registered to: apply_filters("asp_query_args", $args, $search_id, $options);
	 * @noinspection PhpUnused
	 */
	public function aspQueryArgsTranslations($args, $search_id) {
		PolylangStringTranslations::init();
		if ( $args['_ajax_search'] && isset($args["_sd"]['advtitlefield'] ) ) {
			$args["_sd"]['advtitlefield'] = asp_icl_t("Advanced Title Field for Post Type ($search_id)", $args["_sd"]['advtitlefield']);
			$args["_sd"]['user_search_advanced_title_field'] = asp_icl_t("Advanced Title Field for Users ($search_id)", $args["_sd"]['user_search_advanced_title_field']);
			$args["_sd"]['advdescriptionfield'] = asp_icl_t("Advanced Content Field for Post Type ($search_id)", $args["_sd"]['advdescriptionfield']);
			$args["_sd"]['user_search_advanced_description_field'] = asp_icl_t("Advanced Content Field for Users ($search_id)", $args["_sd"]['user_search_advanced_description_field']);
		}
		PolylangStringTranslations::save();
		return $args;
	}
}