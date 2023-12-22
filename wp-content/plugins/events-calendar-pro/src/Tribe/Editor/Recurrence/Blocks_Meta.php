<?php

/**
 * Class Tribe__Events__Pro__Editor__Recurrence__Blocks_Meta
 *
 * @since 4.5
 */
class Tribe__Events__Pro__Editor__Recurrence__Blocks_Meta {
	public static $rules_key = '_tribe_blocks_recurrence_rules';
	public static $exclusions_key = '_tribe_blocks_recurrence_exclusions';
	public static $description_key = '_tribe_blocks_recurrence_description';

	/**
	 * Meta key used to get the rules associated with the recurrence on the new UI
	 *
	 * @since 4.5
	 * @deprecated
	 *
	 * @return string
	 */
	public function get_rules_key() {
		_doing_it_wrong( 'Tribe__Events__Pro__Editor__Recurrence__Blocks_Meta::$rules_key', 'Please use the static variable, this was an incredibly inefficient way of doing things.', '5.12.1' );

		return static::$rules_key;
	}

	/**
	 * Return the meta key used to get the exclusions in a post.
	 *
	 * @since 4.5
	 *
	 * @return string
	 */
	public function get_exclusions_key() {
		_doing_it_wrong( 'Tribe__Events__Pro__Editor__Recurrence__Blocks_Meta::$exclusions_key', 'Please use the static variable, this was an incredibly inefficient way of doing things.', '5.12.1' );

		return static::$exclusions_key;
	}

	/**
	 * Return the name of the key used to reference the recurrence description value
	 *
	 * @since 4.5
	 *
	 * @return string
	 */
	public function get_description_key() {
		_doing_it_wrong( 'Tribe__Events__Pro__Editor__Recurrence__Blocks_Meta::$description_key', 'Please use the static variable, this was an incredibly inefficient way of doing things.', '5.12.1' );

		return static::$description_key;
	}
}
