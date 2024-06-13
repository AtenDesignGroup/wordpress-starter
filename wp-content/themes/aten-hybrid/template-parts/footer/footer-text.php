<?php
/**
 * Displays the footer text widget area.
 *
 * @package WordPress
 * @subpackage ccc
 */

if ( is_active_sidebar( 'sidebar-2' ) ) : ?>

	<aside class="widget-area">
		<?php dynamic_sidebar( 'sidebar-2' ); ?>
	</aside><!-- .widget-area -->

<?php
endif;
