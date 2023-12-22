<?php /** @noinspection PhpReturnValueOfMethodIsNeverUsedInspection */
/** @noinspection PhpMissingParamTypeInspection */

/** @noinspection PhpComposerExtensionStubsInspection */

namespace WPDRMS\ASP\Index;

use WPDRMS\ASP\Media\Parser;
use WPDRMS\ASP\Media\RemoteService\License;
use WPDRMS\ASP\Utils\Html;
use WPDRMS\ASP\Utils\MB;
use WPDRMS\ASP\Utils\Str;

defined( 'ABSPATH' ) or die( "You can't access this file directly." );


class Manager {

	/**
	 * @var array of constructor arguments
	 */
	private $args;

	private $db, $shortcode;

	/**
	 * @var int keywords found and added to database this session
	 */
	private $keywords_found = 0;

	/**
	 * @var int posts indexed through
	 */
	private $posts_indexed_now = 0;

	/**
	 * @var array of post IDs to ignore from selection
	 */
	private $posts_to_ignore = array();

	private $start_time;

	private $abort = false;

	private $lang = '';

	/**
	 * @var Tokenizer
	 */
	private $tokenizer;

	public static $apostrophes = array('"', "'", "`", '’', '‘', '”', '“');

	// ------------------------------------------- PUBLIC METHODS --------------------------------------------------

	function __construct( $args = array() ) {

		$defaults = array(
			// Arguments here
			'index_title'         => 1,
			'index_content'       => 1,
			'index_pdf_content'   => 0,
			'index_pdf_method'    => 'auto',
			'index_text_content'    => 0,
			'index_richtext_content'  => 0,
			'index_msword_content'    => 0,
			'index_msexcel_content'   => 0,
			'index_msppt_content'     => 0,
			'media_service_send_file'     => 1,
			'index_excerpt'       => 1,
			'index_tags'          => 0,
			'index_categories'    => 0,
			'index_taxonomies'    => "",
			'attachment_mime_types' => "",
			'index_permalinks'	  => 0,
			'index_customfields'  => "",
			'index_author_name'   => "",
			'index_author_bio'    => "",
			'blog_id'             => get_current_blog_id(),
			'extend'              => 1,
			'limit'               => 25,
			'use_stopwords'       => 1,
			'stopwords'           => '',
			'min_word_length'     => 3,
			'post_types'          => array('post', 'page'),
			'post_statuses'       => 'publish',
			'extract_gutenberg_blocks'  => 1,
			'extract_shortcodes'  => 1,
			'exclude_shortcodes'  => '',
			'extract_iframes'	  => 0,
			'synonyms_as_keywords'=> 0
		);

		$this->args = wp_parse_args( $args, $defaults );
		$this->args = apply_filters( 'asp_it_args', $this->args, $defaults);

		$this->db = new Database();
		$this->shortcode = new Shortcode();
		$this->tokenizer = new Tokenizer(array(
			'min_word_length' => $this->args['min_word_length'],
			'use_stopwords' => $this->args['use_stopwords'],
			'stopwords' => $this->args['stopwords'],
			'synonyms_as_keywords' => $this->args['synonyms_as_keywords']
		));

		// Swap here to have the asp_posts_indexed option for each blog different
		if ( is_multisite() && !empty($this->args['blog_id']) && $this->args['blog_id'] != get_current_blog_id() ) {
			switch_to_blog( $this->args['blog_id'] );
		}

		$this->initIngoreList();

		do_action('asp_index_init');
	}

	/**
	 * Re-generates the index table
	 *
	 * @return array (posts to index, posts indexed)
	 */
	function newIndex(): array {
		$this->emptyIndex( false );
		$this->emptyIgnoreList();

		return $this->extendIndex();
	}


