<?php
/**
 * An extension of The Events Calendar rewrite handler to handle PRO rules.
 *
 * @since   4.7.5
 * @package Tribe\Events\Pro\Rewrite
 */

namespace Tribe\Events\Pro\Rewrite;

use Tribe\Events\Pro\Views\V2\Views\Week_View;
use Tribe__Events__Main as TEC;
use Tribe__Events__Organizer as Organizer;
use Tribe__Events__Venue as Venue;

/**
 * Class Rewrite
 *
 * @since   4.7.5
 * @package Tribe\Events\Pro\Rewrite
 */
class Rewrite extends \Tribe__Events__Rewrite {
	/**
	 * An override of the base class method to make sure we're taking Pro rewrite rules into account when parsing a URL.
	 *
	 * @since 4.7.5
	 *
	 * @param array $query_vars An array of query vars found in the current URL.
	 *
	 * @return array The dynamic matchers, modified if required.
	 */
	protected function get_dynamic_matchers( array $query_vars ) {
		$dynamic_matchers = parent::get_dynamic_matchers( $query_vars );
		$bases            = (array) $this->get_bases();

		if ( isset( $query_vars[ TEC::POSTTYPE ] ) ) {
			if ( isset( $query_vars['tribe_recurrence_list'] ) ) {
				// Request for the /events/all/<slug> URI.
				$all_regex = $bases['all'];
				preg_match( '/^\(\?:(?<slugs>[^\\)]+)\)/', $all_regex, $matches );
				if ( isset( $matches['slugs'] ) ) {
					$slugs = explode( '|', $matches['slugs'] );
					// The localized version is the last.
					$localized_slug                           = end( $slugs );
					$dynamic_matchers["([^/]+)/{$all_regex}"] = "{$query_vars[TEC::POSTTYPE]}/{$localized_slug}";
				}
			} elseif ( isset( $query_vars['eventDate'] ) ) {
				// Request for a specific date in the context of a recurring event.
				$dynamic_matchers['([^/]+)']                = $query_vars[ TEC::POSTTYPE ];
				$dynamic_matchers['(\\d{4}-\\d{2}-\\d{2})'] = $query_vars['eventDate'];

				if ( isset( $query_vars['eventSequence'] ) ) {
					// Support sequence number for Occurrences of the same event on the same day.
					$dynamic_matchers['(\\d+)'] = (int) $query_vars['eventSequence'];
				}
			}
		}


		if ( isset( $query_vars[ Venue::POSTTYPE ] ) ) {
			// Add the Venue slug as a dynamic matcher.
			$dynamic_matchers['([^/]+)'] = $query_vars[ Venue::POSTTYPE ];
		}

		if ( isset( $query_vars[ Organizer::POSTTYPE ] ) ) {
			// Add the Organizer slug as a dynamic matcher.
			$dynamic_matchers['([^/]+)'] = $query_vars[ Organizer::POSTTYPE ];
		}

		if (
			( $query_vars['eventDisplay'] ?? false ) === Week_View::get_view_slug()
			&& ( $query_vars['eventDate'] ?? false )
			&& preg_match( '/^\\d{1,2}$/', $query_vars['eventDate'] )
		) {
			// Add the week number dynamic matcher.
			$dynamic_matchers['(\d{2})'] = (int) $query_vars['eventDate'];
		}

		$dynamic_matchers = array_merge(
			$dynamic_matchers,
			$this->get_geoloc_dynamic_matchers( $query_vars, $bases, $dynamic_matchers )
		);

		return $dynamic_matchers;
	}

	/**
	 * Overrides the parent method to handle the Geolocation localized matchers in a back-compatible manner.
	 *
	 * @since 5.0.1
	 *
	 * return array<string,array> The filtered list of localized matchers.
	 */
	protected function get_localized_matchers() {
		$localized_matchers              = parent::get_localized_matchers();
		$localized_matchers_by_query_var = [];
		foreach ( $localized_matchers as $localized_placeholder => $localized_matcher ) {
			if ( ! isset( $localized_matcher['query_var'] ) ) {
				continue;
			}
			$localized_matchers_by_query_var[ $localized_matcher['query_var'] ] = array_merge(
				$localized_matcher,
				[ 'placeholder' => $localized_placeholder ]
			);
		}

		/*
		 * Duplicate the event archive matcher to add the Geoloc format one: "(.*)events".
		 * Back-compatibility here is a requirement.
		 */
		if ( isset( $localized_matchers_by_query_var['post_type'] ) ) {
			$archive_key = $localized_matchers_by_query_var['post_type']['placeholder'];

			// Use `Tribe__Events__Main::$rewriteSlug` property as this is the one used by Geoloc.
			$localized_matchers[ '(.*)' . TEC::instance()->rewriteSlug ] = $localized_matchers[ $archive_key ];
		}

		return $localized_matchers;
	}

