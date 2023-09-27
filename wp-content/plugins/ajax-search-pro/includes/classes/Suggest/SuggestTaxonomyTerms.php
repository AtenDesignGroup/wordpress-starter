<?php
namespace WPDRMS\ASP\Suggest;

use WPDRMS\ASP\Query\QueryArgs;
use WPDRMS\ASP\Utils\MB;
use WPDRMS\ASP\Utils\Suggest;

defined('ABSPATH') or die("You can't access this file directly.");


class SuggestTaxonomyTerms extends AbstractSuggest {
	protected $args;

	function __construct($args = array()) {
		$defaults = array(
			'maxCount' => 10,
			'maxCharsPerWord' => 25,
			'taxonomy' => 'post_tag',
			'match_start' => false,
			'search_id' => 0
		);
		$this->args = wp_parse_args( $args, $defaults );
	}

	function getKeywords( string $q ): array {
		$res = array();

		$exclude = array();
		if ( $this->args['search_id'] > 0 ) {
			$search_args = QueryArgs::get($this->args['search_id'], array());
			if ( !empty($search_args['taxonomy_terms_exclude']) ) {
				if ( is_array($search_args['taxonomy_terms_exclude']) )
					$exclude = $search_args['taxonomy_terms_exclude'];
				else
					$exclude = explode(',', $search_args['taxonomy_terms_exclude']);
				foreach ($exclude as $k=>$v)
					$exclude[$k] = trim($v);
			}
			if ( !empty($search_args['taxonomy_terms_exclude2']) ) {
				$exclude = array_merge($exclude, $search_args['taxonomy_terms_exclude2']);
			}

			if ( isset($search_args['post_tax_filter']) ) {
				foreach ($search_args['post_tax_filter'] as $filter) {
					if ( $filter['taxonomy'] == $this->args['taxonomy'] ) {
						$exclude = array_merge($exclude, $filter['exclude']);
					}
				}
			}

			if ( is_array($exclude) ) {
				$exclude = implode(',', array_unique( $exclude) );
			}
		}

		if ( $this->args['match_start'] ) {
			$tags = get_terms(array($this->args['taxonomy']), array('name__like' => $q, 'number' => $this->args['maxCount'], 'hide_empty' => false, 'exclude' => $exclude));
			foreach ($tags as $tag) {
				if (!is_object($tag)) continue;
				$t = MB::strtolower($tag->name);
				$q = MB::strtolower($q);
				if (
					$t != $q &&
					('' != $str = wd_substr_at_word($t, $this->args['maxCharsPerWord'], ''))
				) {
					if ( MB::strpos($t, $q) === 0 )
						$res[] = $str;
				}
			}
		} else {
			$tags = wpd_get_terms(array(
				'taxonomy' => $this->args['taxonomy'],
				'fields' => 'names',
				'number' => 25000,
				'hide_empty' => false,
				'exclude' => $exclude
			));
			if ( !is_wp_error($tags) && count($tags) > 0 )
				$res = Suggest::getSimilarText($tags, $q, $this->args['maxCount']);
		}

		return apply_filters('asp/suggestions/taxonomy/results', $res, $q, $this->args['taxonomy'], $this->args);
	}
}
