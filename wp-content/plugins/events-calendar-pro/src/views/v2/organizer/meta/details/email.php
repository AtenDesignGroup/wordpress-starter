<?php
/**
 * View: Organizer meta details - Email
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events-pro/v2/organizer/meta/details/email.php
 *
 * See more documentation about our views templating system.
 *
 * @link https://evnt.is/1aiy
 *
 * @version 6.2.0
 * @since 6.2.0 Avoid rendering if the email should not be visible.
 *
 * @var WP_Post $organizer The organizer post object.
 *
 */

if ( ! tec_events_pro_organizer_email_is_visible( 'organizer-single' ) ) {
	return;
}


$email = tribe_get_organizer_email( $organizer->ID );

if ( empty( $email ) ) {
	return;
}

?>
<div class="tribe-events-pro-organizer__meta-email tribe-common-b1 tribe-common-b2--min-medium">
	<?php $this->template( 'components/icons/mail', [ 'classes' => [ 'tribe-events-pro-organizer__meta-email-icon-svg' ] ] ); ?>
	<a
		href="mailto:<?php echo esc_attr( $email ); ?>"
		class="tribe-events-pro-organizer__meta-email-link tribe-common-anchor"
	><?php echo esc_html( $email ); ?></a>
</div>
