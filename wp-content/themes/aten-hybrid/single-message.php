<?php
/**
 * The template for displaying all single message posts
 *
 *
 * @package WordPress
 * @subpackage ccc
 */

get_header();

/* Start the Loop */
while ( have_posts() ) :
	the_post();

	get_template_part( 'template-parts/content/content-single-message' );

endwhile;

get_footer();
