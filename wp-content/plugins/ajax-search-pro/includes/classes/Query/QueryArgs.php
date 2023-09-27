<?php
namespace WPDRMS\ASP\Query;

use WPDRMS\ASP\Index\Manager;
use WPDRMS\ASP\Utils\Str;

defined('ABSPATH') or die("You can't access this file directly.");

class QueryArgs {
	/**
	 * Translates search data and $_POST options to query arguments to use with SearchQuery
	 *
	 * @param $search_id
	 * @param $o
	 * @param array $args
	 * @return array
	 */
	public static function get($search_id, $o, array $args = array()): array {

		// Always return an emtpy array if something goes wrong
		if ( !wd_asp()->instances->exists($search_id) )
			return array();

		$search = wd_asp()->instances->get($search_id);
		$sd = $search['data'];
		// See if we post the preview data through
		if ( !empty($_POST['asp_preview_options']) && (current_user_can("manage_options") || ASP_DEMO) ) {
			if ( is_array($_POST['asp_preview_options']) ) {
				$sd = array_merge($sd, $_POST['asp_preview_options']);
			} else {
				parse_str($_POST['asp_preview_options'], $preview_options);
				$sd = array_merge($sd, $preview_options);
			}
		}

		$args = empty($args) ? SearchQuery::$defaults : array_merge(SearchQuery::$defaults, $args);
		$comp_options = wd_asp()->o['asp_compatibility'];
		$it_options = wd_asp()->o['asp_it_options'];

		$exclude_post_ids = array_unique(array_merge(
			$sd['exclude_cpt']['ids'],
			explode(',', str_replace(' ', '', $sd['excludeposts']))
		));
		foreach ( $exclude_post_ids as $k=>$v) {
			if ($v == '') {
				unset($exclude_post_ids[$k]);
			} else {
				$exclude_post_ids[$k] = intval($v);
			}
		}

		$include_post_ids = array_unique($sd['include_cpt']['ids']);
		foreach ( $include_post_ids as $k=>$v) {
			if ($v == '')
				unset($include_post_ids[$k]);
		}

		// Parse the filters, so the objects exist
		asp_parse_filters($search_id, $sd, true, false);

		// ----------------------------------------------------------------
		// 1. CPT + INDEXTABLE
		// ----------------------------------------------------------------
		$args = array_merge($args, array(
			"_sd"                   => $sd, // Search Data
			'_sid'                  => $search_id,
			"keyword_logic"         => $sd['keyword_logic'],
			'secondary_logic'       => $sd['secondary_kw_logic'],
			'engine'                => $sd['search_engine'],
			"post_not_in"           => $exclude_post_ids,
			"post_in"               => $include_post_ids,
			"post_primary_order"    => $sd['orderby_primary'],
			"post_secondary_order"  => $sd['orderby'],
			'_post_meta_allow_null' => $sd['cf_allow_null'],
			'_post_meta_logic'      => $sd['cf_logic'],
			'_post_use_relevance'   => $sd['userelevance'],
			'_db_force_case'        => $comp_options['db_force_case'],
			'_db_force_utf8_like'   => $comp_options['db_force_utf8_like'],
			'_db_force_unicode'     => $comp_options['db_force_unicode'],
			'_post_allow_empty_tax_term' => $sd["frontend_terms_empty"],
			'_taxonomy_group_logic' =>  $sd['taxonomy_logic'],

			// LIMITS
			'posts_limit' => $sd['posts_limit'],
			'posts_limit_override' => $sd['posts_limit_override'],
			'posts_limit_distribute' => $sd['posts_limit_distribute'],
			'taxonomies_limit'  => $sd['taxonomies_limit'],
			'taxonomies_limit_override' => $sd['taxonomies_limit_override'],
			'users_limit' => $sd['users_limit'],
			'users_limit_override' => $sd['users_limit_override'],
			'blogs_limit' => $sd['blogs_limit'],
			'blogs_limit_override' => $sd['blogs_limit_override'],
			'buddypress_limit' => $sd['buddypress_limit'],
			'buddypress_limit_override' => $sd['buddypress_limit_override'],
			'comments_limit' => $sd['comments_limit'],
			'comments_limit_override' => $sd['comments_limit_override'],
			'attachments_limit' => $sd['attachments_limit'],
			'attachments_limit_override' => $sd['attachments_limit_override']
		));
		$args["_qtranslate_lang"] = $o['qtranslate_lang'] ?? "";

		if ( $sd['polylang_compatibility'] == 1 ) {
			$args["_polylang_lang"] = $o['polylang_lang'] ?? (function_exists('pll_current_language') ? pll_current_language() : '');
		}

		// Exclusions via the options (auto populate)
		if ( isset($o['not_in']) ) {
			if ( isset($o['not_in']['pagepost']) && is_array($o['not_in']['pagepost']) ) {
				$args['post_not_in'] = array_unique(
					array_merge(
						$args['post_not_in'],
						array_map('intval', $o['not_in']['pagepost'])
					)
				);
			}
			if ( isset($o['not_in']['attachment']) && is_array($o['not_in']['attachment']) ) {
				$args['attachment_exclude'] = array_unique(
					array_merge(
						$args['attachment_exclude'],
						array_map('intval', $o['not_in']['attachment'])
					)
				);
			}
			if ( isset($o['not_in']['term']) && is_array($o['not_in']['term']) ) {
				$args['taxonomy_terms_exclude2'] = array_unique(
					array_merge(
						$args['taxonomy_terms_exclude2'],
						array_map('intval', $o['not_in']['term'])
					)
				);
			}
			if ( isset($o['not_in']['user']) && is_array($o['not_in']['user']) ) {
				$args['user_search_exclude_ids'] = array_unique(
					array_merge(
						$args['user_search_exclude_ids'],
						array_map('intval', $o['not_in']['user'])
					)
				);
			}
		}

		$args["_exact_matches"] = isset($o['asp_gen']) && is_array($o['asp_gen']) && in_array('exact', $o['asp_gen']) ? 1 : 0;
		$args["_exact_match_location"] = $sd['exact_match_location'];

		// Is the secondary logic allowed on exact matching?
		if ( $args["_exact_matches"] == 1 && $sd['exact_m_secondary'] == 0 )
			$args['secondary_logic'] = 'none';

		// Minimal word length to be a keyword
		$args["min_word_length"] = $sd['min_word_length'];

		/*----------------------- Meta key order ------------------------*/
		if ( strpos($sd['orderby_primary'], 'customfp') !== false ) {
			if ( !empty($sd['orderby_primary_cf']) ) {
				$args['_post_primary_order_metakey'] = $sd['orderby_primary_cf'];
				$args['post_primary_order_metatype'] = $sd['orderby_primary_cf_type'];
			}
		}
		if ( strpos($sd['orderby'], 'customfs') !== false ) {
			if ( !empty($sd['orderby_secondary_cf']) ) {
				$args['_post_secondary_order_metakey'] = $sd['orderby_secondary_cf'];
				$args['post_secondary_order_metatype'] = $sd['orderby_secondary_cf_type'];
			}
		}

		/*----------------------- Auto populate -------------------------*/
		if ( isset($o['force_count']) ) {
			// Set the advanced limit parameter to be distributed later
			$args['limit'] = $o['force_count'] + 0;
			$args['force_count'] = $o['force_count'] + 0;
		}
		if ( isset($o['force_order']) ) {
			if ( $o['force_order'] == 1 ) {
				$args["post_primary_order"] = "post_date DESC";
				$args['force_order'] = 1;
			} else if ( $o['force_order'] == 2 ) {
				$args["post_primary_order"] = "RAND()";
				$args['force_order'] = 2;
			}
		}

		/*------------------------- Statuses ----------------------------*/
		$args['post_status'] = explode(',', str_replace(' ', '', $sd['post_status']));

		/*--------------------- Password protected ----------------------*/
		$args['has_password'] = $sd['post_password_protected'] == 1;

		/*----------------------- Gather Types --------------------------*/
		$args['post_type'] = $sd['customtypes'];
		if ( is_array($o) ) {
			$frontend_custom_post_types = array();
			foreach ( wd_asp()->front_filters->get('position', 'post_type') as $filter ) {
				foreach ( $filter->get() as $item ) {
					$frontend_custom_post_types[] = $item->value;
				}
			}
			$args['post_type'] = array_diff($args['post_type'], $frontend_custom_post_types);
			if ( isset($o['customset']) && is_array($o['customset']) && count( $o['customset'] ) > 0 ) {
				$o['customset'] = Str::escape( $o['customset'], true, ' ;:.,(){}@[]!?&|#^=' );
				if ( in_array(-1, $o['customset']) ) {
					$args['post_type'] = $sd['customtypes'];
				} else {
					$args['post_type'] = array_unique( array_merge($args['post_type'], $o['customset']) );
				}
			}
		}
		foreach ( $args['post_type'] as $vv) {
			if ( $vv == "page" && count($sd['exclude_cpt']['parent_ids']) > 0 ) {
				$args['_exclude_page_parent_child'] = implode(',', $sd['exclude_cpt']['parent_ids']);
				break;
			}
		}

		/*--------------------- OTHER FILTER RELATED --------------------*/
		$args['filters_changed'] = isset($o['filters_changed']) ? $o['filters_changed'] == 1 : $args['filters_changed'];
		$args['filters_initial'] = isset($o['filters_initial']) ? $o['filters_initial'] == 1 : $args['filters_initial'];

		/*--------------------- GENERAL FIELDS --------------------------*/
		$args['search_type'] = array();
		$args['post_fields'] = array();

		if ( $args['engine'] == 'regular' ) {
			if ( $sd['searchinterms'] == 1 ) $args['post_fields'][] = "terms";
			if ( $sd['searchintitle'] == 1 ) $args['post_fields'][] = 'title';
			if ( $sd['searchincontent'] == 1 ) $args['post_fields'][] = 'content';
			if ( $sd['searchinexcerpt'] == 1 ) $args['post_fields'][] = 'excerpt';
		} else {
			$args['post_fields'][] = "terms";
			if ( $it_options['it_index_title'] == 1 ) $args['post_fields'][] = 'title';
			if ( $it_options['it_index_content'] == 1 ) $args['post_fields'][] = 'content';
			if ( $it_options['it_index_excerpt'] == 1 ) $args['post_fields'][] = 'excerpt';
		}

		if ( is_array($o) ) {
			$frontend_generic_filters = array();
			foreach ( wd_asp()->front_filters->get('position', 'generic') as $filter ) {
				foreach ( $filter->get() as $item ) {
					$frontend_generic_filters[] = $item->value;
				}
			}
			$_original_post_fields = $args['post_fields'];
			$args['post_fields'] = array_diff($args['post_fields'], $frontend_generic_filters);
			if ( isset($o['asp_gen']) && is_array($o['asp_gen']) ) {
				if ( in_array(-1, $o['asp_gen']) ) {
					$args['post_fields'] = $_original_post_fields;
				} else {
					$args['post_fields'] = array_unique(array_merge($args['post_fields'], $o['asp_gen']));
				}
			}
		}

		if ( $sd['search_in_ids'] ) $args['post_fields'][] = "ids";
		if ( $sd['search_in_permalinks'] ) $args['post_fields'][] = "permalink";

		/*--------------------- CUSTOM FIELDS ---------------------------*/
		$args['post_custom_fields_all'] = $sd['search_all_cf'];
		$args['post_custom_fields'] = $sd['selected-customfields'] ?? array();

		if ( count($args['post_fields']) > 0 ||
			$args['post_custom_fields_all'] == 1 ||
			count($args['post_custom_fields']) > 0 ||
			count($args['post_type']) > 0
		)
			$args['search_type'][] = "cpt";

		// Are there any additional tags?
		if (
			count(wd_asp()->o['asp_glob']['additional_tag_posts']) > 0 &&
			!in_array('_asp_additional_tags', $args['post_custom_fields'])
		) {
			$args['post_custom_fields'][] = '_asp_additional_tags';
		}

		/*-------------------------- WPML -------------------------------*/
		if ( $sd['wpml_compatibility'] == 1 ) {
			if ( isset( $o['wpml_lang'] ) && $args['_ajax_search'] )
				$args['_wpml_lang'] = $o['wpml_lang'];
			elseif (
				defined('ICL_LANGUAGE_CODE')
				&& ICL_LANGUAGE_CODE != ''
				&& defined('ICL_SITEPRESS_VERSION')
			)
				$args['_wpml_lang'] = ICL_LANGUAGE_CODE;

			/**
			 * Switching the language will resolve issues with get_terms(...) and other functions
			 * Otherwise wrong taxonomy terms would be returned etc...
			 */
			global $sitepress;
			if ( is_object($sitepress) && method_exists($sitepress, 'switch_lang') ) {
				$sitepress->switch_lang($args['_wpml_lang']);
			}
		}

		/*-------------------- Content, Excerpt -------------------------*/
		$args['_post_get_content'] = (
			( $sd['showdescription'] == 1 ) ||
			( $sd['resultstype'] == "isotopic" && $sd['i_ifnoimage'] == 'description' ) ||
			( $sd['resultstype'] == "polaroid" && ($sd['pifnoimage'] == 'descinstead' || $sd['pshowdesc'] == 1) )
		);
		$args['_post_get_excerpt'] = (
			$sd['primary_titlefield'] == 1 ||
			$sd['secondary_titlefield'] == 1 ||
			$sd['primary_descriptionfield'] == 1 ||
			$sd['secondary_descriptionfield'] == 1
		);

		/*---------------------- Taxonomy Terms -------------------------*/
		$args['post_tax_filter'] = self::getTaxonomyArgs($sd, $o);

		/*--------------------------- Tags ------------------------------*/
		self::getTagArgs($sd, $o, $args);

		/*----------------------- Custom Fields -------------------------*/
		$args['post_meta_filter'] = self::getCustomFieldArgs($sd, $o);

		/*----------------------- Date Filters --------------------------*/
		$args['post_date_filter'] = self::getDateArgs($sd, $o);

		/*----------------------- User Filters --------------------------*/
		if ( count($sd['exclude_content_by_users']['users']) ) {
			foreach ($sd['exclude_content_by_users']['users'] as $uk => $uv) {
				if ( $uv == -2 )
					$sd['exclude_content_by_users']['users'][$uk] = get_current_user_id();
			}
			$args['post_user_filter'][$sd['exclude_content_by_users']['op_type']] = $sd['exclude_content_by_users']['users'];
		}

		/*---------------------- Selected blogs -------------------------*/
		$args['_selected_blogs'] = w_isset_def($sd['selected-blogs'], array(0 => get_current_blog_id()));
		if ($args['_selected_blogs'] === "all") {
			if (is_multisite())
				$args['_selected_blogs'] = wpdreams_get_blog_list(0, "all", true);
			else
				$args['_selected_blogs'] = array(0 => get_current_blog_id());
		}
		if (count($args['_selected_blogs']) <= 0) {
			$args['_selected_blogs'] = array(0 => get_current_blog_id());
		}

		// ----------------------------------------------------------------
		// 2. ATTACHMENTS
		// ----------------------------------------------------------------
		if ( $sd['return_attachments'] == 1 )
			$args['search_type'][] = "attachments";
		/*-------------------- Allowed mime types -----------------------*/
		if ( $sd['attachment_mime_types'] != "") {
			$args['attachment_mime_types'] = wpd_comma_separated_to_array($sd['attachment_mime_types']);
			foreach ($args['attachment_mime_types'] as $k => $v) {
				$args['attachment_mime_types'][$k] = trim($v);
			}

		}
		/*------------------------ Exclusions ---------------------------*/

		if ( $sd['attachment_exclude'] != '') {
			$args['attachment_exclude'] = explode(',', str_replace(' ', '', $sd['attachment_exclude']));
		}

		$args['attachments_use_index'] = $sd['attachments_use_index'] == 'index';
		$args['attachments_search_terms'] = $sd['search_attachments_terms'] == 1;
		$args['attachments_search_title'] = $sd['search_attachments_title'] == 1;
		$args['attachments_search_content'] = $sd['search_attachments_content'] == 1;
		$args['attachments_search_caption'] = $sd['search_attachments_caption'] == 1;
		$args['attachments_search_ids'] = $sd['search_attachments_ids'] == 1;
		$args['attachments_cf_filters'] = $sd['search_attachments_cf_filters'] == 1;
		$args['attachment_use_image'] = $sd['attachment_use_image'] == 1;
		$args['attachment_link_to'] = $sd['attachment_link_to'];
		$args['attachment_link_to_secondary'] = $sd['attachment_link_to_secondary'];
		$args['attachment_pdf_image'] = $sd['attachment_pdf_image'];

		// ----------------------------------------------------------------
		// 3. BLOGS
		// ----------------------------------------------------------------
		if ( $sd['searchinblogtitles'] == 1 )
			$args['search_type'][] = "blogs";

		// ----------------------------------------------------------------
		// 4. BUDDYPRESS
		// ----------------------------------------------------------------
		$args['bp_groups_search'] = $sd['search_in_bp_groups'] == 1;
		$args['bp_groups_search_public'] = $sd['search_in_bp_groups_public'] == 1;
		$args['bp_groups_search_private'] = $sd['search_in_bp_groups_private'] == 1;
		$args['bp_groups_search_hidden'] = $sd['search_in_bp_groups_hidden'] == 1;
		$args['bp_activities_search'] = $sd['search_in_bp_activities'] == 1;
		if ($args['bp_groups_search'] || $sd['search_in_bp_activities'])
			$args['search_type'][] = "buddypress";

		// ----------------------------------------------------------------
		// 5. COMMENTS
		// ----------------------------------------------------------------
		if ( $sd['searchincomments'] == 1 ) {
			$args['search_type'][] = "comments";
			$args['comments_search'] = true;
		}

		// ----------------------------------------------------------------
		// 6. TAXONOMY TERMS
		// ----------------------------------------------------------------
		$args['taxonomy_include'] = array();

		if ( $sd['return_categories'] == 1 ) $args['taxonomy_include'][] = "category";
		if ( $sd['return_tags'] == 1 ) $args['taxonomy_include'][] = "post_tag";
		if ( count(w_isset_def($sd['selected-return_terms'], array())) > 0 )
			$args['taxonomy_include'] = array_merge($args['taxonomy_include'], $sd['selected-return_terms']);
		$args['taxonomy_terms_exclude'] = $sd['return_terms_exclude'];     // terms to exclude by ID
		$args['taxonomy_terms_exclude_empty'] = $sd['return_terms_exclude_empty'];     // exclude empty taxonomy terms
		if ( count($args['taxonomy_include']) > 0 )
			$args['search_type'][] = "taxonomies";
		$args['taxonomy_terms_search_titles'] = $sd['search_term_titles'] == 1;
		$args['taxonomy_terms_search_description'] = $sd['search_term_descriptions'] == 1;
		$args['taxonomy_terms_search_term_meta'] = $sd['search_term_meta'] == 1;

		// ----------------------------------------------------------------
		// 7. USERS results
		// ----------------------------------------------------------------
		if ( $sd['user_search'] == 1 )
			$args['search_type'][] = "users";
		$args['user_login_search'] = $sd['user_login_search'];
		$args['user_display_name_search'] = $sd['user_display_name_search'];
		$args['user_first_name_search'] = $sd['user_first_name_search'];
		$args['user_last_name_search'] = $sd['user_last_name_search'];
		$args['user_bio_search'] = $sd['user_bio_search'];
		$args['user_email_search'] = $sd['user_email_search'];
		$args['user_search_meta_fields'] = $sd['user_search_meta_fields'];
		$args['user_search_bp_fields'] = w_isset_def( $sd['selected-user_bp_fields'], array() );
		$args['user_search_exclude_roles'] = w_isset_def( $sd['selected-user_search_exclude_roles'], array() );
		if ( count($sd['user_search_exclude_users']['users']) ) {
			foreach ($sd['user_search_exclude_users']['users'] as $uk => $uv) {
				if ( $uv == -2 )
					$sd['user_search_exclude_users']['users'][$uk] = get_current_user_id();
			}
			$args['user_search_exclude'][$sd['user_search_exclude_users']['op_type']] = $sd['user_search_exclude_users']['users'];
		}
		$args['user_login_search'] = $sd['user_login_search'];
		$args['user_display_name_search'] = $sd['user_display_name_search'];
		$args['user_first_name_search'] = $sd['user_first_name_search'];
		$args['user_last_name_search'] = $sd['user_last_name_search'];
		$args['user_bio_search'] = $sd['user_bio_search'];
		$args['user_email_search'] = $sd['user_email_search'];
		$args['user_search_meta_fields'] = $sd['user_search_meta_fields'];
		$args['user_search_bp_fields'] = w_isset_def( $sd['selected-user_bp_fields'], array() );
		$args['user_search_exclude_roles'] = w_isset_def( $sd['selected-user_search_exclude_roles'], array() );
		if ( count($sd['user_search_exclude_users']['users']) ) {
			foreach ($sd['user_search_exclude_users']['users'] as $uk => $uv) {
				if ( $uv == -2 )
					$sd['user_search_exclude_users']['users'][$uk] = get_current_user_id();
			}
			$args['user_search_exclude'][$sd['user_search_exclude_users']['op_type']] = $sd['user_search_exclude_users']['users'];
		}
		$args['user_meta_filter'] = self::getCustomFieldArgs($sd, $o, 'usermeta');
		/*------------------- Meta key order for user search -----------------*/
		$args['user_primary_order'] = $sd['user_orderby_primary'];
		$args['user_secondary_order'] = $sd['user_orderby_secondary'];
		if ( strpos($sd['user_orderby_primary'], 'customfp') !== false ) {
			if ( !empty($sd['user_orderby_primary_cf']) ) {
				$args['_user_primary_order_metakey'] = $sd['user_orderby_primary_cf'];
				$args['user_primary_order_metatype'] = $sd['user_orderby_primary_cf_type'];
			}
		}
		if ( strpos($sd['user_orderby_secondary'], 'customfs') !== false ) {
			if ( !empty($sd['user_orderby_secondary_cf']) ) {
				$args['_user_secondary_order_metakey'] = $sd['user_orderby_secondary_cf'];
				$args['user_secondary_order_metatype'] = $sd['user_orderby_secondary_cf_type'];
			}
		}
		// ----------------------------------------------------------------

		/*-------------------- FORCE CORRECT ORDERING -------------------*/
		$correct_cpt_orders = array(
			'relevance DESC', 'post_title DESC', 'post_title ASC', 'post_date DESC', 'post_date ASC', 'RAND()',
			'id DESC', 'id ASC',
			'customfp DESC', 'customfp ASC', 'customfs DESC', 'customfs ASC',
			'menu_order DESC', 'menu_order ASC'
		);
		$correct_user_orders = array(
			'relevance DESC', 'title DESC', 'title ASC', 'date DESC', 'date ASC', 'RAND()',
			'id DESC', 'id ASC',
			'customfp DESC', 'customfp ASC', 'customfs DESC', 'customfs ASC',
			'menu_order DESC', 'menu_order ASC'
		);
		if ( !in_array($args["post_primary_order"], $correct_cpt_orders) )
			$args["post_primary_order"] = 'relevance DESC';
		if ( !in_array($args["post_secondary_order"], $correct_cpt_orders) )
			$args["post_secondary_order"] = 'date DESC';
		if ( !in_array($args["user_primary_order"], $correct_user_orders) )
			$args["user_primary_order"] = 'relevance DESC';
		if ( !in_array($args["user_secondary_order"], $correct_user_orders) )
			$args["user_secondary_order"] = 'date DESC';

		// ----------------------------------------------------------------
		// 8. Peepso Groups & Activities
		// ----------------------------------------------------------------
		if (
			($sd['peep_gs_public'] == 1 || $sd['peep_gs_closed'] == 1 || $sd['peep_gs_secret'] == 1) &&
			($sd['peep_gs_title'] == 1 || $sd['peep_gs_content'] == 1 || $sd['peep_gs_categories'] == 1)
		) {
			$args['search_type'][] = "peepso_groups";
			if ( $sd['peep_gs_public'] == 1 )
				$args['peepso_group_privacy'][] = 0;
			if ( $sd['peep_gs_closed'] == 1 )
				$args['peepso_group_privacy'][] = 1;
			if ( $sd['peep_gs_secret'] == 1 )
				$args['peepso_group_privacy'][] = 2;
			if ( $sd['peep_gs_title'] == 1 )
				$args['peepso_group_fields'][] = 'title';
			if ( $sd['peep_gs_content'] == 1 )
				$args['peepso_group_fields'][] = 'content';
			if ( $sd['peep_gs_categories'] == 1 )
				$args['peepso_group_fields'][] = 'categories';

			$args['peepso_group_not_in'] = wd_explode(',', $sd['peep_gs_exclude']);
			$args['peepso_groups_limit'] = $sd['peepso_groups_limit'];
			$args['peepso_groups_limit_override'] = $sd['peepso_groups_limit_override'];
		}

		if ( $sd['peep_s_posts'] == 1 || $sd['peep_s_comments'] == 1 ) {
			$args['search_type'][] = "peepso_activities";
			$args['peepso_activity_types'] = array();
			if ( $sd['peep_s_posts'] == 1)
				$args['peepso_activity_types'][] = 'peepso-post';
			if ( $sd['peep_s_comments'] == 1)
				$args['peepso_activity_types'][] = 'peepso-comment';

			$args['peepso_activity_follow'] = $sd['peep_pc_follow'];

			if ( $sd['peep_pc_public'] == 1 )
				$args['peepso_group_activity_privacy'][] = 0;
			if ( $sd['peep_pc_closed'] == 1 )
				$args['peepso_group_activity_privacy'][] = 1;
			if ( $sd['peep_pc_secret'] == 1 )
				$args['peepso_group_activity_privacy'][] = 2;

			$args['peepso_activities_limit'] = $sd['peepso_activities_limit'];
			$args['peepso_activities_limit_override'] = $sd['peepso_activities_limit_override'];
		}
		// ----------------------------------------------------------------

		// ----------------------------------------------------------------
		// 9. Content Type filters
		// ---------------------------------------------------------------
		if ( $o !== false )
			self::getContentTypeArgs($sd, $o, $args);

		// ----------------------------------------------------------------
		// 10. INDEX TABLE SEARCH
		// ---------------------------------------------------------------
		if ( $it_options['it_pool_size_auto'] ) {
			$s_pool_s = Manager::suggestPoolSizes();
			$args['it_pool_size_one'] = $s_pool_s['one'];
			$args['it_pool_size_two'] = $s_pool_s['two'];
			$args['it_pool_size_three'] = $s_pool_s['three'];
			$args['it_pool_size_rest'] = $s_pool_s['rest'];
		} else {
			$args['it_pool_size_one'] = $it_options['it_pool_size_one'];
			$args['it_pool_size_two'] = $it_options['it_pool_size_two'];
			$args['it_pool_size_three'] = $it_options['it_pool_size_three'];
			$args['it_pool_size_rest'] = $it_options['it_pool_size_rest'];
		}

		// ----------------------------------------------------------------
		// X. MISC FIXES
		// ----------------------------------------------------------------
		$args['_show_more_results'] = $sd['showmoreresults'] == 1;
		$args["woo_currency"] = $o['woo_currency'] ?? (function_exists('get_woocommerce_currency') ? get_woocommerce_currency() : '');
		$args['_page_id'] = $o['current_page_id'] ?? $args['_page_id'];
		$args['_is_autopopulate'] = isset($_POST['autop']);

		if ( $sd['groupby_cpt_title'] == 1 && $args['cpt_query']['groupby'] == '' ) {
			$args['cpt_query']['groupby'] = "title";
		}
		if ( $sd['groupby_term_title'] == 1 && $args['term_query']['groupby'] == '' ) {
			$args['term_query']['groupby'] = 'title';
		}
		if ( $sd['groupby_user_title'] == 1 && $args['user_query']['groupby'] == '' ) {
			$args['user_query']['groupby'] = 'title';
		}
		if ( $sd['groupby_attachment_title'] == 1 && $args['attachment_query']['groupby'] == '' ) {
			$args['attachment_query']['groupby'] = 'title';
		}
		// ----------------------------------------------------------------
		return $args;
	}

