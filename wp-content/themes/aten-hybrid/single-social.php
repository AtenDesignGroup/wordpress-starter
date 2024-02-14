<?php
/**
 * The template for displaying all single social posts
 *
 *
 * @package WordPress
 * @subpackage ccc
 */

get_header();

/* Start the Loop */
while ( have_posts() ) :
	the_post();

	get_template_part( 'template-parts/content/content-single-social' );

endwhile;

get_footer();