	/**
	 * Extends the index database
	 *
	 * @param $switching_blog - will clear the indexed posts array
	 *
	 * @return array (posts to index, posts indexed)
	 * @noinspection PhpUnusedParameterInspection
	 * @noinspection PhpMissingParamTypeInspection
	 */
	function extendIndex( $switching_blog = false ): array {

		do_action('asp_index_start');
		$this->start_time = microtime(true);

		// this respects the limit, no need to count again
		$posts = $this->db->getPostIdsToIndex( $this->args, $this->posts_to_ignore );

		foreach ( $posts as $tpost ) {
			if ( $this->abort )
				break;

			$this->updateIgnoreList($tpost);

			if ( $this->indexDocument( $tpost->ID, false ) ) {
				$this->posts_indexed_now++;
				// The post stays on ignore list otherwise (even on error 500)
				$this->updateIgnoreList($tpost, true);
			}

		}

		// THIS MUST BE HERE!!
		// .the statment below restores the blog before getting the correct count!
		$return = array(
			'postsToIndex'    => $this->getPostIdsToIndexCount(),
			'postsIndexedNow' => $this->getPostsIndexedNow(),
			'keywordsFound'   => $this->keywords_found,
			//'totalKeywords'   => $this->getTotalKeywords(),
			'totalIgnored'    => $this->getIgnoredList( true )
		);

		if ( is_multisite() ) {
			restore_current_blog();
		}

		return $return;
	}

	function initIngoreList() {
		$this->posts_to_ignore = get_option("_asp_index_ignore", array());
	}

	function getIgnoredList( $count_only = false ) {
		if ( $count_only ) {
			return count($this->posts_to_ignore, COUNT_RECURSIVE) - count($this->posts_to_ignore);
		} else {
			return $this->posts_to_ignore;
		}
	}

	function emptyIgnoreList() {
		delete_option("_asp_index_ignore");
		$this->posts_to_ignore = array();
	}

	function updateIgnoreList( $post = null, $remove = false ) {
		if ( !empty($post) ) {
			if ( $remove ) {
				if (
					isset($this->posts_to_ignore[$this->args['blog_id']]) &&
					in_array($post->ID, $this->posts_to_ignore[$this->args['blog_id']])
				) {
					if (($key = array_search($post->ID, $this->posts_to_ignore[$this->args['blog_id']])) !== false) {
						unset($this->posts_to_ignore[$this->args['blog_id']][$key]);
					}
					if ( empty($this->posts_to_ignore[$this->args['blog_id']]) )
						unset($this->posts_to_ignore[$this->args['blog_id']]);
				}
			} else {
				if ( !isset($this->posts_to_ignore[$this->args['blog_id']]) )
					$this->posts_to_ignore[$this->args['blog_id']] = array();
				$this->posts_to_ignore[$this->args['blog_id']][] = $post->ID;
			}
		}
		update_option("_asp_index_ignore", $this->posts_to_ignore);
	}

