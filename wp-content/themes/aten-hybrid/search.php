<?php
/**
 * The template for displaying search results pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#search-result
 *
 * @package WordPress
 * @subpackage Twenty_Twenty_One
 * @since Twenty Twenty-One 1.0
 */

get_header();

$search_post_type = isset($_GET['post_types']) ? $_GET['post_types'] : '';

if ( have_posts() && $search_post_type ) {
	// Get template part for search results based on type of resource
	$template_part = 'template-parts/archives/' . $search_post_type;
	get_template_part( $template_part );

	// If no content, include the "No posts found" template.
} else {
	get_template_part( 'template-parts/content/content-none' );
}

get_footer();