	/**
	 * Converts search data and options to Taxonomy Term query argument arrays to use with SearchQuery
	 *
	 * @param $sd
	 * @param $o
	 * @return array
	 */
	private static function getTaxonomyArgs($sd, $o): array {
		$ret = array();

		if ( ! isset( $o['termset'] ) || $o['termset'] == "" )
			$o['termset'] = array();

		$taxonomies = array();

		// Excluded by terms (advanced option -> exclude results)
		$sd_exclude = array();
		foreach($sd['exclude_by_terms']['terms'] as $t) {
			if ( !in_array($t['taxonomy'], $taxonomies) )
				$taxonomies[] = $t['taxonomy'];

			if ( !isset($sd_exclude[$t['taxonomy']]) )
				$sd_exclude[$t['taxonomy']] = array();
			if ($t['id'] == -1) {
				$tt_terms = get_terms($t['taxonomy'], array(
					'hide_empty' => false,
					'fields' => 'ids'
				));
				if ( !is_wp_error($tt_terms) ) {
					$sd_exclude[$t['taxonomy']] = $tt_terms;
				}
			} else {
				$sd_exclude[$t['taxonomy']][] = $t['id'];
			}
		}

		// Include by terms (advanced option -> exclude results)
		$sd_include = array();
		foreach($sd['include_by_terms']['terms'] as $t) {
			if ( !in_array($t['taxonomy'], $taxonomies) )
				$taxonomies[] = $t['taxonomy'];

			if ( !isset($sd_include[$t['taxonomy']]) )
				$sd_include[$t['taxonomy']] = array();
			if ($t['id'] == -1) {
				$tt_terms = get_terms($t['taxonomy'], array(
					'hide_empty' => false,
					'fields' => 'ids'
				));
				if ( !is_wp_error($tt_terms) ) {
					$sd_include[$t['taxonomy']] = $tt_terms;
				}
			} else {
				$sd_include[$t['taxonomy']][] = $t['id'];
			}
		}

		if ( count( $sd['show_terms']['terms'] ) > 0 ||
			count( $sd_exclude ) > 0 ||
			count( $sd_include ) > 0 ||
			count( $o['termset'] ) > 0 ||
			isset($o['termset_single']) > 0
		) {
			// If the term settings are invisible, ignore the excluded frontend terms, reset to empty array
			if ( $sd['showsearchintaxonomies'] == 0 ) {
				$sd['show_terms']['terms'] = array();
			}

			// First task is to get any taxonomy related
			foreach ($sd['show_terms']['terms'] as $t)
				if ( !in_array($t['taxonomy'], $taxonomies) )
					$taxonomies[] = $t['taxonomy'];
			if ( count($o['termset']) ) {
				foreach ($o['termset'] as $taxonomy => $terms)
					if ( !in_array($taxonomy, $taxonomies) )
						$taxonomies[] = $taxonomy;
			}

			foreach ($taxonomies as $taxonomy) {
				// If no value is selected, transform it to an array
				$option_set = $o['termset_single'] ?? (!isset($o['termset'][$taxonomy]) ? array() : $o['termset'][$taxonomy]);

				// If radio or drop is selected, convert it to array
				$option_set =
					!is_array( $option_set ) ? array( $option_set ) : $option_set;

				if (
					count( $option_set ) == 1 && in_array(-1, $option_set) && // Is it the "Choose one" option?
					count( $sd_exclude ) == 0   // ...and there are no exclusions
				) {
					continue;
				} else {
					if ( count($option_set) == 1 && in_array(-1, $option_set) )
						$option_set = array(); // Reset the options to empty, as '-1' won't work
				}

				$display_mode = 'checkboxes';
				$term_logic = $sd['term_logic'];
				$is_checkboxes = true;
				// If not the checkboxes are used, and there is no forced inclusion, temporary force the OR logic
				if ( count($sd['show_terms']['display_mode']) > 0 ) {
					if (isset($sd['show_terms']['display_mode'][$taxonomy])) {
						if ( $sd['show_terms']['display_mode'][$taxonomy]['type'] != "checkboxes" ) {
							//$term_logic = "or";
							$display_mode = $sd['show_terms']['display_mode'][$taxonomy]['type'];
							$is_checkboxes = false;
						}
					}
				}

				// Gather the API terms
				$api_terms = array();
				$override_allow_empty = '';
				foreach ( wd_asp()->front_filters->get('position', 'taxonomy') as $filter ) {
					if ( $filter->is_api && $filter->data['taxonomy'] == $taxonomy ) {
						//if ( $filter->data['taxonomy'] == $taxonomy ) {
						$fterms = $filter->get();
						foreach ( $fterms as $fterm ) {
							if ( $fterm->id > 0 )
								$api_terms[] = $fterm->id;
						}
						if ( count($api_terms) > 0 ) {
							$is_checkboxes = $filter->display_mode == 'checkboxes';
							if ( $filter->data['logic'] !== '' ) {
								$term_logic = strtolower( $filter->data['logic'] );
							}
							if ( $filter->data['allow_empty'] !== '' ) {
								$override_allow_empty = $filter->data['allow_empty'];
							}
						}
					}
				}
				// Check if the term filters are visible for the user?
				$term_filters_visible = count( $sd['show_terms']['terms'] ) > 0 || count($api_terms) > 0;

				// If the term settings are invisible,
				// ...or nothing is chosen, but filters are active
				// ignore the excluded frontend terms, reset to empty array
				$exclude_showterms = array();
				if (
					$term_filters_visible && ($sd['frontend_terms_ignore_empty'] != 1 || !empty($option_set)) && $is_checkboxes
				) {
					foreach ($sd['show_terms']['terms'] as $t) {
						if ( $t['taxonomy'] == $taxonomy ) {
							if ( $t['id'] == -1 ) {
								$t_terms = get_terms($taxonomy, array(
									'hide_empty' => false,
									'fields' => 'ids',
									'exclude' => $t["ex_ids"]
								));
								if ( $taxonomy == 'post_format' )
									$t_terms[] = -200;
								if ( !is_wp_error($t_terms) )
									$exclude_showterms = array_merge($exclude_showterms, $t_terms);
							} else {
								$exclude_showterms[] = $t['id'];
							}
						}
					}
				}
				$exclude_showterms = array_unique( array_merge($exclude_showterms, $api_terms) );
				$exclude_t = $sd_exclude[$taxonomy] ?? array();

				// Force logic for checkbox and single drop-downs
				if ( $display_mode == 'dropdown' || $display_mode == 'dropdownsearch' || $display_mode == 'radio' ) {
					$term_logic = 'or';
				}

				$include_terms = array();
				$exclude_terms = array();

				/*
				 AND -> Posts NOT in an array of term ids
				 OR  -> Posts in an array of term ids
				*/
				if ( $term_logic == 'and' ) {
					if ( $is_checkboxes || !$term_filters_visible ) {
						$include_terms = array();
					} else {
						if ( $sd['frontend_terms_ignore_empty'] == 1 && empty($option_set) ) {
							$include_terms = array();
						} else {
							$include_terms = count($option_set) == 0 ? array(-10) : $option_set;
							if ( count($option_set) > 0 )
								$term_logic = 'andex';
						}
					}

					if ( $term_filters_visible ) {
						$exclude_terms = array_diff( array_merge($exclude_t, $exclude_showterms) , $option_set );
						/*if ( count($option_set) > 0 && count($exclude_terms) == 0 )
							$exclude_terms = array(-11);*/
					} else {
						$exclude_terms = $exclude_t;
					}
				} else if ( $term_logic == 'or' || $term_logic == 'andex' ) {
					$exclude_terms = $exclude_t;

					if ( $term_filters_visible ) {
						if ( $sd['frontend_terms_ignore_empty'] == 1 && empty($option_set) )
							$include_terms = array();
						else
							$include_terms = count( $option_set ) == 0 ? array( -10 ) : $option_set;
					} else {
						$include_terms = array();
					}
				}

				// Manage inclusions from the back-end
				if ( isset($sd_include[$taxonomy]) && count($sd_include[$taxonomy]) > 0 )
					$include_terms = array_unique( array_merge($include_terms, $sd_include[$taxonomy]) );

				if ( !empty($include_terms) || !empty($exclude_terms) ) {
					$add = array(
						'taxonomy' => $taxonomy,
						'include'  => $include_terms ?? array(),
						'exclude'  => $exclude_terms ?? array(),
						'logic'    => $term_logic,
						'_termset' => $o['termset'][$taxonomy] ?? array(),
						'_is_checkbox' => $is_checkboxes
					);
					if ( $override_allow_empty !== '' ) {
						$add['allow_empty'] = $override_allow_empty;
					}
					$ret[] = $add;
				}
			}
		}
		return $ret;
	}