	/**
	 * Indexes a document based on its ID
	 *
	 * @param int $post_id the post id
	 * @param bool $remove_first
	 * @param bool $post_editor_context
	 * @return bool
	 */
	function indexDocument( $post_id, $remove_first = true, $post_editor_context = false ): bool {
		$args = $this->args;

		// array of all needed tokens
		$tokens = array();

		// forbidden post types
		$forbidden_pt = array('tablepress_table', 'vc_grid_item', 'revision', 'nav_menu_item', 'custom_css', 'acf');

		// On creating or extending the index, no need to remove
		if ( $remove_first ) {
			$this->removeDocument( $post_id );
		}

		/**
		 * This prevents the fancy quotes and special characters to HTML output
		 * NOTE: it has to be executed here before every get_post() call!!
		 */
		remove_filter('the_title', 'wptexturize');
		remove_filter('the_title', 'convert_chars');

		$the_post = get_post( $post_id );
		// Return false to skip the post from the index
		$the_post = apply_filters('asp_index_post', $the_post, $args);

		if ( $the_post == null ) {
			return false;
		}

		// This needs to be here, after the get_post()
		if ( $post_editor_context === true ) {
			if ( count($args['post_types']) ) {
				if ( !in_array($the_post->post_type, $args['post_types']) )
					return false;
			} else {
				return false;
			}
		}

		// Is this a forbidden post type?
		if ( in_array($the_post->post_type, $forbidden_pt) )
			return false;

		// Check if attachment, if so, check the mime types allowed
		if ( $the_post->post_type == 'attachment' ) {
			$mimes_arr = wpd_comma_separated_to_array($args['attachment_mime_types']);
			if ( !in_array($the_post->post_mime_type, $mimes_arr) ) {
				return false;
			}
		}

		// Password protected excluded
		if ( $args['post_password_protected'] == 0 && post_password_required($the_post) ) {
			return false;
		}

		// --- GET THE LANGUAGE INFORMATION, IF ANY ---
		$lang = '';
		// Is WPML used?
		if ( class_exists('SitePress') )
			$lang = $this->wpml_langcode_post_id( $the_post );
		// Is Polylang used?
		if ( function_exists('pll_get_post_language') && $lang == "" ) {
			if ( $the_post->post_type == 'product_variation' && class_exists('WooCommerce') ) {
				/** @noinspection PhpRedundantOptionalArgumentInspection */
				$lang = pll_get_post_language($the_post->post_parent, 'slug');
			} else {
				/** @noinspection PhpRedundantOptionalArgumentInspection */
				$lang = pll_get_post_language($the_post->ID, 'slug');
			}
		}
		$this->lang = $lang;

		/**
		 * For product variations set the title, content and excerpt to the original product
		 */
		if ( $the_post->post_type == "product_variation" ) {
			$parent_post = get_post($the_post->post_parent);
			if ( !empty($parent_post) ) {
				$the_post->post_title .= " " . $parent_post->post_title;
				$the_post->post_content = $parent_post->post_content;
				$the_post->post_excerpt = $parent_post->post_excerpt;
			}
		}

		if ( $args['index_content'] == 1 ) {
			$this->tokenizeContent( $the_post, $tokens );
		}

		if ( $args['index_title'] == 1 ) {
			$this->tokenizeTitle( $the_post, $tokens );
		}

		if ( $the_post->post_type == 'attachment' ) {
			$this->tokenizeMedia( $the_post, $tokens );
		}

		if ( $args['index_excerpt'] == 1 ) {
			$this->tokenizeExcerpt( $the_post, $tokens );
		}

		if ( $args['index_categories'] == 1 || $args['index_tags'] == 1 || $args['index_taxonomies'] != "" ) {
			$this->tokenizeTerms( $the_post, $tokens );
		}

		if ( $args['index_author_name'] == 1 || $args['index_author_bio'] == 1 ) {
			$this->tokenizeAuthor( $the_post, $tokens );
		}

		if ( $args['index_permalinks'] == 1 ) {
			$this->tokenizePermalinks( $the_post, $tokens );
		}

		$this->tokenizeCustomFields( $the_post, $tokens );

		$this->tokenizeAdditionalKeywords( $the_post, $tokens );

		if ( count( $tokens ) > 0 ) {
			$this->keywords_found += $this->db->insertTokensToDB( $the_post, $tokens, $args['blog_id'], $this->lang );
			return true;
		}

		/*
		 DO NOT call finishOperation() here, it would switch back the blog too early.
		 Calling this function from an action hooks does not require switching the blog,
		 as the correct one is in use there.
		*/

		return false;
	}

	/**
	 * Removes a document from the index (in case of deleting posts, etc.)
	 *
	 * @param int|array $post_id the post id
	 */
	function removeDocument( $post_id ) {
		$this->db->removeDocument( $post_id );

		/*
		 DO NOT call finishOperation() here, it would switch back the blog too early.
		 Calling this function from an action hooks does not require switching the blog,
		 as the correct one is in use there.
		*/
	}


