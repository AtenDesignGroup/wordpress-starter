<?php
/**
 * Class used to register a new series post type.
 *
 * @since 6.0.0
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Series;

use WP_Error;
use WP_Post;
use WP_Post_Type;
use Tribe__Events__Main as TEC;

/**
 * Class Series
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Series
 */
class Post_Type {
	/**
	 * Post Type name.
	 *
	 * @since 6.0.0
	 *
	 * @var string
	 */
	const POSTTYPE = 'tribe_event_series';

	/**
	 * Key to cache the flush rewrite.
	 *
	 * @since 6.0.0
	 *
	 * @var string
	 */
	const FLUSH_REWRITE_TRANSIENT = 'tribe_events_series_flush_rewrite';

	/**
	 * Args for series post type
	 *
	 * @since 6.0.0
	 *
	 * @var array
	 */
	public $post_type_args = [
		'public'             => true,
		'publicly_queryable' => true,
		'show_in_rest'       => false,
		'supports'           => [
			'title',
			'editor',
			'author',
		],
		'show_in_menu'       => false,
		'rewrite'            => [
			'with_front' => false,
		],
		'has_archive'        => false,
	];

	/**
	 * Singular label.
	 *
	 * @since 6.0.0
	 *
	 * @var string
	 */
	public $singular_label;

	/**
	 * Lowercase singular label.
	 *
	 * @since 6.0.0
	 *
	 * @var string
	 */
	public $singular_label_lowercase;

	/**
	 * Plural label.
	 *
	 * @since 6.0.0
	 *
	 * @var string
	 */
	public $plural_label;

	/**
	 * Lowercase plural label.
	 *
	 * @since 6.0.0
	 *
	 * @var string
	 */
	public $plural_label_lowercase;

	/**
	 * Constructor.
	 *
	 * @since 6.0.0
	 */
	public function __construct() {
		$this->singular_label           = $this->get_label_singular();
		$this->singular_label_lowercase = $this->get_label_singular_lowercase();
		$this->plural_label             = $this->get_label_plural();
		$this->plural_label_lowercase   = $this->get_label_plural_lowercase();

		$this->post_type_args['rewrite']['slug'] = tribe( 'events.rewrite' )->prepare_slug( $this->singular_label, static::POSTTYPE, false );
		$this->post_type_args['label']           = $this->plural_label;
		$this->post_type_args['show_in_menu']    = sprintf( 'edit.php?post_type=%s', TEC::POSTTYPE );

		/**
		 * Provides an opportunity to modify the labels used for the series post type.
		 *
		 * @since 6.0.0
		 *
		 * @param array $args Array of arguments for register_post_type labels
		 */
		$this->post_type_args['labels'] = apply_filters( 'tribe_events_register_series_post_type_labels', [
			'name'                     => $this->plural_label,
			'singular_name'            => $this->singular_label,
			'singular_name_lowercase'  => $this->singular_label_lowercase,
			'plural_name_lowercase'    => $this->plural_label_lowercase,
			'add_new'                  => esc_html__( 'Add New', 'tribe-events-calendar-pro' ),
			'add_new_item'             => sprintf( esc_html__( 'Add New %s', 'tribe-events-calendar-pro' ), $this->singular_label ),
			'edit_item'                => sprintf( esc_html__( 'Edit %s', 'tribe-events-calendar-pro' ), $this->singular_label ),
			'new_item'                 => sprintf( esc_html__( 'New %s', 'tribe-events-calendar-pro' ), $this->singular_label ),
			'view_item'                => sprintf( esc_html__( 'View %s', 'tribe-events-calendar-pro' ), $this->singular_label ),
			'search_items'             => sprintf( esc_html__( 'Search %s', 'tribe-events-calendar-pro' ), $this->plural_label ),
			'not_found'                => sprintf( esc_html__( 'No %s found', 'tribe-events-calendar-pro' ), $this->plural_label_lowercase ),
			'not_found_in_trash'       => sprintf( esc_html__( 'No %s found in Trash', 'tribe-events-calendar-pro' ), $this->plural_label_lowercase ),
			'item_published'           => sprintf( esc_html__( '%s published.', 'tribe-events-calendar-pro' ), $this->singular_label ),
			'item_published_privately' => sprintf( esc_html__( '%s published privately.', 'tribe-events-calendar-pro' ), $this->singular_label ),
			'item_reverted_to_draft'   => sprintf( esc_html__( '%s reverted to draft.', 'tribe-events-calendar-pro' ), $this->singular_label ),
			'item_scheduled'           => sprintf( esc_html__( '%s scheduled.', 'tribe-events-calendar-pro' ), $this->singular_label ),
			'item_updated'             => sprintf( esc_html__( '%s updated.', 'tribe-events-calendar-pro' ), $this->singular_label ),
			'item_link'                => sprintf(
			// Translators: %s: Series singular.
				esc_html__( '%s Link', 'tribe-events-calendar-pro' ), $this->singular_label
			),
			'item_link_description'    => sprintf(
			// Translators: %s: Series singular.
				esc_html__( 'A link to a particular %s.', 'tribe-events-calendar-pro' ), $this->singular_label
			),
		] );
	}

