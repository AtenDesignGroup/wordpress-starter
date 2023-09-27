<?php

namespace WPDRMS\ASP\Api\Rest0;
use ASP_Post, WP_Post, WP_REST_Request;
use WPDRMS\ASP\Query\SearchQuery;

class RouteSearch implements RouteInterface {
	private $args, $params;

	/**
	 * Handles the Search Route
	 *
	 * @param WP_REST_Request $request
	 * @return WP_Post[]|ASP_Post[]
	 */
	function handle( WP_REST_Request $request ): array {
		$defaults = $this->args = array(
			's' => '',
			'id' => -1,
			'is_wp_json' => true,
			'posts_per_page' => 9999
		);
		$this->params = $request->get_params();
		foreach ($defaults as $k => $v) {
			if ( isset($this->params[$k]) && $this->params[$k] !== null ) {
				$this->args[$k] = $this->params[$k];
			}
		}

		$this->getPostTypeArgs();
		$this->getTaxonomyArgs();
		$this->getPostMetaArgs();
		$this->getExclusionArgs();
		$this->getInclusionArgs();

		$this->args = apply_filters('asp_rest_search_query_args', $this->args, $this->args['id'], $request);
		$asp_query = new SearchQuery($this->args, $this->args['id']);
		return $asp_query->posts;
	}

	private function getTaxonomyArgs() {
		// Post taxonomy filter
		if ( isset($this->params['post_tax_filter']) ) {
			$this->args['post_tax_filter'] = array();
			foreach ($this->params['post_tax_filter'] as $taxonomy => $terms) {
				if ( taxonomy_exists($taxonomy) && is_array($terms) && count($terms) ) {
					$this->args['post_tax_filter'][] = array(
						'taxonomy' => $taxonomy,
						'include' => array_map('intval', $terms),
						'allow_empty' => false
					);
				}
			}
		}
	}

	private function getPostTypeArgs() {
	}

	private function getPostMetaArgs() {
	}

	private function getExclusionArgs() {
	}

	private function getInclusionArgs() {
	}
}