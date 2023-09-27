<?php

namespace WPDRMS\ASP\Api\Rest0;
use WP_REST_Request;
use WPDRMS\ASP\Query\SearchQuery;


class RouteWooSearch implements RouteInterface {
	function handle( WP_REST_Request $request): array {
		$defaults = $args = array(
			's' => '',
			'id' => -1,
			'is_wp_json' => true,
			'posts_per_page' => 9999
		);
		foreach ($defaults as $k => $v) {
			$param = $request->get_param($k);
			if ( $param !== null ) {
				$args[$k] = $param;
			}
		}

		if ( $args['id'] == -1 ) {
			// Fetch the search ID, which is probably the WooCommerce search
			foreach (wd_asp()->instances->get() as $instance) {
				if ( in_array('product', $instance['data']['customtypes']) ) {
					$args['id'] = $instance['id'];
					break;
				}
			}
		}

		// No search was found with products enabled, set it explicitly
		if ( $args['id'] == -1 ) {
			$args['post_type'] = array('product');
		}

		$args = apply_filters('asp_rest_woo_search_query_args', $args, $args['id'], $request);

		$asp_query = new SearchQuery($args, $args['id']);

		return $asp_query->posts;
	}
}