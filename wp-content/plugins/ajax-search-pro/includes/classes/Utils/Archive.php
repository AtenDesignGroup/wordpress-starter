<?php
namespace WPDRMS\ASP\Utils;

defined('ABSPATH') or die("You can't access this file directly.");

class Archive {
	public static function isTaxonomyArchive(): bool {
		return is_archive() && ( is_tax() || is_category() || is_tag() );
	}

	public static function isPostTypeArchive(): bool {
		global $wp_query;
		// For posts archive it is a mess
		if ( isset($wp_query) ) {
			$is_post_archive = $wp_query->is_home && !$wp_query->is_single && ( $wp_query->get( 'post_type' ) == 'post' || strpos($wp_query->request, ".post_type = 'post'") !== false );
		} else {
			$is_post_archive = get_post_type() == 'post' && is_home() && !is_archive() && !self::isTaxonomyArchive();
		}
		return is_post_type_archive() || $is_post_archive;
	}

	public static function getCurrentArchiveURL(): string {
		$return = '';
		if ( self::isTaxonomyArchive() ) {
			$term_id = get_queried_object_id();
			if ( !empty($term_id) && !is_wp_error($term_id) ) {
				$return = get_term_link( $term_id );
			}
		} else if ( self::isPostTypeArchive() ) {
			if ( get_post_type() == 'post' ) {
				if ( empty(get_option( 'page_for_posts' )) ) {
					$return = get_site_url(get_current_blog_id(), '?post_type=post');
				} else {
					$return = get_permalink( get_option( 'page_for_posts' ) );
				}
			} else {
				$return = get_post_type_archive_link( get_post_type() );
			}
		}
		if ( defined('ICL_LANGUAGE_CODE') ) {
			$return = apply_filters( 'wpml_permalink', $return, ICL_LANGUAGE_CODE);
		}
		return $return;
	}
}