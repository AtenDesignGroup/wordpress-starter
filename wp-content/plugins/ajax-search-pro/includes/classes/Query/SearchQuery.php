<?php
namespace WPDRMS\ASP\Query;

use WPDRMS\ASP\Misc\PriorityGroups;
use WPDRMS\ASP\Index as Index;
use WPDRMS\ASP\Misc\Statistics;
use WPDRMS\ASP\Search\SearchAttachments;
use WPDRMS\ASP\Search\SearchBlogs;
use WPDRMS\ASP\Search\SearchBuddyPress;
use WPDRMS\ASP\Search\SearchComments;
use WPDRMS\ASP\Search\SearchIndex;
use WPDRMS\ASP\Search\SearchPeepsoActivities;
use WPDRMS\ASP\Search\SearchPeepsoGroups;
use WPDRMS\ASP\Search\SearchPostTypes;
use WPDRMS\ASP\Search\SearchTaxonomyTerms;
use WPDRMS\ASP\Search\SearchUsers;
use WPDRMS\ASP\Suggest\KeywordSuggest;
use WPDRMS\ASP\Utils\MB;
use WPDRMS\ASP\Utils\Str;

defined('ABSPATH') or die("You can't access this file directly.");


class SearchQuery {
	/*
	 * Results array
	 */
	public $posts;

	/*
	 * All Results array, regardless of current page
	 */
	public $all_results;

	/**
	 * The real overall results count
	 *
	 * @var int
	 */
	public $found_posts = 0;

	/**
	 * The returned number of results for this stack
	 *
	 * @var int
	 */
	public $returned_posts = 0;

	/**
	 * The options passed from the search form, when requested from the front-end
	 *
	 * @var array
	 */
	public $options = array();

