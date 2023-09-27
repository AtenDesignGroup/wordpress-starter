<?php
namespace WPDRMS\ASP\Index;

use WPDRMS\ASP\Utils\Plugin;

defined('ABSPATH') or die("You can't access this file directly.");

if ( !class_exists(__NAMESPACE__ . '\Database') ) {
	class Database {
		private $table_name;

		function __construct() {
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			$this->table_name = wd_asp()->db->table('index');
		}

		function create(): array {
			global $wpdb;
			$return = array();

			$charset_collate = "";

			if ( ! empty( $wpdb->charset ) ) {
				$charset_collate_bin_column = "CHARACTER SET $wpdb->charset";
				$charset_collate            = "DEFAULT $charset_collate_bin_column";
			}
			if ( strpos( $wpdb->collate, "_" ) > 0 ) {
				$charset_collate .= " COLLATE $wpdb->collate";
			}

			$query      = "
				CREATE TABLE IF NOT EXISTS " . $this->table_name . " (
					doc bigint(20) UNSIGNED NOT NULL DEFAULT '0',
					term varchar(150) NOT NULL DEFAULT '0',
					term_reverse varchar(150) NOT NULL DEFAULT '0',
					blogid mediumint(9) UNSIGNED NOT NULL DEFAULT '0',
					content smallint(9) UNSIGNED NOT NULL DEFAULT '0',
					title tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
					comment tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
					tag tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
					link tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
					author tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
					excerpt tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
					customfield smallint(9) UNSIGNED NOT NULL DEFAULT '0',
					post_type varchar(50) NOT NULL DEFAULT 'post',
					lang varchar(20) NOT NULL DEFAULT '0',
			    PRIMARY KEY doctermitem (doc, term, blogid)) $charset_collate";

			dbDelta( $query );

			$return[] = $query;
			$query            = "SHOW INDEX FROM $this->table_name";
			$indices          = $wpdb->get_results( $query );
			$existing_indices = array();

			foreach ( $indices as $index ) {
				if ( isset( $index->Key_name ) ) {
					$existing_indices[] = $index->Key_name;
				}
			}

			// Worst case scenario optimal indexes
			if ( ! in_array( 'term_ptype_bid_lang', $existing_indices ) ) {
				$sql = "CREATE INDEX term_ptype_bid_lang ON $this->table_name (term(20), post_type(20), blogid, lang(10))";
				$wpdb->query( $sql );
				$return[] = $sql;
			}
			if ( ! in_array( 'rterm_ptype_bid_lang', $existing_indices ) ) {
				$sql = "CREATE INDEX rterm_ptype_bid_lang ON $this->table_name (term_reverse(20), post_type(20), blogid, lang(10))";
				$wpdb->query( $sql );
				$return[] = $sql;
			}

			return $return;
		}

		public function scheduled() {
			global $wpdb;

			// 4.20.3
			if ( Plugin::previousVersion('4.20.2') ) {
				if ($wpdb->get_var( "SHOW COLUMNS FROM `$this->table_name` LIKE 'taxonomy';" ) ) {
					$query = "ALTER TABLE `$this->table_name` 
                    DROP COLUMN `taxonomy`,
                    DROP COLUMN `category`,
                    DROP COLUMN `item`";
					$wpdb->query($query);

					$query = "ALTER TABLE `$this->table_name` 
                    MODIFY COLUMN `content` smallint(9) UNSIGNED,
                    MODIFY COLUMN `title` tinyint(3) UNSIGNED,
                    MODIFY COLUMN `comment` tinyint(3) UNSIGNED,
                    MODIFY COLUMN `tag` tinyint(3) UNSIGNED,
                    MODIFY COLUMN `link` tinyint(3) UNSIGNED,
                    MODIFY COLUMN `author` tinyint(3) UNSIGNED,
                    MODIFY COLUMN `excerpt` tinyint(3) UNSIGNED,
                    MODIFY COLUMN `customfield` smallint(9) UNSIGNED";
					$wpdb->query( $query );

					$query = "OPTIMIZE TABLE `$this->table_name`";
					$wpdb->query( $query );
				}
			}
		}

		/**
		 * Runs a table optimize query on the index table
		 *
		 * @return bool|false|int
		 */
		public function optimize() {
			global $wpdb;
			// In innoDB this is mapped to "ALTER TABLE .. FORCE", aka. defragmenting
			// OPTIMIZE only needs SELECT and INSERT privileges
			return $wpdb->query( "OPTIMIZE TABLE ".$this->table_name );
		}

		public function truncate() {
			global $wpdb;
			$wpdb->query( "TRUNCATE TABLE ".$this->table_name );
		}

		function removeDocument( $post_id ) {
			global $wpdb;

			if ( is_array($post_id) ) {
				foreach ( $post_id as &$v )
					$v = $v + 0;
				$post_ids = implode(', ', $post_id);
				$wpdb->query( "DELETE FROM $this->table_name WHERE doc IN ($post_ids)"  );
			} else {
				$wpdb->query( $wpdb->prepare(
					"DELETE FROM $this->table_name WHERE doc = %d", $post_id
				) );
			}

			/*
			 DO NOT call finishOperation() here, it would switch back the blog too early.
			 Calling this function from an action hooks does not require switching the blog,
			 as the correct one is in use there.
			*/
		}

		/**
		 * Generates the query based on the post and the token array and inserts into DB
		 *
		 * @return int
		 */
		function insertTokensToDB( $the_post, $tokens, $blog_id, $lang ) {
			global $wpdb;
			$values = array();

			if ( count( $tokens ) <= 0 ) {
				return false;
			}

			foreach ( $tokens as $d ) {
				// If it's numeric, delete the leading space
				$term = trim( $d['_keyword'] );

				if ( isset($d['_no_reverse']) && $d['_no_reverse'] === true ) {
					$value    = $wpdb->prepare(
						"(%d, %s, %s, %d, %d, %d, %d, %d, %d, %d, %d, %d, %s, %s)",
						$the_post->ID, $term, '', $blog_id, $d['content'], $d['title'], $d['comment'], $d['tag'],
						$d['link'], $d['author'], $d['excerpt'], $d['customfield'],
						$the_post->post_type, $lang
					);
				} else {
					$value    = $wpdb->prepare(
						"(%d, %s, REVERSE(%s), %d, %d, %d, %d, %d, %d, %d, %d, %d, %s, %s)",
						$the_post->ID, $term, $term,
						$blog_id, $d['content'], $d['title'], $d['comment'], $d['tag'],
						$d['link'], $d['author'], $d['excerpt'], $d['customfield'],
						$the_post->post_type, $lang
					);
				}

				$values[] = $value;

				// Split INSERT at every 200 records
				if ( count( $values ) > 199 ) {
					$values = implode( ', ', $values );
					$query  = "INSERT IGNORE INTO $this->table_name
                    (`doc`, `term`, `term_reverse`, `blogid`, `content`, `title`, `comment`, `tag`, `link`, `author`,
                     `excerpt`, `customfield`, `post_type`, `lang`)
                    VALUES $values";
					$wpdb->query( $query );
					$values = array();
				}
			}

			// Add the remaining as well
			if ( count( $values ) > 0 ) {
				$values = implode( ', ', $values );
				$query  = "INSERT IGNORE INTO $this->table_name
				(`doc`, `term`, `term_reverse`, `blogid`, `content`, `title`, `comment`, `tag`, `link`, `author`,
				 `excerpt`, `customfield`, `post_type`, `lang`)
				VALUES $values";
				$wpdb->query( $query );
			}

			return count( $tokens );
		}

		/**
		 * Gets the post IDs to index
		 *
		 * @return array of post IDs
		 */
		function getPostIdsToIndex($args, $posts_to_ignore): array {
			global $wpdb;
			$parent_join = '';

			$_statuses = explode(",", $args['post_statuses']);
			foreach ($_statuses as &$sv)
				$sv = trim($sv);
			$valid_status    = "'" . implode("', '", $_statuses ) . "'";

			if ( count($args['post_types']) > 0 ) {
				$post_types = $args['post_types'];
				if ( class_exists('WooCommerce') && in_array('product_variation', $post_types) ) { // Special case for Woo variations
					$post_types = array_diff($post_types, array('product_variation'));
					$rest = '';
					if (count($post_types) > 0)
						$rest = " OR post.post_type IN('".implode("', '", $post_types)."') ";
					// In case of product variation the parent post status must also match, otherwise it is not relevant
					$parent_join = "LEFT JOIN $wpdb->posts parent ON (post.post_parent = parent.ID)";
					$restriction = " AND ( (post.post_type = 'product_variation' AND parent.post_status IN($valid_status) ) $rest )";
				} else {
					$restriction = " AND post.post_type IN ('" .implode("', '", $post_types). "')";
				}
			} else {
				return array();
			}

			$post_password = '';
			if ( $args['post_password_protected'] == 0 ) {
				$post_password = " AND (post.post_password = '') ";
			}

			$ignore_posts = "";
			if ( !empty($posts_to_ignore[$args['blog_id']]) )
				$ignore_posts = " AND post.ID NOT IN( ".implode(',', $posts_to_ignore[$args['blog_id']])." )";

			$mimes_restrict = '';
			if ( $args['attachment_mime_types'] != '' ) {
				$mimes_arr = wpd_comma_separated_to_array($args['attachment_mime_types']);
				if ( count($mimes_arr) > 0 )
					$mimes_restrict = "OR ( post.post_status = 'inherit' AND post.post_mime_type IN ('" . implode("','", $mimes_arr) . "') )";
			}

			$limit        = $args['limit'] > 1000 ? 1000 : ( $args['limit'] + 0 );

			if ( $args['extend'] == 1 ) {
				// We are extending, so keep the existing
				$q = "SELECT post.ID
						FROM $wpdb->posts post
						$parent_join
						LEFT JOIN $this->table_name r ON (post.ID = r.doc AND r.blogid = " . $args['blog_id'] . ")
						WHERE
								r.doc is null
						AND
                            (
                                post.post_status IN ($valid_status)
                                $mimes_restrict
                            )
						$restriction
						$ignore_posts
						$post_password
						ORDER BY post.ID ASC
						LIMIT $limit";
			} else {
				$q = "SELECT post.ID
						FROM $wpdb->posts post
						$parent_join
						WHERE
                            (
                                post.post_status IN ($valid_status)
                                $mimes_restrict
                            )
						$restriction
						$ignore_posts
						$post_password
						ORDER BY post.ID ASC
						LIMIT $limit";

			}
			return $wpdb->get_results( $q );
		}

		/**
		 * Gets the number documents to index
		 *
		 */
		public function getPostIdsToIndexCount( $args, $posts_to_ignore, $check_only = false ): int {
			if ( defined('ASP_INDEX_BYPASS_COUNT') ) {
				return 9999;
			}
			global $wpdb;
			$parent_join = '';

			$_statuses = explode(",", $args['post_statuses']);
			foreach ($_statuses as &$sv)
				$sv = trim($sv);
			$valid_status    = "'" . implode("', '", $_statuses ) . "'";

			if ( count($args['post_types']) > 0  ) {
				$post_types = $args['post_types'];
				if ( class_exists('WooCommerce') && in_array('product_variation', $post_types) ) { // Special case for Woo variations
					$post_types = array_diff($post_types, array('product_variation'));
					$rest = '';
					if (count($post_types) > 0) // are there any left?
						$rest = " OR post.post_type IN('".implode("', '", $post_types)."') ";
					// In case of product variation the parent post status must also match, otherwise it is not relevant
					$parent_join = "LEFT JOIN $wpdb->posts parent ON (post.post_parent = parent.ID)";
					$restriction = " AND ( (post.post_type = 'product_variation' AND parent.post_status IN($valid_status) ) $rest )";
				} else {
					$restriction = " AND post.post_type IN ('" .implode("', '", $post_types). "')";
				}
			} else {
				return 0;
			}

			$post_password = '';
			if ( $args['post_password_protected'] == 0 ) {
				$post_password = " AND (post.post_password = '') ";
			}

			$ignore_posts = "";
			if ( !empty($posts_to_ignore[$args['blog_id']]) )
				$ignore_posts = " AND post.ID NOT IN( ".implode(',', $posts_to_ignore[$args['blog_id']])." )";

			$mimes_restrict = '';
			if ( $args['attachment_mime_types'] != '' ) {
				$mimes_arr = wpd_comma_separated_to_array($args['attachment_mime_types']);
				if ( count($mimes_arr) > 0 ) {
					$mimes_restrict = "OR ( post.post_status = 'inherit' AND post.post_mime_type IN ('" . implode("','", $mimes_arr) . "') )";
				}
			}

			if ( $check_only ) {
				$q = "SELECT 1
                        FROM $wpdb->posts post
                        $parent_join
                        LEFT JOIN $this->table_name r ON (post.ID = r.doc AND r.blogid = " . $args['blog_id'] . ")
                        WHERE
                            r.doc is null
                        AND
                            (
                                post.post_status IN ($valid_status)
                                $mimes_restrict
                            )
                        $restriction
                        $ignore_posts
                        $post_password
                        LIMIT 1";
			} else {
				$q = "SELECT COUNT(DISTINCT post.ID)
                        FROM $wpdb->posts post
                        $parent_join
                        LEFT JOIN $this->table_name r ON (post.ID = r.doc AND r.blogid = " . $args['blog_id'] . ")
                        WHERE
                                r.doc is null
                        AND
                            (
                                post.post_status IN ($valid_status)
                                $mimes_restrict
                            )
                        $restriction
                        $ignore_posts
                        $post_password";
			}

			return intval( $wpdb->get_var( $q ) );
		}


		public function getPostsIndexed() {
			if ( defined('ASP_INDEX_BYPASS_COUNT') ) {
				return 9999;
			}
			global $wpdb;
			// Tested faster as a regular single query count
			$sql = "SELECT COUNT(count) FROM (SELECT 1 as count FROM ".wd_asp()->db->table('index')." GROUP BY doc) as A";
			return $wpdb->get_var($sql);
		}

		public function getTotalKeywords() {
			if ( defined('ASP_INDEX_BYPASS_COUNT') ) {
				return 9999;
			}
			global $wpdb;

			if ( is_multisite() )
				$sql = "SELECT COUNT(doc) FROM " . wd_asp()->db->table('index');
			else
				$sql = "SELECT COUNT(doc) FROM " . wd_asp()->db->table('index') . " WHERE blogid = " . get_current_blog_id();

			return $wpdb->get_var($sql);
		}

		public function isEmpty(): bool {
			global $wpdb;
			return $wpdb->query("SELECT 1 FROM ".wd_asp()->db->table('index')." LIMIT 1") == 0;
		}
	}
}