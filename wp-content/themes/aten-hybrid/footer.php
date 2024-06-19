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

<footer id="colophon" class="site-footer" role="contentinfo">

		<div class="site-info">
			<div class="site-logo">
                <figure class="footer-logo wp-block-image size-full">
                    <a href="/" class="footer-logo-link">
												<img decoding="async" loading="lazy" src="/wp-content/themes/aten-hybrid/assets/logo.svg" alt="Aten logo" class="mobile-only" />
                        <!-- <img decoding="async" loading="lazy" src="/wp-content/themes/aten-hybrid/assets/logo.svg" alt="Aten logo" class="tablet-up" /> -->
                    </a>
                </figure>
			</div><!-- .site-logo -->

			<?php if ( has_nav_menu( 'footer' ) ) : ?>
                <nav aria-label="<?php esc_attr_e( 'footer', 'aten-hybrid' ); ?>" class="footer-navigation">
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

			<div class="footer-text">
				<?php get_template_part( 'template-parts/footer/footer-text' ); ?>
			</div>

			<div class="top-arrow-wrapper">
				<a href="#aten-hybrid" id="back-to-top" class="back-to-top" aria-label="Scroll back to top">
					<icon>
						<img src="/wp-content/themes/aten-hybrid/assets/icons/arrow_circle_up.svg" alt="arrow up icon" class="static-version">
						<!-- <img src="/wp-content/themes/aten-hybrid/assets/icons/arrow_circle_up.svg" alt="arrow up icon" class="hover-version"> -->
					</icon>
				</a>
			</div>

		</div><!-- .site-info -->
	</footer><!-- #colophon -->

</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