	/**
	 * Default query parameter values
	 *
	 * @var array
	 */
	public static $defaults = array(
		// ----------------------------------------------------------------
		// 1. GENERIC arguments
		// ----------------------------------------------------------------
		's' => '',                  // search query
		/**
		 *  @param string|array search_type
		 *      cpt -> posts, pages, custom post types
		 *      taxonomies -> tags, categories and taxonomy terms based on taxonomy slug
		 *      users -> users
		 *      blogs -> multisite blog titles
		 *      buddypress -> buddypress groups or activities
		 *      comments -> comment results
		 *      attachments -> file attachments
		 */
		'search_type' => 'cpt',
		'engine' => 'regular',      // regular|index -> index only used on cpt
		'posts_per_page' =>  0,     // posts per page, for non ajax requests only. If 0, then get_option(posts_per_page) is used
		'page' => 1,                // which page of results, starts from 1
		'keyword_logic' => 'OR',    // OR|AND|OREX|ANDEX
		'secondary_logic' => '',    // OR|AND|OREX|none or empty string
		'min_word_length' => 2,     // Minimum word length of each word to be considered as a standalone word in the phrase (removed if shorter)
		// ----------------------------------------------------------------

		// ----------------------------------------------------------------
		// 2. POST and CUSTOM POST TYPE related arguments
		// ----------------------------------------------------------------
		'post_type' => array('post', 'page'),       // post types to look for
		'post_status' => array('publish'),          // post statuses
		'has_password' => false,                    // password protected
		'post_fields' => array(                     // post fields to search within
			// (title, content, excerpt, terms, permalink)
			'title', 'ids', 'excerpt', 'terms'
		),
		'post_custom_fields_all' => 0,              // search all custom fields
		'post_custom_fields' => array(),            // ...or search within custom fields
		'post_in' => array(),                       // string|array -> limit potential results pool to array of IDs
		'post_not_in' => array(),                   // string|array -> explicity exclude IDs from search results
		'post_not_in2' => array(),                  // array -> secondary exclusion for manual override
		'post_parent' => array(),                   // array -> post parent IDs
		'post_parent_exclude' => array(),           // array -> post parent IDs
		'post_tax_filter' => array(                 // taxonomy filter support
			/*
			array(
				'taxonomy'    => 'category',          // taxonomy name
				'include'     => array(1, 2, 3, 4),   // array of taxonomy term IDs to include
				'exclude'     => array(5, 6, 7, 8),   // array of taxonomy term IDs to exclude
				'allow_empty' => false                // allow (empty) items with no connection to any of the taxonomy terms filter
			)
			*/
		),
		'post_meta_filter' => array(      // meta_query support
			/*
			array(
				'key'     => 'age',         // meta key
				'value'   => array( 3, 4 ), // int|float|string|array|timestamp|datetime
				 // @param string|array compare
				 // Numeric Operators
				 //      '<' -> less than
				 //      '>' -> more than
				 //      '<>' -> not equals
				 //      '=' -> equals
				 //      'BETWEEN' -> between two values
				 // String Operators
				 //      'LIKE'
				 //      'NOT LIKE'
				 //      'IN'
				'operator' => 'BETWEEN',
				'allow_missing' => false   // allow match if this custom field is unset
			)
			*/
		),
		'post_date_filter' => array(        // date_query support
			/*
			array(
				'year'  => 2015,            // year, month, day ...
				'month' => 6,
				'day'   => 1,
				'date'  => "2015-06-01",     // .. or date parameter in y-m-d format
				'operator' => 'include',    // include|exclude
				'interval' => 'before'      // before|after
			)
			*/
		),
		'post_user_filter' => array(
			/*
			'include' => (1, 2, 3, 4),  // include by IDs
			'exclude' => (5, 6, 7, 8)   // exclude by IDs
			*/
		),
		'post_primary_order' => "relevance DESC", // CAN be a custom field name
		'post_secondary_order' => "post_date DESC",
		'post_primary_order_metatype'   => false, // false (if not meta), 'numeric', 'string'
		'post_secondary_order_metatype' => false, // false (if not meta), 'numeric', 'string'
		'_post_primary_order_metakey' => false,   // gets parsed later, do not touch
		'_post_secondary_order_metakey' => false, // gets parsed later  do not touch
		// ADVANCED
		'_post_get_content' => false,
		'_post_get_excerpt' => false,
		'_post_allow_empty_tax_term' => false,
		'_post_use_relevance' => true,
		// Special post tag filtering
		'_post_tags_active'  => false,
		'_post_tags_include' => array(),
		'_post_tags_exclude' => array(),
		'_post_tags_logic' => "OR",
		'_post_tags_empty' => 0,
		'_post_meta_logic' => "AND",
		'_post_meta_allow_null' => 0,
		// ----------------------------------------------------------------

		// ----------------------------------------------------------------
		// 3. ATTACHMENT search related arguments
		// ----------------------------------------------------------------
		'attachments_use_index' => false,
		'attachments_search_title'  => true,
		'attachments_search_content' => true,
		'attachments_search_caption' => true,
		'attachments_search_terms' => false,
		'attachments_search_ids' => true,
		'attachments_cf_filters' => true,
		'attachment_use_image' => true,
		'attachment_mime_types' => array('image/jpeg', 'image/gif', 'image/png', 'image/tiff', 'image/x-icon'),
		'attachment_exclude'    => array(), // array of IDs
		'attachment_link_to'    => 'page',          // page, file, parent
		'attachment_link_to_secondary' => 'page',   // if previous set to 'parent', then this is a fallback: page, file
		'attachment_pdf_image' => false,
		// ----------------------------------------------------------------

		// ----------------------------------------------------------------
		// 4. BUDDYPRESS related arguments
		'bp_groups_search'          => false,
		'bp_groups_search_public'   => true,
		'bp_groups_search_private'  => true,
		'bp_groups_search_hidden'   => true,
		'bp_activities_search'      => true,
		// ----------------------------------------------------------------

		// ----------------------------------------------------------------
		// 5. COMMENTS related arguments
		// Nothing here yet..
		// ----------------------------------------------------------------

		// ----------------------------------------------------------------
		// 6. TAXONOMY TERM search related arguments
		// ----------------------------------------------------------------
		'taxonomy_include' => array("category", "post_tag"), // taxonomies to search for terms
		'taxonomy_terms_exclude' => array(),     // string|array terms to exclude by ID
		'taxonomy_terms_exclude2'=> array(),     // array only
		'taxonomy_terms_search_description' => true,
		'taxonomy_terms_search_term_meta' => false,
		// ADVANCED
		'_taxonomy_posts_affected' => true,
		// ----------------------------------------------------------------

		// ----------------------------------------------------------------
		// 7. USER SEARCH
		// ----------------------------------------------------------------
		'user_login_search' => true,
		'user_display_name_search' => true,
		'user_first_name_search' => true,
		'user_last_name_search' => true,
		'user_bio_search' => true,
		'user_email_search' => false,
		'user_search_meta_fields' => array(),
		/**
		 * For each user meta field use an EXISTS(SELECT...) subquery instead of the existing LEFT JOIN.
		 * With the subquery, each comparision is treated as a separate field, so it can be distinguished by the search logic
		 **/
		'user_search_meta_fields_separate_subquery' => 0,
		'user_search_bp_fields' => array(),
		'user_search_exclude_roles' => array(),
		'user_search_exclude_ids' => array(),
		'user_search_exclude' => array(
			/*
			'include' => array(1, 2, 3), // Include by IDs
			'exclude' => array(4, 5, 6)  // Exclude by IDs
			*/
		),
		'user_meta_filter' => array(      // meta_query support
			/*
			array(
				'key'     => 'age',         // meta key
				'value'   => array( 3, 4 ), // mixed|array
				 // @param string|array compare
				 // Numeric Operators
				 //      '<' -> less than
				 //      '>' -> more than
				 //      '<>' -> not equals
				 //      '=' -> equals
				 //      'BETWEEN' -> between two values
				 // String Operators
				 //      'LIKE'
				 //      'NOT LIKE'
				 //      'IN'
				 //
				'operator' => 'BETWEEN',
				'allow_missing' => false   // allow match if this custom field is unset
			)
			*/
		),
		'user_primary_order' => "relevance DESC", // CAN be a custom field name
		'user_secondary_order' => "date DESC",
		'user_primary_order_metatype'   => false, // false (if not meta), 'numeric', 'string'
		'user_secondary_order_metatype' => false, // false (if not meta), 'numeric', 'string'
		'_user_primary_order_metakey' => false,   // gets parsed later, do not touch
		'_user_secondary_order_metakey' => false, // gets parsed later  do not touch
		// ----------------------------------------------------------------

		// ----------------------------------------------------------------
		// 8. BLOG NAME SEARCH
		// ----------------------------------------------------------------
		'blog_exclude' => array(),
		// ----------------------------------------------------------------

		// ----------------------------------------------------------------
		// 9. Peepso Group & Activity
		// ----------------------------------------------------------------
		'peepso_group_privacy' => array(),  // array(0, 1, 2)
		'peepso_group_fields' => array(),   // array('title', 'content', 'categories')
		'peepso_group_not_in' => array(),   // array(1, 2, 3...)

		'peepso_activity_types' => array(), // array('group-post', 'group-comment')
		'peepso_group_activity_privacy' => array(), // array(0, 1, 2)
		'peepso_activity_follow' => 0,
		'peepso_activity_not_in' => array(),
		// ----------------------------------------------------------------

		// ----------------------------------------------------------------
		// 10. QUERY FIELDS
		// ----------------------------------------------------------------
		'cpt_query' => array(
			'fields' => '',
			'join' => '',
			'where' => '',
			'orderby' => '',
			'groupby' => ''
		),
		'term_query' => array(
			'fields' => '',
			'join' => '',
			'where' => '',
			'orderby' => '',
			'groupby' => ''
		),
		'user_query' => array(
			'fields' => '',
			'join' => '',
			'where' => '',
			'orderby' => '',
			'groupby' => ''
		),
		'attachment_query' => array(
			'fields' => '',
			'join' => '',
			'where' => '',
			'orderby' => '',
			'groupby' => ''
		),
		'buddypress_groups_query' => array(
			'fields' => '',
			'join' => '',
			'where' => '',
			'orderby' => '',
			'groupby' => ''
		),
		'buddypress_activities_query' => array(
			'fields' => '',
			'join' => '',
			'where' => '',
			'orderby' => '',
			'groupby' => ''
		),
		// ----------------------------------------------------------------

		// ----------------------------------------------------------------
		// 11. INDEX TABLE SEARCH
		// ----------------------------------------------------------------
		'it_pool_size_one'      => 500,
		'it_pool_size_two'      => 800,
		'it_pool_size_three'    => 2000,
		'it_pool_size_rest'     => 2000,
		// ----------------------------------------------------------------

		/**
		 * OTHER ADVANCED ATTRIBUTES
		 *
		 * Don't use/override these, unless you know what you are doing.
		 */
		'_id' => -1,
		'_o'  => false,
		// LIMITS
		'limit' => 0, // overall results limit, if >=0, then evenly distributed between sources
		'_limit' => 0, // calculated limit based on the previous limit parameter
		/**
		 * _call_num ->
		 *  Number of the consecutive ajax requests with the same configuration triggered by
		 *  clicking on the 'More results...' link
		 *  This is required to calculate the correct start of the result slicing
		 */
		'_call_num' => 0,
		'posts_limit' => 10,
		'posts_limit_override' => 50,
		'posts_limit_distribute' => 0,
		'taxonomies_limit'  => 10,
		'taxonomies_limit_override' => 20,
		'users_limit' => 10,
		'users_limit_override' => 20,
		'blogs_limit' => 10,
		'blogs_limit_override' => 20,
		'buddypress_limit' => 10,
		'buddypress_limit_override' => 20,
		'comments_limit' => 10,
		'comments_limit_override' => 20,
		'attachments_limit' => 10,
		'attachments_limit_override' => 20,
		'peepso_groups_limit' => 10,
		'peepso_groups_limit_override' => 20,

		// Set true when this is a search request via REST API
		'is_wp_json' => false,

		'global_found_posts' => 0, // Number of posts found overall at any point on mixed search queries
		'_charcount'        => 0,
		'_is_autopopulate'        => false,
		'_keyword_count_limit' => 6, // Number of words in the search phrase allowed
		'_exact_matches'   => false,
		'_exact_match_location' => 'anywhere',  // anywhere, start, end
		'_qtranslate_lang' => "en",         // qtranslatex language data
		'_wpml_lang'       => "",           // WPML language
		'_polylang_lang'       => "",       // Polylang language
		'_exclude_page_parent_child' => "", // parent page exclusion data (comma separated list)
		'_taxonomy_group_logic' => 'AND',
		'_db_force_case'        => 'none',
		'_db_force_utf8_like'   => 0,
		'_db_force_unicode'     => 0,
		'_ajax_search'          => false,     // Needs to be set explicitly to TRUE in search Ajax Handler class
		'_no_post_process'      => false,     // Forcefully turns off post-processing to return RAW results

		/**
		 * Other stuff
		 */
		'_page_id' => 0,                // Current Page ID
		/**
		 * Remaining Limit Modifier
		 *      This is used mostly for more results overall limit.
		 *      Overall Limit = LIMIT * _remaining_limit_mod
		 */
		'_remaining_limit_mod' => 1000,
		'_show_more_results' => false,  // Show more results feature enabled (only used via ajax search instance)
		'filters_changed'   => false,   // Only via AJAX - if the filters have been touched by the user
		'filters_initial'   => true     // Only via AJAX - if the filters are on the initial state
	);

