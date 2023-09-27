<?php
/* Prevent direct access */
defined( 'ABSPATH' ) or die( "You can't access this file directly." ); ?>
<div id="asp-side-container">
	<a class="wd-accessible-switch" data-aenable="<?php esc_attr_e('ENABLE ACCESSIBILITY', 'ajax-search-pro'); ?>" data-adisable="<?php esc_attr_e('DISABLE ACCESSIBILITY', 'ajax-search-pro'); ?>" href="#"><?php echo isset($_COOKIE['asp-accessibility']) ?
	__('DISABLE ACCESSIBILITY', 'ajax-search-pro') :
	__('ENABLE ACCESSIBILITY', 'ajax-search-pro'); ?></a>
	<?php if ( isset($_GET['page'], $_GET['asp_sid']) && $_GET['page'] == 'asp_main_settings' ): ?>
	<h2><?php _e("Can't find an option?", 'ajax-search-pro' ); ?></h2>
	<input type="text" value="" id="asp-os-input" placeholder="<?php echo esc_attr__('Search in options', 'ajax-search-pro'); ?>">
	<div id="asp-os-results"></div>
	<?php endif; ?>
	<div class="newsletter">
		<h2>Subscribe to our newsletter</h2>
		<p>Get the latest news and updates</p>
		<form action="https://wp-dreams.us9.list-manage.com/subscribe/post?u=370663b5e3df02747aa5673ed&amp;id=65e28ba277&amp;f_id=00220ae1f0" method="post" name="mc-embedded-subscribe-form" target="_blank">
			<input name="EMAIL" id="email" type="email" placeholder="email@domain.com"><input type="submit" value="Subscribe" name="subscribe">
		</form>
	</div>
	<h2><?php _e("Socials", 'ajax-search-pro' ); ?></h2>
	<p class="socials">
		<a class="facebook" target="_blank" href="https://www.facebook.com/wpdreams">
			<svg width="18" height="18" aria-hidden="true" role="img" focusable="false">
				<svg id="ifacebook" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M400 32H48A48 48 0 000 80v352a48 48 0 0048 48h137.25V327.69h-63V256h63v-54.64c0-62.15 37-96.48 93.67-96.48 27.14 0 55.52 4.84 55.52 4.84v61h-31.27c-30.81 0-40.42 19.12-40.42 38.73V256h68.78l-11 71.69h-57.78V480H400a48 48 0 0048-48V80a48 48 0 00-48-48z"></path></svg>
			</svg>
			WPDreams
		</a>
		<a class="twitter" target="_blank" href="https://twitter.com/ernest_marcinko">
			<svg width="18" height="18" aria-hidden="true" role="img" focusable="false">
				<svg id="itwitter" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M459.37 151.716c.325 4.548.325 9.097.325 13.645 0 138.72-105.583 298.558-298.558 298.558-59.452 0-114.68-17.219-161.137-47.106 8.447.974 16.568 1.299 25.34 1.299 49.055 0 94.213-16.568 130.274-44.832-46.132-.975-84.792-31.188-98.112-72.772 6.498.974 12.995 1.624 19.818 1.624 9.421 0 18.843-1.3 27.614-3.573-48.081-9.747-84.143-51.98-84.143-102.985v-1.299c13.969 7.797 30.214 12.67 47.431 13.319-28.264-18.843-46.781-51.005-46.781-87.391 0-19.492 5.197-37.36 14.294-52.954 51.655 63.675 129.3 105.258 216.365 109.807-1.624-7.797-2.599-15.918-2.599-24.04 0-57.828 46.782-104.934 104.934-104.934 30.213 0 57.502 12.67 76.67 33.137 23.715-4.548 46.456-13.32 66.599-25.34-7.798 24.366-24.366 44.833-46.132 57.827 21.117-2.273 41.584-8.122 60.426-16.243-14.292 20.791-32.161 39.308-52.628 54.253z"></path></svg>
			</svg>
			Ernest Marcinko
		</a>
	</p>
	<div class="asp-back-help">
		<h2><?php echo __('Need help?', 'ajax-search-pro'); ?></h2>
		<p><?php echo sprintf( __('Check the <a href="%s">Help & Updates</a> menu for resources.', 'ajax-search-pro'), get_admin_url() . "admin.php?page=asp_updates_help" ); ?></p>
	</div>
</div>