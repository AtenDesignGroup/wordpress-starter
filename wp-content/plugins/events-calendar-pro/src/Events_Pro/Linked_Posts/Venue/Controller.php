<?php
/**
 * Service Provider for Linked_Posts\Venue functionality.
 *
 * @since 6.2.0
 *
 * @package TEC\Events_Pro\Linked_Posts\Venue
 */

namespace TEC\Events_Pro\Linked_Posts\Venue;

use TEC\Common\Contracts\Provider\Controller as Controller_Contract;
use Tribe__Events__Editor__Blocks__Event_Venue as Venue_Block;
use Tribe__Template as Template;
use WP_Post;

/**
 * Class Provider
 *
 * @since 6.2.0

 * @package TEC\Events_Pro\Linked_Posts\Venue
 */
class Controller extends Controller_Contract {
	/**
	 * Determines if this controller will register.
	 * This is present due to how UOPZ works, it will fail if method belongs to the parent/abstract class.
	 *
	 * @since 6.2.0
	 *
	 * @return bool
	 */
	public function is_active(): bool {
		return true;
	}

	/**
	 * Boot the Controller.
	 *
	 * This function is used to instantiate the singleton classes and register any other providers.
	 *
	 * @since   6.2.0
	 */
	public function boot() {
		$this->container->register( Taxonomy\Category::class );
		$this->container->singleton( Multiple_Modifier::class, Multiple_Modifier::class );

		// When rendering multi-venue, we need the block editor template.
		if ( ! $this->container->has( 'events.editor.template' ) ) {
			$this->container->singleton( 'events.editor.template', 'Tribe__Events__Editor__Template' );
		}

		// When rendering multi-venue, we need the venue block.
		if ( ! $this->container->has( 'events.editor.blocks.event-venue' ) ) {
			$this->container->singleton( 'events.editor.blocks.event-venue', Venue_Block::class, [ 'load' ] );
		}
	}

	/**
	 * Register the controller.
	 *
	 * @since 6.2.0
	 */
	public function do_register(): void {
		$this->boot();

		$this->add_actions();
		$this->add_filters();
	}

	/**
	 * Unregister the controller.
	 *
	 * @since 6.2.0
	 *
	 * @return void
	 */
	public function unregister(): void {
		$this->remove_actions();
		$this->remove_filters();
	}

	/**
	 * Add the action hooks.
	 *
	 * @since 6.2.0
	 *
	 * @return void
	 */
	protected function add_actions(): void {
		add_action( 'wp_enqueue_scripts', [ $this, 'action_maybe_enqueue_venue_block_styles' ] );
		add_action( 'tec_events_after_venue_map_fields', [ $this, 'action_show_map_field_hint' ] );
		add_action( 'tec_events_view_venue_after_address', [ $this, 'action_append_multi_venue_suffix' ], 10, 2 );
	}

	/**
	 * Add the action hooks.
	 *
	 * @since 6.2.0
	 *
	 * @return void
	 */
	protected function add_filters(): void {
		add_filter( 'tec_events_blocks_event_venue_id', [ $this, 'filter_venue_id_to_venue_id_in_block' ], 10, 2 );
		add_filter( 'tribe_events_linked_post_type_args', [ $this, 'filter_enable_multi_venue_support' ], 50, 2 );

		// Frontend rendering.
		add_filter( 'tribe_get_template_part_templates', [ $this, 'filter_template_parts_to_include_modules_meta' ], 10, 2 );
		add_filter( 'tribe_get_template_part_templates', [ $this, 'filter_template_parts_to_include_modules_meta_venue' ], 10, 2 );
		add_filter( 'tec_block_tribe/event-venue_has_block', [ $this, 'filter_has_venue_block' ], 10, 4 );
		add_filter( 'tec_events_views_v2_assets_should_enqueue_single_event_block_editor_styles', [ $this, 'filter_should_enqueue_block_styles' ] );
		add_filter( 'tec_events_blocks_event_venue_should_enqueue_assets', [ $this, 'filter_should_enqueue_block_styles' ] );
	}

	/**
	 * Removes actions.
	 *
	 * @since 6.2.0
	 *
	 * @return void
	 */
	protected function remove_actions(): void {
		remove_action( 'wp_enqueue_scripts', [ $this, 'action_maybe_enqueue_venue_block_styles' ] );
		remove_action( 'tec_events_after_venue_map_fields', [ $this, 'action_show_map_field_hint' ] );
		remove_action( 'tec_events_view_venue_after_address', [ $this, 'action_append_multi_venue_suffix' ] );
	}

