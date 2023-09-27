<?php
namespace WPDRMS\ASP\Search;

use WPDRMS\ASP\Utils\MB;
use WPDRMS\ASP\Utils\Str;

defined('ABSPATH') or die("You can't access this file directly.");


class SearchPeepsoGroups extends SearchPostTypes {
	/**
	 * @var array of query parts
	 */
	protected $parts = array();

	/**
	 * The search function
	 *
	 * @return array of results
	 */
	protected function doSearch(): array {
		global $wpdb;
		$args = &$this->args;

		$sd = $args["_sd"] ?? array();

		$s = $this->s;
		$_s = $this->_s;

		if ( $args['_limit'] > 0 ) {
			$limit = $args['_limit'];
		} else {
			if ( $args['_ajax_search'] )
				$limit = $args['peepso_groups_limit'];
			else
				$limit = $args['peepso_groups_limit_override'];
		}
		$query_limit = $limit * 3;

		if ( $limit <= 0 )
			return array();

		// Prefixes and suffixes
		$pre_field = '';
		$suf_field = '';
		$pre_like = '';
		$suf_like = '';
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

		$kw_logic = $args['keyword_logic'];
		$category_join = '';

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
			if ( in_array('title', $args['peepso_group_fields']) ) {
				if ( $kw_logic == 'or' || $kw_logic == 'and' || $is_exact ) {
					$parts[] = "( " . $pre_field . $wpdb->posts . ".post_title" . $suf_field . " LIKE $pre_like'$wcl" . $word . "$wcr'$suf_like )";
				} else {
					$parts[] = "
					   ( " . $pre_field . $wpdb->posts . ".post_title" . $suf_field . " LIKE $pre_like'% " . $word . " %'$suf_like
					OR  " . $pre_field . $wpdb->posts . ".post_title" . $suf_field . " LIKE $pre_like'" . $word . " %'$suf_like
					OR  " . $pre_field . $wpdb->posts . ".post_title" . $suf_field . " LIKE $pre_like'% " . $word . "'$suf_like
					OR  " . $pre_field . $wpdb->posts . ".post_title" . $suf_field . " = '" . $word . "')";
				}

				if ( !$relevance_added ) {
					$relevance_parts[] = "(case when
					(" . $pre_field . $wpdb->posts . ".post_title" . $suf_field . " LIKE '$s%')
					 then " . (w_isset_def($sd['etitleweight'], 10) * 2) . " else 0 end)";

					$relevance_parts[] = "(case when
					(" . $pre_field . $wpdb->posts . ".post_title" . $suf_field . " LIKE '%$s%')
					 then " . w_isset_def($sd['etitleweight'], 10) . " else 0 end)";

					// The first word relevance is higher
					if ( isset($_s[0]) ) {
						$relevance_parts[] = "(case when
					  (" . $pre_field . $wpdb->posts . ".post_title" . $suf_field . " LIKE '%" . $_s[0] . "%')
					   then " . w_isset_def($sd['etitleweight'], 10) . " else 0 end)";
					}
				}
			}
			/*---------------------------------------------------------------*/

			/*---------------------- Content query --------------------------*/
			if ( in_array('content', $args['peepso_group_fields']) ) {
				if ($kw_logic == 'or' || $kw_logic == 'and') {
					$parts[] = "( " . $pre_field . $wpdb->posts . ".post_content" . $suf_field . " LIKE $pre_like'$wcl" . $word . "$wcr'$suf_like )";
				} else {
					$parts[] = "
					   (" . $pre_field . $wpdb->posts . ".post_content" . $suf_field . " LIKE $pre_like'% " . $word . " %'$suf_like
					OR  " . $pre_field . $wpdb->posts . ".post_content" . $suf_field . " LIKE $pre_like'" . $word . " %'$suf_like
					OR  " . $pre_field . $wpdb->posts . ".post_content" . $suf_field . " LIKE $pre_like'% " . $word . "'$suf_like
					OR  " . $pre_field . $wpdb->posts . ".post_content" . $suf_field . " = '" . $word . "')";
				}

				if ( !$relevance_added ) {
					if ( isset($_s[0]) ) {
						$relevance_parts[] = "(case when
						(" . $pre_field . $wpdb->posts . ".post_content" . $suf_field . " LIKE '%" . $_s[0] . "%')
						 then " . w_isset_def($sd['contentweight'], 10) . " else 0 end)";
					}
					$relevance_parts[] = "(case when
					(" . $pre_field . $wpdb->posts . ".post_content" . $suf_field . " LIKE '%$s%')
					 then " . w_isset_def($sd['econtentweight'], 10) . " else 0 end)";
				}
			}
			/*---------------------------------------------------------------*/

			/*----------------------- Category query ---------------------------*/
			if ( in_array('categories', $args['peepso_group_fields']) ) {
				if ( $category_join == '' ) {
					$category_join = "
						LEFT JOIN " . $wpdb->prefix . "peepso_group_categories pgc ON pgc.gm_group_id = $wpdb->posts.ID
						LEFT JOIN $wpdb->posts as pcj ON pcj.ID = pgc.gm_cat_id
					";
				}

				if ($kw_logic == 'or' || $kw_logic == 'and') {
					$parts[] = "( " . $pre_field . "pcj.post_title" . $suf_field . " LIKE $pre_like'$wcl" . $word . "$wcr'$suf_like )";
				} else {
					$parts[] = "
					   ( " . $pre_field . "pcj.post_title" . $suf_field . " LIKE $pre_like'% " . $word . " %'$suf_like
					OR  " . $pre_field . "pcj.post_title" . $suf_field . " LIKE $pre_like'" . $word . " %'$suf_like
					OR  " . $pre_field . "pcj.post_title" . $suf_field . " LIKE $pre_like'% " . $word . "'$suf_like
					OR  " . $pre_field . "pcj.post_title" . $suf_field . " = '" . $word . "')";
				}

				if ( !$relevance_added ) {
					$relevance_parts[] = "(case when
					(" . $pre_field . "pcj.post_title" . $suf_field . " LIKE '%$s%')
					 then " . w_isset_def($sd['titleweight'], 10) . " else 0 end)";
				}
			}
			/*---------------------------------------------------------------*/

			$this->parts[] = array( $parts, $relevance_parts );
			$relevance_added = true;
		}

		/*----------------------- Date filtering ------------------------*/
		$date_query = "";
		$date_query_parts = $this->get_date_query_parts();
		if ( count($date_query_parts) > 0 )
			$date_query = " AND (" . implode(" AND ", $date_query_parts) . ") ";
		/*---------------------------------------------------------------*/

		/*------------------------ Group Privacy --------------------------*/
		if ( !empty($args['peepso_group_privacy']) ) {
			$group_ids = is_array($args['peepso_group_privacy']) ? $args['peepso_group_privacy'] : explode(',', $args['peepso_group_privacy']);
			$group_ids = implode(',', $group_ids);
			$peeps_privacy_query = "
				AND (EXISTS (SELECT 1 FROM $wpdb->postmeta pgm 
					 WHERE pgm.post_id = $wpdb->posts.ID AND 
						   pgm.meta_key LIKE 'peepso_group_privacy' AND 
						   pgm.meta_value IN ($group_ids)
					 ))
			";
		} else {
			// No Privacy selected
			return array();
		}
		/*---------------------------------------------------------------*/

		/*------------------------ Exclude ids --------------------------*/
		if ( !empty($args['peepso_group_not_in']) )
			$exclude_posts = "AND ($wpdb->posts.ID NOT IN (".(is_array($args['peepso_group_not_in']) ? implode(",", $args['peepso_group_not_in']) : $args['peepso_group_not_in'])."))";
		else
			$exclude_posts = "";
		/*---------------------------------------------------------------*/


		/*----------------------- Exclude USER id -----------------------*/
		$user_query = "";
		if ( isset($args['post_user_filter']['include']) ) {
			if ( !in_array(-1, $args['post_user_filter']['include']) ) {
				$user_query = "AND $wpdb->posts.post_author IN (" . implode(", ", $args['post_user_filter']['include']) . ")
			";
			}
		}
		if ( isset($args['post_user_filter']['exclude']) ) {
			if ( !in_array(-1, $args['post_user_filter']['exclude']) )
				$user_query = "AND $wpdb->posts.post_author NOT IN (".implode(", ", $args['post_user_filter']['exclude']).") ";
			else
				return array();
		}
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
		$wpdb->posts.ID as id,
		$this->c_blogid as blogid,
		'peepso-group' as post_type,
		$wpdb->posts.post_author as user_id,
		$wpdb->posts.post_title as title,
		$wpdb->posts.post_content as content,
		$wpdb->posts.post_excerpt as excerpt,
		'' as author,
		'peepso_group' as content_type,
		'peepso_groups' as g_content_type,
		$wpdb->posts.post_date as date,
		{relevance_query} as relevance
		FROM 
		  $wpdb->posts
		  $category_join
		WHERE
	  $wpdb->posts.post_type = 'peepso-group'	
	  $peeps_privacy_query
	  $date_query
	  $user_query
	  AND {like_query}
	  $exclude_posts
		GROUP BY
			$wpdb->posts.ID
		ORDER BY $orderby_primary, $orderby_secondary
		LIMIT " . $query_limit;

		$querystr = $this->buildQuery( $this->parts );
		$results = $wpdb->get_results($querystr, OBJECT);

		$this->results_count = count($results);

		/* For non-ajax search, results count needs to be limited to the maximum limit,
		 * as nothing is parsed beyond that */
		if ($args['_ajax_search'] == false && $this->results_count > $limit) {
			$this->results_count = $limit;
		}

		$results = array_slice($results, $args['_call_num'] * $limit, $limit);
		$this->results = &$results;
		$this->return_count = count($this->results);

		return $results;
	}


	/** @noinspection PhpFullyQualifiedNameUsageInspection */
	public function postProcess(): array {
		$r = &$this->results;
		$s          = $this->s;
		$_s         = $this->_s;
		$args = $this->args;

		$sd = $args["_sd"] ?? array();

		if ( class_exists('\\PeepSoGroup') ) {
			foreach ($r as $k => $v) {
				$pg = new \PeepSoGroup($v->id);
				$v->link = $pg->get_url();
				$v->title = $pg->name;

				// Get the image
				if ( isset($sd['image_options']) ) {
					$image_settings = $sd['image_options'];
					if ($image_settings['show_images'] != 0 &&
						'' !== ($im = $pg->get_cover_url())
					) {
						if ($image_settings['image_cropping'] == 0) {
							$v->image = $im;
						} else {
							if (strpos($im, "mshots/v1") === false) {
								$bfi_params = array('width'  => $image_settings['image_width'],
									'height' => $image_settings['image_height'],
									'crop'   => true
								);
								if (w_isset_def($image_settings['image_transparency'], 1) != 1)
									$bfi_params['color'] = wpdreams_rgb2hex($image_settings['image_bg_color']);
								$v->image = asp_bfi_thumb($im, $bfi_params);
							} else {
								$v->image = $im;
							}
						}
					}
				}

				// Remove styles and scripts
				$_content = preg_replace( array(
					'#<script(.*?)>(.*?)</script>#is',
					'#<style(.*?)>(.*?)</style>#is'
				), '', $v->content );

				$_content = wd_strip_tags_ws( $_content, $sd['striptagsexclude'] );

				// Get the words from around the search phrase, or just the description
				if ( $sd['description_context'] == 1 && count( $_s ) > 0 && $s != '') {
					// Try for an exact match
					$_ex_content = $this->contextFind(
						$_content, $s,
						floor($sd['descriptionlength'] / 6),
						$sd['descriptionlength'],
						$sd['description_context_depth'],
						true
					);
					if ( $_ex_content === false ) {
						// No exact match, go with the first keyword
						$_content = $this->contextFind(
							$_content, $_s[0],
							floor($sd['descriptionlength'] / 6),
							$sd['descriptionlength'],
							$sd['description_context_depth']
						);
					} else {
						$_content = $_ex_content;
					}
				} else if ( $_content != '' && (  MB::strlen( $_content ) > $sd['descriptionlength'] ) ) {
					$_content = wd_substr_at_word($_content, $sd['descriptionlength']);
				}

				$v->content = Str::fixSSLURLs( wd_closetags( $_content ) );

				/* Remove the results in polaroid mode */
				if ($args['_ajax_search'] && empty($r->image) && isset($sd['resultstype']) &&
					$sd['resultstype'] == 'polaroid' && $sd['pifnoimage'] == 'removeres') {
					unset($this->results[$k]);
					continue;
				}
				// --------------------------------- DATE -----------------------------------
				if ( isset($sd["showdate"]) && $sd["showdate"] == 1 ) {
					$post_time = strtotime($v->date);

					if ( $sd['custom_date'] == 1) {
						$date_format = w_isset_def($sd['custom_date_format'], "Y-m-d H:i:s");
					} else {
						$date_format = get_option('date_format', "Y-m-d") . " " . get_option('time_format', "H:i:s");
					}

					$v->date = @date_i18n($date_format, $post_time);
				}
				// --------------------------------------------------------------------------
			}
		}
		return $r;
	}

}