	/*
	 * Array of phrases of all synonym variations
	 */
	private $finalPhrases = array();

	/*
	 * Constructor args
	 */
	private $args;

	/**
	 * SearchQuery constructor.
	 *
	 * @param $args - array of arguments
	 * @param $search_id - search ID
	 * @param $options - options from $_POST
	 * @noinspection PhpMissingParamTypeInspection
	 */
	public function __construct($args, $search_id = -1, $options = false ) {
		// Expressions not allowed in static context
		self::$defaults['_selected_blogs'] = array(get_current_blog_id());

		if ( $search_id > -1 ) {
			// Translate search data and options to args
			// args priority $args > $search_args > $defaults
			$search_args = QueryArgs::get($search_id, $options, $args);
			$search_args = wp_parse_args( $search_args, self::$defaults );
			$args = wp_parse_args( $args, $search_args );
		} else {
			// No search instance, use default args
			$args = wp_parse_args( $args, self::$defaults );
		}

		// Store the options for later use
		$this->options = $options;

		// This should not be needed, up until this point
		wd_asp()->priority_groups = PriorityGroups::getInstance();

		$args = $this->preProcessOptions($args);

		$args = apply_filters("asp_query_args", $args, $search_id, $options);
		$this->args = $args;

		do_action('asp_before_search', $args['s']);

		$this->args['s'] = apply_filters('asp_search_phrase_before_cleaning', $this->args['s']);
		$this->args['s'] = Str::clear($this->args['s']);
		if ($args['engine'] == 'index' && !$args['_exact_matches'] && $args['s'] != '') {
			$this->args['s'] = Index\Content::hebrewUnvocalize($this->args['s']);
			$this->args['s'] = Index\Content::arabicRemoveDiacritics($this->args['s']);
		}
		$this->args['s'] = apply_filters('asp_search_phrase_after_cleaning', $this->args['s']);

		$this->processOptions();
		$this->get_posts();
	}

	private function preProcessOptions($args) {
		if ( !$args['_ajax_search'] && $args['_page_id'] == 0) {
			$args['_page_id'] = get_the_ID();
		}

		return $args;
	}