	/**
	 * Removes filters.
	 *
	 * @since 6.2.0
	 *
	 * @return void
	 */
	protected function remove_filters(): void {
		remove_filter( 'tec_events_blocks_event_venue_id', [ $this, 'filter_venue_id_to_venue_id_in_block' ] );
		remove_filter( 'tribe_events_linked_post_type_args', [ $this, 'filter_enable_multi_venue_support' ], 50 );

		remove_filter( 'tribe_get_template_part_templates', [ $this, 'filter_template_parts_to_include_modules_meta' ] );
		remove_filter( 'tribe_get_template_part_templates', [ $this, 'filter_template_parts_to_include_modules_meta_venue' ] );
		remove_filter( 'tec_block_tribe/event-venue_has_block', [ $this, 'filter_has_venue_block' ] );
		remove_filter( 'tec_events_views_v2_assets_should_enqueue_single_event_block_editor_styles', [ $this, 'filter_should_enqueue_block_styles' ] );
		remove_filter( 'tec_events_blocks_event_venue_should_enqueue_assets', [ $this, 'filter_should_enqueue_block_styles' ] );
	}

	/**
	 * Appends the multi-venue suffix to views.
	 *
	 * @since 6.2.0
	 *
	 * @param \WP_Post $event Event post object.
	 * @param string $slug View slug.
	 *
	 * @return void
	 */
	public function action_append_multi_venue_suffix( $event, $slug ): void {
		$template = new Template();
		$template->set_template_origin( tribe( 'events-pro.main' ) );
		$template->set_template_folder( 'src/views' );
		$template->set_template_folder_lookup( true );
		$template->set_template_context_extract( true );
		$template->template( 'v2/components/multi-venue/suffix', [ 'event' => $event, 'slug' => $slug ] );
	}

	/**
	 * Maybe enqueue the venue block styles.
	 *
	 * @since 6.2.0
	 *
	 * @return void
	 */
	public function action_maybe_enqueue_venue_block_styles() {
		/** @var Multiple_Modifier $multiple_modifier */
		$multiple_modifier = $this->container->get( Multiple_Modifier::class );
		$multiple_modifier->maybe_enqueue_venue_block_styles();
	}

	/**
	 * Shows the map link hint text.
	 *
	 * @since 6.2.0
	 *
	 * @return void
	 */
	public function action_show_map_field_hint(): void {
		?>
		<p class="description">
			<?php esc_html_e( 'This setting applies to all of the venues added to the event.', 'tribe-events-calendar-pro' ); ?>
		</p>
		<?php
	}

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
	public function filter_enable_multi_venue_support( $args, $post_type ) {
		/** @var Multiple_Modifier $multiple_modifier */
		$multiple_modifier = $this->container->get( Multiple_Modifier::class );
		return $multiple_modifier->enable_support_in_args( (array) $args, (string) $post_type );
	}

	/**
	 * Filters the venue ID to the venue ID in the block.
	 *
	 * @since 6.2.0
	 *
	 * @param int $venue_id Venue ID.
	 * @param array<string, mixed> $attributes Attributes from block.
	 *
	 * @return int|mixed
	 */
	public function filter_venue_id_to_venue_id_in_block( $venue_id, $attributes ) {
		/** @var Multiple_Modifier $multiple_modifier */
		$multiple_modifier = $this->container->get( Multiple_Modifier::class );
		return $multiple_modifier->get_venue_id_from_attributes( (int) $venue_id, (array) $attributes );
	}

	/**
	 * Filters the templates for the meta module to use the pro version.
	 *
	 * @since 6.2.0
	 *
	 * @param array $templates Array of templates.
	 * @param string $slug Template slug.
	 *
	 * @return string[]
	 */
	public function filter_template_parts_to_include_modules_meta( $templates, $slug ) {
		/** @var Multiple_Modifier $multiple_modifier */
		$multiple_modifier = $this->container->get( Multiple_Modifier::class );
		return $multiple_modifier->filter_template_parts_to_include_override( (array) $templates, (string) $slug, 'modules/meta' );
	}

	/**
	 * Filters the templates for the meta/venue module to use the pro version.
	 *
	 * @since 6.2.0
	 *
	 * @param array  $templates Array of templates.
	 * @param string $slug      Template slug.
	 *
	 * @return string[]
	 */
	public function filter_template_parts_to_include_modules_meta_venue( $templates, $slug ) {
		/** @var Multiple_Modifier $multiple_modifier */
		$multiple_modifier = $this->container->get( Multiple_Modifier::class );
		return $multiple_modifier->filter_template_parts_to_include_override( (array) $templates, (string) $slug, 'modules/meta/venue' );
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
	public function filter_has_venue_block( $has_block, ?WP_Post $post, ?int $post_id ): bool {
		/** @var Multiple_Modifier $multiple_modifier */
		$multiple_modifier = $this->container->get( Multiple_Modifier::class );
		return $multiple_modifier->filter_has_venue_block( (bool) $has_block, $post, $post_id );
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
	public function filter_should_enqueue_block_styles( $should_enqueue ): bool {
		/** @var Multiple_Modifier $multiple_modifier */
		$multiple_modifier = $this->container->get( Multiple_Modifier::class );
		return $multiple_modifier->filter_should_enqueue_block_styles( (bool) $should_enqueue );
	}
}
