<?php

/**
 * Register the required Meta fields for Blocks Editor API saving
 * Initialize Gutenberg Event Meta fields
 *
 * @since 4.5
 */
class Tribe__Events__Pro__Editor__Meta extends Tribe__Editor__Meta {
	/**
	 * Register the required Meta fields for good Gutenberg saving
	 *
	 * @since 4.5
	 *
	 * @return void
	 */
	public function register() {
		register_meta( 'post', Tribe__Events__Pro__Editor__Recurrence__Blocks_Meta::$rules_key, $this->text() );
		register_meta( 'post', Tribe__Events__Pro__Editor__Recurrence__Blocks_Meta::$description_key, $this->text() );
		register_meta( 'post', Tribe__Events__Pro__Editor__Recurrence__Blocks_Meta::$exclusions_key, $this->text() );

		$this->register_additional_fields();

		$this->hook();
	}

	/**
	 * Register the fields used by dynamic fields into the REST API
	 *
	 * @since 4.5
	 */
	public function register_additional_fields() {
		$additional_fields = array_values( tribe_get_option( 'custom-fields', array() ) );
		foreach ( $additional_fields as $field ) {

			$has_fields = isset( $field['name'], $field['type'], $field['gutenberg_editor'] );
			if ( ! $has_fields ) {
				continue;
			}

			switch ( $field['type'] ) {
				case 'textarea':
					$args = $this->textarea();
					break;
				case 'url':
					$args = $this->url();
					break;
				case 'checkbox':
					$args = $this->text();
					global $wp_version;

					/**
					 * Removing the line below fixes a problem that was plaguing users on version
					 *
					 * @link https://theeventscalendar.atlassian.net/browse/ECP-746
					 */
					if ( version_compare( $wp_version, '4.7.0', '<' ) ) {
						register_meta( 'post', '_' . $field['name'], $this->text_array() );
					}
					break;
				default:
					$args = $this->text();
					break;
			}
			register_meta( 'post', $field['name'], $args );
		}
	}

	/**
	 * Add filters into the Meta class
	 *
	 * @since 4.5
	 */
	public function hook() {
		$valid_request = is_admin() || wp_doing_ajax() || Tribe__REST__System::is_rest_api();

		if ( ! $valid_request ) {
			return;
		}

		add_filter( 'get_post_metadata', array( $this, 'fake_blocks_response' ), 15, 4 );
		add_filter( 'get_post_metadata', array( $this, 'fake_recurrence_description' ), 15, 4 );
		add_action( 'deleted_post_meta', array( $this, 'remove_recurrence_meta' ), 10, 3 );
		add_filter( 'tribe_events_pro_show_recurrence_meta_box', array( $this, 'show_recurrence_classic_meta' ), 10, 2 );
		add_filter( 'tribe_events_pro_split_redirect_url', array( $this, 'split_series_link' ), 10, 2 );
	}

	/**
	 * Return a fake response with the data from the old classic meta field into the new meta field keys
	 * used by the new recurrence UI, returns only: rules and exclusions
	 *
	 * @since 4.5
	 *
	 * @param null|array|string $value The value get_metadata() should return a single metadata value, or an
	 *                                    array of values.
	 * @param int               $post_id Post ID.
	 * @param string            $meta_key Meta key.
	 * @param string|array      $single Meta value, or an array of values.
	 *
	 * @return array|null|string The attachment metadata value, array of values, or null.
	 */
	public function fake_blocks_response( $value, $post_id, $meta_key, $single ) {
		$keys_map = [
			Tribe__Events__Pro__Editor__Recurrence__Blocks_Meta::$rules_key      => 'rules',
			Tribe__Events__Pro__Editor__Recurrence__Blocks_Meta::$exclusions_key => 'exclusions',
		];

		if ( ! array_key_exists( $meta_key, $keys_map ) ) {
			return $value;
		}

		$key = $keys_map[ $meta_key ];

		// Fetch the database value directly.
		$result = $this->get_value( $post_id, $meta_key );

		if ( ! empty( $result ) ) {
			// The database, or the filtered value, is not empty: use this.
			$data = json_decode( $result, true );
		} else {
			// Work out the Blocks format rules, or exclusions, from the `_EventRecurrence` format ones.
			$recurrence = get_post_meta( $post_id, '_EventRecurrence', true );

			/**
			 * Filter the `_EventRecurrence` meta value after it's read from the database.
			 *
			 * @since 6.0.0
			 *
			 * @param array<string,mixed> $recurrence The `_EventRecurrence` meta value.
			 * @param int                 $post_id    The Event post ID.
			 */
			$recurrence = apply_filters( 'tec_events_pro_recurrence_meta_get', $recurrence, $post_id );

			if ( empty( $recurrence ) ) {
				// We cannot work it out since we lack information: return the unfiltered value.
				return $value;
			}

			if ( empty( $recurrence[ $key ] ) ) {
				return $value;
			}

			$rules = $recurrence[ $key ];
			$data = $this->parse_for_rules( $rules );
		}

		/**
		 * Filters the data produced by the Blocks Editor to represent an Event recurrence or exclusion rules.
		 *
		 * @since 6.0.0
		 *
		 * @param array<array<string,mixed>> $data       A list of the Event recurrence rules, in the format used
		 *                                               by the Blocks Editor.
		 * @param string                     $key        Either `rules` or `exclusions` to indicate the type of data
		 *                                               that is being filtered.
		 * @param int                        $post_id    The Event post ID.
		 */
		$data = apply_filters( 'tec_events_pro_blocks_recurrence_meta', $data, $key, (int) $post_id );

		$encoded = json_encode( $data, JSON_UNESCAPED_SLASHES );

		return $single ? $encoded : array( $encoded );
	}

