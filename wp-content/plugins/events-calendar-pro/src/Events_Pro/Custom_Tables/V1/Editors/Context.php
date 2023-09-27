<?php
/**
 * Models the context of the current request for the specific purposes of the queueing
 * of Editor assets.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Editors;
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Editors;

use TEC\Events_Pro\Custom_Tables\V1\Series\Post_Type as Series;
use Tribe__Events__Main as TEC;

/**
 * Class Context.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Editors;
 */
class Context {
	/*
	 * A flag property to indicate whether the object did init already or not.
	 *
	 * @since 6.0.0
	 *
	 * @var bool
	 */
	private $did_init = false;

	/**
	 * A filtered map indicating what query methods should return true or false
	 * returning callbacks.
	 *
	 * @since 6.0.0
	 *
	 * @var array<string,bool>
	 */
	private $flags = [];

	/**
	 * Determine whether the current screen is the series edit screen or not.
	 *
	 * @since 6.0.0
	 *
	 * @return boolean Whether the current screen is the series edit screen or not.
	 */
	public function screen_is_series_edit(): bool {
		$current_screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

		if ( ! $current_screen instanceof \WP_Screen ) {
			return false;
		}

		$post_type = get_post_type();

		if ( false === $post_type ) {
			return false;
		}

		return Series::POSTTYPE === $post_type && 'edit' === $current_screen->base;
	}

	/**
	 * Determine whether the current screen is the series post screen or not.
	 *
	 * @since 6.0.0
	 *
	 * @return boolean Whether the current screen is the series post screen or not.
	 */
	public function screen_is_series_post(): bool {
		$current_screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

		if ( ! $current_screen instanceof \WP_Screen ) {
			return false;
		}

		$post_type = get_post_type();

		if ( false === $post_type ) {
			return false;
		}

		return Series::POSTTYPE === $post_type && 'post' === $current_screen->base;
	}

	/**
	 * Determine whether the current screen is classic editor and event post screen.
	 *
	 * @since 6.0.0
	 *
	 * @return boolean Whether the current screen is classic editor and event post screen.
	 */
	public function screen_is_classic_event_post(): bool {
		$current_screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

		if ( ! $current_screen instanceof \WP_Screen ) {
			return false;
		}

		$post_type = get_post_type();

		if ( false === $post_type ) {
			return false;
		}

		$is_classic = ! ( tribe()->isBound( 'events-pro.editor' ) && tribe( 'events-pro.editor' )->should_load_blocks() );

		$is_event_post_screen = TEC::POSTTYPE === $post_type && 'post' === $current_screen->base;

		return $is_classic && $is_event_post_screen;
	}

	/**
	 * Determine whether the current screen is block editor and event post screen.
	 *
	 * @since 6.0.0
	 *
	 * @return boolean Whether the current screen is block editor and event post screen.
	 */
	public function screen_is_blocks_event_post(): bool {
		$current_screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

		if ( ! $current_screen instanceof \WP_Screen ) {
			return false;
		}

		$post_type = get_post_type();

		if ( false === $post_type ) {
			return false;
		}

		$is_blocks = tribe()->isBound( 'events-pro.editor' ) && tribe( 'events-pro.editor' )->should_load_blocks();

		$is_event_post_screen = TEC::POSTTYPE === $post_type && 'post' === $current_screen->base;

		return $is_blocks && $is_event_post_screen;
	}

	/**
	 * Lazily init the object flags map once.
	 *
	 * @since 6.0.0
	 *
	 * @return array<string,bool> The object flags map.
	 */
	private function init(): array {
		if ( $this->did_init ) {
			return $this->flags;
		}

		$this->flags = [
			'is_series_post_screen'        => $this->screen_is_series_post(),
			'is_series_edit_screen'        => $this->screen_is_series_edit(),
			'is_classic_event_post_screen' => $this->screen_is_classic_event_post(),
			'is_blocks_event_post_screen'  => $this->screen_is_blocks_event_post()
		];

		/**
		 * Filters the map that shape the context of the enqueueing of Editor assets in the Custom
		 * Tables v1 implementation. The map will be used to build conditional callbacks in the enqueueing
		 * process to be fed to the assets library.
		 *
		 * @since 6.0.0
		 *
		 * @param array<string,bool> $map A map from context flag properties to their values.
		 */
		$this->flags = apply_filters( 'tec_events_pro_custom_tables_v1_editor_asset_context', $this->flags );

		$this->did_init = true;

		return $this->flags;
	}

	/**
	 * Determine whether the current screen is the series edit screen or not.
	 *
	 * This method is provided to the assets' library, expecting callables to be returned
	 * for conditionals.
	 *
	 * @since 6.0.0
	 *
	 * @return callable A true or false returning callback depending on the flag value.
	 */
	public function is_series_post_screen(): callable {
		return function (): bool {
			$this->init();

			return $this->flags['is_series_post_screen'];
		};
	}

	/**
	 * Determine whether the current screen is the series edit screen or not.
	 *
	 * This method is provided to the assets' library, expecting callables to be returned
	 * for conditionals.
	 *
	 * @since 6.0.0
	 *
	 * @return callable A true or false returning callback depending on the flag value.
	 */
	public function is_series_edit_screen(): callable {
		return function (): bool {
			$this->init();

			return $this->flags['is_series_edit_screen'];
		};
	}

	/**
	 * Determine whether the current screen is the classic editor and event post screen.
	 *
	 * This method is provided to the assets' library, expecting callables to be returned
	 * for conditionals.
	 *
	 * @since 6.0.0
	 *
	 * @return callable A true or false returning callback depending on the flag value.
	 */
	public function is_classic_event_post_screen(): callable {
		return function (): bool {
			$this->init();

			return $this->flags['is_classic_event_post_screen'];
		};
	}

	/**
	 * Determine whether the current screen is the block editor and event post screen.
	 *
	 * This method is provided to the assets' library, expecting callables to be returned
	 * for conditionals.
	 *
	 * @since 6.0.0
	 *
	 * @return callable A true or false returning callback depending on the flag value.
	 */
	public function is_blocks_event_post_screen(): callable {
		return function (): bool {
			$this->init();

			return $this->flags['is_blocks_event_post_screen'];
		};
	}
}