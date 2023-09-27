<?php
/**
 * View: Summary View Nav Today Button
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events-pro/v2/summary/nav/today.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @var string $today_url The URL to the today page.
 *
 * @version 5.7.0
 */

?>
<li class="tribe-events-c-nav__list-item tribe-events-c-nav__list-item--today">
	<a
		href="<?php echo esc_url( $today_url ); ?>"
		class="tribe-common-b2 tribe-events-c-nav__today"
		data-js="tribe-events-view-link"
		aria-label="<?php echo esc_attr( $today_title ); ?>"
		title="<?php echo esc_attr( $today_title ); ?>"
	>
		<?php echo esc_html( $today_label ); ?>
	</a>
</li>