	/**
	 * Empties the index table
	 *
	 * @param bool $restore_current_blog if set to false, it won't restore multiste blog - for internal usage mainly
	 * @return array
	 */
	function emptyIndex( $restore_current_blog = true ): array {
		global $wpdb;
		$this->db->truncate();

		if ( is_multisite() ) {
			$current = get_current_blog_id();
			$blogs   = $wpdb->get_results( "SELECT blog_id FROM $wpdb->blogs", ARRAY_A );
			if ( $blogs ) {
				foreach ( $blogs as $blog ) {
					switch_to_blog( $blog['blog_id'] );
				}
				// Switch back to the current, like nothing happened
				switch_to_blog( $current );
			}
		}

		if ( $restore_current_blog && is_multisite() ) {
			restore_current_blog();
		}

		return array(
			'postsToIndex'    => $this->getPostIdsToIndexCount(),
			'totalKeywords'   => $this->getTotalKeywords()
		);
	}

	/**
	 * Suggests pool sizes for the index table search process
	 *
	 * @return array
	 * @noinspection PhpUnused
	 */
	public static function suggestPoolSizes( ): array {
		return array(
			'one'   => 50000,
			'two'   => 90000,
			'three' => 90000,
			'rest'  => 90000
		);
	}


	// ------------------------------------------- PRIVATE METHODS -------------------------------------------------

	/**
	 * Generates the content tokens and puts them into the tokens array
	 *
	 * @param object $the_post the post object
	 * @param array $tokens tokens array
	 *
	 * @return int keywords count
	 * @noinspection PhpReturnValueOfMethodIsNeverUsedInspection
	 */
	private function tokenizeContent( $the_post, &$tokens ): int {
		$args = $this->args;

		// Some custom editors like Themeco X Pro requires the global $post object for it to work.
		global $post;
		$post = $the_post;

		$content = apply_filters( 'asp_post_content_before_tokenize_clear', $the_post->post_content, $the_post );

		if ( $args['extract_shortcodes'] ) {
			$content = $this->shortcode->execute( $content, $the_post, $args['exclude_shortcodes'] );
		}

		if ( $args['extract_gutenberg_blocks'] ) {
			$content = do_blocks( $content );
		}

		if ( $args['extract_iframes'] == 1 ) {
			$content .= ' ' . Html::extractIframeContent($content);
		}

		// Strip the remaining shortcodes
		$content = strip_shortcodes( $content );

		$filtered_content = apply_filters( 'asp_post_content_before_tokenize', $content, $the_post );

		if ( $filtered_content == "" ) {
			return 0;
		}

		$content_keywords = $this->tokenizer->tokenize( $filtered_content, $the_post, $this->lang );

		foreach ( $content_keywords as $keyword ) {
			/** @noinspection PhpRedundantOptionalArgumentInspection */
			$this->insertToken( $tokens, $keyword[0], $keyword[1], 'content' );
		}

		return count( $content_keywords );
	}

	/**
	 * Generates the excerpt tokens and puts them into the tokens array
	 *
	 * @param object $the_post the post object
	 * @param array $tokens tokens array
	 *
	 * @return int keywords count
	 * @noinspection PhpReturnValueOfMethodIsNeverUsedInspection
	 */
	private function tokenizeExcerpt( $the_post, &$tokens ): int {
		$args = $this->args;

		if ( $the_post->post_excerpt == "" ) {
			return 0;
		}

		$filtered_excerpt = apply_filters( 'asp_post_excerpt_before_tokenize', $the_post->post_excerpt, $the_post );

		if ( $args['extract_shortcodes'] ) {
			$filtered_excerpt = $this->shortcode->execute( $filtered_excerpt, $the_post, $args['exclude_shortcodes'] );
		}

		$excerpt_keywords = $this->tokenizer->tokenize( $filtered_excerpt, $the_post, $this->lang );

		foreach ( $excerpt_keywords as $keyword ) {
			$this->insertToken( $tokens, $keyword[0], $keyword[1], 'excerpt' );
		}

		return count( $excerpt_keywords );
	}

