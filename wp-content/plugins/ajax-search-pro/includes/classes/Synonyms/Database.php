<?php
namespace WPDRMS\ASP\Synonyms;

defined('ABSPATH') or die("You can't access this file directly.");

if ( !class_exists(__NAMESPACE__ . '\Database') ) {
	class Database {
		function add($keyword, $synonyms_arr, $language = '') {
			global $wpdb;
			$table = wd_asp()->db->table('synonyms');
			// Use INSERT IGNORE to prevent any errors (only warnings)
			$query = $wpdb->prepare(
				"INSERT IGNORE INTO `$table` (keyword, synonyms, lang) VALUES( '%s', '%s', '%s')",
				stripslashes_deep($keyword),
				implode(',', stripslashes_deep($synonyms_arr)),
				$language
			);
			return $wpdb->query($query);
		}

		function update($keyword, $synonyms_arr, $language = '') {
			global $wpdb;
			$table = wd_asp()->db->table('synonyms');
			$query = $wpdb->prepare(
				"UPDATE `$table` SET synonyms='%s' WHERE keyword='%s' AND lang='%s'",
				implode(',', $synonyms_arr),
				$keyword,
				$language
			);
			return $wpdb->query($query);
		}

		function delete($keyword, $language = '') {
			global $wpdb;
			$table = wd_asp()->db->table('synonyms');
			$query = $wpdb->prepare(
				"DELETE FROM `$table` WHERE keyword='%s' AND lang='%s'",
				$keyword,
				$language
			);
			return $wpdb->query($query);
		}

		function select() {
			global $wpdb;
			$table = wd_asp()->db->table('synonyms');
			return $wpdb->get_results(
				"SELECT keyword, synonyms, lang FROM `$table` ORDER BY id DESC LIMIT 15000",
				ARRAY_A
			);
		}

		function deleteByID($id) {
			global $wpdb;
			$table = wd_asp()->db->table('synonyms');
			$query = $wpdb->prepare(
				"DELETE FROM `$table` WHERE id=%d",
				$id
			);
			return $wpdb->query($query);
		}

		public function wipe() {
			global $wpdb;
			$table = wd_asp()->db->table('synonyms');
			if ( $table != '' )
				$wpdb->query( "TRUNCATE TABLE `$table`" );
		}

		public function find($keyword = '', $language = '', $limit = 30, $exact = false) {
			global $wpdb;
			$table = wd_asp()->db->table('synonyms');
			if ( $keyword == '' ) {
				if ( $language != 'any' )
					$lang_query = "WHERE lang LIKE '".esc_sql($language)."' ";
				else
					$lang_query = '';
				$res = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT keyword, synonyms, lang, id
                     FROM `$table`
                     $lang_query
                     ORDER BY id DESC LIMIT %d",
						($limit + 0)
					),
					ARRAY_A
				);
			} else {
				if ( $language != 'any' )
					$lang_query = "AND lang LIKE '".esc_sql($language)."' ";
				else
					$lang_query = '';
				$kw = $exact == true ? $keyword : '%' . $wpdb->esc_like($keyword) . '%';
				$res = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT keyword, synonyms, lang, id,
                     (
                      (case when
                        (keyword LIKE '%s')
                         then 1 else 0 end
                      ) +
                      (case when
                        (keyword LIKE '%s')
                         then 1 else 0 end
                      )
                     ) as relevance
                     FROM `$table`
                     WHERE
                      keyword LIKE '%s' 
                      $lang_query
                     ORDER BY relevance DESC, id DESC LIMIT %d",
						$keyword,
						$wpdb->esc_like($keyword) . '%',
						$kw,
						($limit + 0)
					),
					ARRAY_A
				);
			}

			return $res;
		}

		public function export() {
			global $wpdb;
			$table = wd_asp()->db->table('synonyms');
			return $wpdb->get_results(
				"SELECT keyword, synonyms, lang
                 FROM `$table`
                 ORDER BY id ASC LIMIT 100000",
				ARRAY_A
			);
		}

		public function import( $synonyms ) {
			global $wpdb;
			$inserted = 0;
			$values = array();
			$table = wd_asp()->db->table('synonyms');
			foreach ( $synonyms as $syn ) {
				$value    = $wpdb->prepare(
					"(%s, %s, %s)",
					$syn['keyword'],
					$syn['synonyms'],    // this is a single string now
					$syn['lang']
				);
				$values[] = $value;

				// Split INSERT at every 200 records
				if ( count( $values ) > 199 ) {
					$values = implode( ', ', $values );
					$query  = "INSERT IGNORE INTO `$table`
                                (`keyword`, `synonyms`, `lang`)
                                VALUES $values";
					$inserted += $wpdb->query( $query );
					$values = array();
				}
			}
			// Remaining synonyms
			if ( count( $values ) > 0 ) {
				$values = implode( ', ', $values );
				$query  = "INSERT IGNORE INTO `$table`
                                (`keyword`, `synonyms`, `lang`)
                                VALUES $values";
				$inserted += $wpdb->query( $query );
			}

			return $inserted;
		}

		public function createTable($table_name = ''): array {
			global $wpdb;
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			$return = array();

			if ($table_name == '')
				$table_name = wd_asp()->db->table('synonyms');

			$charset_collate = "";
			if ( ! empty( $wpdb->charset ) ) {
				$charset_collate_bin_column = "CHARACTER SET $wpdb->charset";
				$charset_collate            = "DEFAULT $charset_collate_bin_column";
			}
			if ( strpos( $wpdb->collate, "_" ) > 0 ) {
				$charset_collate .= " COLLATE $wpdb->collate";
			}

			$query = "
            CREATE TABLE IF NOT EXISTS `$table_name` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `keyword` varchar(50) NOT NULL,
              `synonyms` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
              `lang` varchar(20) NOT NULL,
              PRIMARY KEY (`id`),
              UNIQUE (`keyword`, `lang`)
            ) $charset_collate AUTO_INCREMENT=1 ;";
			dbDelta($query);
			$wpdb->query($query);
			$return[] = $query;

			$query            = "SHOW INDEX FROM $table_name";
			$indices          = $wpdb->get_results( $query );
			$existing_indices = array();

			foreach ( $indices as $index ) {
				if ( isset( $index->Key_name ) ) {
					$existing_indices[] = $index->Key_name;
				}
			}

			// Worst case scenario optimal indexes
			if ( ! in_array( 'keyword_lang', $existing_indices ) ) {
				$sql = "CREATE INDEX keyword_lang ON $table_name (keyword(50), lang(20))";
				$wpdb->query( $sql );
				$return[] = $sql;
			}

			return $return;
		}
	}
}