	/**
	 * Converts search data and options to Tag query argument arrays to use with SearchQuery
	 *
	 * @param $sd
	 * @param $o
	 * @param $args
	 */
	private static function getTagArgs($sd, $o, &$args) {

		// Get the tag options, by default the active param is enough, as it is disabled.
		$st_options = w_isset_def( $sd["selected-show_frontend_tags"], array("active" => 0) );
		$tag_logic = $sd["frontend_tags_logic"];
		$no_tags_exist = false;
		$exc_tags = w_isset_def( $sd['selected-exclude_post_tags'], array() );

		$args['_post_tags_active'] = $st_options['active'];
		$args['_post_tags_logic'] = $sd["frontend_tags_logic"];
		$args['_post_tags_empty'] = $sd['frontend_tags_empty'];

		$exclude_tags = array();
		$include_tags = array();

		if ( ($st_options['active'] == 1) || count($exc_tags) > 0) {
			// If no value is selected, transform it to an array
			$o['post_tag_set'] = !isset($o['post_tag_set']) ? array() : $o['post_tag_set'];
			// If radio or drop is selected, convert it to array
			$o['post_tag_set'] =
				!is_array( $o['post_tag_set'] ) ? array( $o['post_tag_set'] ) : $o['post_tag_set'];

			// Is this the "All" option?
			if (
				count($o['post_tag_set']) == 1 && (in_array(-1, $o['post_tag_set']) || in_array(0, $o['post_tag_set'])) ||
				( $sd['frontend_terms_ignore_empty'] == 1 && empty($o['post_tag_set']) )
			) {
				if ( count($exc_tags) > 0 )
					$args['post_tax_filter'][] = array(
						"taxonomy" => 'post_tag',
						"include"  => $include_tags,
						"exclude"  => $exc_tags,
						'allow_empty' => true // Needs to be allowed, as otherwise posts with no tags will be hidden
					);
				return;
			}

			// If not the checkboxes are used, force the OR logic
			if ($st_options['display_mode'] != "checkboxes")
				$tag_logic = "or";

			if ($st_options['source'] == "all") {
				// Limit all tags to 500. I mean that should be more than enough...
				$exclude_showtags = get_terms("post_tag", array("number"=>400, "fields"=>"ids"));
				if ( is_wp_error($exclude_showtags) || count($exclude_showtags) == 0)
					$no_tags_exist = true;
			} else {
				$exclude_showtags = $st_options['tag_ids'];
			}

			/*
			 AND -> Posts NOT in an array of term ids
			 OR  -> Posts in an array of term ids
			*/
			if ( $tag_logic == 'and' ) {
				if ( $st_options['active'] == 1 ) {
					$exclude_tags = array_diff( array_merge($exc_tags, $exclude_showtags) , $o['post_tag_set'] );
				} else {
					$exclude_tags = $exc_tags;
				}

			} else {
				$exclude_tags = $exc_tags;

				// If there are no tags at all, then show all posts, because no filtering is required
				if ($no_tags_exist) {
					$include_tags = count($o['post_tag_set']) == 0 ? array() : $o['post_tag_set'];
				} else {
					if ( $st_options['active'] == 1 ) {
						$include_tags = count( $o['post_tag_set'] ) == 0 ? array( -10 ) : $o['post_tag_set'];
					} else {
						$include_tags = array();
					}
				}

			}
		}

		$args['_post_tags_exclude'] = $exclude_tags;
		$args['_post_tags_include'] = $include_tags;

		/**
		 * @since 4.10
		 * Append to post tax filter to use, instead of separate query
		 */
		if ( count($exclude_tags) > 0 || count($include_tags) > 0 ) {
			$args['post_tax_filter'][] = array(
				"taxonomy" => 'post_tag',
				"include" => $include_tags,
				"exclude" => $exclude_tags,
				"logic" => $tag_logic,
				'_termset' => $o['post_tag_set'],
				'_is_checkbox' => $st_options['display_mode'] == "checkboxes"
			);
		}
	}

