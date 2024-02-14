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
?>

<?php if ( have_posts() ) : ?>
	<?php get_template_part( 'template-parts/archives/message' ); ?>
<?php else : ?>
	<?php get_template_part( 'template-parts/content/content-none' ); ?>
<?php endif; ?>

<?php
get_footer();