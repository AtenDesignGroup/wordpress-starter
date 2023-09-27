<?php
/**
 * View: Organizer meta
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events-pro/v2/organizer/meta.php
 *
 * See more documentation about our views templating system.
 *
 * @link    https://evnt.is/1aiy
 *
 * @version 6.2.0
 * @since   6.2.0 Significantly reworked the logic to support the updated organizer meta and featured image rendering.
 *
 * @var WP_Post $organizer The organizer post object.
 *
 */

$classes = [ 'tribe-events-pro-organizer__meta', 'tribe-common-g-row' ];

$content            = tribe_get_the_content( null, false, $organizer->ID );
$url                = tribe_get_organizer_website_url( $organizer->ID );
$email              = tribe_get_organizer_email( $organizer->ID );
$phone              = tribe_get_organizer_phone( $organizer->ID );
$categories         = tec_events_pro_get_organizer_categories( $organizer->ID );
$has_featured_image = $organizer->thumbnail->exists;

$has_content  = ! empty( $content );
$has_details  = ! empty( $url ) || ! empty( $email ) || ! empty( $phone );
$has_taxonomy = ! empty( $categories );

if ( ! $has_content && ! $has_details && ! $has_featured_image && ! $has_taxonomy ) {
	return;
}

$classes['tribe-events-pro-organizer__meta--has-content']        = $has_content;
$classes['tribe-events-pro-organizer__meta--has-featured-image'] = $has_featured_image;
$classes['tribe-events-pro-organizer__meta--has-details']        = $has_details;
$classes['tribe-events-pro-organizer__meta--has-taxonomy']       = $has_taxonomy;

$conditionals = compact( 'has_content', 'has_details', 'has_featured_image', 'has_taxonomy' );
$template_vars = array_merge( [ 'organizer' => $organizer, ], $conditionals )
?>
<div <?php tribe_classes( $classes ); ?>>
	<div class="tec-events-c-view-box-border">
		<?php $this->template( 'organizer/meta/featured-image', $template_vars ); ?>

		<?php if ( $has_content || $has_details || $has_taxonomy ) : ?>
			<div <?php tribe_classes( [ 'tribe-events-pro-organizer__meta-data', 'tribe-common-g-col' => ( $has_content || $has_details || $has_taxonomy ) ] );?>>

				<div <?php tribe_classes( [ 'tribe-events-pro-organizer__meta-row', 'tribe-common-g-row' => ( $has_content || $has_details || $has_taxonomy ) ] );?>>

					<?php $this->template( 'organizer/meta/details', $template_vars ); ?>

					<?php $this->template( 'organizer/meta/content', $template_vars ); ?>

					<?php $this->template( 'organizer/meta/categories', $template_vars ); ?>

				</div>

			</div>
		<?php endif; ?>
	</div>
</div>