	/**
	 * Returns the dynamic matchers produced as result of the Geoloc/Map rewrite rules.
	 *
	 * The particular care of this method is to keep back-compatibility, this enforces a non ideal construction of the
	 * dynamic matchers using either English-only slugs or partially localized slugs. See notes in the code for more.
	 *
	 * @since 5.0.1
	 *
	 * @param array<string,string> $query_vars       A map of the query vars matched and their values.
	 * @param array<string,string> $bases            A map of the plugin(s) managed rewrite rules bases and their
	 *                                               localized version.
	 * @param array<string,string> $dynamic_matchers A map of the current dynamic matchers and their replacement.
	 *
	 * @return array<string,string> A map of the Geoloc dynamic matchers and their replacement; might be empty if not
	 *                              required.
	 */
	protected function get_geoloc_dynamic_matchers( array $query_vars, array $bases, array $dynamic_matchers ) {
		$localized_matchers = $this->get_localized_matchers();
		$tax_key = isset( $bases['tax']) ? $bases['tax'] . static::$localized_matcher_delimiter . 'tax' : false;

		/**
		 * Add a dynamic matcher to match the specific format used by Geolocation: "category/...", w/o the "(?:...)".
		 * Do this by copying the dynamic matcher for category found for category by the parent method.
		 */
		if (
			$tax_key && isset( $query_vars[ TEC::TAXONOMY ], $bases['tax'], $localized_matchers[ $tax_key ] )
		) {
			$tax_dynamic_matcher = false;
			$tax_dynamic_match   = false;

			foreach ( $dynamic_matchers as $dynamic_matcher => $match ) {
				if ( 0 === strpos( $dynamic_matcher, $bases['tax'] ) ) {
					$tax_dynamic_matcher = $dynamic_matcher;
					$tax_dynamic_match   = $match;
					break;
				}
			}

			if ( $tax_dynamic_matcher && $tax_dynamic_match ) {
				/*
				 * The Geolocation routes uses the deprecated `Tribe__Events__Main::$taxRewriteSlug` property, English.
				 * Here we create an entry that will replace w/ the same match as the existing one, but under a
				 * key that uses the format used by Geoloc, so, at the end, we'll have:
				 * "(?:category|categorie)/(?:[^/]+/)*([^/]+)" - the current and localized version we get from TEC.
				 * "category/(?:[^/]+/)*([^/]+)" - the version we get from Geoloc.
				 */
				$en_slug = $localized_matchers[ $tax_key ]['en_slug'];
				// Add the English version of the dynamic matcher.
				$en_geoloc_tax_key                      = str_replace( $bases['tax'], $en_slug, $tax_dynamic_matcher );
				$dynamic_matchers[ $en_geoloc_tax_key ] = $tax_dynamic_match;

				$localized_slug = $localized_matchers[ $tax_key ]['localized_slug'];
				if ( $localized_slug !== $en_slug ) {
					// Add the localized version of the dynamic matcher.
					$localized_geoloc_tax_key = str_replace( $bases['tax'], $localized_slug, $tax_dynamic_matcher );

					$dynamic_matchers[ $localized_geoloc_tax_key ] = $tax_dynamic_match;
				}
			}
		}

		return $dynamic_matchers;
	}

	/**
	 * Filters the handled rewrite rules to add the ones specific to ECP and Recurring Events.
	 *
	 * @since 6.0.7
	 *
	 * @return array<string,string> The filtered list of rewrite rules.
	 */
	protected function get_handled_rewrite_rules() {
		$cache       = tribe_cache();
		$ecp_handled = $cache['ecp_handled_rewrite_rules'] ?? null;

		if ( ! is_array( $ecp_handled ) ) {
			$tec_handled                        = parent::get_handled_rewrite_rules();
			$all_rules                          = isset( $this->rewrite->rules ) ? (array) $this->rewrite->rules : [];
			$recurring_event_rules              = array_filter( $all_rules, static function ( $rule_query_string ) {
				return is_string( $rule_query_string )
				       && strpos( $rule_query_string, 'index.php?tribe_events=$matches[1]' ) === 0;
			} );
			$ecp_handled                        = array_merge( $tec_handled, $recurring_event_rules );
			$cache['ecp_handled_rewrite_rules'] = $ecp_handled;
		}

		return $ecp_handled;
	}
}