	private function processOptions() {
		$args = &$this->args;
		// ---------------- Part 1. Query variables --------------------

		// These parameters can be arrays and strings/numeric as well -> convert them to array
		$array_param_keys = array(
			'post_type',
			'post_parent',
			'post_status',
			'post_fields',
			'post_custom_fields',
			//'post_not_in',
			'_post_tags_include',
			'_post_tags_exclude',
			'attachment_mime_types',
			'taxonomy_include'
			//'taxonomy_terms_exclude'
		);
		foreach ($array_param_keys as $k)
			$args[$k] = !is_array($args[$k]) ? array($args[$k]) : $args[$k];

		// Do not allow private posts for non-editors
		if ( !current_user_can('read_private_posts') )
			$args['post_status'] = array_diff($args['post_status'], array('private'));

		if ( !is_array($args['search_type']) )
			$args['search_type'] = array($args['search_type']);
		if ( $args['limit'] > 0 && count($args['search_type']) > 0 )
			$args['_limit'] = floor($args['limit']/count($args['search_type']));
		if ( $args['posts_per_page'] == 0 ) {
			$args['posts_per_page'] = get_option('posts_per_page');
		} else if ( $args['posts_per_page'] < 0 ) {
			$args['posts_per_page'] = 999999;
		}

		$args['keyword_logic'] = strtolower($args['keyword_logic']);

		// Primary order is a meta field
		if (
			$args['post_primary_order_metatype'] !== false &&
			$args['_post_primary_order_metakey'] === false // this might be set in the helpers class, the nothing to do
		) {
			preg_match('/(.*?)[ ]+(.*)/', $args['post_primary_order'], $match);
			if ( isset($match[2]) ) {
				// 'field DESC' to 'customfp DESC'
				$args['post_primary_order'] = 'customfp ' .$match[2];
				$args['_post_primary_order_metakey'] = $match[1];
			}
		}

		// Secondary order is a meta field
		if (
			$args['post_secondary_order_metatype'] !== false &&
			$args['_post_secondary_order_metakey'] === false // this might be set in the helpers class, the nothing to do
		) {
			preg_match('/(.*?)[ ]+(.*)/', $args['post_secondary_order'], $match);
			if ( isset($match[2]) ) {
				// 'field DESC' to 'customfs DESC'
				$args['post_secondary_order'] = 'customfs ' .$match[2];
				$args['_post_secondary_order_metakey'] = $match[1];
			}
		}

		// Parse custom query strings
		$args['cpt_query'] = wp_parse_args($args['cpt_query'], self::$defaults['cpt_query']);
		$args['term_query'] = wp_parse_args($args['term_query'], self::$defaults['term_query']);
		$args['user_query'] = wp_parse_args($args['user_query'], self::$defaults['user_query']);
		$args['attachment_query'] = wp_parse_args($args['attachment_query'], self::$defaults['attachment_query']);
		$args['buddypress_groups_query'] = wp_parse_args($args['buddypress_groups_query'], self::$defaults['buddypress_groups_query']);
		$args['buddypress_activities_query'] = wp_parse_args($args['buddypress_activities_query'], self::$defaults['buddypress_activities_query']);

		// Woocommerce - Excluded catalogue or search products, when variations are selected
		if (
			in_array('product_variation', $args['post_type']) &&
			wd_in_array_r('product_visibility', $args['post_tax_filter'])
		) {
			foreach ( $args['post_tax_filter'] as $items ) {
				if ( $items['taxonomy'] == 'product_visibility' && count($items['exclude']) > 0 ) {
					$product_ids = get_posts(array(
						'post_type'     => 'product',
						'numberposts'   => 250,
						'tax_query'     => array(
							array(
								'taxonomy'  => 'product_visibility',
								'field'     => 'id',
								'terms'     => $items['exclude'],
								'operator'  => 'IN'
							),
						),
						'fields'        => 'ids'  // Only get post IDs
					));
					if ( !is_wp_error($product_ids) && !empty($product_ids) ) {
						$args['post_parent_exclude'] = array_unique( array_merge($args['post_parent_exclude'], $product_ids) );
					}
					break;
				}
			}
		}
		// ----------------- User search Stuff -------------------------
		//  -- Exclude users by IDs
		if ( isset($args['user_search_exclude']['exclude']) ) {
			if ( is_string($args['user_search_exclude']['exclude']) ) {
				$args['user_search_exclude']['exclude'] = explode(',', $args['user_search_exclude']['exclude']);
				$args['user_search_exclude']['exclude'] = array_map('trim', $args['user_search_exclude']['exclude']);
			}
			if ( is_array($args['user_search_exclude']['exclude']) )
				$args['user_search_exclude_ids'] = array_merge(
					$args['user_search_exclude_ids'],
					$args['user_search_exclude']['exclude']
				);
		}
		// -- Include users by IDs
		if (
			isset($args['user_search_exclude']['include']) &&
			is_string($args['user_search_exclude']['include'])
		) {
			$args['user_search_exclude']['include'] = explode(',', $args['user_search_exclude']['include']);
			$args['user_search_exclude']['include'] = array_map('trim', $args['user_search_exclude']['include']);
		}

		// Primary order is a meta field on user search
		if (
			$args['user_primary_order_metatype'] !== false &&
			$args['_user_primary_order_metakey'] === false // this might be set in the helpers class, the nothing to do
		) {
			preg_match('/(.*?)[ ]+(.*)/', $args['user_primary_order'], $match);
			if ( isset($match[2]) ) {
				// 'field DESC' to 'customfp DESC'
				$args['user_primary_order'] = 'customfp ' .$match[2];
				$args['_user_primary_order_metakey'] = $match[1];
			}
		}
		// Secondary order is a meta field on user search
		if (
			$args['user_secondary_order_metatype'] !== false &&
			$args['_user_secondary_order_metakey'] === false // this might be set in the helpers class, the nothing to do
		) {
			preg_match('/(.*?)[ ]+(.*)/', $args['user_secondary_order'], $match);
			if ( isset($match[2]) ) {
				// 'field DESC' to 'customfs DESC'
				$args['user_secondary_order'] = 'customfs ' .$match[2];
				$args['_user_secondary_order_metakey'] = $match[1];
			}
		}
		// ------------------ Part 2. Search data ----------------------

		// Break after this point, if no search data is provided
		if ( !isset($this->args['_sd']) ) {
			return;
		}

		$sd = &$this->args['_sd'];

		$search_type_by_result_type = array(
			'bp_activities' => 'buddypress',
			'bp_groups' => 'buddypress',
			'blogs' => 'blogs',
			'bp_users' => 'users',
			'peepso_groups' => 'peepso_groups',
			'peepso_activities' => 'peepso_activities',
			'terms' => 'taxonomies',
			'post_page_cpt' => 'cpt',
			'comments' => 'comments',
			'attachments' => 'attachments',
		);
		$results_order = $args['_sd']['results_order'];
		if (strpos($results_order, 'attachments') === false)
			$results_order .= "|attachments";

		$results_order_arr = explode('|', $results_order);
		$ordered_search_types = array();
		foreach ( $results_order_arr as $result_type ) {
			$search_type = $search_type_by_result_type[$result_type];
			if ( in_array($search_type, $args['search_type']) ) {
				$ordered_search_types[] = $search_type;
			}
		}
		$args['search_type'] = array_unique($ordered_search_types);

		// Disabled compact layout if the box is hidden anyways
		if ( $sd['box_sett_hide_box'] == 1 ) {
			$sd['box_compact_layout'] = 0;
			$sd['frontend_search_settings_visible'] = 1;
			$sd['show_frontend_search_settings'] = 1;
			$sd['frontend_search_settings_position'] = "block";
			$sd['resultsposition'] = "block";
			$sd['charcount'] = 0;
			if ( $sd['trigger_on_facet'] == 0 && $sd['fe_search_button'] == 0 )
				$sd['trigger_on_facet'] = 1;
		}

		$args['_charcount'] = $sd['charcount'];

		$sd['image_options'] = array(
			'image_cropping' => wd_asp()->o['asp_caching']['image_cropping'],
			'show_images' => $sd['show_images'],
			'image_bg_color' => $sd['image_bg_color'],
			'image_transparency' => $sd['image_transparency'],
			'apply_content_filter' => $sd['image_apply_content_filter'],
			'image_width' => $sd['image_width'],
			'image_height' => $sd['image_height'],
			'image_source1' => $sd['image_source1'],
			'image_source2' => $sd['image_source2'],
			'image_source3' => $sd['image_source3'],
			'image_source4' => $sd['image_source4'],
			'image_source5' => $sd['image_source5'],
			'image_default' => $sd['image_default'],
			'image_source_featured' => $sd['image_source_featured'],
			'image_custom_field' => $sd['image_custom_field']
		);

		if (isset($_POST['asp_get_as_array']))
			$sd['image_options']['show_images'] = 0;

		// ----------------- Recalculate image width/height ---------------
		switch ($sd['resultstype']) {
			case "horizontal":
				/* Same width as height */
				$sd['image_options']['image_width'] = wpdreams_width_from_px($sd['hreswidth']);
				$sd['image_options']['image_height'] = wpdreams_width_from_px($sd['hor_img_height']);
				break;
			case "polaroid":
				$sd['image_options']['image_width'] = (int)($sd['preswidth']);
				$sd['image_options']['image_height'] = (int)($sd['preswidth']);
				break;
			case "isotopic":
				if ( strpos($sd['i_item_width'], '%') === false ) {
					$sd['image_options']['image_width'] = (int)((int)$sd['i_item_width'] * 1.5);
				} else {
					$sd['image_options']['image_width'] = (int)(1920 / ( 100 / (int)$sd['i_item_width'] ));
				}
				if ( strpos($sd['i_item_height'], '%') === false ) {
					$sd['image_options']['image_height'] = (int)((int)$sd['i_item_height'] * 1.5);
				} else {
					$sd['image_options']['image_height'] = (int)(1920 / ( 100 / (int)$sd['i_item_height'] ));
				}
				break;
		}

		// Disable image cropping in non-ajax mode
		if ( !$args['_ajax_search'] ) {
			$sd['image_options']['image_cropping'] = 0;
		}

	}

	public function getArgs() {
		return $this->args;
	}

