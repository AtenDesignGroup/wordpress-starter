<?php
/**
 * The template for displaying archive message pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package WordPress
 * @subpackage ccc
 */

get_header();
if ( have_posts() ) : 
	get_template_part( 'template-parts/archives/message' );
else: 
	get_template_part( 'template-parts/content/content-none' );
endif;
get_footer();