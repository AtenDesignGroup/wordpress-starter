<?php
/**
 * The template for displaying archive research pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package WordPress
 * @subpackage ccc
 */

get_header();
?>

<?php if ( have_posts() ) : ?>
	<?php get_template_part( 'template-parts/archives/research' ); ?>
<?php else : ?>
	<?php get_template_part( 'template-parts/content/content-none' ); ?>
<?php endif; ?>

<?php
get_footer();