<?php
namespace WPDRMS\ASP\Suggest;

use stdClass;
use WPDRMS\ASP\Query\QueryArgs;
use WPDRMS\ASP\Utils\MB;
use WPDRMS\ASP\Utils\Str;
use WPDRMS\ASP\Utils\Suggest;

defined('ABSPATH') or die("You can't access this file directly.");

class SuggestPostTypeTitles extends AbstractSuggest {
	protected $args;
	private $res = array();

	function __construct($args = array()) {
		$defaults = array(
			'maxCount' => 10,
			'maxCharsPerWord' => 25,
			'postTypes' => 'any',
			'excludeTerms' => array(), // 'taxonomy' => array(1,2,3)
			'excludeUsers' => array(),
			'excludeCPTByID' => array(),
			'match_start' => false,
			'search_id' => 0,
			'options' => array(),
			'args' => array()
		);
		$this->args = wp_parse_args( $args, $defaults );
	}

	function getKeywords( string $q): array {
		$this->getResults($q);

		return $this->res;
	}

	function getResults($q) {
		global $wpdb;

		$q = Str::escape($q);

		if (strlen($q) == 0) return;

		$count = $this->args['maxCount'] - count($this->res);
		$words = implode("','", $this->args['postTypes']);
		$post_types = "AND ($wpdb->posts.post_type IN ('" . $words . "') )";

		$allowed_statuses = " AND ( $wpdb->posts.post_status IN ('publish') ) ";
		$exclusions_by_taxonomy = '';
		$exclusions_by_user = '';
		$exclusions_by_id = '';
		$exclusions_by_date = '';
		$wpml_query = "(1)";
		$polylang_query = "";

		if ( $this->args['search_id'] > 0 ) {
			$search_args = QueryArgs::get($this->args['search_id'], $this->args['options'], $this->args['args']);

			// Allowed statuses
			if ( count($search_args['post_status']) > 0) {
				$_allowed_statuses = "'".implode( "','", $search_args['post_status'] )."'";
				$allowed_statuses = "AND (" . $wpdb->posts . ".post_status"  . " IN ($_allowed_statuses) )";
			}

			// Taxonomy term exclusions
			$exclusions_by_taxonomy = $this->buildTermQuery($search_args, "$wpdb->posts.ID", "$wpdb->posts.post_type");

			// User based exclusions and inclusions
			if ( isset($search_args['post_user_filter']['include']) ) {
				if ( !in_array(-1, $search_args['post_user_filter']['include']) ) {
					$exclusions_by_user = "AND $wpdb->posts.post_author IN (" . implode(", ", $search_args['post_user_filter']['include']) . ") ";
				}
			}
			if ( isset($search_args['post_user_filter']['exclude']) ) {
				if ( !in_array(-1, $search_args['post_user_filter']['exclude']) )
					$exclusions_by_user = "AND $wpdb->posts.post_author NOT IN (".implode(", ", $search_args['post_user_filter']['exclude']).") ";
			}

			// Posts by ID exclusions
			if ( !empty($search_args['post_not_in']) )
				$exclusions_by_id = "AND ($wpdb->posts.ID NOT IN (".(is_array($search_args['post_not_in']) ? implode(",", $search_args['post_not_in']) : $search_args['post_not_in'])."))";
			else
				$exclusions_by_id = "";
			if ( !empty($search_args['post_not_in2']) )
				$exclusions_by_id .= "AND ($wpdb->posts.ID NOT IN (".implode(",", $search_args['post_not_in2'])."))";

			// Date exclusions
			$date_query_parts = $this->getDateQueryParts($search_args);
			if ( count($date_query_parts) > 0 )
				$exclusions_by_date = " AND (" . implode(" AND ", $date_query_parts) . ") ";

			/*------------------------- WPML filter -------------------------*/
			if ( $search_args['_wpml_lang'] != "" ) {
				global $sitepress;
				$site_lang_selected = false;
				$wpml_post_types_arr = array();

				foreach ($search_args['post_type'] as $tt) {
					$wpml_post_types_arr[] = "post_" . $tt;
				}
				$wpml_post_types = implode( "','", $wpml_post_types_arr );

				// Let us get the default site language if possible
				if ( is_object($sitepress) && method_exists($sitepress, 'get_default_language') ) {
					$site_lang_selected = $sitepress->get_default_language() == $search_args['_wpml_lang'];
				}

				$_wpml_query_id_field = "$wpdb->posts.ID";
				// Product variations are not translated, so we need to use the parent ID (product) field to compare
				if ( in_array('product_variation', $search_args['post_type']) ) {
					$_wpml_query_id_field = "(IF($wpdb->posts.post_type='product_variation', $wpdb->posts.post_parent, $wpdb->posts.ID))";
				}

				$wpml_query = "
			EXISTS (
				SELECT DISTINCT(wpml.element_id)
				FROM " . $wpdb->prefix . "icl_translations as wpml
				WHERE
					$_wpml_query_id_field = wpml.element_id AND
					wpml.language_code = '" . Str::escape( $search_args['_wpml_lang'] ) . "' AND
					wpml.element_type IN ('$wpml_post_types')
			)";

				/**
				 * For missing translations...
				 * If the site language is used, the translation can be non-existent
				 */
				if ($site_lang_selected) {
					$wpml_query = "
				NOT EXISTS (
					SELECT DISTINCT(wpml.element_id)
					FROM " . $wpdb->prefix . "icl_translations as wpml
					WHERE
						$_wpml_query_id_field = wpml.element_id AND
						wpml.element_type IN ('$wpml_post_types')
				) OR
				" . $wpml_query;
				}
			}
			/*---------------------------------------------------------------*/

			/*----------------------- POLYLANG filter -----------------------*/
			if ( $search_args['_polylang_lang'] != "" ) {
				$languages = get_terms('language', array(
						'hide_empty' => false,
						'fields' => 'ids',
						'orderby' => 'term_group',
						'slug' => $search_args['_polylang_lang'])
				);
				if ( !empty($languages) && !is_wp_error($languages) && isset($languages[0]) ) {
					$polylang_query = " AND (
				$wpdb->posts.ID IN ( SELECT DISTINCT(tr.object_id)
					FROM $wpdb->term_relationships AS tr
					LEFT JOIN $wpdb->term_taxonomy as tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id AND tt.taxonomy = 'language')
					WHERE tt.term_id = $languages[0]
				 ) )";
				}
			}
			/*---------------------------------------------------------------*/

		}

		if ( $count <= 0 ) return;
		// Is this multiple keyword suggestions?
		$title_match = '(1)';
		$soundex_order = '';
		$limit = $count;



		if ( $this->args['match_start'] ) {
			$title_match = "( post_title LIKE '".$q."%'";
			if ( $count > 1 ) {
				$title_match .= " OR SOUNDEX( '". $q ."') LIKE SOUNDEX(post_title)";
				$soundex_order = 'SOUNDEX(post_title),';
			}
			$title_match .= ")";
		} else {
			$limit = 15000;
		}

		$the_query = "
			SELECT
				DISTINCT (post_title),
				ID
			FROM $wpdb->posts
			WHERE
			  $title_match
			  $post_types
			  $exclusions_by_taxonomy
			  $exclusions_by_user
			  $exclusions_by_id 
			  $exclusions_by_date
			  $allowed_statuses
			  AND ( $wpml_query )
			  $polylang_query
			ORDER BY $soundex_order post_title ASC
			LIMIT $limit
		";

		$the_query = apply_filters('asp/suggestions/post_type/query', $the_query, $q);

		$results = $wpdb->get_results($the_query, OBJECT);

		$results = apply_filters('asp/suggestions/post_type/results', $results, $q);

		// The Loop
		if ( is_array($results) ) {
			$similar = array();

			if ( !$this->args['match_start'] ) {
				$titles = array();
				$additional_shortwords = array();
				foreach ( $results as $r ) {
					$titles[$r->ID] = MB::strtolower($r->post_title);
					$title_words = explode(' ', $titles[$r->ID]);
					if ( count($title_words) > 1 ) {
						$title_words = array_filter($title_words, function ($w) {
							return MB::strlen($w) > 2;
						});
					}
					if ( count($title_words) > 1 ) {
						$additional_shortwords = array_merge($additional_shortwords, $title_words);
					}
				}
				$titles = array_merge(array_unique($additional_shortwords), $titles);
				$similar = Suggest::getSimilarText($titles, $q, $count);
			} else {
				foreach ( $results as $r ) {
					$similar[] = $r->post_title;
				}
			}

			foreach ($similar as $res) {
				$t = MB::strtolower($res);
				$q = MB::strtolower($q);
				if (
					$q != $t  &&
					!in_array($t, $this->res) &&
					('' != $str = wd_substr_at_word($t, $this->args['maxCharsPerWord'], ''))
				) {
					if ($this->args['match_start'] && MB::strpos($t, $q) === 0)
						$this->res[] = $str;
					elseif (!$this->args['match_start'])
						$this->res[] = $str;
				}
			}
		}
	}

	protected function buildTermQuery($args, $post_id_field, $post_type_field): string {
		global $wpdb;

		if ( isset($_GET['ignore_op']) ) return "";

		$term_query = "";
		$term_query_parts = array();

		foreach ($args['post_tax_filter'] as $item) {
			$tax_term_query = '';
			$taxonomy = $item['taxonomy'];

			// Is there an argument set to allow empty items for this taxonomy filter?
			$allow_empty_tax_term = $item['allow_empty'] ?? ($taxonomy == 'post_tag' ? $args["_post_tags_empty"] : $args['_post_allow_empty_tax_term']);

			if ( $allow_empty_tax_term == 1 ) {
				$empty_terms_query = "
				NOT EXISTS (
					SELECT *
					FROM $wpdb->term_relationships as xt
					INNER JOIN $wpdb->term_taxonomy as tt ON ( xt.term_taxonomy_id = tt.term_taxonomy_id AND tt.taxonomy = '$taxonomy')
					WHERE
						xt.object_id = $post_id_field
				) OR ";
			} else {
				$empty_terms_query = "";
			}

			// Quick explanation for the AND
			// ... MAIN SELECT: selects all object_ids that are not in the array
			// ... SUBSELECT:   excludes all the object_ids that are part of the array
			// This is used because of multiple object_ids (posts in more than 1 tag)
			if ( !empty($item['exclude']) ) {
				$words = implode( ',', $item['exclude'] );
				$tax_term_query = " (
					$empty_terms_query

					$post_id_field IN (
						SELECT DISTINCT(tr.object_id)
							FROM $wpdb->term_relationships AS tr
							LEFT JOIN $wpdb->term_taxonomy as tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id AND tt.taxonomy = '$taxonomy')
											WHERE
												tt.term_id NOT IN ($words)
												AND tr.object_id NOT IN (
													SELECT DISTINCT(trs.object_id)
													FROM $wpdb->term_relationships AS trs
								LEFT JOIN $wpdb->term_taxonomy as tts ON (trs.term_taxonomy_id = tts.term_taxonomy_id AND tts.taxonomy = '$taxonomy')
													WHERE tts.term_id IN ($words)
												)
									)
								)";
			}
			if ( !empty($item['include']) ) {
				$words = implode( ',', $item['include'] );
				if ( !empty($tax_term_query) )
					$tax_term_query .= " AND ";
				$tax_term_query .= "(
					$empty_terms_query

					$post_id_field IN ( SELECT DISTINCT(tr.object_id)
						FROM $wpdb->term_relationships AS tr
						LEFT JOIN $wpdb->term_taxonomy as tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id AND tt.taxonomy = '$taxonomy')
						WHERE tt.term_id IN ($words)
				  ) )";
			}


			/**
			 * POST TAG SPECIFIC ONLY
			 *
			 * At this point we need to check if the user wants to hide the empty tags but the $tag_query
			 * turned out to be empty. (if not all tags are used and all of them are selected).
			 * If so, then return true on every post type other than 'post' OR check if any tags
			 * are associated with the post.
			 */
			if (
				$taxonomy == 'post_tag' &&
				$args['_post_tags_active'] == 1 &&
				$tax_term_query == "" &&
				$args["_post_tags_empty"] == 0
			) {
				$tax_term_query = "
				(
					($post_type_field != 'post') OR

					EXISTS (
						SELECT *
						FROM $wpdb->term_relationships as xt
						INNER JOIN $wpdb->term_taxonomy as tt ON ( xt.term_taxonomy_id = tt.term_taxonomy_id AND tt.taxonomy = 'post_tag')
						WHERE
							xt.object_id = $post_id_field
					)
				)";
			}
			// ----------------------------------------------------

			if ( !empty($tax_term_query) )
				$term_query_parts[] = "(" . $tax_term_query . ")";
		}

		if ( !empty($term_query_parts) )
			$term_query = "AND (" . implode(" ".strtoupper($args['_taxonomy_group_logic'])." ", $term_query_parts) . ") ";


		return $term_query;
	}

	protected function getDateQueryParts( $args, $table_alias = "", $date_field = "post_date" ): array {
		global $wpdb;

		if ( empty($table_alias) )
			$table_alias = $wpdb->posts;

		$date_query_parts = array();

		foreach( $args['post_date_filter'] as $date_filter ) {
			$date = $date_filter['date'] ?? $date_filter['year'] . "-" . sprintf("%02d", $date_filter['month']) . "-" . sprintf("%02d", $date_filter['day']);

			if ($date_filter['interval'] == "before") {
				$op = $date_filter['operator'] == "exclude" ? ">" : "<=";
				$date_query_parts[] = "$table_alias.$date_field $op '" . $date . " 23:59:59'";
			} else {
				$op = $date_filter['operator'] == "exclude" ? "<" : ">=";
				$date_query_parts[] = "$table_alias.$date_field $op '" . $date . " 00:00:00'";
			}
		}

		return $date_query_parts;
	}

}