	/**
	 * Converts search data and options to Custom Field query argument arrays to use with SearchQuery
	 *
	 * @param $sd
	 * @param $o
	 * @param string $source 'all', 'postmeta', 'usermeta' to get meta sources
	 * @return array
	 * @noinspection PhpUnusedParameterInspection
	 */
	private static function getCustomFieldArgs($sd, $o, string $source = 'postmeta'): array {

		$meta_fields = array();

		if ( isset( $o['aspf'] ) ) {

			foreach ( wd_asp()->front_filters->get('position', 'custom_field') as $filter ) {

				if ( $source != 'all' && $filter->data['source'] != $source ) {
					continue;
				}

				/** @noinspection PhpPossiblePolymorphicInvocationInspection */
				$unique_field = $filter->getUniqueFieldName( true );

				// Field is missing, continue
				if ( !isset($o['aspf'][ $unique_field ]) ) {
					continue;
				}

				$posted = Str::escape( $o['aspf'][ $unique_field ] );
				// Select2 script issue - empty item will not show up in select2, so this fake value is used
				if ( $posted == "__any__" ) {
					$posted = "";
				}

				// NULL (empty) values accept
				if ( $posted == "" && $filter->type() != 'hidden' ) continue;

				// Manage the posted values first, in case these are multiple values
				if ( is_array($posted) ) {
					// The order is important for the range slider
					if ( isset($posted['lower'], $posted['upper']) ) {
						$posted = array($posted['lower'], $posted['upper']);
					} else {
						$posted = array_values($posted);
					}
					$add_posted = array();
					foreach ( $posted as $pk => $pv ) {
						// Multiple values passed separated by :: -> convert them to array
						if ( strpos($pv, '::') !== false ) {
							$pv = explode('::', $pv);
							foreach ( $pv as &$vv )
								$vv = trim($vv);
							if ( !empty($pv) ) {
								// Memorize the new values, and remove the old
								$add_posted = array_merge($add_posted, $pv);
								unset($posted[$pk]);
							}
						}
					}
					// Old values were removed, add the new ones to the old array
					if ( count($add_posted) > 0 ) {
						$posted = array_unique( array_merge($posted, $add_posted) );
					}
				} else if ( is_string($posted) ) {
					// Multiple values passed separated by :: -> convert them to array
					if ( strpos($posted, '::') !== false ) {
						$posted = explode('::', $posted);
						foreach ( $posted as &$vv )
							$vv = trim($vv);
					}
				}

				if ( isset( $filter->data['operator'] ) ) {
					switch ( strtolower($filter->data['operator']) ) {
						case 'neq':
						case '<>':
							$operator = "<>";
							$posted   = Str::forceNumeric( $posted );
							break;
						case 'lt':
						case '<':
							$operator = "<";
							$posted   = Str::forceNumeric( $posted );
							break;
						case 'let':
						case '<=':
							$operator = "<=";
							$posted   = Str::forceNumeric( $posted );
							break;
						case 'gt':
						case '>':
							$operator = ">";
							$posted   = Str::forceNumeric( $posted );
							break;
						case 'get':
						case '>=':
							$operator = ">=";
							$posted   = Str::forceNumeric( $posted );
							break;
						case 'between':
							$operator = is_array($posted) ? "BETWEEN" : '=';
							if ( is_array($posted) && isset($posted[0], $posted[1]) ) {
								if ( $posted[0] == '' && $posted[1] == '' ) {
									continue 2;
								} else if ( $posted[0] == '' ) {
									$operator = "<=";
									$posted = $posted[1];
								} else if ( $posted[1] == '' ) {
									$operator = ">=";
									$posted = $posted[0];
								}
							}
							$posted   = Str::forceNumeric( $posted );
							break;
						case 'not elike':
							$operator = "NOT ELIKE";
							break;
						case 'elike':
							$operator = "ELIKE";
							break;
						case 'not like':
							$operator = "NOT LIKE";
							break;
						case 'like':
							$operator = "LIKE";
							break;
						case 'in':
							$operator = "IN";
							break;
						case 'match':
							$operator = "=";
							break;
						case 'nomatch':
							$operator = "<>";
							break;
						case 'before':
							$operator = "<";
							break;
						case 'before_inc':
							$operator = "<=";
							break;
						case 'after':
							$operator = ">";
							break;
						case 'after_inc':
							$operator = ">=";
							break;
						default:
							$operator = "=";
							$posted   = Str::forceNumeric( $posted );
							break;
					}
				} else {
					$operator = "=";
					$posted   = Str::forceNumeric( $posted );
				}

				if ( $filter->display_mode == 'range' ) {
					$operator = "BETWEEN";
				}

				if ( $filter->display_mode == 'range' || $filter->display_mode == 'slider') {
					$posted = Str::forceNumeric($posted);
				}

				if ( $filter->display_mode == "datepicker" ) {

					switch ( w_isset_def($filter->data['date_store_format'], "acf") ) {
						case 'datetime':
							/**
							 * Format YY MM DD is accepted by strtotime as ISO8601 Notation
							 * http://php.net/manual/en/datetime.formats.date.php
							 */
							$posted = strtotime($posted, time());
							$posted = date("Y-m-d", $posted);
							$operator = "datetime ".$operator;
							break;
						case 'timestamp':
							$posted   = strtotime($posted, time());
							$operator = "timestamp ".$operator;
							break;
						default:
							/**
							 * This is ACF aka. yymmdd aka. 20170101
							 * ...so the operators need to be adjusted in cases of < and > to <= and >=
							 **/
							$posted = preg_replace("/[^0-9]/",'',$posted);
							break;
					}
				}

				// Simplifications
				if ( is_array($posted) && count($posted) > 2 ) {
					if ( $operator == '=' ) {
						$operator = 'IN';
					}
				}

				// Data type for JSON serialized objects - like ACF storage
				if (
					$filter->data['acf_type'] !== false
				) {
					if (
						in_array($filter->data['acf_type'], array('multiselect', 'checkbox')) &&
						in_array($operator, array('ELIKE', 'NOT ELIKE', 'IN'))
					) {
						$operator = str_replace('ELIKE', 'LIKE', $operator);
						if ( is_array($posted) ) {
							foreach ( $posted as &$pv ) {
								$pv = ':"' . $pv . '";';
							}
							unset($pv);
						} else {
							$posted = ':"' . $posted . '";';
						}
					}

					if ( $filter->data['acf_type'] == 'post_object' ) {
						$operator = 'ACF_POST_OBJECT';
					}
				}

				$arr = array(
					'key'     => $filter->data['field'],
					'value'   => $posted,
					'operator'=> $operator,
					'logic_and_separate_custom_fields' => $filter->data['logic_and_separate_custom_fields']
				);
				if ( !empty($filter->data['logic']) )
					$arr['logic'] = strtolower( trim($filter->data['logic']) ) == 'and' ? 'AND' : 'OR';
				$meta_fields[] = $arr;
			}
		}
		return $meta_fields;
	}