	public function get_posts() {
		$args = $this->args;
		$_args = $args; // copy to store changes

		$ra = array(
			'blogresults' => array(),

			'allbuddypresults' => array(
				'groupresults' => array(),
				'activityresults' => array()
			),

			'alltermsresults' => array(),
			'allpageposts' => array(),
			'pageposts' => array(),
			'repliesresults' => array(),
			'allcommentsresults' => array(),
			'commentsresults' => array(),
			'userresults' => array(),
			'attachment_results' => array(),
			'peepso_groups' => array(),
			'peepso_activities' => array()
		);

		// True if only CPT search in the index table is active
		$search_only_it_posts =
			$args['engine'] != 'regular' &&
			count($args['search_type']) == 1 &&
			in_array('cpt', $args['search_type']);

		$s = $this->applyExceptions( $args['s'] );

		// Allow empty search phrases only if the char count is 0 or autopopulate
		if ( $s != "" ||
			( isset($args['_is_autopopulate']) || isset($args['force_order']) || isset($args['force_count']) ) ||
			$args['_charcount'] == 0
		)
			$this->finalPhrases[] = $s;

		$this->finalPhrases = apply_filters("asp_final_phrases", $this->finalPhrases);

		$logics = array( $args['keyword_logic'] );
		if ( !empty($args['secondary_logic']) && $args['secondary_logic'] !== 'none' && $args['_call_num'] == 0 )
			$logics[] = strtolower($args['secondary_logic']);

		$it_results_start_offset = 0;

		foreach ( $args['search_type'] as $search_type ) {
			// ---- Search Porcess Starts Here ----
			foreach ($this->finalPhrases as $s) {

				if ( $search_type == 'buddypress' ) {
					$_buddyp = new SearchBuddyPress($args);
					$buddypresults = $_buddyp->search($s); // !!! returns array for each result (group, user, reply) !!!
					foreach ($buddypresults as $k => $v) {
						if ( !isset($ra['allbuddypresults'][$k]) )
							$ra['allbuddypresults'][$k] = array();
						$ra['allbuddypresults'][$k] = array_merge($ra['allbuddypresults'][$k], $v);
					}
					$this->found_posts += $_buddyp->results_count;
				}
				do_action('asp_after_buddypress_results', $s, $ra['allbuddypresults']);

				if ( $search_type == 'users' ) {
					foreach ($logics as $lk => $logic) {
						if ( $lk == 0 && $args['_exact_matches'] == 1 ) {
							// If exact matches is on, disregard the firs logic
							$args['keyword_logic'] = 'or';
						} else {
							if ( $lk > 0 )
								$args['_exact_matches'] = 0;
							$args['keyword_logic'] = $logic;
						}
						$_users = new SearchUsers($args);
						$_users_res = $_users->search($s);
						$ra['userresults'] = array_merge($ra['userresults'], $_users_res);
						if ( $lk > 0 )
							$this->found_posts += $_users->return_count;
						else
							$this->found_posts += $_users->results_count;
						$args['user_search_exclude_ids'] = array_merge($args['user_search_exclude_ids'], $this->getResIdsArr($_users_res));
						$args['_exact_matches'] = $_args['_exact_matches'];
					}
				}
				do_action('asp_after_user_results', $s, $ra['userresults']);

				if ( $search_type == 'peepso_groups' ) {
					foreach ($logics as $lk => $logic) {
						if ( $lk == 0 && $args['_exact_matches'] == 1 ) {
							// If exact matches is on, disregard the firs logic
							$args['keyword_logic'] = 'or';
						} else {
							if ( $lk > 0 )
								$args['_exact_matches'] = 0;
							$args['keyword_logic'] = $logic;
						}
						$_peepg = new SearchPeepsoGroups($args);
						$_peepg_res = $_peepg->search($s);
						$ra['peepso_groups'] = array_merge($ra['peepso_groups'], $_peepg_res);
						if ( $lk > 0 )
							$this->found_posts += $_peepg->return_count;
						else
							$this->found_posts += $_peepg->results_count;
						$args['peepso_group_not_in'] = array_merge($args['peepso_group_not_in'], $this->getResIdsArr($_peepg_res));
						$args['_exact_matches'] = $_args['_exact_matches'];
					}
				}
				do_action('asp_after_peepso_group_results', $s, $ra['peepso_groups']);

				if ( $search_type == 'peepso_activities' ) {
					foreach ($logics as $lk => $logic) {
						if ( $lk == 0 && $args['_exact_matches'] == 1 ) {
							// If exact matches is on, disregard the firs logic
							$args['keyword_logic'] = 'or';
						} else {
							if ( $lk > 0 )
								$args['_exact_matches'] = 0;
							$args['keyword_logic'] = $logic;
						}
						$_peepg = new SearchPeepsoActivities($args);
						$_peepg_res = $_peepg->search($s);
						$ra['peepso_activities'] = array_merge($ra['peepso_activities'], $_peepg_res);
						if ( $lk > 0 )
							$this->found_posts += $_peepg->return_count;
						else
							$this->found_posts += $_peepg->results_count;
						$args['peepso_activity_not_in'] = array_merge($args['peepso_activity_not_in'], $this->getResIdsArr($_peepg_res));
						$args['_exact_matches'] = $_args['_exact_matches'];
					}
				}
				do_action('asp_after_peepso_activity_results', $s, $ra['peepso_activities']);

				if ( $search_type == 'blogs' && is_multisite() ) {
					foreach ($logics as $lk => $logic) {
						if ( $lk == 0 && $args['_exact_matches'] == 1 ) {
							// If exact matches is on, disregard the firs logic
							$args['keyword_logic'] = 'or';
						} else {
							if ( $lk > 0 )
								$args['_exact_matches'] = 0;
							$args['keyword_logic'] = $logic;
						}
						$_blogs = new SearchBlogs($args);
						$_blog_res = $_blogs->search($s);
						$ra['blogresults'] = array_merge($ra['blogresults'], $_blog_res);
						if ( $lk > 0 )
							$this->found_posts += $_blogs->return_count;
						else
							$this->found_posts += $_blogs->results_count;
						$args['blog_exclude'] = array_merge($args['blog_exclude'], $this->getResIdsArr($_blog_res));
						$args['_exact_matches'] = $_args['_exact_matches'];
					}
				}

				if ( is_multisite() && $search_only_it_posts && $s != "" ) {
					// Save huge amounts of server resources by not swapping all the blogs around

					$args['_switch_on_preprocess'] = 1;
					$search_index = new SearchIndex($args);
					$ra['pageposts'] = $search_index->search($s);
					$this->found_posts += $search_index->results_count;

					do_action('asp_after_pagepost_results', $s, $ra['pageposts']);
					$ra['allpageposts'] = array_merge($ra['allpageposts'], $ra['pageposts']);
				} else {
					foreach ($args['_selected_blogs'] as $blog) {
						if ( is_multisite() ) switch_to_blog($blog);

						if ( $search_type == 'taxonomies' && count($args['taxonomy_include']) > 0 ) {
							foreach ($logics as $lk => $logic) {
								if ( $lk == 0 && $args['_exact_matches'] == 1 ) {
									// If exact matches is on, disregard the firs logic
									$args['keyword_logic'] = 'or';
								} else {
									if ( $lk > 0 )
										$args['_exact_matches'] = 0;
									$args['keyword_logic'] = $logic;
								}

								$_terms = new SearchTaxonomyTerms($args);
								$ra['alltermsresults'] = array_merge($ra['alltermsresults'], $_terms->search($s));
								if ( $lk > 0 )
									$this->found_posts += $_terms->return_count;
								else
									$this->found_posts += $_terms->results_count;
								$args['taxonomies_limit'] -= $_terms->return_count;
								$args['taxonomies_limit_override'] -= $_terms->return_count;

								$args['_exact_matches'] = $_args['_exact_matches'];
							}
						}

						if ( $search_type == 'cpt' && count($args['post_type']) > 0 ) {

							if ( $args['posts_limit_distribute'] == 1 ) {
								if ( isset($args['_sd']) && $args['_sd']['use_post_type_order'] == 1 ) {
									$_temp_ptypes = array();
									foreach ($args['_sd']['post_type_order'] as $p_order) {
										if ( in_array($p_order, $args['post_type']) )
											$_temp_ptypes[] = $p_order;
									}
									$_temp_ptypes = array_unique(array_merge($_temp_ptypes, $args['post_type']));
								} else {
									$_temp_ptypes = $args['post_type'];
								}

								$_temp_ptype_limits = array();

								foreach ($_temp_ptypes as $_tptype) {
									$_temp_ptype_limits[$_tptype] = array(
										(int)($args['posts_limit'] / count($_temp_ptypes)),
										(int)($args['posts_limit_override'] / count($_temp_ptypes))
									);
								}

								// Reset this at each loop, as post IDs can be the same across multisite
								$args['post_not_in2'] = array();
								foreach ($_temp_ptypes as $_tptype) {
									foreach ($logics as $lk => $logic) {
										if ( $lk == 0 && $args['_exact_matches'] == 1 ) {
											// If exact matches is on, disregard the firs logic
											$args['keyword_logic'] = 'or';
										} else {
											if ( $lk > 0 )
												$args['_exact_matches'] = 0;
											$args['keyword_logic'] = $logic;
										}
										$args['post_type'] = array($_tptype);
										// Change the limits temporarly for the search
										$args['posts_limit'] = $_temp_ptype_limits[$_tptype][0];
										$args['posts_limit_override'] = $_temp_ptype_limits[$_tptype][1];
										$args['global_found_posts'] = $this->found_posts;
										// For exact matches the regular engine is used
										if ( $args['engine'] == 'regular' || $args['_exact_matches'] == 1 || $args['s'] == '' )
											$_posts = new SearchPostTypes($args);
										else
											$_posts = new SearchIndex($args);
										$_posts_res = $_posts->search($s);
										// Adjust the relevance by R + (N x 1 000 000) as a grouping feature for mutliple logics
										// First logic matches will be more relevant this way
										foreach ($_posts_res as &$posts_re) {
											$posts_re->relevance = $posts_re->relevance + ((count($logics) - $lk) * 1000000);
										}
										$ra['allpageposts'] = array_merge($ra['allpageposts'], $_posts_res);
										if ( $lk > 0 )
											$this->found_posts += $_posts->return_count;
										else
											$this->found_posts += $_posts->results_count;
										$it_results_start_offset += $_posts->start_offset;
										$_temp_ptype_limits[$_tptype][0] -= $_posts->return_count;
										$_temp_ptype_limits[$_tptype][1] -= $_posts->return_count;
										$args['post_not_in2'] = array_merge($args['post_not_in2'], $this->getResIdsArr($_posts_res));
										$args['_exact_matches'] = $_args['_exact_matches'];
									}
								}
								$args['post_type'] = $_temp_ptypes;
							} else {
								// Reset this at each loop, as post IDs can be the same across multisite
								$args['post_not_in2'] = array();
								foreach ($logics as $lk => $logic) {
									if ( $lk == 0 && $args['_exact_matches'] == 1 ) {
										// If exact matches is on, disregard the first logic
										$args['keyword_logic'] = 'or';
									} else {
										if ( $lk > 0 )
											$args['_exact_matches'] = 0;
										$args['keyword_logic'] = $logic;
									}

									$args['global_found_posts'] = $this->found_posts;

									// For exact matches the regular engine is used
									if ( $args['engine'] == 'regular' || $args['_exact_matches'] == 1 || $args['s'] == '' )
										$_posts = new SearchPostTypes($args);
									else
										$_posts = new SearchIndex($args);
									$_posts_res = $_posts->search($s);
									foreach ($_posts_res as &$posts_re) {
										$posts_re->relevance = $posts_re->relevance + ((count($logics) - $lk) * 1000000);
									}
									$ra['allpageposts'] = array_merge($ra['allpageposts'], $_posts_res);
									if ( $lk > 0 )
										$this->found_posts += $_posts->return_count;
									else
										$this->found_posts += $_posts->results_count;
									$it_results_start_offset += $_posts->start_offset;
									$args['posts_limit'] -= $_posts->return_count;
									$args['posts_limit_override'] -= $_posts->return_count;
									$args['post_not_in2'] = array_merge($args['post_not_in2'], $this->getResIdsArr($_posts_res));
									$args['_exact_matches'] = $_args['_exact_matches'];
								}
							}

							do_action('asp_after_pagepost_results', $s, $ra['allpageposts']);
						}

						if ( $search_type == 'comments' ) {
							foreach ($logics as $lk => $logic) {
								if ( $lk == 0 && $args['_exact_matches'] == 1 ) {
									// If exact matches is on, disregard the first logic
									$args['keyword_logic'] = 'or';
								} else {
									if ( $lk > 0 )
										$args['_exact_matches'] = 0;
									$args['keyword_logic'] = $logic;
								}
								$args['global_found_posts'] = $this->found_posts;
								$_comments = new SearchComments($args);
								$ra['allcommentsresults'] = array_merge($ra['allcommentsresults'], $_comments->search($s));
								if ( $lk > 0 )
									$this->found_posts += $_comments->return_count;
								else
									$this->found_posts += $_comments->results_count;
								$args['comments_limit'] -= $_comments->return_count;
								$args['comments_limit_override'] -= $_comments->return_count;
								$args['_exact_matches'] = $_args['_exact_matches'];
							}
						}
						do_action('asp_after_comments_results', $s, $ra['allcommentsresults']);

						if ( $search_type == 'attachments' ) {
							foreach ($logics as $lk => $logic) {
								if ( $lk == 0 && $args['_exact_matches'] == 1 ) {
									// If exact matches is on, disregard the firs logic
									$args['keyword_logic'] = 'or';
								} else {
									if ( $lk > 0 )
										$args['_exact_matches'] = 0;
									$args['keyword_logic'] = $logic;
								}
								$args['global_found_posts'] = $this->found_posts;
								$_attachments = new SearchAttachments($args);
								$_att_res = $_attachments->search($s);
								$ra['attachment_results'] = array_merge($ra['attachment_results'], $_att_res);
								if ( $lk > 0 )
									$this->found_posts += $_attachments->return_count;
								else
									$this->found_posts += $_attachments->results_count;
								$args['attachments_limit'] -= $_attachments->return_count;
								$args['attachments_limit_override'] -= $_attachments->return_count;
								$args['attachment_exclude'] = array_merge($args['attachment_exclude'], $this->getResIdsArr($_att_res));
								$args['_exact_matches'] = $_args['_exact_matches'];
							}
						}
						do_action('asp_after_attachment_results', $s, $ra['attachment_results']);

						if ( is_multisite() ) restore_current_blog();
					}

				}
			}
			// ---- Search Porcess Stops Here ----
		}
		$results_count_adjust = 0;  // Count the changes in results count when using filters

		$rca = count($ra['alltermsresults']);
		$ra['alltermsresults'] = apply_filters('asp_terms_results', $ra['alltermsresults'], $args["_id"], $args);
		$rca -= count($ra['alltermsresults']);
		$results_count_adjust += $rca;

		$rca = count($ra['allpageposts']);
		$ra['allpageposts'] = apply_filters('asp_pagepost_results', $ra['allpageposts'], $args["_id"], $args);
		$ra['allpageposts'] = apply_filters('asp_cpt_results', $ra['allpageposts'], $args["_id"], $args);
		$rca -= count($ra['allpageposts']);
		$results_count_adjust += $rca;

		$rca = count($ra['allcommentsresults']);
		$ra['allcommentsresults'] = apply_filters('asp_comment_results', $ra['allcommentsresults'], $args["_id"], $args);
		$rca -= count($ra['allcommentsresults']);
		$results_count_adjust += $rca;

		$rca = count($ra['allbuddypresults']);
		$ra['allbuddypresults'] = apply_filters('asp_buddyp_results', $ra['allbuddypresults'], $args["_id"], $args);
		$rca -= count($ra['allbuddypresults']);
		$results_count_adjust += $rca;

		$rca = count($ra['blogresults']);
		$ra['blogresults'] = apply_filters('asp_blog_results', $ra['blogresults'], $args["_id"], $args);
		$rca -= count($ra['blogresults']);
		$results_count_adjust += $rca;

		$rca = count($ra['userresults']);
		$ra['userresults'] = apply_filters('asp_user_results', $ra['userresults'], $args["_id"], $args);
		$rca -= count($ra['userresults']);
		$results_count_adjust += $rca;

		$rca = count($ra['attachment_results']);
		$ra['attachment_results'] = apply_filters('asp_attachment_results', $ra['attachment_results'], $args["_id"], $args);
		$rca -= count($ra['attachment_results']);
		$results_count_adjust += $rca;

		$rca = count($ra['peepso_groups']);
		$ra['peepso_groups'] = apply_filters('asp_peepso_group_results', $ra['peepso_groups'], $args["_id"], $args);
		$rca -= count($ra['peepso_groups']);
		$results_count_adjust += $rca;

		$rca = count($ra['peepso_activities']);
		$ra['peepso_activities'] = apply_filters('asp_peepso_activities_results', $ra['peepso_activities'], $args["_id"], $args);
		$rca -= count($ra['peepso_activities']);
		$results_count_adjust += $rca;


		SearchPostTypes::orderBy($ra['allpageposts'], array(
			'primary_ordering' => $args['post_primary_order'],
			'primary_ordering_metatype' => $args['post_primary_order_metatype'],
			'secondary_ordering' => $args['post_secondary_order'],
			'secondary_ordering_metatype' => $args['post_secondary_order_metatype']
		));

		// Results as array, unordered
		$results_arr = array(
			'terms' => $ra['alltermsresults'],
			'blogs' => $ra['blogresults'],
			'bp_activities' => $ra['allbuddypresults']['activityresults'],
			'comments' => $ra['allcommentsresults'],
			'bp_groups' => $ra['allbuddypresults']['groupresults'],
			'bp_users' => $ra['userresults'],
			'post_page_cpt' => $ra['allpageposts'],
			'attachments' => $ra['attachment_results'],
			'peepso_groups' => $ra['peepso_groups'],
			'peepso_activities' => $ra['peepso_activities']
		);

		foreach ( $results_arr as $k => $v ) {
			$final = array();
			foreach ($v as $current ) {
				$found = false;
				foreach ($final as $item) {
					if ($item->id == $current->id && $item->blogid == $current->blogid) {
						$found = true;
						break;
					}
				}
				if ( !$found )
					$final[] = $current;
			}
			$results_arr[$k] = $final;
		}

		// Order if search data is set
		if ( isset($args['_sd']) ) {
			$results_order = $args['_sd']['results_order'];

			if (strpos($results_order, 'attachments') === false)
				$results_order .= "|attachments";

			// These keys are in the right order
			$results_order_arr = explode('|', $results_order);

			$results = array();
			foreach ($results_order_arr as $rv) {
				$results = array_merge($results, $results_arr[$rv]);
			}
			$rca = count($results);
			$results = apply_filters('asp_results', $results, $args['_id'], $args['_ajax_search'], $args);
			$rca -= count($results);
			$results_count_adjust += $rca;
			// Group if neccessary
			if (
				$args['_sd']['resultstype'] == 'vertical' &&
				$args['_sd']['group_by'] != "none" &&
				$args['_ajax_search']
			) {
				$results = $this->group( $results );
			}
		} else {
			$results = array();
			foreach ($results_arr as $rv) {
				$results = array_merge($results, $rv);
			}
			$rca = count($results);
			$results = apply_filters('asp_results', $results, -1, false, $args);
			$rca -= count($results);
			$results_count_adjust += $rca;
		}

		// $results_count_adjust > 0 -> posts have been removed, otherwise added
		$this->found_posts -= $results_count_adjust;
		$this->found_posts = $this->found_posts < 0 ? 0 : $this->found_posts; // Make sure this is not 0

		$this->all_results = $results;

		// For non-ajax searches, we need the WP_Post objects
		if ( !$args['_ajax_search'] ) {
			if ( count($results) > $args['posts_per_page'] ) {
				$results = asp_results_to_wp_obj($results, ($args['posts_per_page'] * ($args['page'] - 1)) - $it_results_start_offset, $args['posts_per_page']);
			} else {
				$results = asp_results_to_wp_obj($results, 0, $args['posts_per_page']);
			}
			$results = apply_filters('asp_noajax_results', $results, $args['_id'], false, $args);
			if ( get_option('asp_stat', 0) == 1 ) {
				Statistics::addKeyword($args['_id'], $args['s']);
			}
		}

		if ( isset($results['groups']) ) {
			foreach ( $results['groups'] as $group ) {
				if ( isset($group['items']) ) {
					$this->returned_posts += count($group['items']);
				}
			}
		} else {
			$this->returned_posts = count($results);
		}

		$this->posts = $results;
		return $results;
	}

