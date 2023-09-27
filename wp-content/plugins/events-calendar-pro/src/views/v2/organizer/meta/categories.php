<?php
/**
 * View: Organizer meta details - Categories
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events-pro/v2/organizer/meta/categories.php
 *
 * See more documentation about our views templating system.
 *
 * @link    https://evnt.is/1aiy
 *
 * @version 6.2.0
 * @since   6.2.0
 *
 * @var WP_Post $organizer The organizer post object.
 *
 */

$categories = tec_events_pro_get_organizer_categories( $organizer->ID );

if ( empty( $categories ) ) {
	return;
}

$index = 0;
?>
<div class="tribe-events-pro-organizer__meta-categories tribe-common-b1 tribe-common-b2--min-medium">
	<span class="tribe-events-pro-organizer__meta-categories-label">
		<?php printf( '%1$s: ', tribe( \TEC\Events_Pro\Linked_Posts\Organizer\Taxonomy\Category::class )->get_plural_label_without_linked_post() ); ?>
	</span>
	<?php
	foreach ( $categories as $category_id => $category_name ) :
		$category = get_term( $category_id );
		if ( empty( $category ) ) {
			continue;
		}

		$index ++;
		$classes = [
			'tribe-events-pro-organizer__meta-categories-term-name',
			"tribe-events-pro-organizer__meta-categories-term--{$category->slug}",
		];
		?>
		<span <?php tribe_classes( $classes ); ?>>
			<?php
			// These two are intentionally printed with echos inside a single PHP tag to avoid having a space between them.
			echo '<a class="tribe-events-pro-organizer__meta-categories-term-link tribe-common-anchor" data-js="tribe-events-view-link" href="' . esc_url( get_term_link( $category ) ) . '">' . esc_html( $category_name ) . '</a>';
			if ( count( $categories ) !== $index ) {
				echo '<span class="tribe-events-pro-organizer__meta-categories-term-separator">,</span>';
			}
			?>
		</span>
	<?php endforeach; ?>
</div>
