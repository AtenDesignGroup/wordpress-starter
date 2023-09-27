<?php
/**
 * View: Venue - Single Venue Featured Image
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/venue/meta/featured-image.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @version 6.2.0
 * @since 6.2.0
 *
 * @var WP_Post $venue The event post object with properties added by the `tribe_get_event` function.
 *
 * @see tribe_get_event() For the format of the event object.
 */

if ( ! $venue->thumbnail->exists ) {
	return;
}

$has_details = $has_details ?? false;

$classes = [
	'tribe-events-pro-venue__meta-featured-image-wrapper',
	'tribe-events-pro-venue__meta-featured-image-wrapper--has-details' => $has_details,
];

?>
<div <?php tribe_classes( $classes ); ?>>
	<a
		href="<?php echo esc_url( $venue->permalink ); ?>"
		title="<?php echo esc_attr( $venue->title ); ?>"
		rel="bookmark"
		class="tribe-events-pro-venue__meta-featured-image-link"
		tabindex="-1"
	>
		<img
			src="<?php echo esc_url( $venue->thumbnail->full->url ); ?>"
			<?php if ( ! empty( $venue->thumbnail->srcset ) ) : ?>
				srcset="<?php echo esc_attr( $venue->thumbnail->srcset ); ?>"
			<?php endif; ?>
			<?php if ( ! empty( $venue->thumbnail->alt ) ) : ?>
				alt="<?php echo esc_attr( $venue->thumbnail->alt ); ?>"
			<?php else : // We need to ensure we have an empty alt tag for accessibility reasons if the user doesn't set one for the featured image ?>
				alt=""
			<?php endif; ?>
			<?php if ( ! empty( $venue->thumbnail->title ) ) : ?>
				title="<?php echo esc_attr( $venue->thumbnail->title ); ?>"
			<?php endif; ?>
			class="tribe-events-pro-venue__meta-featured-image"
		/>
	</a>
</div>