	/**
	 * Generates the title tokens and puts them into the tokens array
	 *
	 * @param object $the_post the post object
	 * @param array $tokens tokens array
	 *
	 * @return int keywords count
	 */
	private function tokenizeTitle( $the_post, &$tokens ): int {
		$filtered_title = apply_filters( 'asp_post_title_before_tokenize', $the_post->post_title, $the_post );
		// the_title filter causes issues with empty strings, so check
		if ( trim($filtered_title) == '' ) {
			return 0;
		}

		$title          = apply_filters( 'the_title', $filtered_title, $the_post->ID );
		$title_keywords = $this->tokenizer->tokenize( $title, $the_post, $this->lang );

		// No-reverse exact title
		$single_title = $this->tokenizer->tokenizeSimple($title, $the_post);
		if ( $single_title != '' ) {
			$single_title = MB::substr($single_title, 0, 45);
			$pos = MB::strpos($single_title, ' ');

			/*
			 * The index table unique key is (doc, term, item) - so if this word already exists, then it will be ignored
			 * by the database. To make sure it is added, append a unique string at the end, that is not searched.
			 */
			if ($pos === false)
				$single_title .= '___';

			$this->insertToken($tokens, $single_title, 1, 'title', true);

			$single_title_al = str_replace(self::$apostrophes, '', $single_title);
			if ($single_title_al !== $single_title) {
				$this->insertToken($tokens, $single_title_al, 1, 'title', true);
			}

			$single_title_spaceless = str_replace(' ', '', $single_title_al);
			if ($single_title_spaceless !== $single_title) {
				$this->insertToken($tokens, $single_title_spaceless, 1, 'title', true);
			}
		}

		foreach ( $title_keywords as $keyword ) {
			$this->insertToken( $tokens, $keyword[0], $keyword[1], 'title' );
		}

		$post_type_obj = get_post_type_object($the_post->post_type);
		if ( !is_wp_error($post_type_obj) ) {
			if ( isset($post_type_obj->labels->name) ) {
				$post_type_name = $this->tokenizer->tokenizeSimple($post_type_obj->labels->name, $the_post);
				if ( $post_type_name != '' ) {
					$this->insertToken($tokens, $post_type_name, 1, 'title');
				}
			}
			if ( isset($post_type_obj->labels->singular_name) ) {
				$post_type_name = $this->tokenizer->tokenizeSimple($post_type_obj->labels->singular_name, $the_post);
				if ( $post_type_name != '' ) {
					$this->insertToken($tokens, $post_type_name, 1, 'title');
				}
			}
		}

		return count( $title_keywords );
	}

	/**
	 * Generates the media file contents depending on the media type
	 *
	 * @param $the_post - the post object
	 * @param $tokens - tokens array
	 *
	 * @return int - keywords count
	 * @noinspection PhpIncludeInspection
	 */
	private function tokenizeMedia($the_post, &$tokens ): int {
		$args = $this->args;

		$p = new Parser($the_post, array(
			'pdf_parser' => $args['index_pdf_method'],
			'text_content' => $args['index_text_content'],
			'richtext_content' => $args['index_richtext_content'],
			'pdf_content' => $args['index_pdf_content'],
			'msword_content' => $args['index_msword_content'],
			'msexcel_content' => $args['index_msexcel_content'],
			'msppt_content' => $args['index_msppt_content'],
			'media_service_send_file' => $args['media_service_send_file'],
		));

		$this->abort = true; // Preemptively set the abort flag, so no other document content is indexed
		$contents = apply_filters( 'asp_index_file_custom_parse', '', $the_post );

		if ( $contents == '' ) {

			$time_elapsed_during_remote = microtime(true);
			$contents = $p->parse();
			$time_elapsed_during_remote = microtime(true) - $time_elapsed_during_remote;

			if ( is_wp_error($contents) ) {
				if ( $contents->get_error_code() == 10 ) {
					License::getInstance()->deactivate();
				}
				$contents = '';
			}

			// Attempt local parse, but only if the remote did not take too long
			if ( $contents == '' && $time_elapsed_during_remote < 20 ) {
				$contents = $p->parse( false );
			}

			$time_elapsed_from_start = microtime(true) - $this->start_time;
			// Only abort if the whole indexing process took over 5 seconds so far
			if ( $time_elapsed_from_start < 5 ) {
				$this->abort = false;
			}
		}

		$contents = apply_filters( 'asp_file_contents_before_tokenize', $contents, $the_post );
		$keywords = $this->tokenizer->tokenize( $contents, $the_post, $this->lang );

		foreach ( $keywords as $keyword ) {
			/** @noinspection PhpRedundantOptionalArgumentInspection */
			$this->insertToken( $tokens, $keyword[0], $keyword[1], 'content' );
		}

		return count( $keywords );
	}