	public function resultsSuggestions( $fallback_on_no_results = false ) {
		$suggestions = $this->kwSuggestions();
		$posts = array();
		$return = array();
		if ( isset($suggestions['keywords'], $suggestions['keywords'][0]) ) {
			$this->args['s'] = $suggestions['keywords'][0];
			$posts = $this->get_posts();
		}

		if ( count($posts) > 0 ) {
			$return = array(
				'suggested' => $posts,
				'keywords' => array($suggestions['keywords'][0]),
				'nores' => 1
			);
		} else if ( $fallback_on_no_results && isset($suggestions['keywords']) ) {
			$return = array(
				'keywords' => $suggestions['keywords'],
				'nores' => 1
			);
		}

		return $return;
	}

	/** @noinspection PhpUnused */
	public function kwSuggestions( $single = false ) {
		if ( !isset($this->args['_sd'], $this->args['_sid']) )
			return array();

		$keywords = array();
		$types = array();
		$sd = &$this->args['_sd'];
		$args = $this->args;
		$results = array();
		$count = $single === false ? $sd['keyword_suggestion_count'] : 1;

		if (isset($sd['customtypes']) && count($sd['customtypes']) > 0)
			$types = array_merge($types, $sd['customtypes']);

		if ( function_exists( 'qtranxf_use' ) && $args['_qtranslate_lang'] != "" ) {
			$lang = $args['_qtranslate_lang'];
		} else if ( $args['_wpml_lang'] != "" ) {
			$lang = $args['_wpml_lang'];
		} else if ( $args['_polylang_lang'] != "" ) {
			$lang = $args['_polylang_lang'];
		} else {
			$lang = $sd['keywordsuggestionslang'];
		}

		$phrase = trim($this->args['s']);
		if ( $phrase != '' ) {
			foreach (w_isset_def($sd['selected-keyword_suggestion_source'], array('google')) as $source) {
				if ( empty($source) )
					continue;
				$remaining_count = $count - count($keywords);
				if ($remaining_count <= 0) break;

				$taxonomy = "";
				// Check if this is a taxonomy
				if (strpos($source, 'xtax_') !== false) {
					$taxonomy = str_replace('xtax_', '', $source);
					$source = "terms";
				}

				$t = new KeywordSuggest($source, array(
					'maxCount' => $remaining_count,
					'maxCharsPerWord' => $sd['keyword_suggestion_length'],
					'postTypes' => $types,
					'lang' => $lang,
					'overrideUrl' => '',
					'taxonomy' => $taxonomy,
					'api_key' => $sd['kws_google_places_api'],
					'search_id' => $this->args['_sid'],
					'options' => $this->options,
					'args' => $args
				));

				$keywords = array_merge($keywords, $t->getKeywords($phrase));
			}
		}

		if ( $keywords != false ) {
			$results['keywords'] = $keywords;
			$results['nores'] = 1;
			$results = apply_filters('asp_only_keyword_results', $results);
		}

		return $results;
	}