	/**
	 * Gets the post type args.
	 *
	 * @since 6.0.0
	 *
	 * @return array<string, mixed> An array used to register a new post type.
	 */
	public function get_post_type_args() {
		$args = $this->post_type_args;

		/**
		 * Gets the series post type args.
		 *
		 * @param array<string, mixed> $post_type_args Post Type args.
		 */
		return apply_filters( 'tribe_events_register_series_type_args', $args );
	}

	/**
	 * Registers the post type.
	 *
	 * @since 6.0.0
	 *
	 * @return WP_Error|WP_Post_Type The registered post type or error if it was not registered correctly.
	 */
	public function register_post_type() {
		if ( post_type_exists( static::POSTTYPE ) ) {
			return get_post_type_object( static::POSTTYPE );
		}

		return register_post_type( static::POSTTYPE, $this->get_post_type_args() );
	}

	/**
	 * Since we are adding a new post type, let's flush rewrite rules this once.
	 *
	 * @since 6.0.0
	 */
	public function flush_rewrite() {
		if ( ! get_transient( static::FLUSH_REWRITE_TRANSIENT ) ) {
			set_transient( static::FLUSH_REWRITE_TRANSIENT, true, MONTH_IN_SECONDS );
			flush_rewrite_rules();
		}
	}

	/**
	 * Allow users to specify their own singular label for Series.
	 *
	 * @since 6.0.0
	 *
	 * @return string
	 */
	public function get_label_singular() : string {
		/**
		 * Filter the series post type singular label.
		 *
		 * @param string $label The label.
		 */
		return apply_filters( 'tribe_series_label_singular', _x( 'Series', 'Singular name of the Series post type.', 'tribe-events-calendar-pro' ) );
	}

	/**
	 * Allow users to specify their own plural label for Series.
	 *
	 * @return string
	 */
	public function get_label_plural() : string {
		/**
		 * Filter the series post type plural label.
		 *
		 * @param string $label The label.
		 */
		return apply_filters( 'tribe_series_label_plural', _x( 'Series', 'Plural name of the Series post type', 'tribe-events-calendar-pro' ) );
	}

	/**
	 * Allow users to specify their own lowercase singular label for Series.
	 * @return string
	 */
	public function get_label_singular_lowercase() : string {
		/**
		 * Filter the series post type singular lowercase label.
		 *
		 * @param string $label The label.
		 */
		return apply_filters( 'tribe_series_label_singular_lowercase', _x( 'series', 'Lowercase singular name of the Series post type', 'tribe-events-calendar-pro' ) );
	}

	/**
	 * Allow users to specify their own lowercase plural label for Series.
	 *
	 * @return string
	 */
	public function get_label_plural_lowercase() : string {
		/**
		 * Filter the series post type plural lowercase label.
		 *
		 * @param string $label The label.
		 */
		return (string) apply_filters( 'tribe_series_label_plural_lowercase', _x( 'series', 'Lowercase plural name of the Series post type', 'tribe-events-calendar-pro' ) );
	}

	/**
	 * Detect if the provided post is of the same type as the current post type.
	 *
	 * @since 6.0.0
	 *
	 * @param WP_Post|null $post The post object to compare against with.
	 *
	 * @return boolean `true` if is of $post is of the same type as the current post type, `false` otherwise.
	 */
	public function is_same_type( WP_Post $post = null ) {
		return $post instanceof WP_Post && $post->post_type === static::POSTTYPE;
	}

	/**
	 * Registers the Series post type or throws on failure.
	 *
	 * @since 6.0.2
	 *
	 * @return WP_Post_Type The registered Series post type.
	 *
	 * @throws \RuntimeException If the post type registration fails.
	 */
	public function register_post_type_or_fail(): WP_Post_Type {
		$registered = $this->register_post_type();
		if ( ! $registered instanceof WP_Post_Type ) {
			throw new \RuntimeException( 'Failed to register the Series post type' );
		}

		return $registered;
	}
}