	/**
	 * Generates the permalink tokens and puts them into the tokens array
	 *
	 * @param object $the_post the post object
	 * @param array $tokens tokens array
	 *
	 * @return int keywords count
	 */
	private function tokenizePermalinks( $the_post, &$tokens ): int {
		$filtered_permalink = apply_filters( 'asp_post_permalink_before_tokenize', $the_post->post_name, $the_post );
		// Store the permalink as is, with an occurence of 1
		$this->insertToken( $tokens, $filtered_permalink, 1, 'link' );

		return 1;
	}

	/**
	 * Generates the author display name and biography tokens and puts them into the tokens array
	 *
	 * @param object $the_post the post object
	 * @param array $tokens tokens array
	 *
	 * @return int keywords count
	 */
	private function tokenizeAuthor( $the_post, &$tokens ): int {
		$args = $this->args;
		$bio  = "";

		$user = get_userdata($the_post->post_author);
		$name = $user->display_name . ' ' . $user->first_name . ' ' . $user->last_name . ' ' . $user->nickname;

		if ( $args['index_author_bio'] ) {
			$bio = get_user_meta( $the_post->post_author, 'description', true );
		}

		$author_keywords = $this->tokenizer->tokenize( $name . " " . $bio, $the_post, $this->lang );
		foreach ( $author_keywords as $keyword ) {
			$this->insertToken( $tokens, $keyword[0], $keyword[1], 'author' );
		}

		return count( $author_keywords );
	}

	/**
	 * Generates taxonomy term tokens and puts them into the tokens array
	 *
	 * @param object $the_post the post object
	 * @param array $tokens tokens array
	 *
	 * @return int keywords count
	 */
	private function tokenizeTerms( $the_post, &$tokens ): int {
		$args       = $this->args;
		$taxonomies = array();
		$all_terms  = array();

		if ( $args['index_tags'] ) {
			$taxonomies[] = 'post_tag';
		}
		if ( $args['index_categories'] ) {
			$taxonomies[] = 'category';
		}
		$custom_taxonomies = explode( '|', $args['index_taxonomies'] );

		$taxonomies = array_merge( $taxonomies, $custom_taxonomies );

		foreach ( $taxonomies as $taxonomy ) {
			$terms = wp_get_post_terms( $the_post->ID, trim( $taxonomy ), array( "fields" => "names" ) );
			$terms = apply_filters('asp_index_terms', $terms, $taxonomy, $the_post);
			if ( is_array( $terms ) ) {
				$all_terms = array_merge( $all_terms, $terms );
			}
		}

		if ( count( $all_terms ) > 0 ) {
			foreach ( $all_terms as $term ) {
				// Multi-word taxonomy terms also being added as whole
				if ( strpos($term, ' ') !== false ) {
					$single_term = $this->tokenizer->tokenizeSimple($term, $the_post);
					if ( $single_term != '' ) {
						$this->insertToken( $tokens, $single_term, 1, 'tag' );
					}
				}
			}

			$terms_string  = implode( ' ', $all_terms );
			$term_keywords = $this->tokenizer->tokenize( $terms_string, $the_post, $this->lang );

			// everything goes under the tags, thus the tokinezer is called only once
			foreach ( $term_keywords as $keyword ) {
				$this->insertToken( $tokens, $keyword[0], $keyword[1], 'tag' );
			}

			return count( $term_keywords );
		}

		return 0;
	}