	/**
	 * Handles parsing and converting an array of rules from
	 * classic format to the blocks format.
	 *
	 * @since 6.0.0
	 *
	 * @param array<string,mixed> $rules Classic rules format.
	 *
	 * @return array<string,mixed> Blocks rules format.
	 */
	public function parse_for_rules( $rules = [] ) {
		$data = array();
		foreach ( $rules as $rule ) {
			$blocks = new Tribe__Events__Pro__Editor__Recurrence__Blocks( $rule );
			$blocks->parse();
			$data[] = $blocks->get_parsed();
		}

		return $data;
	}

	/**
	 * Fake the description value from _EventRecurrence into a dynamic meta value that is located at
	 * tribe( 'events-pro.editor.recurrence.blocks-meta' )->get_description_key();
	 *
	 * @since 4.5
	 *
	 * @param $value mixed The original value
	 * @param $post_id int The Id of the post
	 * @param $meta_key string The name of the meta key
	 * @param $single Bool true if a single value should be returned
	 *
	 * @return array|string
	 */
	public function fake_recurrence_description( $value, $post_id, $meta_key, $single ) {
		if ( $meta_key !== Tribe__Events__Pro__Editor__Recurrence__Blocks_Meta::$description_key ) {
			return $value;
		}

		$description = $this->get_value( $post_id, $meta_key );

		if ( empty( $description ) ) {
			$recurrence = get_post_meta( $post_id, '_EventRecurrence', true );
			$description = isset( $recurrence['description'] ) ? $recurrence['description'] : '';
		}

		return $single ? $description : array( $description );
	}

	/**
	 * Return the meta value of a post ID directly from the DB
	 *
	 * @since 4.5
	 *
	 * @param int    $post_id
	 * @param string $meta_key
	 *
	 * @return mixed
	 */
	public function get_value( $post_id = 0, $meta_key = '' ) {
		/**
		 * Allows filtering the value fetched for a specific meta before the default logic
		 * runs.
		 *
		 * @since 6.0.0
		 *
		 * @param mixed|null $value    The initial value, by default `null`.
		 * @param int        $post_id  The post ID the value is being fetched for.
		 * @param string     $meta_key The meta key to fetch the value of.
		 */
		$value = apply_filters( 'tec_events_pro_editor_meta_value', null, $post_id, $meta_key );

		if ( null !== $value ) {
			return $value;
		}

		global $wpdb;
		$query = "SELECT meta_value FROM $wpdb->postmeta WHERE post_id = %d AND meta_key = %s";

		return $wpdb->get_var( $wpdb->prepare( $query, $post_id, $meta_key ) );
	}

	/**
	 * Removes the meta keys that maps into the classic editor when the `_EventRecurrence` is
	 * removed.
	 *
	 * @since 4.5
	 *
	 * @param $meta_id
	 * @param $object_id
	 * @param $meta_key
	 */
	public function remove_recurrence_meta( $meta_id, $object_id, $meta_key ) {
		if ( '_EventRecurrence' !== $meta_key ) {
			return;
		}
		delete_post_meta( $object_id, Tribe__Events__Pro__Editor__Recurrence__Blocks_Meta::$rules_key );
		delete_post_meta( $object_id, Tribe__Events__Pro__Editor__Recurrence__Blocks_Meta::$exclusions_key );
	}

	/**
	 * Remove the recurrence meta box based on recurrence structure for blocks
	 *
	 * @since 4.5
	 * @since 4.5.3 Added $post_id param
	 *
	 * @param  mixed  $show_meta  Default value to display recurrence or not
	 * @param  int    $post_id    Which post we are dealing with
	 *
	 * @return bool
	 */
	public function show_recurrence_classic_meta( $show_meta, $post_id ) {
		/** @var Tribe__Editor $editor */
		$editor = tribe( 'editor' );

		// Return default on non classic editor
		if ( $editor->should_load_blocks() ) {
			return $show_meta;
		}

		// when it doesn't have blocks we return default
		if ( ! has_blocks( absint( $post_id ) ) ) {
			return $show_meta;
		}

		return false;
	}

	/**
	 * Redirect to classic editor if the event does not have any block on it
	 *
	 * @since 4.5
	 *
	 * @param $url
	 * @param $post_id
	 *
	 * @return mixed
	 */
	public function split_series_link( $url, $post_id ) {
		$args = array();
		if ( ! has_blocks( absint( $post_id ) ) ) {
			$args = array( 'classic-editor' => '' );
		}

		return add_query_arg( $args, $url );
	}

	/**
	 * Unsubscribes the instance from the actions and filters it subscribed to
	 * in the `hook` method.
	 *
	 * @since 6.0.0
	 *
	 * @return void The method will unsubscribe the instance from all the hooks it subscribed to.
	 */
	public function unhook(): void {
		remove_filter( 'get_post_metadata', [ $this, 'fake_blocks_response' ], 15 );
		remove_filter( 'get_post_metadata', [ $this, 'fake_recurrence_description' ], 15 );
		remove_action( 'deleted_post_meta', [ $this, 'remove_recurrence_meta' ] );
		remove_filter( 'tribe_events_pro_show_recurrence_meta_box', [ $this, 'show_recurrence_classic_meta' ] );
		remove_filter( 'tribe_events_pro_split_redirect_url', [ $this, 'split_series_link' ] );
	}
}
