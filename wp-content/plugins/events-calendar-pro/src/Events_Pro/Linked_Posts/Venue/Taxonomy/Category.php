<?php

namespace TEC\Events_Pro\Linked_Posts\Venue\Taxonomy;

use TEC\Events_Pro\Linked_Posts\Contracts\Taxonomy_Abstract;
use Tribe\Events\Pro\Views\V2\Views\Venue_View;
use Tribe__Events__Venue as Venue;

/**
 * Class Category
 *
 * @since   6.2.0
 *
 * @package TEC\Events_Pro\Linked_Posts\Venue\Taxonomy
 */
class Category extends Taxonomy_Abstract {
	/**
	 * @inheritDoc
	 */
	protected function define_configuration(): array {
		return [];
	}

	/**
	 * @inheritDoc
	 */
	protected function define_labels(): array {
		return [];
	}

	/**
	 * @inheritDoc
	 */
	public function get_wp_slug(): string {
		return 'tec_venue_category';
	}

	/**
	 * @inheritDoc
	 */
	public function get_slug(): string {
		return 'venue_category';
	}

	/**
	 * @inheritDoc
	 */
	protected function define_singular_label_without_linked_post(): ?string {
		return _x( 'Category', 'Singular name of Venue Category, without the Linked Post label', 'tribe-events-calendar-pro' );
	}

	/**
	 * @inheritDoc
	 */
	protected function define_plural_label_without_linked_post(): ?string {
		return _x( 'Categories', 'Plural name of Venue Category, without the Linked Post label', 'tribe-events-calendar-pro' );
	}

	/**
	 * @inheritDoc
	 */
	protected function define_rewrite_slug_singular(): ?string {
		/**
		 * DO NOT REMOVE THIS, HERE FOR TRANSLATION PURPOSES.
		 */
		_x( 'category', 'Singular name of Venue Category, without the Linked Post label, for rewrite purposes.', 'tribe-events-calendar-pro' );

		return 'category';
	}

	/**
	 * @inheritDoc
	 */
	protected function define_rewrite_slug_plural(): ?string {
		/**
		 * DO NOT REMOVE THIS, HERE FOR TRANSLATION PURPOSES.
		 */
		_x( 'categories', 'Plural name of Venue Category, without the Linked Post label, for rewrite purposes.', 'tribe-events-calendar-pro' );

		return 'categories';
	}

	/**
	 * @inheritDoc
	 */
	protected function define_linked_post_type_rewrite_slug_singular(): ?string {
		return 'venue';
	}

	/**
	 * @inheritDoc
	 */
	protected function define_linked_post_type_rewrite_slug_plural(): ?string {
		return 'venues';
	}

	/**
	 * @inheritDoc
	 */
	protected function define_linked_post_type(): ?string {
		return Venue::POSTTYPE;
	}

	/**
	 * @inheritDoc
	 */
	protected function define_linked_post_type_view_slug(): ?string {
		return Venue_View::get_view_slug();
	}

	/**
	 * @inheritDoc
	 */
	protected function define_linked_post_type_label_singular(): ?string {
		return tribe_get_venue_label_singular();
	}

	/**
	 * @inheritDoc
	 */
	protected function define_linked_post_type_label_singular_lowercase(): ?string {
		return strtolower( tribe_get_venue_label_singular() );
	}

	/**
	 * @inheritDoc
	 */
	protected function define_linked_post_type_label_plural(): ?string {
		return tribe_get_venue_label_plural();
	}

	/**
	 * @inheritDoc
	 */
	protected function define_linked_post_type_label_plural_lowercase(): ?string {
		return strtolower( tribe_get_venue_label_plural() );
	}

	/**
	 * @inheritDoc
	 */
	protected function define_linked_post_type_repository(): ?\Tribe__Repository__Interface {
		return tribe_venues();
	}
}