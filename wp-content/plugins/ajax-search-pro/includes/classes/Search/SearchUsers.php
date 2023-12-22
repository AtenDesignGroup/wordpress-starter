<?php
namespace WPDRMS\ASP\Search;

use WPDRMS\ASP\Utils\MB;
use WPDRMS\ASP\Utils\Str;
use WPDRMS\ASP\Utils\User;

defined('ABSPATH') or die("You can't access this file directly.");

class SearchUsers extends AbstractSearch {
	protected $parts = array();

	/**
	 * The search function
	 *
	 * @return array
	 * @noinspection PhpDuplicateSwitchCaseBodyInspection
	 */
	protected function doSearch(): array {
		global $wpdb;

		$args = &$this->args;

		$sd = $args["_sd"] ?? array();

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

		// Keyword logics
		$kw_logic             = $args['keyword_logic'];

		$s = $this->s; // full keyword
		$_s = $this->_s; // array of keywords

		if ( $args['_limit'] > 0 ) {
			$limit = $args['_limit'];
		} else {
			if ( $args['_ajax_search'] )
				$limit = $args['users_limit'];
			else
				$limit = $args['users_limit_override'];
		}
		$query_limit = $limit * $this->remaining_limit_mod;

		$bp_cf_select = "";

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

			/*---------------------- Login Name query ------------------------*/
			if ( $args['user_login_search'] ) {
				if ( $kw_logic == 'or' || $kw_logic == 'and' || $is_exact ) {
					$parts[] = "( " . $pre_field . $wpdb->users . ".user_login" . $suf_field . " LIKE $pre_like'$wcl" . $word . "$wcr'$suf_like )";
				} else {
					$parts[] = "
					   (" . $pre_field . $wpdb->users . ".user_login" . $suf_field . " LIKE $pre_like'% " . $word . " %'$suf_like
					OR  " . $pre_field . $wpdb->users . ".user_login" . $suf_field . " LIKE $pre_like'" . $word . " %'$suf_like
					OR  " . $pre_field . $wpdb->users . ".user_login" . $suf_field . " LIKE $pre_like'% " . $word . "'$suf_like
					OR  " . $pre_field . $wpdb->users . ".user_login" . $suf_field . " = '" . $word . "')";
				}

				if ( !$relevance_added ) {
					if ( isset($_s[0]) ) {
						$relevance_parts[] = "(case when
						(" . $pre_field . $wpdb->users . ".user_login" . $suf_field . " LIKE '%" . $_s[0] . "%')
						 then " . w_isset_def($sd['titleweight'], 10) . " else 0 end)";
					}
					$relevance_parts[] = "(case when
					(" . $pre_field . $wpdb->users . ".user_login" . $suf_field . " LIKE '%$s%')
					 then ".w_isset_def($sd['titleweight'], 10)." else 0 end)";
				}
			}
			/*---------------------------------------------------------------*/

			/*---------------------- Display Name query ------------------------*/
			if ( $args['user_display_name_search'] ) {
				if ( $kw_logic == 'or' || $kw_logic == 'and' || $is_exact ) {
					$parts[] = "( " . $pre_field . $wpdb->users . ".display_name" . $suf_field . " LIKE $pre_like'$wcl" . $word . "$wcr'$suf_like )";
				} else {
					$parts[] = "
					   (" . $pre_field . $wpdb->users . ".display_name" . $suf_field . " LIKE $pre_like'% " . $word . " %'$suf_like
					OR  " . $pre_field . $wpdb->users . ".display_name" . $suf_field . " LIKE $pre_like'" . $word . " %'$suf_like
					OR  " . $pre_field . $wpdb->users . ".display_name" . $suf_field . " LIKE $pre_like'% " . $word . "'$suf_like
					OR  " . $pre_field . $wpdb->users . ".display_name" . $suf_field . " = '" . $word . "')";
				}

				if ( !$relevance_added ) {
					if ( isset($_s[0]) ) {
						$relevance_parts[] = "(case when
						(" . $pre_field . $wpdb->users . ".display_name" . $suf_field . " LIKE '%" . $_s[0] . "%')
						 then " . w_isset_def($sd['titleweight'], 10) . " else 0 end)";
					}
					$relevance_parts[] = "(case when
					(" . $pre_field . $wpdb->users . ".display_name" . $suf_field . " LIKE '$s%')
					 then " . (w_isset_def($sd['titleweight'], 10) * 2) . " else 0 end)";
					$relevance_parts[] = "(case when
					(" . $pre_field . $wpdb->users . ".display_name" . $suf_field . " LIKE '%$s%')
					 then " . w_isset_def($sd['titleweight'], 10) . " else 0 end)";
				}
			}
			/*---------------------------------------------------------------*/

			/*---------------------- First Name query -----------------------*/
			if ( $args['user_first_name_search'] ) {
				if ( $kw_logic == 'or' || $kw_logic == 'and' || $is_exact ) {
					$parts[] = "( $wpdb->usermeta.meta_key = 'first_name' AND ( " . $pre_field . $wpdb->usermeta . ".meta_value" . $suf_field . " LIKE $pre_like'$wcl" . $word . "$wcr'$suf_like ) )";
				} else {
					$parts[] = "( $wpdb->usermeta.meta_key = 'first_name' AND
					   (" . $pre_field . $wpdb->usermeta . ".meta_value" . $suf_field . " LIKE $pre_like'% " . $word . " %'$suf_like
					OR  " . $pre_field . $wpdb->usermeta . ".meta_value" . $suf_field . " LIKE $pre_like'" . $word . " %'$suf_like
					OR  " . $pre_field . $wpdb->usermeta . ".meta_value" . $suf_field . " LIKE $pre_like'% " . $word . "'$suf_like
					OR  " . $pre_field . $wpdb->usermeta . ".meta_value" . $suf_field . " = '" . $word . "') )";
				}
			}
			/*---------------------------------------------------------------*/

			/*---------------------- Last Name query ------------------------*/
			if ( $args['user_last_name_search'] ) {
				if ( $kw_logic == 'or' || $kw_logic == 'and' || $is_exact ) {
					$parts[] = "( $wpdb->usermeta.meta_key = 'last_name' AND ( " . $pre_field . $wpdb->usermeta . ".meta_value" . $suf_field . " LIKE $pre_like'$wcl" . $word . "$wcr'$suf_like ) )";
				} else {
					$parts[] = "( $wpdb->usermeta.meta_key = 'last_name' AND
					   (" . $pre_field . $wpdb->usermeta . ".meta_value" . $suf_field . " LIKE $pre_like'% " . $word . " %'$suf_like
					OR  " . $pre_field . $wpdb->usermeta . ".meta_value" . $suf_field . " LIKE $pre_like'" . $word . " %'$suf_like
					OR  " . $pre_field . $wpdb->usermeta . ".meta_value" . $suf_field . " LIKE $pre_like'% " . $word . "'$suf_like
					OR  " . $pre_field . $wpdb->usermeta . ".meta_value" . $suf_field . " = '" . $word . "') )";
				}
			}
			/*---------------------------------------------------------------*/

			/*---------------------- Email query ------------------------*/
			if ( $args['user_email_search'] ) {
				if ( $kw_logic == 'or' || $kw_logic == 'and' || $is_exact ) {
					$parts[] = "( " . $pre_field . $wpdb->users . ".user_email" . $suf_field . " LIKE $pre_like'$wcl" . $word . "$wcr'$suf_like )";
				} else {
					$parts[] = "
					   (" . $pre_field . $wpdb->users . ".user_email" . $suf_field . " LIKE $pre_like'% " . $word . " %'$suf_like
					OR  " . $pre_field . $wpdb->users . ".user_email" . $suf_field . " LIKE $pre_like'" . $word . " %'$suf_like
					OR  " . $pre_field . $wpdb->users . ".user_email" . $suf_field . " LIKE $pre_like'% " . $word . "'$suf_like
					OR  " . $pre_field . $wpdb->users . ".user_email" . $suf_field . " = '" . $word . "')";

				}
			}
			/*---------------------------------------------------------------*/

			/*---------------------- Biography query ------------------------*/
			if ($args['user_bio_search']) {
				if ( $kw_logic == 'or' || $kw_logic == 'and' || $is_exact ) {
					$parts[] = "( $wpdb->usermeta.meta_key = 'description' AND ( " . $pre_field . $wpdb->usermeta . ".meta_value" . $suf_field . " LIKE $pre_like'$wcl" . $word . "$wcr'$suf_like ) )";
				} else {
					$parts[] = "( $wpdb->usermeta.meta_key = 'description' AND 
					   (" . $pre_field . $wpdb->usermeta . ".meta_value" . $suf_field . " LIKE $pre_like'% " . $word . " %'$suf_like
					OR  " . $pre_field . $wpdb->usermeta . ".meta_value" . $suf_field . " LIKE $pre_like'" . $word . " %'$suf_like
					OR  " . $pre_field . $wpdb->usermeta . ".meta_value" . $suf_field . " LIKE $pre_like'% " . $word . "'$suf_like
					OR  " . $pre_field . $wpdb->usermeta . ".meta_value" . $suf_field . " = '" . $word . "') )";
				}
			}
			/*---------------------------------------------------------------*/

			/*-------------------- Other selected meta ----------------------*/
			$args['user_search_meta_fields'] = !is_array($args['user_search_meta_fields']) ? array($args['user_search_meta_fields']) : $args['user_search_meta_fields'];
			foreach ($args['user_search_meta_fields'] as $meta_field) {
				$meta_field = trim($meta_field);
				if ( empty($meta_field) )
					continue;
				if ( $args['user_search_meta_fields_separate_subquery'] ) {
					if ( $kw_logic == 'or' || $kw_logic == 'and' || $is_exact ) {
						$parts[] = "EXISTS( SELECT 1 FROM $wpdb->usermeta sums WHERE 
							sums.user_id = $wpdb->users.ID AND sums.meta_key = '" . $meta_field . "' AND 
							( " . $pre_field . "sums.meta_value" . $suf_field . " LIKE $pre_like'$wcl" . $word . "$wcr'$suf_like ) )";
					} else {
						$parts[] = "EXISTS( SELECT 1 FROM $wpdb->usermeta sums WHERE 
							sums.user_id = $wpdb->users.ID AND
							sums.meta_key = '" . $meta_field . "' AND
							   (" . $pre_field . "sums.meta_value" . $suf_field . " LIKE $pre_like'% " . $word . " %'$suf_like
							OR  " . $pre_field . "sums.meta_value" . $suf_field . " LIKE $pre_like'" . $word . " %'$suf_like
							OR  " . $pre_field . "sums.meta_value" . $suf_field . " LIKE $pre_like'% " . $word . "'$suf_like
							OR  " . $pre_field . "sums.meta_value" . $suf_field . " = '" . $word . "') )";
					}
				} else {
					if ( $kw_logic == 'or' || $kw_logic == 'and' || $is_exact ) {
						$parts[] = "( $wpdb->usermeta.meta_key = '" . $meta_field . "' AND ( " . $pre_field . $wpdb->usermeta . ".meta_value" . $suf_field . " LIKE $pre_like'$wcl" . $word . "$wcr'$suf_like ) )";
					} else {
						$parts[] = "( $wpdb->usermeta.meta_key = '" . $meta_field . "' AND
						   (" . $pre_field . $wpdb->usermeta . ".meta_value" . $suf_field . " LIKE $pre_like'% " . $word . " %'$suf_like
						OR  " . $pre_field . $wpdb->usermeta . ".meta_value" . $suf_field . " LIKE $pre_like'" . $word . " %'$suf_like
						OR  " . $pre_field . $wpdb->usermeta . ".meta_value" . $suf_field . " LIKE $pre_like'% " . $word . "'$suf_like
						OR  " . $pre_field . $wpdb->usermeta . ".meta_value" . $suf_field . " = '" . $word . "') )";
					}
				}

			}
			/*---------------------------------------------------------------*/

			$this->parts[] = array( $parts, $relevance_parts );
			$relevance_added = true;
		}

		/*------------------ BP Xprofile field meta ---------------------*/
		$args['user_search_bp_fields'] = !is_array($args['user_search_bp_fields']) ? array($args['user_search_bp_fields']) : $args['user_search_bp_fields'];
		$bp_meta_table = $wpdb->base_prefix . "bp_xprofile_data";
		$bp_cf_parts = array();

		if (count($args['user_search_bp_fields']) > 0 && $wpdb->get_var("SHOW TABLES LIKE '$bp_meta_table'") == $bp_meta_table) {
			foreach ($args['user_search_bp_fields'] as $field_id) {
				if ($kw_logic == 'or' || $kw_logic == 'and') {
					$op = strtoupper($kw_logic);
					if (count($_s) > 0)
						$_like = implode("%'$suf_like " . $op . " " . $pre_field . $bp_meta_table . ".value" . $suf_field . " LIKE $pre_like'%", $words);
					else
						$_like = $s;
					$bp_cf_parts[] = "( $bp_meta_table.field_id = '" . $field_id . "' AND ( " . $pre_field . $bp_meta_table . ".value" . $suf_field . " LIKE $pre_like'$wcl" . $_like . "$wcr'$suf_like ) )";
				} else {
					$_like = array();
					$op = $kw_logic == 'andex' ? 'AND' : 'OR';
					foreach ($words as $word) {
						$_like[] = "
					   (" . $pre_field . $bp_meta_table . ".value" . $suf_field . " LIKE $pre_like'% " . $word . " %'$suf_like
					OR  " . $pre_field . $bp_meta_table . ".value" . $suf_field . " LIKE $pre_like'" . $word . " %'$suf_like
					OR  " . $pre_field . $bp_meta_table . ".value" . $suf_field . " LIKE $pre_like'% " . $word . "'$suf_like
					OR  " . $pre_field . $bp_meta_table . ".value" . $suf_field . " = '" . $word . "')";
					}
					$bp_cf_parts[] = "( $bp_meta_table.field_id = '" . $field_id . "' AND (" . implode(' ' . $op . ' ', $_like) . ") )";
				}
			}

			if (count($bp_cf_parts) > 0) {
				$bp_cf_query = implode(" OR ", $bp_cf_parts);
				$bp_cf_select = "
			OR ( (
				SELECT COUNT(*) FROM $bp_meta_table WHERE
					$bp_meta_table.user_id = $wpdb->users.ID
				AND
					($bp_cf_query)
			) > 0 )";
			}
		}
		/*---------------------------------------------------------------*/

		/*------------------------ Exclude Roles ------------------------*/
		$roles_query = '';
		$args['user_search_exclude_roles'] = !is_array($args['user_search_exclude_roles']) ? array($args['user_search_exclude_roles']) : $args['user_search_exclude_roles'];
		if (count($args['user_search_exclude_roles']) > 0) {
			$role_parts = array();
			foreach ($args['user_search_exclude_roles'] as $role) {
				$role_parts[] = $wpdb->usermeta . '.meta_value LIKE \'%"' . $role . '"%\'';
			}
			// Capabilities meta field is prefixed with the DB prefix
			$roles_query = "AND $wpdb->users.ID NOT IN (
				SELECT DISTINCT($wpdb->usermeta.user_id)
				FROM $wpdb->usermeta
				WHERE $wpdb->usermeta.meta_key='".$wpdb->base_prefix."capabilities' AND (" . implode(' OR ', $role_parts) . ")
			)";
		}
		/*---------------------------------------------------------------*/

		/*------------- Custom Fields with Custom selectors -------------*/
		$cf_select = $this->buildCffQuery( $wpdb->users.".ID" );
		/*---------------------------------------------------------------*/

		/*------------------------ Exclude Users ------------------------*/
		$exclude_query = '';
		if ( count($args['user_search_exclude_ids']) > 0 ) {
			$exclude_query .= " AND $wpdb->users.ID NOT IN(" . implode(',',$args['user_search_exclude_ids']) . ") ";
		}
		$include_query = '';
		if ( isset($args['user_search_exclude']['include']) ) {
			$include_query .= " AND $wpdb->users.ID IN(" . implode(',',$args['user_search_exclude']['include']) . ") ";
		}
		/*---------------------------------------------------------------*/

		/*----------------------- Title Field ---------------------------*/
		switch ( w_isset_def($sd['user_search_title_field'], "display_name") ) {
			case 'login':
				$uname_select = "$wpdb->users.user_login";
				break;
			case 'display_name':
				$uname_select = "$wpdb->users.display_name";
				break;
			default:
				$uname_select = "$wpdb->users.display_name";
				break;
		}
		/*---------------------------------------------------------------*/

		/*-------------- Additional Query parts by Filters --------------*/
		/**
		 * Use these filters to add additional parts to the select, join or where
		 * parts of the search query.
		 */
		$add_select = apply_filters('asp_user_query_add_select', '', $args, $s, $_s);
		$add_join = apply_filters('asp_user_query_add_join', '', $args, $s, $_s);
		$add_where = apply_filters('asp_user_query_add_where', '', $args, $s, $_s);
		/*---------------------------------------------------------------*/

		/*---------------- Primary custom field ordering ----------------*/
		$custom_field_selectp = "1 ";
		if (
			strpos($args['user_primary_order'], 'customfp') !== false &&
			$args['_user_primary_order_metakey'] !== false
		) {
			$custom_field_selectp = "(SELECT IF(meta_value IS NULL, 0, meta_value)
			FROM $wpdb->usermeta
			WHERE
				$wpdb->usermeta.meta_key='".esc_sql($args['_user_primary_order_metakey'])."' AND
				$wpdb->usermeta.user_id=$wpdb->users.ID
			LIMIT 1
			) ";
		}
		/*---------------------------------------------------------------*/

		/*--------------- Secondary custom field ordering ---------------*/
		$custom_field_selects = "1 ";
		if (
			strpos($args['user_secondary_order'], 'customfs') !== false &&
			$args['_user_secondary_order_metakey'] !== false
		) {
			$custom_field_selects = "(SELECT IF(meta_value IS NULL, 0, meta_value)
			FROM $wpdb->usermeta
			WHERE
				$wpdb->usermeta.meta_key='".esc_sql($args['_user_secondary_order_metakey'])."' AND
				$wpdb->usermeta.user_id=$wpdb->users.ID
			LIMIT 1
			) ";
		}
		/*---------------------------------------------------------------*/

		$orderby_primary = $args['user_primary_order'];
		$orderby_secondary = $args['user_secondary_order'];
		if (
			$args['user_primary_order_metatype'] !== false &&
			$args['user_primary_order_metatype'] == 'numeric'
		)
			$orderby_primary = str_replace('customfp', 'CAST(customfp as SIGNED)', $orderby_primary);

		if (
			$args['user_secondary_order_metatype'] !== false &&
			$args['user_secondary_order_metatype'] == 'numeric'
		)
			$orderby_secondary = str_replace('customfs', 'CAST(customfs as SIGNED)', $orderby_secondary);

		$this->query = "
		SELECT
			$add_select
			{args_fields}
			$wpdb->users.ID as id,
			$this->c_blogid as blogid,
			$uname_select as title,
			$wpdb->users.user_registered as date,
			'' as author,
			'' as content,
			'user' as content_type,
			'users' as g_content_type,
			{relevance_query} as relevance,
			$wpdb->users.user_login as user_login,
			$wpdb->users.user_nicename as user_nicename,
			$wpdb->users.display_name as user_display_name,
			$custom_field_selectp as customfp,
			$custom_field_selects as customfs
		FROM
			$wpdb->users
			LEFT JOIN $wpdb->usermeta ON $wpdb->usermeta.user_id = $wpdb->users.ID
			$add_join
			{args_join}
		WHERE
			(
			  {like_query}
			  $bp_cf_select
			)
			$add_where
			$roles_query
			AND $cf_select
			$exclude_query
			$include_query
			{args_where}
		GROUP BY 
			{args_groupby}
		ORDER BY {args_orderby} $orderby_primary, $orderby_secondary, id DESC
		LIMIT ".$query_limit;

		// Place the argument query fields
		if ( isset($args['user_query']) && is_array($args['user_query']) ) {
			$this->query = str_replace(
				array('{args_fields}', '{args_join}', '{args_where}', '{args_orderby}'),
				array($args['user_query']['fields'], $args['user_query']['join'], $args['user_query']['where'], $args['user_query']['orderby']),
				$this->query
			);
		} else {
			$this->query = str_replace(
				array('{args_fields}', '{args_join}', '{args_where}', '{args_orderby}'),
				'',
				$this->query
			);
		}
		if ( isset($args['user_query'], $args['user_query']['groupby']) && $args['user_query']['groupby'] != '' ) {
			$this->query = str_replace('{args_groupby}', $args['user_query']['groupby'], $this->query);
		} else {
			$this->query = str_replace('{args_groupby}', "id", $this->query);
		}

		$querystr = $this->buildQuery( $this->parts );
		$querystr = apply_filters('asp_query_users', $querystr, $args, $args['_id'], $args['_ajax_search']);
		$userresults = $wpdb->get_results($querystr, OBJECT);
		$this->results_count = count($userresults);

		/* For non-ajax search, results count needs to be limited to the maximum limit,
		 * as nothing is parsed beyond that */
		if ($args['_ajax_search'] == false && $this->results_count > $limit) {
			$this->results_count = $limit;
		}

		$userresults = array_slice($userresults, $args['_call_num'] * $limit, $limit);

		$this->results = $userresults;

		return $this->results;
	}

	/** @noinspection PhpDuplicateSwitchCaseBodyInspection */
	protected function buildCffQuery( $post_id_field ): string {
		global $wpdb;
		$args = $this->args;
		$parts = array();

		$allow_cf_null = $args['_post_meta_allow_null'];

		foreach ( $args['user_meta_filter'] as $data ) {

			$operator = $data['operator'];
			$posted = $data['value'];
			$field = $data['key'];

			// Is this a special case of date operator?
			if (strpos($operator, "datetime") === 0) {
				switch ($operator) {
					case 'datetime =':
						$current_part = "($wpdb->usermeta.meta_value BETWEEN '$posted 00:00:00' AND '$posted 23:59:59')";
						break;
					case 'datetime <>':
						$current_part = "($wpdb->usermeta.meta_value NOT BETWEEN '$posted 00:00:00' AND '$posted 23:59:59')";
						break;
					case 'datetime <':
						$current_part = "($wpdb->usermeta.meta_value < '$posted 00:00:00')";
						break;
					case 'datetime <=':
						$current_part = "($wpdb->usermeta.meta_value <= '$posted 23:59:59')";
						break;
					case 'datetime >':
						$current_part = "($wpdb->usermeta.meta_value > '$posted 23:59:59')";
						break;
					case 'datetime >=':
						$current_part = "($wpdb->usermeta.meta_value >= '$posted 00:00:00')";
						break;
					default:
						$current_part = "($wpdb->usermeta.meta_value < '$posted 00:00:00')";
						break;
				}
				// Is this a special case of timestamp?
			} else if (strpos($operator, "timestamp") === 0) {
				switch ($operator) {
					case 'timestamp =':
						$current_part = "($wpdb->usermeta.meta_value BETWEEN $posted AND ".($posted + 86399).")";
						break;
					case 'timestamp <>':
						$current_part = "($wpdb->usermeta.meta_value NOT BETWEEN $posted AND ".($posted + 86399).")";
						break;
					case 'timestamp <':
						$current_part = "($wpdb->usermeta.meta_value < $posted)";
						break;
					case 'timestamp <=':
						$current_part = "($wpdb->usermeta.meta_value <= ".($posted + 86399).")";
						break;
					case 'timestamp >':
						$current_part = "($wpdb->usermeta.meta_value > ".($posted + 86399).")";
						break;
					case 'timestamp >=':
						$current_part = "($wpdb->usermeta.meta_value >= $posted)";
						break;
					default:
						$current_part = "($wpdb->usermeta.meta_value < $posted)";
						break;
				}
				// Check BETWEEN first -> range slider
			} else if ( $operator === "BETWEEN" ) {
				$current_part = "($wpdb->usermeta.meta_value BETWEEN " . $posted[0] . " AND " . $posted[1] . " )";
				// If not BETWEEN but value is array, then drop-down or checkboxes
			} else if ( is_array($posted) ) {
				// Is there a logic sent?
				$logic  = $data['logic'] ?? "OR";
				$values = '';
				if ($operator === "IN" ) {
					$val = implode("','", $posted);
					if ( !empty($val) ) {
						if ($values != '') {
							$values .= " $logic $wpdb->usermeta.meta_value $operator ('" . $val . "')";
						} else {
							$values .= "$wpdb->usermeta.meta_value $operator ('" . $val . "')";
						}
					}
				} else {
					foreach ($posted as $v) {
						if ($operator === "ELIKE" || $operator === "NOT ELIKE") {
							$_op = $operator === 'ELIKE' ? 'LIKE' : 'NOT LIKE';
							if ($values != '') {
								$values .= " $logic $wpdb->usermeta.meta_value $_op '" . $v . "'";
							} else {
								$values .= "$wpdb->usermeta.meta_value $_op '" . $v . "'";
							}
						} else if ($operator === "NOT LIKE" || $operator === "LIKE") {
							if ($values != '') {
								$values .= " $logic $wpdb->usermeta.meta_value $operator '%" . $v . "%'";
							} else {
								$values .= "$wpdb->usermeta.meta_value $operator '%" . $v . "%'";
							}
						} else {
							if ($values != '') {
								$values .= " $logic $wpdb->usermeta.meta_value $operator " . $v;
							} else {
								$values .= "$wpdb->usermeta.meta_value $operator " . $v;
							}
						}
					}
				}

				$values  = $values == '' ? '0' : $values;
				$current_part = "($values)";
				// String operations
			} else if ($operator === "NOT LIKE" || $operator === "LIKE") {
				$current_part = "($wpdb->usermeta.meta_value $operator '%" . $posted . "%')";
			} else if ($operator === "ELIKE" || $operator === "NOT ELIKE") {
				$_op = $operator === 'ELIKE' ? 'LIKE' : 'NOT LIKE';
				$current_part = "($wpdb->usermeta.meta_value $_op '$posted')";
				// Numeric operations or problematic stuff left
			} else {
				$current_part = "($wpdb->usermeta.meta_value $operator $posted  )";
			}

			// Finally, add the current part to the parts array
			if ( $current_part != "") {
				$allowance = $data['allow_missing'] ?? $allow_cf_null;

				$parts[] = array($field, $current_part, $allowance);
			}
		}

		// The correct count is the unique fields count
		//$meta_count = count( $unique_fields );

		$cf_select = "(1)";
		$cf_select_arr = array();

		/**
		 * NOTE 1:
		 * With the previous NOT EXISTS(...) subquery solution the search would hang in some cases
		 * when checking if empty values are allowed. No idea why though...
		 * Eventually using separate sub-queries for each field is the best.
		 *
		 * NOTE 2:
		 * COUNT(post_id) is a MUST in the nested IF() statement !! Otherwise, the query will return empty rows, no idea why either
		 */

		foreach ( $parts as $part ) {
			$field = $part[0]; 			// Field name
			$def = $part[2] ? "(
				SELECT IF((meta_key IS NULL OR meta_value = ''), -1, COUNT(umeta_id))
				FROM $wpdb->usermeta
				WHERE $wpdb->usermeta.user_id = $post_id_field AND $wpdb->usermeta.meta_key='$field'
				LIMIT 1
			  ) = -1
			 OR" : '';                  // Allowance
			$qry = $part[1];            // Query condition
			$cf_select_arr[] = "
			(
			  $def
			  (
				SELECT COUNT(umeta_id) as mtc
				FROM $wpdb->usermeta
				WHERE $wpdb->usermeta.user_id = $post_id_field AND $wpdb->usermeta.meta_key='$field' AND $qry
				GROUP BY umeta_id
				ORDER BY mtc
				LIMIT 1
			  ) >= 1
			)";
		}
		if ( count($cf_select_arr) ) {
			// Connect them based on the meta logic
			$cf_select = "( ". implode( $args['_post_meta_logic'], $cf_select_arr ) . " )";
		}

		return $cf_select;
	}

	protected function postProcess(): array {
		$userresults = is_array($this->results) ? $this->results : array();

		$s          = $this->s;
		$_s         = $this->_s;
		$args = &$this->args;

		if ( !isset($args['_sd']) )
			return $this->results;
		$sd = $args['_sd'];
		$com_options = wd_asp()->o['asp_compatibility'];

		foreach ($userresults as $k => &$r) {

			if ( $args['_ajax_search'] ) {
				// If no image and defined, remove the result here, to perevent JS confusions
				if (empty($r->image) && $sd['resultstype'] == "isotopic" && $sd['i_ifnoimage'] == 'removeres') {
					unset($userresults[$k]);
					continue;
				}
				/* Same for polaroid mode */
				if (empty($r->image) && isset($sd['resultstype']) &&
					$sd['resultstype'] == 'polaroid' && $sd['pifnoimage'] == 'removeres'
				) {
					unset($userresults[$k]);
					continue;
				}
			}

			/*--------------------------- Link ------------------------------*/
			switch ( $sd['user_search_url_source'] ) {
				case "bp_profile":
					if (function_exists('bp_core_get_user_domain'))
						$r->link = bp_core_get_user_domain($r->id);
					else
						$r->link = get_author_posts_url($r->id);
					break;
				case "custom":
					$r->link = function_exists("pll_home_url") ? @pll_home_url() : home_url("/");
					$r->link .= str_replace(
						array("{USER_ID}", "{USER_LOGIN}", "{USER_NICENAME}", "{USER_DISPLAYNAME}"),
						array($r->id, $r->user_login, $r->user_nicename, $r->user_display_name),
						$sd['user_search_custom_url']
					);
					if ( strpos($r->link, '{USER_NICKNAME}') !== false ) {
						$r->link = str_replace('{USER_NICKNAME}', get_user_meta( $r->id, 'nickname', true ), $r->link);
					}
					break;
				default:
					$r->link = get_author_posts_url($r->id);
			}
			/*---------------------------------------------------------------*/

			/*-------------------------- Image ------------------------------*/
			if ( $sd['user_search_display_images'] ) {
				if ( $sd['user_search_image_source'] == 'buddypress' &&
					function_exists('bp_core_fetch_avatar') ) {

					$im = bp_core_fetch_avatar(array('item_id' => $r->id, 'html' => false));
				} else {
					$im = $this->get_avatar_url($r->id);
				}

				$image_settings = $sd['image_options'];
				if ( !empty($im) ) {
					$r->image = $im;
					if ( $image_settings['image_cropping'] == 0 ) {
						$r->image = $im;
					} else {
						if ( strpos( $im, "mshots/v1" ) === false && strpos( $im, ".gif" ) === false ) {
							$bfi_params = array( 'width'  => $image_settings['image_width'],
								'height' => $image_settings['image_height'],
								'crop'   => true
							);
							if ( w_isset_def( $image_settings['image_transparency'], 1 ) != 1 )
								$bfi_params['color'] = wpdreams_rgb2hex( $image_settings['image_bg_color'] );
							$r->image = asp_bfi_thumb( $im, $bfi_params );
						} else {
							$r->image = $im;
						}
					}
				}

				// Default, if defined and available
				if ( empty($r->image) && !empty($sd['user_image_default']) ) {
					$r->image = $sd['user_image_default'];
				}
			}
			/*---------------------------------------------------------------*/

			if ( !empty($sd['user_search_advanced_title_field']) )
				$r->title = $this->advField(
					array(
						'main_field_slug' => 'titlefield',
						'main_field_value'=> $r->title,
						'r' => $r,
						'field_pattern' => stripslashes( $sd['user_search_advanced_title_field'] )
					),
					$com_options['use_acf_getfield']
				);

			/*---------------------- Description ----------------------------*/
			switch ( $sd['user_search_description_field'] ) {
				case 'buddypress_last_activity':
					$update = get_user_meta($r->id, 'bp_latest_update', true);
					if (is_array($update) && isset($update['content']))
						$r->content = $update['content'];
					break;
				case 'nothing':
					$r->content = "";
					break;
				default:
					$content = get_user_meta($r->id, 'description', true);
					if ($content != '')
						$r->content = $content;
			}

			// Remove inline styles and scripts
			$_content = preg_replace( array(
				'#<script(.*?)>(.*?)</script>#is',
				'#<style(.*?)>(.*?)</style>#is'
			), '', $r->content );

			$_content = wd_strip_tags_ws( $_content, $sd['striptagsexclude'] );

			// Get the words from around the search phrase, or just the description
			if ( $sd['description_context'] == 1 && count( $_s ) > 0 && $s != '') {
				// Try for an exact match
				$_ex_content = $this->contextFind(
					$_content, $s,
					floor($sd['user_res_descriptionlength'] / 6),
					$sd['user_res_descriptionlength'],
					$sd['description_context_depth'],
					true
				);
				if ( $_ex_content === false ) {
					// No exact match, go with the first keyword
					$_content = $this->contextFind(
						$_content, $_s[0],
						floor($sd['user_res_descriptionlength'] / 6),
						$sd['user_res_descriptionlength'],
						$sd['description_context_depth']
					);
				} else {
					$_content = $_ex_content;
				}
			} else if ( $_content != '' && (  MB::strlen( $_content ) > $sd['user_res_descriptionlength'] ) ) {
				$_content = wd_substr_at_word($_content, $sd['user_res_descriptionlength']);
			}
			$r->content   = wd_closetags( $_content );

			if ( !empty($sd['user_search_advanced_description_field']) )
				$r->content = $this->advField(
					array(
						'main_field_slug' => 'descriptionfield',
						'main_field_value'=> $r->content,
						'r' => $r,
						'field_pattern' => stripslashes( $sd['user_search_advanced_description_field'] )
					),
					$com_options['use_acf_getfield']
				);
			/*---------------------------------------------------------------*/

			// --------------------------------- DATE -----------------------------------
			if ($sd["showdate"] == 1) {
				$post_time = strtotime($r->date);

				if ( $sd['custom_date'] == 1) {
					$date_format = w_isset_def($sd['custom_date_format'], "Y-m-d H:i:s");
				} else {
					$date_format = get_option('date_format', "Y-m-d") . " " . get_option('time_format', "H:i:s");
				}

				$r->date = @date_i18n($date_format, $post_time);
			}
			// --------------------------------------------------------------------------

		}

		$this->results = $userresults;

		return $userresults;
	}

	/**
	 * Gets the avatar URL as a similar function is only supported in WP 4.2 +
	 *
	 * @param $user_id - the user ID
	 * @param $size - the size of the avatar
	 * @return string
	 * @noinspection PhpMissingParamTypeInspection
	 */
	protected function get_avatar_url($user_id, $size = 96): string {
		$get_avatar = get_avatar($user_id, $size);
		preg_match('/src=(.*?) /i', $get_avatar, $matches);
		if (isset($matches[1]))
			return str_replace(array('"',"'"), '', $matches[1]);
		return '';
	}

	/**
	 * Generates the final field, based on the advanced field pattern
	 *
	 *
	 * @param array     $f_args             Field related arguments
	 * @param boolean   $use_acf            If true, uses ACF get_field() function to get the meta
	 * @param boolean   $empty_on_missing   If true, returns an empty string if any of the fields is empty.
	 *
	 * @return string Final result title
	 * @noinspection PhpMissingParamTypeInspection
	 */
	protected function advField( $f_args, $use_acf = false, $empty_on_missing = false ): string {

		$f_args = wp_parse_args($f_args, array(
			'main_field_slug' => 'titlefield',  // The 'slug', aka the original field name
			'main_field_value'=> '',            // The default field value
			'r' => null,                        // Result object
			'field_pattern' => '{titlefield}'   // The field pattern
		));
		$_f_args = $f_args;

		if ( $f_args['field_pattern'] == '' ) {
			return $f_args['field_value'];
		}
		$field_pattern = $f_args['field_pattern']; // Let's not make changes to arguments, shall we.

		// Find conditional patterns, like [prefix {field} suffix}
		/** @noinspection RegExpRedundantEscape */
		preg_match_all( "/(\[.*?\])/", $field_pattern, $matches );
		if ( isset( $matches[0] ) && isset( $matches[1] ) && is_array( $matches[1] ) ) {
			foreach ( $matches[1] as $fieldset ) {
				// Pass on each section to this function again, the code will never get here
				$_f_args['field_pattern'] = str_replace(array('[', ']'), '', $fieldset);
				$processed_fieldset = $this->advField(
					$_f_args,
					$use_acf,
					true
				);
				// Replace the original with the processed version, first occurrence, in case of duplicates
				$field_pattern = Str::replaceFirst($fieldset, $processed_fieldset, $field_pattern);
			}
		}

		preg_match_all( "/{(.*?)}/", $field_pattern, $matches );
		if ( isset( $matches[0] ) && isset( $matches[1] ) && is_array( $matches[1] ) ) {
			foreach ( $matches[1] as $complete_field ) {
				$field_args = shortcode_parse_atts($complete_field);
				if ( is_array($field_args) && isset($field_args[0]) ) {
					$field = array_shift($field_args);
				} else {
					continue;
				}
				if ( $field == $f_args['main_field_slug'] ) {
					$val = $f_args['main_field_value'];
					if ( isset($field_args['maxlength']) ) {
						$val = wd_substr_at_word($val, $field_args['maxlength']);
					}
					// value, field name, post object, field arguments
					$val = apply_filters('asp_user_advanced_field_value', $val, $field, $f_args['r'], $f_args);
					$field_pattern = str_replace( '{'.$complete_field.'}', $val, $field_pattern );
				} else {
					$val        = User::getCFValue($field, $f_args['r'], $use_acf);
					// For the recursive call to break, if any of the fields is empty
					if ( $empty_on_missing && $val == '')
						return '';
					$val = Str::fixSSLURLs($val);
					$val = apply_filters('asp_user_advanced_field_value', $val, $field, $f_args['r'], $f_args);
					$field_pattern = str_replace( '{' . $field . '}', $val, $field_pattern );
				}
			}
		}

		return $field_pattern;
	}
}