	/** @noinspection PhpUnusedPrivateMethodInspection */
	private function kwPossiblePhrases($phrase ) {
		$pa = explode(" ", $phrase);
		if ( MB::strlen($pa[0]) > 3 ) {
			return array(
				$pa[0], MB::substr($pa[0], 0, -1), MB::substr($pa[0], 1)
			);
		} else {
			return $pa;
		}
	}

	private function group( $results ) {
		if ( !isset($this->args['_sd']) )
			return false;

		$sd = &$this->args['_sd'];
		$id = $this->args['_id'];

		$groups = array();

		$other_res_group = array(
			"title" => asp_icl_t( "Group (".$id."):", $sd["group_other_results_head"]),
			"items" => array()
		);

		$group_prefix = asp_icl_t( "Group header prefix (".$id.")", $sd['group_header_prefix']);
		$group_suffix = asp_icl_t( "Group header suffix (".$id.")", $sd['group_header_suffix']);

		if ( $sd['group_by'] == "post_type" ) {
			foreach ( $sd['groupby_cpt'] as $v ) {
				$groups[$v["post_type"]] = array(
					"title" => $group_prefix . " " . asp_icl_t( "Group (".$id."): " . $v["name"], $v["name"]) . " " . $group_suffix,
					"items" => array()
				);
			}
			foreach ($results as $r) {
				if ( $r->content_type == "pagepost" && isset($groups[$r->post_type]) ) {
					$groups[$r->post_type]['items'][] = $r;
				} else if($sd["group_result_no_group"] != "remove") {
					$other_res_group['items'][] = $r;
				}
			}
		} else if ( $sd['group_by'] == "categories_terms" ) {
			$taxonomies = array();
			foreach ( $sd['groupby_terms']['terms'] as $t ) {
				if ( $t['id'] == -1) {
					$terms = get_terms($t['taxonomy']);
					foreach ($terms as $tt) {
						$groups["_" . $tt->term_id] = array(
							"title" => $group_prefix . " " . $tt->name . " " . $group_suffix,
							"items" => array()
						);
					}
				} else {
					$tt = get_term($t['id'], $t['taxonomy']);
					$groups["_" . $t['id']] = array(
						"title" => $group_prefix . " " . $tt->name . " " . $group_suffix,
						"items" => array()
					);
				}
				if ( !in_array($t['taxonomy'], $taxonomies) )
					$taxonomies[] = $t['taxonomy'];
			}
			foreach ($results as $r) {
				if ( $r->content_type == "pagepost" && count($taxonomies) > 0 ) {
					$terms = wp_get_object_terms( $r->id, $taxonomies, array('fields'=>'ids') );
					$matched_a_term = false;
					foreach ($terms as $tt) {
						if ( isset($groups["_" . $tt]) ) {
							$groups["_" . $tt]['items'][] = $r;
							$matched_a_term = true;
						}
						if ($sd['group_exclude_duplicates'] == 1)
							break;
					}
					if ( !$matched_a_term && $sd["group_result_no_group"] != "remove" )
						$other_res_group['items'][] = $r;
				} else if($sd["group_result_no_group"] != "remove") {
					$other_res_group['items'][] = $r;
				}
			}
		} else if ( $sd['group_by'] == "content_type" ) {
			foreach ( $sd['groupby_content_type'] as $k => $v ) {
				$groups[$k] = array(
					"title" => $group_prefix . " " . asp_icl_t( "Group (".$id."): " . $v, $v) . " " . $group_suffix,
					"items" => array()
				);
			}
			foreach ($results as $r) {
				if ( isset($groups[$r->g_content_type]) ) {
					$groups[$r->g_content_type]['items'][] = $r;
				} else if($sd["group_result_no_group"] != "remove") {
					$other_res_group['items'][] = $r;
				}
			}
		}

		// We don't need the other results group whatsoever, if it's empty
		if ( count($other_res_group['items']) > 0 ) {
			if ($sd['group_other_location'] == 'bottom')
				$groups["_other_res"] = $other_res_group;
			else
				array_unshift($groups, $other_res_group);
		}

		foreach ($groups as $k => $g) {
			// Remove empty groups
			if ( $sd['group_show_empty'] == 0 ) {
				if (count($g['items']) == 0) {
					unset($groups[$k]);
					continue;
				}
			} else {
				// Move items if empty groups are allowed
				if (count($g['items']) == 0) {
					if ($sd['group_show_empty_position'] == 'top') {
						$new = $g;
						array_unshift($groups, $new);
						unset($groups[$k]);
						continue;
					} else if ($sd['group_show_empty_position'] == 'bottom') {
						$new = $g;
						$groups[] = $new;
						unset($groups[$k]);
						continue;
					}
				}
			}
			if ( $sd['group_result_count'] == 1 )
				$groups[$k]['title'] .= " (".count($groups[$k]['items']).")";
		}

		if ( $sd['group_reorder_by_pr'] == 1 ) {
			$ordered = array();
			foreach ( $groups as $gkey => $group ) {
				$ordered[$gkey] = array(
					'group_priority' => 0,
					'priority' => 0,
					'relevance' => 0
				);
				if ( isset($group['items']) ) {
					foreach ($group['items'] as $result) {
						if ( isset($result->group_priority) && intval($result->group_priority) > $ordered[$gkey]['group_priority'] ) {
							$ordered[$gkey]['group_priority'] = intval($result->group_priority);
						}
						if ( isset($result->priority) && intval($result->priority) > $ordered[$gkey]['priority'] ) {
							$ordered[$gkey]['priority'] = intval($result->priority);
						}
						if ( isset($result->relevance) && intval($result->relevance) > $ordered[$gkey]['relevance'] ) {
							$ordered[$gkey]['relevance'] = intval($result->relevance);
						}
					}
				}
			}
			uasort($ordered, array($this, 'usort_groups'));
			$new_groups = array();
			foreach ( $ordered as $okey => $values ) {
				$new_groups[$okey] = $groups[$okey];
			}
			$groups = $new_groups;
		}

		$groups = apply_filters('asp_result_groups', $groups, $this->args['_id'], $this->args);

		if ( count($groups) > 0)
			return array("grouped" => 1, "groups" => $groups);
		else
			return array();
	}

