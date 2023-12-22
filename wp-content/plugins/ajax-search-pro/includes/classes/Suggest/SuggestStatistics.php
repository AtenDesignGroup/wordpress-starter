<?php
namespace WPDRMS\ASP\Suggest;

use WPDRMS\ASP\Utils\MB;

defined('ABSPATH') or die("You can't access this file directly.");


class SuggestStatistics extends AbstractSuggest {
	protected $args;

	function __construct($args = array()) {
		$defaults = array(
			'maxCount' => 10,
			'maxCharsPerWord' => 25,
			'match_start' => false
		);
		$this->args = wp_parse_args( $args, $defaults );
	}

	function getKeywords(string $q): array {
		global $wpdb;
		$keywords = array();
		$res = array();

		$query = $wpdb->prepare(
			"SELECT keyword FROM ".$wpdb->base_prefix."ajaxsearchpro_statistics WHERE keyword LIKE '%s' ORDER BY num desc LIMIT %d"
			, $q . '%', $this->args['maxCount'] + 50);

		$query = apply_filters('asp/suggestions/statistics/query', $query, $q);

		$_keywords = $wpdb->get_results($query, ARRAY_A);

		$_keywords = apply_filters('asp/suggestions/statistics/results', $_keywords, $q);

		foreach($_keywords as $v) {
			$keywords[] = $v['keyword'];
		}

		foreach ($keywords as $keyword) {
			$t = MB::strtolower($keyword);
			$q = MB::strtolower($q);
			if (
				$t != $q &&
				('' != $str = wd_substr_at_word($t, $this->args['maxCharsPerWord'], ''))
			) {
				if ($this->args['match_start'] && MB::strpos($t, $q) === 0)
					$res[] = $str;
				elseif (!$this->args['match_start'])
					$res[] = $str;
			}
			if ( count($res) >= $this->args['maxCount'] )
				break;
		}

		return array_slice($res, 0, $this->args['maxCount']);
	}

}