	/**
	 * Converts search data and options to Date query argument arrays to use with SearchQuery
	 *
	 * @param $sd
	 * @param $o
	 * @return array
	 */
	private static function getDateArgs($sd, $o): array {
		$date_parts = array();
		if ($sd['exclude_dates_on'] == 1) {
			$exc_dates = &$sd['selected-exclude_dates'];
			if ($exc_dates['from'] != "disabled") {
				if ( $exc_dates['from'] == "date" ) {
					$exc_from_d = $exc_dates["fromDate"];
				} else {
					$exc_from_d = date(
						"y-m-d",
						strtotime(" ".(-1) * $exc_dates['fromInt'][0]." year ".(-1) * $exc_dates['fromInt'][1]." month ".(-1) * $exc_dates['fromInt'][2]." day",
							time())
					);
				}
				$date_parts[] = array(
					'date'     => $exc_from_d,
					'operator' => $exc_dates['mode'],
					'interval' => 'after'
				);
			}
			if ($exc_dates['to'] != "disabled") {
				if ( $exc_dates['to'] == "date" ) {
					$exc_to_d = $exc_dates["toDate"];
				} else {
					$exc_to_d = date(
						"y-m-d",
						strtotime(" ".(-1) * $exc_dates['toInt'][0]." year ".(-1) * $exc_dates['toInt'][1]." month ".(-1) * $exc_dates['toInt'][2]." day",
							time())
					);
				}
				$date_parts[] = array(
					'date'     => $exc_to_d,
					'operator' => $exc_dates['mode'],
					'interval' => 'before'
				);
			}
		}

		// Filters from front-end
		if ( !empty($o['post_date_from']) && Str::checkDate($o['post_date_from']) ) {
			//preg_match("/(\d+)\-(\d+)\-(\d+)$/", $o['post_date_from'], $m);
			$date_parts[] = array(
				'date'     => $o['post_date_from'],
				'operator' => 'include',
				'interval' => 'after'
			);
		}

		if ( !empty($o['post_date_to'])  && Str::checkDate($o['post_date_to']) ) {
			//preg_match("/(\d+)\-(\d+)\-(\d+)$/", $o['post_date_to'], $m);
			$date_parts[] = array(
				'date'     => $o['post_date_to'],
				'operator' => 'include',
				'interval' => 'before'
			);
		}

		return $date_parts;
	}


	private static function getContentTypeArgs($sd, $o, &$args) {
		$ctf = $sd['content_type_filter'];
		if ( count($ctf['selected']) > 0 ) {
			$o['asp_ctf'] = !isset($o['asp_ctf']) ? array() : $o['asp_ctf'];
			$o['asp_ctf'] = !is_array($o['asp_ctf']) ? array($o['asp_ctf']) : $o['asp_ctf'];
			if ( $ctf['display_mode'] == 'checkboxes' ) {
				$unchecked = array_diff($ctf['selected'], $o['asp_ctf']);
				$args['search_type'] = array_diff($args['search_type'], $unchecked);
			} else {
				// Only if 'Choose any' is not selected
				if ( !in_array(-1, $o['asp_ctf']) )
					$args['search_type'] = array_intersect($args['search_type'], $o['asp_ctf']);
			}
		}
	}
}