	private function usort_groups($a, $b) {
		if ( $a['group_priority'] == $b['group_priority'] ) {
			if ( $a['priority'] == $b['priority'] ) {
				return $b['relevance'] - $a['relevance'] ;
			}
			return $b['priority'] - $a['priority'];
		}
		return $b['group_priority'] - $a['group_priority'];
	}

	private function applyExceptions( $s ) {
		if ( !isset($this->args['_sd']) )
			return $s;

		$sd = &$this->args['_sd'];

		if ($sd["kw_exceptions"] == "" && $sd["kw_exceptions_e"] == "") return $s;

		if ($sd["kw_exceptions"] != "") {
			$exceptions = stripslashes( str_replace(array(" ,", ", ", " , "), ",", $sd["kw_exceptions"]) );
			if ( $exceptions != '' ) {
				$s = trim(str_ireplace(explode(",", $exceptions), "", $s));
				$s = preg_replace('/\s+/', ' ', $s);
			}
		}

		if ($sd["kw_exceptions_e"] != "") {
			$exceptions = stripslashes( str_replace(array(" ,", ", ", " , "), ",", $sd["kw_exceptions_e"]) );
			$exceptions = explode(',', $exceptions);
			foreach ($exceptions as &$v)
				$v = '/\b' . $v . '\b/ui';
			unset($v);
			if ( count($exceptions) > 0 ) {
				$s = trim(preg_replace($exceptions, '', $s));
				$s = preg_replace('/\s+/', ' ', $s);
			}
		}

		return $s;
	}

	private function getResIdsArr( $r ): array {
		$ret = array();
		if ( is_array($r) )
			foreach ($r as $v)
				if ( isset($v->id) )
					$ret[] = $v->id;
		return $ret;
	}
}
