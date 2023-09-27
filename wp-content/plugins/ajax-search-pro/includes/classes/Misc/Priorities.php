<?php
namespace WPDRMS\ASP\Misc;

use WPDRMS\ASP\Utils\Str;

if (!defined('ABSPATH')) die('-1');


class Priorities {
	public static function count() {
		$count = get_site_option('_asp_priority_count');

		if ( $count === false ) {
			global $wpdb;
			$wpdb->get_var( "SELECT 1 FROM " . wd_asp()->db->table('priorities') . " LIMIT 1" );
			$count = $wpdb->num_rows;
			update_site_option('_asp_priority_count', $count);
		}
		return $count;
	}

	static function ajax_get_posts() {
		global $wpdb;
		parse_str($_POST['options'], $o);

		$w_post_type = '';
		$w_filter = '';
		$w_limit = (int)$o['p_asp_limit'] + 0;
		$w_limit = $w_limit < 1 ? 20 : $w_limit;
		$w_limit = $w_limit > 200 ? 200 : $w_limit;
		$pt = wd_asp()->db->table("priorities");

		if (isset($o['blog_id']) && $o['blog_id'] != 0 && is_multisite())
			switch_to_blog($o['p_asp_blog']);

		if (isset($o['p_asp_filter']) && $o['p_asp_filter'] != '') {
			$w_filter = "AND $wpdb->posts.post_title LIKE '%" . Str::escape($o['p_asp_filter']) . "%'";
		}

		if (isset($o['p_asp_post_type']) && $o['p_asp_post_type'] != 'all') {
			$w_post_type = "AND $wpdb->posts.post_type = '" . Str::escape($o['p_asp_post_type']) . "'";
		}

		if ( $o['p_asp_post_type'] == 'attachment' ) {
			$post_status = "$wpdb->posts.post_status IN ('inherit')";
		} else {
			$post_status = "$wpdb->posts.post_status IN ('publish', 'pending')";
		}

		$allowed_orderings = array(
			'id DESC', 'id ASC', 'title DESC', 'title ASC', 'priority DESC', 'priority ASC'
		);
		if ( !isset($o['p_asp_ordering']) || !in_array($o['p_asp_ordering'], $allowed_orderings) ) {
			$o['p_asp_ordering'] = 'id DESC';
		}

		$querystr = "
		SELECT
	  $wpdb->posts.post_title as title,
	  $wpdb->posts.ID as id,
	  $wpdb->posts.post_date as date,
	  $wpdb->users.user_nicename as author,
	  $wpdb->posts.post_type as post_type,
	  CASE WHEN $pt.priority IS NULL
			   THEN 100
			   ELSE $pt.priority
	  END AS priority
		FROM $wpdb->posts
	LEFT JOIN $wpdb->users ON $wpdb->users.ID = $wpdb->posts.post_author
	LEFT JOIN $pt ON ($pt.post_id = $wpdb->posts.ID AND $pt.blog_id = " . get_current_blog_id() . ")
	WHERE
	  $wpdb->posts.ID>0 AND
	  $post_status AND
	  $wpdb->posts.post_type NOT IN ('revision')
	  $w_post_type
	  $w_filter
	GROUP BY
	  $wpdb->posts.ID
	ORDER BY " . $o['p_asp_ordering'] . "
	LIMIT $w_limit";

		echo "!!PASPSTART!!" . json_encode($wpdb->get_results($querystr, OBJECT)) . '!!PASPEND!!';

		if (is_multisite())
			restore_current_blog();

		die();
	}


	/**
	 *
	 */
	static function ajax_set_priorities() {
		global $wpdb;
		$i = 0;
		parse_str($_POST['options'], $o);

		if ($o['p_blogid'] == 0)
			$o['p_blogid'] = get_current_blog_id();

		foreach ($o['priority'] as $k => $v) {

			// See if the value changed, count them
			if ($v != $o['old_priority'][$k]) {

				$i++;
				$query = "INSERT INTO ".wd_asp()->db->table("priorities")."
				(post_id, blog_id, priority)
				VALUES($k, " . $o['p_blogid'] . ", $v)
				ON DUPLICATE KEY UPDATE priority=" . $v;
				$wpdb->query($query);
			}
		}
		echo "!!PSASPSTART!!" . $i . "!!PSASPEND!!";

		if (is_multisite())
			restore_current_blog();

		// Cleanup
		$wpdb->query("DELETE FROM " . wd_asp()->db->table("priorities") . " WHERE priority=100");

		$wpdb->get_var( "SELECT 1 FROM ".wd_asp()->db->table("priorities")." LIMIT 1" );
		$count = $wpdb->num_rows;
		update_site_option('_asp_priority_count', $count);

		die();
	}
}