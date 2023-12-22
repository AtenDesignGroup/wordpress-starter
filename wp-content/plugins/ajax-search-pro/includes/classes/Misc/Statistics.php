<?php
namespace WPDRMS\ASP\Misc;

defined('ABSPATH') or die("You can't access this file directly.");


class Statistics {
	static function addKeyword($id, $s) {
		global $wpdb;

		if ( trim($s) == '' ) {
			$s = '[no keyword]';
		}
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		$in = $wpdb->query(
			$wpdb->prepare(
				"UPDATE " . wd_asp()->db->table('stats') . " SET num=num+1, last_date=%d WHERE (keyword='%s' AND search_id=%d)",
				time(),
				strip_tags($s),
				$id
			)
		);
		if ($in == false) {
			return $wpdb->query(
				$wpdb->prepare(
					"INSERT INTO " . wd_asp()->db->table('stats') . " (search_id, keyword, num, last_date) VALUES (%d, '%s', 1, %d)",
					$id,
					strip_tags($s),
					time()
				)
			);
		}
		return $in;
	}

	static function getTop($count, $id=0, $exclude_empty = false) {
		global $wpdb;

		$fields = "id, search_id, keyword, num, last_date";
		$where = "";
		$group_by = "";
		$id = $id + 0;
		if ( $id > 0 ) {
			$where = " WHERE search_id=" . $id;
			if ( $exclude_empty )
				$where .= " AND keyword NOT LIKE '[no keyword]' ";
		} else {
			$fields = "id, search_id, keyword, SUM(num) as num, last_date";
			$group_by = " GROUP BY keyword ";
			if ( $exclude_empty )
				$where = " WHERE keyword NOT LIKE '[no keyword]' ";
		}

		return $wpdb->get_results($wpdb->prepare(
			"SELECT $fields FROM " . wd_asp()->db->table('stats') . " " . $where . " $group_by ORDER BY num DESC LIMIT %d",
			$count
		)
			,ARRAY_A
		);
	}

	static function getLast($count, $id=0, $exclude_empty = false) {
		global $wpdb;

		$fields = "id, search_id, keyword, num, last_date";
		$where = "";
		$group_by = "";
		$id = $id + 0;
		if ( $id > 0 ) {
			$where = " WHERE search_id=" . $id;
			if ( $exclude_empty )
				$where .= " AND keyword NOT LIKE '[no keyword]' ";
		} else {
			$fields = "id, search_id, keyword, SUM(num) as num, last_date";
			$group_by = " GROUP BY keyword ";
			if ( $exclude_empty )
				$where = " WHERE keyword NOT LIKE '[no keyword]' ";
		}

		return $wpdb->get_results($wpdb->prepare(
			"SELECT $fields FROM " . wd_asp()->db->table('stats') . " " . $where . " $group_by ORDER BY last_date DESC LIMIT %d",
			$count
		)
			,ARRAY_A
		);
	}

	static function clearAll() {
		global $wpdb;

		return $wpdb->query("DELETE FROM " . wd_asp()->db->table('stats'));
	}

	static function deleteKw($id) {
		global $wpdb;

		return $wpdb->query(
			$wpdb->prepare(
				"DELETE FROM " . wd_asp()->db->table('stats') . " WHERE id=%d"
				, ($id+0)
			)
		) ;
	}
}