<?php
namespace WPDRMS\ASP\Search;

use WPDRMS\ASP\Utils\MB;
use WPDRMS\ASP\Utils\Str;

defined('ABSPATH') or die("You can't access this file directly.");


class SearchPeepsoActivities extends SearchPostTypes {
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
				$limit = $args['peepso_activities_limit'];
			else
				$limit = $args['peepso_activities_limit_override'];
		}
		$query_limit = $limit * 3;

		if ( $limit <= 0 )
			return array();

		if ( count($args['peepso_activity_types']) == 0 )
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

		$post_types = "$wpdb->posts.post_type IN ('".implode("','", $args['peepso_activity_types'])."')";

		/*----------------------- Date filtering ------------------------*/
		$date_query = "";
		$date_query_parts = $this->get_date_query_parts();
		if ( count($date_query_parts) > 0 )
			$date_query = " AND (" . implode(" AND ", $date_query_parts) . ") ";
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
			$parts = array();
			$relevance_parts = array();
			$is_exact = $args["_exact_matches"] == 1 || (count($words) > 1 && $k == 0 && ($kw_logic == 'or' || $kw_logic == 'and'));

			/*---------------------- Content query --------------------------*/
			if ( $kw_logic == 'or' || $kw_logic == 'and' || $is_exact ) {
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
			/*---------------------------------------------------------------*/

			$this->parts[] = array( $parts, $relevance_parts );
			$relevance_added = true;
		}

		/*------------------------ Group Privacy --------------------------*/
		$peeps_privacy_query = '';
		if ( !empty($args['peepso_group_activity_privacy']) ) {
			$group_ids = is_array($args['peepso_group_activity_privacy']) ? $args['peepso_group_activity_privacy'] : explode(',', $args['peepso_group_activity_privacy']);
			$group_ids = implode(',', $group_ids);
			$peeps_privacy_query = "
			AND (EXISTS (SELECT 1 FROM $wpdb->postmeta pgm 
				 WHERE pgm.post_id = apm.meta_value AND 
					   pgm.meta_key LIKE 'peepso_group_privacy' AND 
					   pgm.meta_value IN ($group_ids)
				 ))
			";
		}
		/*---------------------------------------------------------------*/

		/*-------------------- Restrict following -----------------------*/
		$follow_query = '';
		if ( $args['peepso_activity_follow'] == 1 ) {
			$user_id = get_current_user_id();
			$follow_query = " AND (EXISTS(SELECT 1 
				FROM ".$wpdb->prefix."peepso_group_followers gf
				WHERE
					apm.meta_value = gf.gf_group_id AND
					gf_user_id = $user_id AND
					gf_follow = 1
			))";
		}
		/*---------------------------------------------------------------*/

		/*------------------------ Exclude ids --------------------------*/
		if ( !empty($args['peepso_activity_not_in']) )
			$exclude_posts = "AND ($wpdb->posts.ID NOT IN (".(is_array($args['peepso_activity_not_in']) ? implode(",", $args['peepso_activity_not_in']) : $args['peepso_activity_not_in'])."))";
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
	pa.act_id as activity_id,
	pa.act_comment_object_id as parent_activity_id,
	apm.meta_value as group_id,
	$this->c_blogid as blogid,
	$wpdb->posts.post_type as post_type,
	$wpdb->posts.post_author as user_id,
	$wpdb->posts.post_title as title,
	$wpdb->posts.post_content as content,
	$wpdb->posts.post_excerpt as excerpt,
	'' as author,
	'peepso_activity' as content_type,
	'peepso_activities' as g_content_type,
	$wpdb->posts.post_date as date,
	{relevance_query} as relevance
	FROM 
	  $wpdb->posts
	  LEFT JOIN ".$wpdb->prefix."peepso_activities pa ON pa.act_external_id = $wpdb->posts.ID
	  LEFT JOIN 
		$wpdb->postmeta apm ON apm.post_id = IF((pa.act_comment_object_id = 0), $wpdb->posts.ID, pa.act_comment_object_id) AND 
		apm.meta_key LIKE 'peepso_group_id'
	WHERE
  $post_types	
  $peeps_privacy_query
  $date_query
  $user_query
  $follow_query
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
		$s = $this->s;
		$_s = $this->_s;
		$args = $this->args;

		$sd = $args["_sd"] ?? array();

		if ( class_exists('\PeepSo') && class_exists('\PeepSoActivity') ) {
			foreach ($r as $k => $v) {

				if ( $v->post_type == 'peepso-post' ) {
					$v->link = \PeepSo::get_page('activity_status') . $v->title . '/';
				} else {
					$PeepSoActivity = \PeepSoActivity::get_instance();
					$parent_post = get_post($v->parent_activity_id);
					$parent_activity = $PeepSoActivity->get_activity_data($v->parent_activity_id, 8);
					// Link structure -> link#comment. + parent activity ID + parent (activity) post ID + activity ID + (activity) post ID
					$v->link = \PeepSo::get_page('activity_status') . $parent_post->post_title . '/#comment.' . $parent_activity->act_id . '.' . $v->parent_activity_id . '.' . $v->activity_id . '.' . $v->id;
				}

				// Remove any shortcodes..
				/* @noinspection All */
				$v->content = preg_replace("~(?:\[/?)[^\]]+/?\]~su", '', $v->content);

				// Remove any mentions
				/* @noinspection All */
				$v->content = preg_replace('/@peepso_user_[0-9]{1,5}\((.*?)\).*?/i', '$1', $v->content);

				// Remove styles and scripts
				$_content = preg_replace(array(
					'#<script(.*?)>(.*?)</script>#is',
					'#<style(.*?)>(.*?)</style>#is'
				), '', $v->content);

				$_content = wd_strip_tags_ws($_content, $sd['striptagsexclude']);

				// Get the words from around the search phrase, or just the description
				if ( $sd['description_context'] == 1 && count($_s) > 0 && $s != '' ) {
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
				} else if ( $_content != '' && (MB::strlen($_content) > $sd['descriptionlength']) ) {
					$_content = wd_substr_at_word($_content, $sd['descriptionlength']);
				}

				$v->content = Str::fixSSLURLs(wd_closetags($_content));

				$v->title = wd_substr_at_word($v->content, 120);
				if ( MB::strlen($v->content) > MB::strlen($v->title) )
					$v->title .= '..';

				/* Remove the results in polaroid mode */
				if ( $args['_ajax_search'] && empty($r->image) && isset($sd['resultstype']) &&
					$sd['resultstype'] == 'polaroid' && $sd['pifnoimage'] == 'removeres' ) {
					unset($this->results[$k]);
					continue;
				}
				// --------------------------------- DATE -----------------------------------
				if ( isset($sd["showdate"]) && $sd["showdate"] == 1 ) {
					$post_time = strtotime($v->date);

					if ( $sd['custom_date'] == 1 ) {
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