	private function tokenizeAdditionalKeywords( $the_post, &$tokens ) {
		$values = get_post_meta( $the_post->ID, '_asp_additional_tags', false );
		$values = is_array($values) ? $values : array($values);
		$values = apply_filters( 'asp_post_additional_keywords_before_tokenize', $values, $the_post, $tokens );
		if ( count($values) > 0 ) {
			$keywords = $this->tokenizer->tokenizePhrases($values, $the_post);
			foreach ($keywords as $keyword) {
				$this->insertToken($tokens, $keyword[0], $keyword[1], 'customfield');
			}

			$keywords = $this->tokenizer->tokenize($values, $the_post, $this->lang);
			foreach ($keywords as $keyword) {
				$this->insertToken($tokens, $keyword[0], $keyword[1], 'customfield');
			}
		}
	}

	/**
	 * Generates selected custom field tokens and puts them into the tokens array
	 *
	 * @param object $the_post the post object
	 * @param array $tokens tokens array
	 *
	 * @return int keywords count
	 */
	private function tokenizeCustomFields( $the_post, &$tokens ): int {
		$args = $this->args;

		if ( function_exists("mb_strlen") )
			$fn_strlen = "mb_strlen";
		else
			$fn_strlen = "strlen";

		// all the CF content to this variable
		$cf_content = "";

		if ( $args['index_customfields'] != "" )
			$custom_fields = explode( '|', $args['index_customfields'] );
		else
			$custom_fields = array();

		if ( in_array('_asp_additional_tags', $custom_fields) ) {
			$custom_fields = array_diff($custom_fields, array('_asp_additional_tags'));
		}


		foreach ( $custom_fields as $field ) {
			$values = array();
			if ( strpos($field, '__pods__') !== false ) {
				$field = str_replace('__pods__', '', $field);
				if ( function_exists('pods') ) {
					$p = pods($the_post->post_type, $the_post->ID);
					if ( is_object($p) ) {
						$values = $p->field($field, false);
					}
				}
			}

			if ( empty($values) )
				$values = get_post_meta( $the_post->ID, $field, false );
			$values = is_array($values) ? $values : array($values);
			$values = apply_filters( 'asp_post_custom_field_before_tokenize', $values, $the_post, $field, $tokens );

			/**
			 * For ACF fields, we also need to index the labels if present (checkbox, radio etc...)
			 */
			$acf_labels = array();
			if ( function_exists( 'get_field_object') ) {
				$acf_labels = asp_acf_get_field_choices($field);
			}

			foreach ( $values as $value ) {
				if ( is_string($value) && Str::isJson($value) ) {
					$value = json_decode($value, true);
				}

				$value = !is_array($value) ? array($value) : $value;

				foreach ( $value as $v ) {
					$v = Str::anyToString( $v );
					if ( $v != '' ) {
						if ( isset($acf_labels[$v]) && $v != $acf_labels[$v] ) {
							$v .= ' ' . $acf_labels[$v];
						}
						$cf_content .= " " . $v;
						// Without spaces for short values (for example product SKUs)
						if ( $fn_strlen($v) <= 50 ) {
							$spaceless_value = str_replace(' ', '', $v);
							if ( $spaceless_value != $v ) {
								$cf_content .= " " . $spaceless_value;
							}
						}
					}
				}
			}

			if ( count($values) == 1 ) {

				/**
				 * For numeric values where fields end with "_id" or "ID", try to fetch a reference post
				 */
				if ( empty($acf_labels) ) {
					foreach ($values as $value) {
						if (
							is_numeric($value) &&
							( substr($field,-strlen('ID'))==='ID' || substr($field,-strlen('_id'))==='_id' )
						) {
							$reference_post_title = get_the_title($value);
							if ( !is_wp_error($reference_post_title) && $reference_post_title != '' ) {
								$cf_content .= " " . $reference_post_title;
							}
						}
					}
				}

				foreach ( $values as $value ) {
					// No-reverse exact field
					$single_cf_content = $this->tokenizer->tokenizeSimple($value, $the_post);
					if ( $single_cf_content != '' ) {
						$single_cf_content = MB::substr($single_cf_content, 0, 45);
						$pos = MB::strpos($single_cf_content, ' ');

						/*
						 * The index table unique key is (doc, term, item) - so if this word already exists, then it will be ignored
						 * by the database. To make sure it is added, append a unique string at the end, that is not searched.
						 */
						if ( $pos === false )
							$single_cf_content .= '___';
						$this->insertToken($tokens, $single_cf_content, 1, 'customfield', true);

						$single_cf_content_al = str_replace(self::$apostrophes, '', $single_cf_content);
						if ( $single_cf_content_al !== $single_cf_content ) {
							$this->insertToken($tokens, $single_cf_content_al, 1, 'customfield', true);
						}
					}
				}
			}
		}

		$cf_content = apply_filters('asp_index_cf_contents_before_tokenize', $cf_content, $the_post);

		if ( $cf_content != "" ) {
			$cf_keywords = $this->tokenizer->tokenize( $cf_content, $the_post, $this->lang );
			foreach ( $cf_keywords as $keyword ) {
				$this->insertToken( $tokens, $keyword[0], $keyword[1], 'customfield' );
			}

			return count( $cf_keywords );
		}

		return 0;
	}


