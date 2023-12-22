<?php
namespace WPDRMS\ASP\Utils;

defined('ABSPATH') or die("You can't access this file directly.");

if ( !class_exists(__NAMESPACE__ . '\FrontendFilters') ) {
	class FrontendFilters {
		public static function getCFValues($field, $type = 'post', $args = array()) {
			if ( is_string($args) ) {
				$args = shortcode_parse_atts(trim($args, '[]{}'));
				if ( is_array($args) && isset($args[0]) ) {
					array_shift($args);
				}
			}
			$args = wp_parse_args($args, array(
				'exclude' => '',
				'order' => 'ASC',
				'post_type' => 'any',
				'post_status' => 'any',
				'is_post_id' => false
			));
			$args = array_map('trim', $args);
			$order = $args['order'] == 'ASC' ? 'ASC' : 'DESC';
			$args['post_type'] = array_map('trim', explode(',', $args['post_type']) );
			$args['post_status'] = array_map('trim', explode(',', $args['post_status']) );
			$args['exclude'] = array_map('trim', explode(',', $args['exclude']) );
			$ret = array();
			$used_acf = false;


			if ( $type == 'post' && function_exists('get_field_object') ) {
				foreach ( asp_acf_get_field_choices($field) as $v => $k ) {
					if (
						(count($args['post_type']) == 0 || in_array('any', $args['post_type'])) &&
						(count($args['post_status']) == 0 || in_array('any', $args['post_status']))
					) {
						$ret[] = array('label' => $k, 'value' => $v);
					} else {
						$pargs = array(
							'fields' => 'ids',
							'post_type' => in_array('any', $args['post_type']) ? 'any' : $args['post_type'],
							'post_status' => in_array('any', $args['post_status']) ? 'any' : $args['post_status'],
							'meta_query' => array(
								'relation' => 'OR',
								array(
									'key' => $field,
									'value' => $v,
									'compare' => '='
								),
								array(
									'key' => $field,
									'value' => ':"' . $v . '";',
									'compare' => 'LIKE'
								)
							)
						);
						$posts = get_posts( $pargs );
						if ( !is_wp_error($posts) && count($posts) > 0 ) {
							$ret[] = array('label' => $k, 'value' => $v);
						}
					}
				}

				$ret = array_filter($ret, function($item) use($args) {
					return !in_array($item['value'], $args['exclude']);
				});
				if ( count($ret) > 0 ) {
					if ( $order == 'ASC' ) {
						asort($ret);
					} else {
						arsort($ret);
					}
					$used_acf = true;
				}
			}
			if ( !$used_acf ) {
				global $wpdb;
				if ( $type == 'post' ) {
					$post_type_query = '';
					$post_status_query = '';
					$post_join = '';
					$exclude_query = '';
					if ( count($args['post_type']) > 0 && !in_array('any', $args['post_type']) ) {
						$post_type_query = "AND p.post_type IN ('".implode("','", Str::escape( $args['post_type'] ))."')";
					}
					if ( count($args['post_status']) > 0 && !in_array('any', $args['post_status']) ) {
						$post_status_query = "AND p.post_status IN ('".implode("','", Str::escape($args['post_status']))."')";
					}
					if ( count($args['exclude']) > 0 ) {
						$exclude_query = "AND pm.meta_value NOT IN ('".implode("','", Str::escape($args['exclude']))."')";
					}
					if ( $post_status_query != '' || $post_type_query != '' ) {
						$post_join = "LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id";
					}
					$r = $wpdb->get_col( $wpdb->prepare( "
						SELECT DISTINCT(pm.meta_value) FROM {$wpdb->postmeta} pm
						$post_join
						WHERE pm.meta_key = %s 
						$exclude_query
						$post_type_query
						$post_status_query
						ORDER BY pm.meta_value $order
					", $field ) );
				} else {
					$r = $wpdb->get_col( $wpdb->prepare( "
						SELECT DISTINCT(um.meta_value) FROM {$wpdb->usermeta} um
						WHERE um.meta_key = %s 
						ORDER BY um.meta_value $order
						LIMIT 5000
					", $field ) );
				}

				if ( !is_wp_error($r) ) {
					foreach ( $r as $v ) {
						$label = $v;
						if ( $args['is_post_id'] && is_numeric($v) ) {
							$label = get_the_title($v);
							if ( is_wp_error($label) || $label == '' ) {
								$label = $v;
							}
						}
						$ret[] = array('label' => $label, 'value' => $v);
					}
				}
			}

			return array_values($ret);
		}

	}
}