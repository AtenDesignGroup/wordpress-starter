<?php
/**
 * The main template file
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
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
