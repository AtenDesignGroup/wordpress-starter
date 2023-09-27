<?php
namespace WPDRMS\ASP\Search;

use WPDRMS\ASP\Utils\MB;
use WPDRMS\ASP\Utils\Str;

defined('ABSPATH') or die("You can't access this file directly.");


class SearchTaxonomyTerms extends AbstractSearch {
	private $parts = array();

	/**
	 * The search function
	 *
	 * @return array of results
	 */
	protected function doSearch(): array {
		global $wpdb;
		global $q_config;

		$args = &$this->args;

		$sd = $args["_sd"] ?? array();

		$termmeta_join = '';

		// Prefixes and suffixes
		$pre_field = $this->pre_field;
		$suf_field = $this->suf_field;
		$pre_like  = $this->pre_like;
		$suf_like  = $this->suf_like;
		$wcl = '%'; // Wildcard Left
		$wcr = '%'; // Wildcard right
		if ( $args["_exact_matches"] == 1 ) {
			if ( $args['_exact_match_location'] == 'start' ) {
				$wcl = '';
			} else if ( $args['_exact_match_location'] == 'end' ) {
				$wcr = '';
			} else if ( $args['_exact_match_location'] == 'full' ) {
				$wcr = '';
				$wcl = '';
			}
		}

		$kw_logic             = $args['keyword_logic'];
		$q_config['language'] = $args['_qtranslate_lang'];

		$s = $this->s;      // full keyword
		$_s = $this->_s;    // array of keywords

		if ( $args['_limit'] > 0 ) {
			$limit = $args['_limit'];
		} else {
			if ( $args['_ajax_search'] )
				$limit = $args['taxonomies_limit'];
			else
				$limit = $args['taxonomies_limit_override'];
		}
		$query_limit = $limit * $this->remaining_limit_mod;

		/*----------------------- Gather Types --------------------------*/
		$taxonomies = "( $wpdb->term_taxonomy.taxonomy IN ('".implode("','", $args['taxonomy_include'])."') )";
		/*---------------------------------------------------------------*/

		$words = $args["_exact_matches"] == 1 && $s != '' ? array($s) : $_s;
		/**
		 * Ex.: When the minimum word count is 2, and the user enters 'a' then $_s is empty.
		 *      But $s is not actually empty, thus the wrong query will be executed.
		 */
		if ( count($words) == 0 && $s != '' ) {
			$words = array($s);
			// Allow only beginnings
			if ( $args["_exact_matches"] == 0 )
				$wcl = '';
		}
		if ( $s != '' )
			$words = !in_array($s, $words) ? array_merge(array($s), $words) : $words;

		$relevance_added = false;
		foreach ( $words as $k => $word ) {
			$parts           = array();
			$relevance_parts = array();
			$is_exact = $args["_exact_matches"] == 1 || ( count($words) > 1 && $k == 0 && ($kw_logic == 'or' || $kw_logic == 'and') );

			/*----------------------- Title query ---------------------------*/
			if ($args['taxonomy_terms_search_titles']) {
				if ($kw_logic == 'or' || $kw_logic == 'and' || $is_exact) {
					$parts[] = "( " . $pre_field . $wpdb->terms . ".name" . $suf_field . " LIKE $pre_like'$wcl" . $word . "$wcr'$suf_like )";
				} else {
					$parts[] = "
					   (" . $pre_field . $wpdb->terms . ".name" . $suf_field . " LIKE $pre_like'% " . $word . " %'$suf_like
					OR  " . $pre_field . $wpdb->terms . ".name" . $suf_field . " LIKE $pre_like'" . $word . " %'$suf_like
					OR  " . $pre_field . $wpdb->terms . ".name" . $suf_field . " LIKE $pre_like'% " . $word . "'$suf_like
					OR  " . $pre_field . $wpdb->terms . ".name" . $suf_field . " = '" . $word . "')";
				}

				if (!$relevance_added) {
					if (isset($_s[0])) {
						$relevance_parts[] = "(case when
					(" . $pre_field . $wpdb->terms . ".name" . $suf_field . " LIKE '%" . $_s[0] . "%')
					 then " . w_isset_def($sd['titleweight'], 10) . " else 0 end)";
					}
					$relevance_parts[] = "(case when
				(" . $pre_field . $wpdb->terms . ".name" . $suf_field . " LIKE '$s%')
				 then " . (w_isset_def($sd['etitleweight'], 10) * 2) . " else 0 end)";
					$relevance_parts[] = "(case when
				(" . $pre_field . $wpdb->terms . ".name" . $suf_field . " LIKE '%$s%')
				 then " . w_isset_def($sd['etitleweight'], 10) . " else 0 end)";
				}
			}
			/*---------------------------------------------------------------*/

			/*--------------------- Description query -----------------------*/
			if ($args['taxonomy_terms_search_description']) {
				if ( $kw_logic == 'or' || $kw_logic == 'and' || $is_exact ) {
					$parts[] = "( " . $pre_field . $wpdb->term_taxonomy . ".description" . $suf_field . " LIKE $pre_like'$wcl" . $word . "$wcr'$suf_like )";
				} else {
					$parts[] = "
					   (" . $pre_field . $wpdb->term_taxonomy . ".description" . $suf_field . " LIKE $pre_like'% " . $word . " %'$suf_like
					OR  " . $pre_field . $wpdb->term_taxonomy . ".description" . $suf_field . " LIKE $pre_like'" . $word . " %'$suf_like
					OR  " . $pre_field . $wpdb->term_taxonomy . ".description" . $suf_field . " LIKE $pre_like'% " . $word . "'$suf_like
					OR  " . $pre_field . $wpdb->term_taxonomy . ".description" . $suf_field . " = '" . $word . "')";
				}
				if ( !$relevance_added ) {
					$relevance_parts[] = "(case when
					(" . $pre_field . $wpdb->term_taxonomy . ".description" . $suf_field . " LIKE '%$s%')
					 then " . w_isset_def($sd['contentweight'], 8) . " else 0 end)";
				}
			}
			/*---------------------------------------------------------------*/

			if ( $args['taxonomy_terms_search_term_meta'] == 1 ) {
				if ( $termmeta_join == '' )
					$termmeta_join = " LEFT JOIN $wpdb->termmeta tm ON tm.term_id = $wpdb->terms.term_id";

				if ( $kw_logic == 'or' || $kw_logic == 'and' || $is_exact ) {
					$parts[] = "( " . $pre_field . "tm.meta_value" . $suf_field . " LIKE $pre_like'$wcl" . $word . "$wcr'$suf_like )";
				} else {
					$parts[] = "
					   (" . $pre_field . "tm.meta_value" . $suf_field . " LIKE $pre_like'% " . $word . " %'$suf_like
					OR  " . $pre_field . "tm.meta_value" . $suf_field . " LIKE $pre_like'" . $word . " %'$suf_like
					OR  " . $pre_field . "tm.meta_value" . $suf_field . " LIKE $pre_like'% " . $word . "'$suf_like
					OR  " . $pre_field . "tm.meta_value" . $suf_field . " = '" . $word . "')";
				}
			}

			if ( !empty($parts) )
				$this->parts[] = array( $parts, $relevance_parts );
			$relevance_added = true;
		}

		/*------------------------ Exclude id's -------------------------*/
		$exclude_terms = "";
		if ( !empty($args['taxonomy_terms_exclude']) ) {
			$exclude_terms = " AND ($wpdb->terms.term_id NOT IN (" .(is_array($args['taxonomy_terms_exclude']) ? implode(",", $args['taxonomy_terms_exclude']) : $args['taxonomy_terms_exclude']). "))";
		}
		if ( !empty($args['taxonomy_terms_exclude2']) ) {
			$exclude_terms .= " AND ($wpdb->terms.term_id NOT IN (" .implode(",", $args['taxonomy_terms_exclude2']). "))";
		}
		/*---------------------------------------------------------------*/

		/*------------------- Exclude empty terms -----------------------*/
		$exclude_empty = '';
		if ( $args['taxonomy_terms_exclude_empty'] == 1 ) {
			$exclude_empty = " AND ($wpdb->term_taxonomy.count > 0) ";
		}
		/*---------------------------------------------------------------*/

		/*----------------------- POLYLANG filter -----------------------*/
		$polylang_query = "";
		if ( $args['_polylang_lang'] != "" ) {
			$languages = get_terms('term_language', array(
					'hide_empty' => false,
					'fields' => 'ids',
					'orderby' => 'term_group',
					'slug' => 'pll_' . $args['_polylang_lang'])
			);
			if ( !empty($languages) && !is_wp_error($languages) && isset($languages[0]) ) {
				$polylang_query = " AND (
				$wpdb->term_taxonomy.term_taxonomy_id IN ( SELECT DISTINCT(tr.object_id)
					FROM $wpdb->term_relationships AS tr
					LEFT JOIN $wpdb->term_taxonomy as tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id AND tt.taxonomy = 'term_language')
					WHERE tt.term_id = $languages[0]
				 ) )";
			}
		}
		/*---------------------------------------------------------------*/

		/*------------------------- WPML filter -------------------------*/
		// New sub-select method instead of join
		$wpml_query = "(1)";
		if ( $args['_wpml_lang'] != "" )
			$wpml_query = "
				EXISTS (
					SELECT DISTINCT(wpml.element_id)
					FROM " . $wpdb->prefix . "icl_translations as wpml
					WHERE
						$wpdb->term_taxonomy.term_taxonomy_id = wpml.element_id AND
						wpml.language_code LIKE '" . Str::escape($args['_wpml_lang']) . "' AND
						wpml.element_type LIKE CONCAT('tax_%')
				)";
		/*---------------------------------------------------------------*/

		/*-------------- Additional Query parts by Filters --------------*/
		/**
		 * Use these filters to add additional parts to the select, join or where
		 * parts of the search query.
		 */
		$add_select = apply_filters('asp_term_query_add_select', '', $args, $this->s, $this->_s);
		$add_join = apply_filters('asp_term_query_add_join', '', $args, $this->s, $this->_s);
		$add_where = apply_filters('asp_term_query_add_where', '', $args, $this->s, $this->_s);
		/*---------------------------------------------------------------*/

		if (
			strpos($args['post_primary_order'], 'customfp') !== false ||
			strpos($args['post_primary_order'], 'menu_order') !== false
		) {
			$orderby_primary = 'relevance DESC';
		} else {
			$orderby_primary = str_replace('post_', '', $args['post_primary_order']);
		}

		if (
			strpos($args['post_secondary_order'], 'customfs') !== false ||
			strpos($args['post_secondary_order'], 'menu_order') !== false
		) {
			$orderby_secondary = 'date DESC';
		} else {
			$orderby_secondary = str_replace('post_', '', $args['post_secondary_order']);
		}

		$this->query = "
		SELECT
		  $add_select
		  {args_fields}
		  $wpdb->terms.name as title,
		  $wpdb->terms.term_id as id,
		  $this->c_blogid as blogid,
		  $wpdb->term_taxonomy.description as content,
		  '' as date,
		  '' as author,
		  $wpdb->term_taxonomy.taxonomy as taxonomy,
		  'term' as content_type,
		  'terms' as g_content_type,
		  {relevance_query} as relevance
		FROM
		  $wpdb->terms
		  LEFT JOIN $wpdb->term_taxonomy ON $wpdb->terms.term_id = $wpdb->term_taxonomy.term_id
		  $termmeta_join
		  $add_join
		  {args_join}
		WHERE
			$taxonomies
			AND {like_query}
			$exclude_terms
			$exclude_empty
			AND $wpml_query
			$polylang_query
			$add_where
			{args_where}
		GROUP BY 
			{args_groupby}
		";
		$this->query .= " ORDER BY {args_orderby} $orderby_primary, $orderby_secondary, $wpdb->terms.name ASC
	LIMIT " . $query_limit;

		// Place the argument query fields
		if ( isset($args['term_query']) && is_array($args['term_query']) ) {
			$this->query = str_replace(
				array('{args_fields}', '{args_join}', '{args_where}', '{args_orderby}'),
				array($args['term_query']['fields'], $args['term_query']['join'], $args['term_query']['where'], $args['term_query']['orderby']),
				$this->query
			);
		} else {
			$this->query = str_replace(
				array('{args_fields}', '{args_join}', '{args_where}', '{args_orderby}'),
				'',
				$this->query
			);
		}
		if ( isset($args['term_query'], $args['term_query']['groupby']) && $args['term_query']['groupby'] != '' ) {
			$this->query = str_replace('{args_groupby}', $args['term_query']['groupby'], $this->query);
		} else {
			$this->query = str_replace('{args_groupby}', "$wpdb->terms.term_id", $this->query);
		}

		$querystr = $this->buildQuery( $this->parts );
		$querystr = apply_filters('asp_query_terms', $querystr, $args, $args['_id'], $args['_ajax_search']);
		$term_res = $wpdb->get_results($querystr, OBJECT);
		$this->results_count = count($term_res);

		/* For non-ajax search, results count needs to be limited to the maximum limit,
		 * as nothing is parsed beyond that */
		if ($args['_ajax_search'] == false && $this->results_count > $limit) {
			$this->results_count = $limit;
		}

		$term_res = array_slice($term_res, $args['_call_num'] * $limit, $limit);

		$this->results = $term_res;
		$this->return_count = count($this->results);

		return $term_res;

	}

	/**
	 * Post-processing the results
	 *
	 * @return array
	 */
	protected function postProcess(): array {
		$args = &$this->args;
		$s = $this->s;      // full keyword
		$_s = $this->_s;    // array of keywords

		if ( !isset($args['_sd']) )
			$sd = array();
		else
			$sd = $args['_sd'];

		$term_res = is_array($this->results)?$this->results:array();

		// Get term affected post count if enabled
		if ( w_isset_def($sd["display_number_posts_affected"], 0) == 1 ) {
			foreach ($term_res as $v) {
				$term = get_term_by('id', $v->id, $v->taxonomy);
				$v->title .= " (" . $term->count . ")";
			}
		}


		$image_settings = $sd['image_options'];
		if ( $image_settings['show_images'] != 0 ) {
			foreach($term_res as $result) {
				if ( !empty($result->image) ) continue;

				$image = '';

				/* WooCommerce Term image integration */
				if ( function_exists('get_term_meta') ) {
					$thumbnail_id = get_term_meta( $result->id, 'thumbnail_id', true );
					if ( !is_wp_error($thumbnail_id) && !empty($thumbnail_id) )
						$image = wp_get_attachment_url($thumbnail_id);
				}

				// Categories images plugin
				if ( function_exists('z_taxonomy_image_url') ) {
					$image = z_taxonomy_image_url($result->id);
				}

				// Try parsing term meta
				if ( empty($image) && !empty($sd['tax_image_custom_field']) ) {
					$value = get_term_meta( $result->id, $sd['tax_image_custom_field'], true );
					if ( ( is_array($value) || is_object($value) ) ) {
						if ( isset($value['url']) ) {
							$value = $value['url'];
						} else if ( isset($value['guid']) ) {
							$value = $value['guid'];
						} else if ( isset($value['id']) ) {
							$value = $value['id'];
						} else if ( isset($value['ID']) ) {
							$value = $value['ID'];
						} else if ( isset($value[0]) ) {
							$value = $value[0];
						}
					}
					if ( !empty($value) ) {
						// Is this an image attachment ID
						if ( is_numeric($value) ) {
							$img = wp_get_attachment_image_src( $value );
							if ( isset($img[0]) )
								$image = $img[0];
						} else {
							// Probably the image URL
							$image = $value;
						}
					}
				}

				if ( !empty($image) ) {
					$result->image = $image;
					if ( $image_settings['image_cropping'] == 0 ) {
						$result->image = $image;
					} else {
						if ( strpos( $image, "mshots/v1" ) === false ) {
							$bfi_params = array( 'width'  => $image_settings['image_width'],
								'height' => $image_settings['image_height'],
								'crop'   => true
							);
							if ( w_isset_def( $image_settings['image_transparency'], 1 ) != 1 )
								$bfi_params['color'] = wpdreams_rgb2hex( $image_settings['image_bg_color'] );

							$result->image = asp_bfi_thumb( $image, $bfi_params );
						} else {
							$result->image = $image;
						}
					}
				}

				// Default, if defined and available
				if ( empty($result->image) && !empty($sd['tax_image_default']) ) {
					$result->image = $sd['tax_image_default'];
				}

				if ( !empty($result->image) ) {
					$result->image = Str::fixSSLURLs($result->image);
				}
			}
		}

		/**
		 * Do this here, so the term image might exist.
		 * If you move this loop up, then the WooImage script might not work with isotope
		 */
		foreach ($term_res as $k=>$v) {

			if ( $args["_ajax_search"]) {
				// If no image and defined, remove the result here, to perevent JS confusions
				if (isset($sd['resultstype']) && empty($v->image) && $sd['resultstype'] == "isotopic" && $sd['i_ifnoimage'] == 'removeres') {
					unset($term_res[$k]);
					continue;
				}
				/* Same for polaroid mode */
				if (empty($v->image) && isset($sd['resultstype']) &&
					$sd['resultstype'] == 'polaroid' && $sd['pifnoimage'] == 'removeres'
				) {
					unset($term_res[$k]);
					continue;
				}
			}

			// ------------------------ CONTENT & CONTEXT --------------------------
			// Get the words from around the search phrase, or just the description
			$_content = $v->content;
			$_description_context = $sd['description_context'] ?? 0;
			$_description_context_depth = $sd['description_context_depth'] ?? 10000;
			$_descriptionlength = $sd['tax_res_descriptionlength'] ?? 220;
			$_content = strip_tags( $_content, $sd['striptagsexclude'] );
			if ( $_description_context == 1 && count( $_s ) > 0 ) {
				// Try for an exact match
				$_ex_content = $this->contextFind(
					$_content, $s,
					floor($_descriptionlength / 6),
					$_descriptionlength,
					$_description_context_depth,
					true
				);
				if ( $_ex_content === false ) {
					// No exact match, go with the first keyword
					$_content = $this->contextFind(
						$_content, $_s[0],
						floor($_descriptionlength / 6),
						$_descriptionlength,
						$_description_context_depth
					);
				} else {
					$_content = $_ex_content;
				}
			} else if ( $_content != '' && (  MB::strlen( $_content ) > $_descriptionlength ) ) {
				$_content = wd_substr_at_word($_content, $_descriptionlength);
			}
			$_content   = wd_closetags( $_content );
			$v->content = $_content;
			// ---------------------------------------------------------------------

			$term_url = get_term_link( (int)$v->id, $v->taxonomy);
			if ( $args['_wpml_lang'] != '' )
				$term_url = apply_filters( 'wpml_permalink', $term_url, $args['_wpml_lang'] );

			// In case of unset taxonomy term
			if ( !is_wp_error($term_url) )
				$v->link = $term_url;
			else
				unset($term_res[$k]);
		}

		$this->results = $term_res;

		return $term_res;

	}

}