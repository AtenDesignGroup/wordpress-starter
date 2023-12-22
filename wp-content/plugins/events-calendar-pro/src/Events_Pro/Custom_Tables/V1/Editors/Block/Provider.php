<?php
/**
 * Handles the plugin integration with the Blocks Editor (Gutenberg) from the UI point of view.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Editors\Block
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Editors\Block;

use TEC\Events\Custom_Tables\V1\Migration\State;
use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use TEC\Events_Pro\Custom_Tables\V1\Editors\Event;
use TEC\Events_Pro\Custom_Tables\V1\Editors\Recurrence_Strings;
use TEC\Events_Pro\Custom_Tables\V1\Models\Provisional_Post;

use Tribe__Events__Pro__Editor as Pro_Editor;
use TEC\Common\Contracts\Service_Provider;

/**
 * Class Provider
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Editors\Block
 */
class Provider extends Service_Provider {


	/**
	 * Registers the plugin integration with the Blocks Editor if active for Events.
	 *
	 * @since 6.0.0
	 */
	public function register() {
		if ( ! tribe()->isBound( 'events-pro.editor' ) ) {
			return;
		}

		/** @var Pro_Editor $pro_editor */
		$pro_editor = tribe( 'events-pro.editor' );

		if ( ! $pro_editor->should_load_blocks() ) {
			return;
		}

		$this->container->singleton( Ajax::class );

		add_filter( 'tribe_events_pro_recurrence_strings', [ $this, 'filter_recurrence_strings' ] );
		add_filter( 'tribe_events_pro_editor_config', [ $this, 'filter_tribe_events_pro_editor_config' ] );
		add_filter( 'tec_events_pro_editor_meta_value', [ $this, 'redirect_edit_meta_values' ], 10, 3 );

		add_action( 'enqueue_block_editor_assets', [ $this, 'localize_ajax_information' ] );
		add_action( 'wp_ajax_' . Ajax::SERIES_ACTION, [ $this, 'handle_series_data_ajax' ] );
		add_action( 'wp_ajax_' . Ajax::REDIRECT_ACTION, [ $this, 'handle_redirect_data_ajax' ] );
		add_filter( 'tec_events_pro_blocks_recurrence_meta', [ $this, 'add_off_pattern_dtstart_flag' ], 10, 3 );

		$this->duplicate_hooks();
		$this->intercept_classic_blocks_conversion();
	}

	/**
	 * Determines if we should intercept the classic -> blocks conversion.
	 *
	 * @since 6.0.0
	 */
	protected function intercept_classic_blocks_conversion() {
		if ( ! isset( $_GET['post'] ) ) {
			return;
		}
		if ( tribe( 'editor' )->is_classic_editor() ) {
			return;
		}

		if ( ! tribe( Provisional_Post::class )->is_provisional_post_id( $_GET['post'] ) ) {
			return;
		}

		$blocks_cb = [ tribe( 'events.editor' ), 'flag_post_from_classic_editor' ];
		if ( has_action( 'load-post.php', $blocks_cb ) === false ) {
			return;
		}

		remove_action( 'load-post.php', $blocks_cb, 0 );
		add_action( 'load-post.php', [ $this, 'redirect_provisional_id_for_blocks' ], 0 );
	}

	/**
	 * Intercept occurrence_id and replace with the post_id, which is the intended target of the flag_post_from_classic_editor() logic. Several
	 * steps require this post_id, including postmeta flags and post_content updates, and they rely on the request containing the ID.
	 *
	 * @since 6.0.0
	 */
	public function redirect_provisional_id_for_blocks() {
		$occurrence_id = $_GET['post'];
		$occurrence    = Occurrence::find( tribe( Provisional_Post::class )->normalize_provisional_post_id( $occurrence_id ), 'occurrence_id' );

		if ( $occurrence ) {
			// We need to temporarily hijack this query var to give the true post_id, so the flag_post_from_classic_editor() can do all it's appropriate updates.
			$_GET['post'] = $occurrence->post_id;
		}

		tribe( 'events.editor' )->flag_post_from_classic_editor();
		$_GET['post'] = $occurrence_id;
	}

	/**
	 * Get the event details data.
	 *
	 * @since 6.0.0
	 *
	 * @return array<string,mixed>
	 */
	public function get_event_details() {
		return $this->container->make( Event::class )->get_event_details( get_the_ID() );
	}

	/**
	 * Filter the recurrence strings used in the Block Editor.
	 *
	 * @since 6.0.0
	 *
	 * @param array<string,array> $strings Strings to be updated.
	 *
	 * @return array<string,array> Updated strings.
	 */
	public function filter_recurrence_strings( $strings ) {
		return $this->container->make( Recurrence_Strings::class )->update_recurrence_strings( $strings );
	}

