<?php

namespace TEC\Events_Pro\Linked_Posts\Venue;

use Tribe__Events__Venue as Venue;
use WP_Post;

class Multiple_Modifier {
	/**
	 * Filters the enabling of support for multiple venues.
	 *
	 * @since 6.2.0
	 *
	 * @param array  $args      Array of Linked Post arguments.
	 * @param string $post_type Post type.
	 *
	 * @return array
	 */
	public function enable_support_in_args( array $args, string $post_type ): array {
		if ( Venue::POSTTYPE !== $post_type ) {
			return $args;
		}

		$args['allow_multiple'] = true;

		return $args;
	}

	/**
	 * Filters whether the event venue block should be shown.
	 *
	 * @since 6.2.0
	 *
	 * @param bool     $has_block Whether the block has the block.
	 * @param ?WP_Post $post      Post object.
	 * @param ?int     $post_id   Post ID.
	 *
	 * @return bool
	 */
	public function filter_has_venue_block( bool $has_block, ?WP_Post $post, ?int $post_id ): bool {
		if ( ! $post_id ) {
			return $has_block;
		}

		if ( ! tec_get_venue_ids( $post_id ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Filters whether the event venue block should be shown.
	 *
	 * @since 6.2.0
	 *
	 * @param bool $should_enqueue Whether the block styles should enqueue.
	 *
	 * @return bool
	 */
	public function filter_should_enqueue_block_styles( bool $should_enqueue ): bool {
		$context = tribe_context();

		if (
			! $context->is( 'single' )
			|| ! $context->is( 'event_post_type' )
		) {
			return $should_enqueue;
		}

		$post_id = $context->get( 'post_id' );

		if ( ! $post_id ) {
			return $should_enqueue;
		}

		if ( ! tec_get_venue_ids( $post_id ) ) {
			return $should_enqueue;
		}

		return true;
	}

	/**
	 * Filters the templates for the meta module to use the pro version.
	 *
	 * @since 6.2.0
	 *
	 * @param array $templates Array of templates.
	 * @param string $slug Template slug.
	 * @param string $override Template slug override.
	 *
	 * @return string[]
	 */
	public function filter_template_parts_to_include_override( array $templates, string $slug, string $override ): array {
		if ( $slug !== $override ) {
			return $templates;
		}

		$venue_ids = tec_get_venue_ids();
		if ( count( $venue_ids ) < 2 ) {
			return $templates;
		}

		return [ "pro/{$override}" ];
	}

	/**
	 * Filters the venue ID to the venue ID in the block.
	 *
	 * @since 6.2.0
	 *
	 * @param int $venue_id Venue ID.
	 * @param array<string, mixed> $attributes Attributes from block.
	 *
	 * @return int
	 */
	public function get_venue_id_from_attributes( int $venue_id, array $attributes ): int {
		if ( ! isset( $attributes['venue'] ) ) {
			return $venue_id;
		}

		return (int) $attributes['venue'];
	}

	/**
	 * Maybe enqueue the venue block styles.
	 *
	 * @since 6.2.0
	 *
	 * @return void
	 */
	public function maybe_enqueue_venue_block_styles() {
		$context = tribe_context();

		if (
			! $context->is( 'single' )
			|| ! $context->is( 'event_post_type' )
		) {
			return;
		}

		tribe( 'events.editor.blocks.event-venue' )->load();
	}
}