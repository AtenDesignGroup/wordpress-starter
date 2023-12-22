<?php

namespace TEC\Events_Pro\Linked_Posts\Organizer\Taxonomy;

use TEC\Events_Pro\Linked_Posts\Contracts\Taxonomy_Abstract;
use Tribe\Events\Pro\Views\V2\Views\Organizer_View;
use Tribe__Events__Organizer as Organizer;

/**
 * Class Category
 *
 * @since   6.2.0
 *
 * @package TEC\Events_Pro\Linked_Posts\Organizer\Taxonomy
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
		return 'tec_organizer_category';
	}

	/**
	 * @inheritDoc
	 */
	public function get_slug(): string {
		return 'organizer_category';
	}

	/**
	 * @inheritDoc
	 */
	protected function define_singular_label_without_linked_post(): ?string {
		return _x( 'Category', 'Singular name of Organizer Category, without the Linked Post label', 'tribe-events-calendar-pro' );
	}

	/**
	 * @inheritDoc
	 */
	protected function define_plural_label_without_linked_post(): ?string {
		return _x( 'Categories', 'Plural name of Organizer Category, without the Linked Post label', 'tribe-events-calendar-pro' );
	}

	/**
	 * @inheritDoc
	 */
	protected function define_rewrite_slug_singular(): ?string {
		/**
		 * DO NOT REMOVE THIS, HERE FOR TRANSLATION PURPOSES.
		 */
		_x( 'category', 'Singular name of Organizer Category, without the Linked Post label, for rewrite purposes.', 'tribe-events-calendar-pro' );

		return 'category';
	}

	/**
	 * @inheritDoc
	 */
	protected function define_rewrite_slug_plural(): ?string {
		/**
		 * DO NOT REMOVE THIS, HERE FOR TRANSLATION PURPOSES.
		 */
		_x( 'categories', 'Plural name of Organizer Category, without the Linked Post label, for rewrite purposes.', 'tribe-events-calendar-pro' );

		return 'categories';
	}

	/**
	 * @inheritDoc
	 */
	protected function define_linked_post_type_rewrite_slug_singular(): ?string {
		return 'organizer';
	}

	/**
	 * @inheritDoc
	 */
	protected function define_linked_post_type_rewrite_slug_plural(): ?string {
		return 'organizers';
	}

	/**
	 * @inheritDoc
	 */
	protected function define_linked_post_type(): ?string {
		return Organizer::POSTTYPE;
	}

	/**
	 * @inheritDoc
	 */
	protected function define_linked_post_type_view_slug(): ?string {
		return Organizer_View::get_view_slug();
	}

	/**
	 * @inheritDoc
	 */
	protected function define_linked_post_type_label_singular(): ?string {
		return tribe_get_organizer_label_singular();
	}

	/**
	 * @inheritDoc
	 */
	protected function define_linked_post_type_label_singular_lowercase(): ?string {
		return strtolower( tribe_get_organizer_label_singular() );
	}

	/**
	 * @inheritDoc
	 */
	protected function define_linked_post_type_label_plural(): ?string {
		return tribe_get_organizer_label_plural();
	}

	/**
	 * @inheritDoc
	 */
	protected function define_linked_post_type_label_plural_lowercase(): ?string {
		return strtolower( tribe_get_organizer_label_plural() );
	}

	/**
	 * @inheritDoc
	 */
	protected function define_linked_post_type_repository(): ?\Tribe__Repository__Interface {
		return tribe_organizers();
	}
}