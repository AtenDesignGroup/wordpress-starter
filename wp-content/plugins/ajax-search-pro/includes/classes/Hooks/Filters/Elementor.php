<?php
namespace WPDRMS\ASP\Hooks\Filters;

use WPDRMS\ASP\Query\SearchQuery;
use WPDRMS\ASP\Utils\Search;

if (!defined('ABSPATH')) die('-1');


class Elementor extends AbstractFilter {
	function handle() {}

	public function posts( $args = array(), $widget = array() ) {
		if ( isset($_GET['asp_ls']) ) {
			if ( isset($_GET['p_asid']) ) {
				$id = intval( $_GET['p_asid'] );
			} else if ( isset($_POST['p_asid']) ) {
				$id = intval( $_POST['p_asid'] );
			} else if ( get_option("asp_st_override", -1) > 0 ) {
				$id = get_option("asp_st_override", -1);
			} else {
				return $args;
			}
			$data = $widget->get_data();
			if (
				wd_asp()->instances->exists( $id ) &&
				isset($data['settings'], $data['settings']['_css_classes']) &&
				strpos($data['settings']['_css_classes'], 'asp_es_'.$id) !== false
			) {
				if ( isset($_GET['asp_force_reset_pagination']) ) {
					// For the correct pagination highlight
					$args['paged']= 1;
				}
				$args['post_type'] = 'asp_override';
				$args['is_elementor'] = true;
			}
		}
		return $args;
	}

	public function posts_archive( $args = array() ) {
		if ( isset($_GET['asp_ls']) ) {
			if ( isset($_GET['p_asid']) ) {
				$id = intval( $_GET['p_asid'] );
			} else if ( isset($_POST['p_asid']) ) {
				$id = intval( $_POST['p_asid'] );
			} else if ( get_option("asp_st_override", -1) > 0 ) {
				$id = get_option("asp_st_override", -1);
			} else {
				return $args;
			}

			if (
				wd_asp()->instances->exists( $id )
			) {
				if ( isset($_GET['asp_force_reset_pagination']) ) {
					// For the correct pagination highlight
					$args['paged'] = 1;
				}
				$args['post_type'] = 'asp_override';
			}
		}
		return $args;
	}

	/** @noinspection PhpUnusedParameterInspection */
	public function products($args = array(), $atts = array() , $type = '') {
		if ( isset($_GET['asp_ls']) ) {
			if ( isset($_GET['p_asid']) ) {
				$id = intval( $_GET['p_asid'] );
			} else if ( isset($_POST['p_asid']) ) {
				$id = intval( $_POST['p_asid'] );
			} else if ( get_option("asp_st_override", -1) > 0 ) {
				$id = get_option("asp_st_override", -1);
			} else {
				return $args;
			}

			if (
				wd_asp()->instances->exists( $id )
			) {
				$ids = array();
				$phrase = $_GET['asp_ls'] ?? $_GET['s'];
				$search_args = array(
					"s" => $phrase,
					"_ajax_search" => false,
					"post_type" => array('product'),
					"search_type" => array('cpt'),
					// Do not recommend going over that, as the post__in argument will generate a
					// too long query to complete, as well as Elementor processes all of these
					// results, yielding a terrible loading time.
					'posts_per_page' =>500
				);
				$search_args = SearchOverride::getAdditionalArgs($search_args);

				if ( isset($_GET['asp_force_reset_pagination']) ) {
					// For the correct pagination highlight
					$search_args['page'] = 1;
				}
				$options = Search::getOptions();
				if ( $options === false || count($options) == 0 )
					$asp_query = new SearchQuery($search_args, $id);
				else
					$asp_query = new SearchQuery($search_args, $id, $options);

				foreach ( $asp_query->posts as $r ) {
					$ids[] = $r->ID;
				}
				if ( count($ids) > 0 ) {
					$args['post__in'] = $ids;
				} else {
					$args['post__in'] = array(-1);
					echo Search::generateHTMLResults(array(), false, $id, $phrase, 'elementor');
				}
				$args['orderby'] = 'post__in';
			}
		}

		return $args;
	}
}