	/**
	 * Filter the Events Pro configuration for block editor.
	 *
	 * @since 6.0.0
	 *
	 * @param array<string,mixed> $editor_config Configuration for block editor.
	 *
	 * @return array<string,mixed> Filtered configuration for block editor.
	 */
	public function filter_tribe_events_pro_editor_config( $editor_config ) {
		$blocks_recurrence_rules = $editor_config['eventsPRO']['blocks_recurrence_rules'];
		$new_rules               = [
			'panel_title_text' => __( 'Repeat This Event', 'tribe-events-calendar-pro' ),
			'add_rule_text'    => __( 'Add More', 'tribe-events-calendar-pro' ),
			'key_limit'        => 10,
			'key_limit_type'   => 'never',
		];

		$editor_config = $this->container->make( Meta::class )->update_editor_config( $editor_config );

		$editor_config['eventsPRO']['blocks_recurrence_rules'] = array_merge( $blocks_recurrence_rules, $new_rules );

		return $editor_config;
	}

	/**
	 * Duplicate Event Hooks for Block Editor
	 *
	 * @since 6.0.0
	 */
	public function duplicate_hooks() {
		$state = tribe( State::class );
		if ( $state->is_running() && ! $state->is_completed() ) {
			return;
		}

		$this->container->make( Meta::class )->register();
	}

	/**
	 * Enqueues the assets required for duplicating events.
	 *
	 * @deprecated Prevent incorrect usage of `tribe_asset()`
	 *
	 * @since 6.0.0
	 */
	public function enqueue_block_editor_duplicate_assets() {
		_deprecated_function( __METHOD__, '6.0.9', 'No replacement.' );
	}

	/**
	 * Handles the AJAX request fired from the context of the Blocks Editor to fetch
	 * a Series data.
	 *
	 * @since 6.0.0
	 *
	 * @return void The method does not return any value and will, instead, echo the response
	 *              to the page.
	 */
	public function handle_series_data_ajax() {
		return $this->container->make( Ajax::class )->handle_series_data_ajax();
	}

	/**
	 * Handles the AJAX request fired from the context of the Blocks Editor to fetch
	 * an Occurrence redirect data.
	 *
	 * @since 6.0.0
	 *
	 * @return void The method does not return any value and will, instead, echo the response
	 *              to the page.
	 */
	public function handle_redirect_data_ajax() {
		return $this->container->make( Ajax::class )->handle_redirect_data_ajax();
	}

	/**
	 * Localizes the data required by the AJAX support of the block editor to fetch the data for
	 * a Series.
	 *
	 * @since 6.0.0
	 *
	 * @return void The method does not return any value and will print a localizes AJAX payload
	 *              on the page.
	 */
	public function localize_ajax_information() {
		$this->container->make( Ajax::class )->localize();
	}

	/**
	 * Fetches the value of a meta field for an Occurrence provisional post ID redirecting
	 * the request to the original Event post.
	 *
	 * @since 6.0.0
	 *
	 * @param mixed|null $value    The original value, as initialized by the filer, initially `null`.
	 * @param int        $post_id  The post ID to fetch the meta value for.
	 * @param string     $meta_key The meta key to fetch the value for.
	 *
	 * @return mixed|null Either the value fetched for the meta key, or `null` if the method should
	 *                    not be redirected.
	 */
	public function redirect_edit_meta_values( $value, $post_id, $meta_key ) {
		return $this->container->make( Meta::class )->get_value( $value, $post_id, $meta_key );
	}

	/**
	 * Adds a flag to each rule data to indicate whether the rule DTSTART is
	 * off-pattern in respect to the rule.
	 *
	 * @since 6.0.0
	 *
	 * @param array<array<string,mixed>> $data       The Event recurrence rules data in the format
	 *                                               used by the Blocks Editor.
	 * @param int                        $post_id    The Event post ID.
	 * @param string                     $key        The type of recurrence rule that is being filtered, either `rules`
	 *                                               or `exclusions`.
	 *
	 * @return array<array<string,mixed>> The Event recurrence meta in the format used by the Blocks
	 *                                    Editor, updated to include the off-pattern DTSTART flag.
	 */
	public function add_off_pattern_dtstart_flag( array $data, string $key, int $post_id ) {
		return $this->container->make( Meta::class )
		                       ->filter_recurrence_meta_for_post( $post_id, $key, $data );
	}
}
