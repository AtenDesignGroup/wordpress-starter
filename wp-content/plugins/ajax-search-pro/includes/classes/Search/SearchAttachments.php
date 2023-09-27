<?php
namespace WPDRMS\ASP\Search;

use WPDRMS\ASP\Misc\Priorities;
use WPDRMS\ASP\Utils\MB;
use WPDRMS\ASP\Utils\Pdf;
use WPDRMS\ASP\Utils\Post;
use WPDRMS\ASP\Utils\Str;

defined('ABSPATH') or die("You can't access this file directly.");

class SearchAttachments extends SearchPostTypes {
	/**
	 * @var array of query parts
	 */
	protected $parts = array();
	/**
	 * @var array of custom field query parts
	 */
	protected $cf_parts = array();

	/**
	 * Content search function
	 *
	 */
	protected function doSearch(): array {
		global $wpdb;
		global $q_config;

		$args = $this->args;

		if ( $args['attachments_use_index'] == 1 ) {
			// Reset a few options;
			$args['_no_post_process'] = 1;
			$args['post_type'] = array('attachment');
			$args['posts_limit'] = $args['attachments_limit'];
			$args['posts_limit_override'] = $args['attachments_limit_override'];
			$args['post_not_in2'] = $args['attachment_exclude'];

			$att_ind = new SearchIndex($args);
			$this->results = $att_ind->search($args['s']);
			$this->results_count = $att_ind->results_count;
			$this->return_count = count($this->results);
			return $this->results;
		}

		$sd = $args["_sd"] ?? array();

		// Prefixes and suffixes
		$pre_field = $this->pre_field;
		$suf_field = $this->suf_field;
		$pre_like = $this->pre_like;
		$suf_like = $this->suf_like;

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
		$q_config['language'] = $args['_qtranslate_lang'];

		$s = $this->s; // full keyword
		$_s = $this->_s; // array of keywords

		$postmeta_join = '';

		if ( $args['_limit'] > 0 ) {
			$this->remaining_limit = $args['_limit'];
		} else {
			if ( $args['_ajax_search'] )
				$this->remaining_limit = $args['attachments_limit'];
			else
				$this->remaining_limit = $args['attachments_limit_override'];
		}
		$query_limit = $this->remaining_limit * $this->remaining_limit_mod;

		if ($this->remaining_limit <= 0)
			return array();

		/*------------------------- Statuses ----------------------------*/
		// Attachments are inherited only
		$post_statuses = "(" . $pre_field . $wpdb->posts . ".post_status" . $suf_field . " = 'inherit' )";
		/*---------------------------------------------------------------*/

		/*----------------------- Gather Types --------------------------*/
		$post_types = "($wpdb->posts.post_type = 'attachment' )";
		/*---------------------------------------------------------------*/

		// ------------------------ Categories/tags/taxonomies ----------------------
		$term_query = $this->buildTermQuery( $wpdb->posts.".ID", $wpdb->posts.'.post_type' );
		// ---------------------------------------------------------------------

		/*------------- Custom Fields with Custom selectors -------------*/
		if ( $args['attachments_cf_filters'] ) {
			$cf_select = $this->buildCffQuery( $wpdb->posts.".ID" );
		} else {
			$cf_select = '(1)';
		}
		/*---------------------------------------------------------------*/


		/*------------------------- Mime Types --------------------------*/
		$mime_types = "";
		if (!empty($args['attachment_mime_types']))
			$mime_types = "AND ( $wpdb->posts.post_mime_type IN ('" . implode("','", $args['attachment_mime_types']) . "') )";
		/*---------------------------------------------------------------*/

		/*------------------------ Exclude id's -------------------------*/
		$exclude_posts = "";
		if (!empty($args['attachment_exclude']))
			$exclude_posts = "AND ($wpdb->posts.ID NOT IN (" . implode(",", $args['attachment_exclude']) . "))";
		/*---------------------------------------------------------------*/


		/*------------------------ Term JOIN -------------------------*/
		// If the search in terms is not active, we don't need this unnecessary big join
		$term_join = "";
		if ($args['attachments_search_terms']) {
			$term_join = "
			LEFT JOIN $wpdb->term_relationships ON $wpdb->posts.ID = $wpdb->term_relationships.object_id
			LEFT JOIN $wpdb->term_taxonomy ON $wpdb->term_taxonomy.term_taxonomy_id = $wpdb->term_relationships.term_taxonomy_id
			LEFT JOIN $wpdb->terms ON $wpdb->term_taxonomy.term_id = $wpdb->terms.term_id";
		}
		/*---------------------------------------------------------------*/

		/*------------------------- WPML filter -------------------------*/
		$wpml_query = "(1)";
		if ( $args['_wpml_lang'] != "" ) {
			global $sitepress;
			$site_lang_selected = false;

			// Let us get the default site language if possible
			if ( is_object($sitepress) && method_exists($sitepress, 'get_default_language') ) {
				$site_lang_selected = $sitepress->get_default_language() == $args['_wpml_lang'];
			}

			$wpml_query = "
			EXISTS (
				SELECT DISTINCT(wpml.element_id)
				FROM " . $wpdb->prefix . "icl_translations as wpml
				WHERE
					$wpdb->posts.ID = wpml.element_id AND
					wpml.language_code = '" . Str::escape( $args['_wpml_lang'] ) . "' AND
					wpml.element_type IN ('post_attachment')
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
						$wpdb->posts.ID = wpml.element_id AND
						wpml.element_type IN ('post_attachment')
				) OR
				" . $wpml_query;
			}
		}
		/*---------------------------------------------------------------*/