	/**
	 * Puts the keyword token into the tokens array.
	 *
	 * @param array $tokens array to the tokens
	 * @param string $keyword keyword
	 * @param int $count keyword occurrence count
	 * @param string $field the field
	 * @param bool $no_reverse if the reverse keyword should be stored
	 */
	private function insertToken(&$tokens, $keyword, $count = 1, $field = 'content', $no_reverse = false) {
		// Take care of accidental empty keyowrds
		if ( trim($keyword) == '' ) {
			return;
		}
		if ( MB::strlen($keyword) > 50 ) {
			$keyword = MB::substr($keyword, 0, 50);
		}
		// Can't use numeric keys, it would break things...
		// We need to trim it at inserting
		$key = $keyword;
		if ( is_numeric( $keyword ) ) {
			$key = " " . $keyword;
		}

		// Preserve the non-reverse key uniqueness
		if ( $no_reverse )
			$key .= '__NOREV__';

		if ( !isset($tokens[$key]) ) {
			$tokens[$key] = array(
				"content" => 0,
				"title" => 0,
				"comment" => 0,
				"tag" => 0,
				"link" => 0,
				"author" => 0,
				"excerpt" => 0,
				"customfield" => 0,
				'_keyword' => $keyword,
				'_no_reverse' => $no_reverse
			);
		}
		$tokens[ $key ][ $field ] += $count;
	}

	/**
	 * A working hack to get the post language by post object WPML
	 *
	 * @param $post
	 *
	 * @return string language string
	 */
	private function wpml_langcode_post_id($post): string {
		global $wpdb;

		$post_type = "post_" . $post->post_type;

		$query = $wpdb->prepare("
			SELECT language_code
			FROM " . $wpdb->prefix . "icl_translations
			WHERE
			element_type = '%s' AND
			element_id = %d"
			, $post_type, $post->ID);
		$query_exec = $wpdb->get_row($query);

		if ( null !== $query_exec )
			return $query_exec->language_code;

		return "";
	}

	public function getPostIdsToIndexCount( $check_only = false ): int {
		return $this->db->getPostIdsToIndexCount( $this->args, $this->posts_to_ignore, $check_only );
	}


	/**
	 * Gets the number of so far indexed documents
	 *
	 * @return int number of indexed documents
	 * @noinspection PhpUnused
	 */
	public function getPostsIndexed(): int {
		return $this->db->getPostsIndexed();
	}

	/**
	 * Gets the number of items in the index table, multisite supported
	 *
	 * @return int number of rows
	 */
	public function getTotalKeywords(): int {
		return $this->db->getTotalKeywords();
	}

	public function isEmpty(): bool {
		return $this->db->isEmpty();
	}

	/**
	 * Gets the number of indexed documents on this run instance
	 *
	 * @return int number of indexed documents
	 */
	private function getPostsIndexedNow(): int {
		return $this->posts_indexed_now;
	}


}