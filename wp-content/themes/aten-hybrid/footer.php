<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package WordPress
 * @subpackage Twenty_Twenty_One
 * @since Twenty Twenty-One 1.0
 */

?>

		</div><!-- #primary -->
	</div><!-- #content -->
</main><!-- #main -->

    <?php get_template_part('legal-disclaimer'); ?>

<footer id="colophon" class="site-footer" role="contentinfo">

		<div class="site-info">
			<div class="site-logo">
                <figure class="footer-logo wp-block-image size-full">
                    <a href="/" class="footer-logo-link">
						<img decoding="async" loading="lazy" src="/wp-content/themes/aten-hybrid/assets/images/logos/logo.svg" alt="Cost & Coverage Collective logo" class="mobile-only" />
                        <img decoding="async" loading="lazy" src="/wp-content/themes/aten-hybrid/assets/images/logos/logo.svg" alt="Cost & Coverage Collective logo" class="tablet-up" />
                    </a>
                </figure>
			</div><!-- .site-logo -->

			<?php if ( has_nav_menu( 'footer' ) ) : ?>
                <nav aria-label="<?php esc_attr_e( 'footer', 'ccc' ); ?>" class="footer-navigation">
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'footer',
							'container'      => false,
							'depth'          => 1,
							'fallback_cb'    => false,
						)
					);
					?>
                </nav><!-- .footer-navigation -->
			<?php endif; ?>

            <div class="project-information-text">
	            <?php get_template_part( 'template-parts/footer/footer-text' ); ?>
            </div>

			<div class="top-arrow-wrapper">
				<a href="#ccc-site" id="back-to-top" class="back-to-top" aria-label="Scroll back to top">
					<icon>
						<img src="/wp-content/themes/aten-hybrid/assets/icons/backtotop.svg" alt="arrow up icon" class="static-version">
						<img src="/wp-content/themes/aten-hybrid/assets/icons/backtotop-hover.svg" alt="arrow up icon" class="hover-version">
					</icon>
				</a>
			</div>

		</div><!-- .site-info -->
	</footer><!-- #colophon -->

</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