		/*----------------------- Date filtering ------------------------*/
		$date_query = "";
		$date_query_parts = $this->get_date_query_parts();
		if (count($date_query_parts) > 0)
			$date_query = " AND (" . implode(" AND ", $date_query_parts) . ") ";
		/*---------------------------------------------------------------*/

		/*----------------------- Exclude USER id -----------------------*/
		$user_query = "";
		if ( isset($args['post_user_filter']['include']) ) {
			if ( !in_array(-1, $args['post_user_filter']['include']) )
				$user_query = "AND $wpdb->posts.post_author IN (".implode(", ", $args['post_user_filter']['include']).")
				";
		}
		if ( isset($args['post_user_filter']['exclude']) ) {
			if ( !in_array(-1, $args['post_user_filter']['exclude']) )
				$user_query = "AND $wpdb->posts.post_author NOT IN (".implode(", ", $args['post_user_filter']['exclude']).") ";
			else
				return array();
		}
		/*---------------------------------------------------------------*/

		/**
		 * Determine if the priorities table should be used or not.
		 */
		$priority_select = Priorities::count() > 0 ? "
		IFNULL((
			SELECT
			aspp.priority
			FROM ".wd_asp()->db->table('priorities')." as aspp
			WHERE aspp.post_id = $wpdb->posts.ID AND aspp.blog_id = " . get_current_blog_id() . "
		), 100)
		" : 100;

