<?php
/**
 * View: Venue meta
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events-pro/v2/venue/meta.php
 *
 * See more documentation about our views templating system.
 *
 * @link    https://evnt.is/1aiy
 *
 * @version 6.2.0
 * @since   5.0.1
 * @since   6.2.0 Significantly reworked the logic to support the updated venue meta and featured image rendering.
 *
 * @var WP_Post $venue       The venue post object.
 * @var bool    $enable_maps Boolean on whether maps are enabled.
 * @var bool    $show_map    Boolean on whether to show map for this venue.
 *
 */

$classes = [ 'tribe-events-pro-venue__meta' ];

$address    = tribe_address_exists( $venue->ID );
$phone      = tribe_get_phone( $venue->ID );
$url        = tribe_get_venue_website_url( $venue->ID );
$content    = tribe_get_the_content( null, false, $venue->ID );
$categories = tec_events_pro_get_venue_categories( $venue->ID );

$has_details        = ! empty( $address ) || ! empty( $phone ) || ! empty( $url );
$has_content        = ! empty( $content );
$has_featured_image = $venue->thumbnail->exists;
$has_taxonomy       = ! empty( $categories );
$has_map            = ( $enable_maps && $show_map );

if ( ! $has_content && ! $has_details && ! $has_featured_image && ! $has_taxonomy && ! $has_map ) {
	return;
}
$classes['tribe-events-pro-venue__meta--has-map']            = $has_map;
$classes['tribe-events-pro-venue__meta--has-content']        = $has_content;
$classes['tribe-events-pro-venue__meta--has-featured-image'] = $has_featured_image;
$classes['tribe-events-pro-venue__meta--has-details']        = $has_details;
$classes['tribe-events-pro-venue__meta--has-taxonomy']       = $has_taxonomy;

$conditionals = compact( 'has_content', 'has_details', 'has_featured_image', 'has_taxonomy', 'has_map' );
$template_vars = array_merge( [ 'venue' => $venue, ], $conditionals )

?>
<div <?php tribe_classes( $classes ); ?>>
	<div class="tec-events-c-view-box-border">

		<div <?php tribe_classes( [ 'tribe-events-pro-venue__meta-row', 'tribe-common-g-row' => ( $has_content || $has_details || $has_taxonomy || ( $has_map && $has_featured_image ) ) ] ); ?>>

			<div <?php tribe_classes( [ 'tribe-events-pro-venue__meta-data', 'tribe-common-g-col' => ( $has_content || $has_details || $has_taxonomy || ( $has_map && $has_featured_image ) ) ] ); ?>>

				<?php $this->template( 'venue/meta/featured-image', $template_vars ); ?>

				<?php $this->template( 'venue/meta/details', $template_vars ); ?>

				<?php $this->template( 'venue/meta/content', $template_vars ); ?>

				<?php $this->template( 'venue/meta/categories', $template_vars ); ?>

			</div>

			<?php if ( $enable_maps && $show_map ) : ?>
				<div class="tribe-events-pro-venue__meta-map tribe-common-g-col">
					<?php $this->template( 'venue/meta/map', $template_vars ); ?>
				</div>
			<?php endif; ?>

		</div>

	</div>
</div>