		/*---------------- Primary custom field ordering ----------------*/
		$custom_field_selectp = "1 ";
		if (
			strpos($args['post_primary_order'], 'customfp') !== false &&
			$args['_post_primary_order_metakey'] !== false
		) {
			$custom_field_selectp = "(SELECT IF(meta_value IS NULL, 0, meta_value)
			FROM $wpdb->postmeta
			WHERE
				$wpdb->postmeta.meta_key='".esc_sql($args['_post_primary_order_metakey'])."' AND
				$wpdb->postmeta.post_id=$wpdb->posts.ID
			LIMIT 1
			) ";
		}
		/*---------------------------------------------------------------*/

		/*--------------- Secondary custom field ordering ---------------*/
		$custom_field_selects = "1 ";
		if (
			strpos($args['post_secondary_order'], 'customfs') !== false &&
			$args['_post_secondary_order_metakey'] !== false
		) {
			$custom_field_selects = "(SELECT IF(meta_value IS NULL, 0, meta_value)
			FROM $wpdb->postmeta
			WHERE
				$wpdb->postmeta.meta_key='".esc_sql($args['_post_secondary_order_metakey'])."' AND
				$wpdb->postmeta.post_id=$wpdb->posts.ID
			LIMIT 1
			) ";
		}
		/*---------------------------------------------------------------*/

		$this->ordering['primary'] = $args['post_primary_order'];
		$this->ordering['secondary'] = $args['post_secondary_order'];

		$_primary_field = explode(" ", $this->ordering['primary']);
		$this->ordering['primary_field'] = $_primary_field[0];

		$orderby_primary    = str_replace( "post_", $wpdb->posts . ".post_",  $args['post_primary_order'] );
		$orderby_secondary  = str_replace( "post_", $wpdb->posts . ".post_",  $args['post_secondary_order'] );

		if (
			$args['post_primary_order_metatype'] !== false &&
			$args['post_primary_order_metatype'] == 'numeric'
		)
			$orderby_primary = str_replace('customfp', 'CAST(customfp as SIGNED)', $orderby_primary);

		if (
			$args['post_secondary_order_metatype'] !== false &&
			$args['post_secondary_order_metatype'] == 'numeric'
		)
			$orderby_secondary = str_replace('customfs', 'CAST(customfs as SIGNED)', $orderby_secondary);

		/**
		 * This is the main query.
		 *
		 * The ttid field is a bit tricky as the term_taxonomy_id doesn't always equal term_id,
		 * so we need the LEFT JOINS :(
		 */
		$this->query = "
		SELECT
			{args_fields}
			$wpdb->posts.ID as id,
			$this->c_blogid as blogid,
			$wpdb->posts.post_title as title,
			$wpdb->posts.post_date as date,
			$wpdb->posts.post_content as content,
			$wpdb->posts.post_excerpt as excerpt,
			$wpdb->posts.post_type as post_type,
			$wpdb->posts.post_mime_type as post_mime_type,
			$wpdb->posts.guid as guid,
			'attachment' as content_type,
			'attachments' as g_content_type,
			(SELECT
				$wpdb->users." . w_isset_def($sd['author_field'], 'display_name') . " as author
				FROM $wpdb->users
				WHERE $wpdb->users.ID = $wpdb->posts.post_author
			) as author,
			'' as ttid,
			$wpdb->posts.post_type as post_type,
			$priority_select AS priority,
			1 AS group_priority,
			1 AS p_type_priority,
			{relevance_query} as relevance,
			$custom_field_selectp as customfp,
			$custom_field_selects as customfs
		FROM $wpdb->posts
			{postmeta_join}
			$term_join
			{args_join}
		WHERE
				$post_types
			AND $post_statuses
			AND {like_query}
			$exclude_posts
			$mime_types
			$term_query
			$date_query
			$user_query
			AND $cf_select
			AND ($wpml_query)
			{args_where}
		GROUP BY
			{args_groupby} 
		ORDER BY {args_orderby} priority DESC, $orderby_primary, $orderby_secondary
		LIMIT $query_limit";

		// Place the argument query fields
		if ( isset($args['attachment_query']) && is_array($args['attachment_query']) ) {
			$this->query = str_replace(
				array('{args_fields}', '{args_join}', '{args_where}', '{args_orderby}'),
				array($args['attachment_query']['fields'], $args['attachment_query']['join'], $args['attachment_query']['where'], $args['attachment_query']['orderby']),
				$this->query
			);
		} else {
			$this->query = str_replace(
				array('{args_fields}', '{args_join}', '{args_where}', '{args_orderby}'),
				'',
				$this->query
			);
		}
		if ( isset($args['attachment_query'], $args['attachment_query']['groupby']) && $args['attachment_query']['groupby'] != '' ) {
			$this->query = str_replace('{args_groupby}', $args['attachment_query']['groupby'], $this->query);
		} else {
			$this->query = str_replace('{args_groupby}', "$wpdb->posts.ID", $this->query);
		}

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
			$is_exact = $args["_exact_matches"] == 1 || ( count($words) > 1 && $k == 0 && ($kw_logic == 'or' || $kw_logic == 'and') );

			/*----------------------- Title query ---------------------------*/
			if ( $args['attachments_search_title'] ) {
				if ($kw_logic == 'or' || $kw_logic == 'and' || $is_exact) {
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
			if ($args['attachments_search_content']) {
				if ($kw_logic == 'or' || $kw_logic == 'and' || $is_exact) {
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

			/*------------------- Caption/Excerpt query ---------------------*/
			if ($args['attachments_search_caption']) {
				if ($kw_logic == 'or' || $kw_logic == 'and' || $is_exact) {
					$parts[] = "( " . $pre_field . $wpdb->posts . ".post_excerpt" . $suf_field . " LIKE $pre_like'$wcl" . $word . "$wcr'$suf_like )";
				} else {
					$parts[] = "
					   (" . $pre_field . $wpdb->posts . ".post_excerpt" . $suf_field . " LIKE $pre_like'% " . $word . " %'$suf_like
					OR  " . $pre_field . $wpdb->posts . ".post_excerpt" . $suf_field . " LIKE $pre_like'" . $word . " %'$suf_like
					OR  " . $pre_field . $wpdb->posts . ".post_excerpt" . $suf_field . " LIKE $pre_like'% " . $word . "'$suf_like
					OR  " . $pre_field . $wpdb->posts . ".post_excerpt" . $suf_field . " = '" . $word . "')";
				}
				if ( !$relevance_added ) {
					if ( isset($_s[0]) )
						$relevance_parts[] = "(case when
						(" . $pre_field . $wpdb->posts . ".post_excerpt" . $suf_field . " LIKE '%" . $_s[0] . "%')
						 then " . w_isset_def($sd['contentweight'], 10) . " else 0 end)";
				}
			}
			/*---------------------------------------------------------------*/

			/*-------------------------- IDs query --------------------------*/
			if ( $args['attachments_search_ids'] ) {
				$parts[] = "($wpdb->posts.ID LIKE '$word')";
			}
			/*---------------------------------------------------------------*/

			/*------------------------ Term query ---------------------------*/
			if ($args['attachments_search_terms']) {
				if ($kw_logic == 'or' || $kw_logic == 'and' || $is_exact) {
					$parts[] = "( " . $pre_field . $wpdb->terms . ".name" . $suf_field . " LIKE $pre_like'$wcl" . $word . "$wcr'$suf_like )";
				} else {
					$parts[] = "
					   (" . $pre_field . $wpdb->terms . ".name" . $suf_field . " LIKE $pre_like'% " . $word . " %'$suf_like
					OR  " . $pre_field . $wpdb->terms . ".name" . $suf_field . " LIKE $pre_like'" . $word . " %'$suf_like
					OR  " . $pre_field . $wpdb->terms . ".name" . $suf_field . " LIKE $pre_like'% " . $word . "'$suf_like
					OR  " . $pre_field . $wpdb->terms . ".name" . $suf_field . " = '" . $word . "')";
				}
			}
			/*---------------------------------------------------------------*/

			/*---------------------- Custom Fields --------------------------*/
			if ( $args['post_custom_fields_all'] == 1 )
				$args['post_custom_fields'] = array("all");

			if ( count($args['post_custom_fields']) > 0 ) {
				$postmeta_join   = "LEFT JOIN $wpdb->postmeta ON $wpdb->postmeta.post_id = $wpdb->posts.ID";

				foreach ( $args['post_custom_fields'] as $cfield ) {
					$key_part = $args['post_custom_fields_all'] == 1 ? "" : "$wpdb->postmeta.meta_key='$cfield' AND ";

					if ( $kw_logic == 'or' || $kw_logic == 'and' ) {
						$parts[] = "( $key_part " . $pre_field . $wpdb->postmeta . ".meta_value" . $suf_field . " LIKE $pre_like'$wcl" . $word . "$wcr'$suf_like )";
					} else {
						$parts[] = "( $key_part
						   (" . $pre_field . $wpdb->postmeta . ".meta_value" . $suf_field . " LIKE $pre_like'% " . $word . " %'$suf_like
						OR  " . $pre_field . $wpdb->postmeta . ".meta_value" . $suf_field . " LIKE $pre_like'" . $word . " %'$suf_like
						OR  " . $pre_field . $wpdb->postmeta . ".meta_value" . $suf_field . " LIKE $pre_like'% " . $word . "'$suf_like
						OR  " . $pre_field . $wpdb->postmeta . ".meta_value" . $suf_field . " = '" . $word . "') )";
					}
					if ( !$relevance_added ) {
						if ($cfield == 'author_field_name')
							$relevance_parts[] = "(case when
						(EXISTS (SELECT 1 FROM $wpdb->postmeta as cfre WHERE cfre.post_id = $wpdb->posts.ID AND cfre.meta_key = '$cfield' AND 
						(cfre.meta_value" . $suf_field . " LIKE '%" . $s . "%')))
						 then 100 else 0 end)";
						if ($cfield == 'fulltext_field_name')
							$relevance_parts[] = "(case when
						(EXISTS (SELECT 1 FROM $wpdb->postmeta as cfre WHERE cfre.post_id = $wpdb->posts.ID AND cfre.meta_key = '$cfield' AND 
						(cfre.meta_value" . $suf_field . " LIKE '%" . $s . "%')))
						 then 10 else 0 end)";
					}
				}
			}
			/*---------------------------------------------------------------*/

			$this->parts[] = array( $parts, $relevance_parts );
			$relevance_added = true;
		}

		// Add the meta join if needed...
		$this->query = str_replace( '{postmeta_join}', $postmeta_join, $this->query );

		$querystr = $this->buildQuery($this->parts);
		$querystr = apply_filters('asp_query_attachments', $querystr, $args, $args['_id'], $args['_ajax_search']);
		$attachments = $wpdb->get_results($querystr, OBJECT);
		$this->results_count = count($attachments);

		/* For non-ajax search, results count needs to be limited to the maximum limit,
		 * as nothing is parsed beyond that */
		if ($args['_ajax_search'] == false && $this->results_count > $this->remaining_limit) {
			$this->results_count = $this->remaining_limit;
		}

		/**
		 * Order them again:
		 *  - The custom field ordering always uses alphanumerical comparision, which is not ok
		 */
		if (
			count($attachments) > 0 &&
			(
				strpos($args['post_primary_order'], 'customfp') !== false ||
				strpos($args['post_secondary_order'], 'customfs') !== false
			)
		) {
			usort( $attachments, array( $this, 'compare_by_primary' ) );
			/**
			 * Let us save some time. There is going to be a user selecting the same sorting
			 * for both primary and secondary. Only do secondary if it is different from the primary.
			 */
			if ( $this->ordering['primary'] != $this->ordering['secondary'] ) {
				$i = 0;
				foreach ($attachments as $pp) {
					$pp->primary_order = $i;
					$i++;
				}

				usort( $attachments, array( $this, 'compare_by_secondary' ) );
			}
		}

		$attachments = array_slice($attachments, $args['_call_num'] * $this->remaining_limit, $this->remaining_limit);

		$this->results = $attachments;
		$this->return_count = count($this->results);

		return $attachments;
	}

	protected function postProcess(): array {
		parent::postProcess();

		$args = &$this->args;
		$s          = $this->s;
		$_s         = $this->_s;
		$sd = $args["_sd"] ?? array();

		foreach ($this->results as $k => $r) {
			if ( !isset($r->post_mime_type) )
				$r->post_mime_type = get_post_mime_type( $r->id );
			if ( !isset($r->guid) )
				$r->guid = get_the_guid( $r->id );

			$r->content = Str::fixSSLURLs($r->content);

			if ($args['attachment_use_image'] == 1 && $r->guid != "") {
				$image_settings = $sd['image_options'];
				$image_args = array(
					'get_content' => false,
					'get_excerpt' => false,
					'image_sources' => array(
						$image_settings['image_source1'],
						$image_settings['image_source2'],
						$image_settings['image_source3'],
						$image_settings['image_source4'],
						$image_settings['image_source5']
					),
					'image_source_size' => $image_settings['image_source_featured'] == "original" ? 'full' : $image_settings['image_source_featured'],
					'image_default' => $image_settings['image_default'],
					'image_number' => $sd['image_parser_image_number'],
					'image_custom_field' => $image_settings['image_custom_field'],
					'exclude_filenames' => $sd['image_parser_exclude_filenames'],
					'image_width' => $image_settings['image_width'],
					'image_height' => $image_settings['image_height'],
					'apply_the_content' => $image_settings['apply_content_filter'],
					'image_cropping' => $image_settings['image_cropping'],
					'image_transparency' => $image_settings['image_transparency'],
					'image_bg_color' => $image_settings['image_bg_color']
				);
				$r->image = Post::parseImage($r, $image_args);
			}

			if ( $r->post_mime_type == 'application/pdf' && $args['attachment_pdf_image'] == 1 && empty($r->image) ) {
				$r->image = Pdf::getThumbnail($r->id);
			}

			/* Remove the results in polaroid mode */
			if ($args['_ajax_search'] && empty($r->image) && isset($sd['resultstype']) &&
				$sd['resultstype'] == 'polaroid' && $sd['pifnoimage'] == 'removeres') {
				unset($this->results[$k]);
				continue;
			}

			// --------------------------------- URL -----------------------------------
			if ( $args['attachment_link_to'] == 'file' ) {
				$_url = wp_get_attachment_url( $r->id );
				if ( $_url !== false )
					$this->results[$k]->link = $_url;
			} else if ( $args['attachment_link_to'] == 'parent' ) {
				$parent_id = wp_get_post_parent_id( $r->id );
				if ( !is_wp_error($parent_id) && !empty($parent_id) ) {
					// Change the link to parent post permalink
					$r->link = get_permalink( $parent_id );
				} else {
					if ( $args['attachment_link_to_secondary'] == 'file' ) {
						$_url = wp_get_attachment_url($r->id);
						if ($_url !== false)
							$this->results[$k]->link = $_url;
					}
				}
			} else {
				$_url = get_attachment_link($r->id);
				if ($_url != false)
					$this->results[$k]->link = $_url;
			}
			// --------------------------------------------------------------------------

			if ( $r->content == '' ) {
				$_content = get_post_meta($r->id, '_asp_attachment_text', true);
				$_content = wd_strip_tags_ws( $_content, $sd['striptagsexclude'] );
				// Get the words from around the search phrase, or just the description
				if ( $_content != '' ) {
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
					} else if ( MB::strlen($_content) > $sd['descriptionlength'] ) {
						$_content = wd_substr_at_word($_content, $sd['descriptionlength']);
					}
					if ( $_content != '' ) {
						$r->content = $_content;
					}
				}
			}

			// --------------------------------- DATE -----------------------------------
			if (isset($sd["showdate"]) && $sd["showdate"] == 1) {
				$post_time = strtotime($this->results[$k]->date);

				if ($sd['custom_date'] == 1) {
					$date_format = w_isset_def($sd['custom_date_format'], "Y-m-d H:i:s");
				} else {
					$date_format = get_option('date_format', "Y-m-d") . " " . get_option('time_format', "H:i:s");
				}

				$this->results[$k]->date = @date_i18n($date_format, $post_time);
			}
			// --------------------------------------------------------------------------
		}

		return $this->results;
	}
}