<?php
namespace WPDRMS\ASP\Asset\Script;

defined('ABSPATH') or die("You can't access this file directly.");

class Requirements {
	public static function isRequired( $handle, $instances = false ): bool {
		if ( wd_asp()->manager->getContext() == "backend" ) {
			return true;
		}

		$unused = self::getUnusedAssets(false, $instances);
		$wp_scripts = wp_scripts();

		$required = false;
		switch ( strtolower($handle) ) {
			case 'jquery':
				if ( !wd_in_array_r('select2', $unused) && !isset($wp_scripts->done['jquery']) ) {
					$required = true;
				}
				break;
			case 'jquery-ui-datepicker':
			case 'datepicker':
				if ( !wd_in_array_r('datepicker', $unused) && !isset($wp_scripts->done['jquery-ui-datepicker']) ) {
					$required = true;
				}
				break;
			case 'wd-asp-photostack':
			case 'wd-asp-ajaxsearchpro-polaroid':
				if ( !wd_in_array_r('polaroid', $unused) ) {
					$required = true;
				}
				break;
			case 'wd-asp-select2':
				if ( !wd_in_array_r('select2', $unused) ) {
					$required = true;
				}
				break;
			case 'wd-asp-lazy':
				if ( wd_asp()->o['asp_compatibility']['load_lazy_js'] == 1 ) {
					$required = true;
				}
				break;
			case 'wd-asp-nouislider':
				if ( !wd_in_array_r('noui', $unused) ) {
					$required = true;
				}
				break;
			case 'wd-asp-rpp-isotope':
				if ( !wd_in_array_r('isotope', $unused) ) {
					$required = true;
				}
				break;
			case 'wd-asp-ajaxsearchpro-settings':
				if ( !wd_in_array_r('settings', $unused) ) {
					$required = true;
				}
				break;
			case 'wd-asp-ajaxsearchpro-compact':
				if ( !wd_in_array_r('compact', $unused) ) {
					$required = true;
				}
				break;
			case 'wd-asp-ajaxsearchpro-vertical':
				if ( !wd_in_array_r('vertical', $unused) ) {
					$required = true;
				}
				break;
			case 'wd-asp-ajaxsearchpro-horizontal':
				if ( !wd_in_array_r('horizontal', $unused) ) {
					$required = true;
				}
				break;
			case 'wd-asp-ajaxsearchpro-isotopic':
				if ( !wd_in_array_r('isotopic', $unused) ) {
					$required = true;
				}
				break;
			case 'wd-asp-ajaxsearchpro-autopopulate':
				if ( !wd_in_array_r('autopopulate', $unused) ) {
					$required = true;
				}
				break;
			case 'wd-asp-ajaxsearchpro-ga':
				if ( !wd_in_array_r('ga', $unused) ) {
					$required = true;
				}
				break;
			case 'wd-asp-ajaxsearchpro-autocomplete':
				if ( !wd_in_array_r('autocomplete', $unused) ) {
					$required = true;
				}
				break;
			case 'wd-asp-ajaxsearchpro-addon-elementor':
				if ( defined('ELEMENTOR_PRO_VERSION') ) {
					$required = true;
				}
				break;
			case 'wd-asp-ajaxsearchpro-addon-divi':
				if ( defined('DE_DB_WOO_VERSION') ) {
					$required = true;
				}
				break;
			case 'wd-asp-ajaxsearchpro-live':
			default:
				$required = true;
				break;
		}

		return $required;
	}

	public static function getUnusedAssets( $return_stored = false, $instances = false ) {
		$dependencies = array(
			'vertical', 'horizontal', 'isotopic', 'polaroid', 'noui', 'datepicker', 'autocomplete',
			'settings', 'compact', 'autopopulate', 'ga'
		);
		$external_dependencies = array(
			'select2', 'isotope'
		);
		if ( $return_stored !== false && $instances === false ) {
			return get_site_option('asp_unused_assets', array(
				'internal' => $dependencies,
				'external' => $external_dependencies
			));
		}

		// --- Analytics
		if ( wd_asp()->o['asp_analytics']['analytics'] != 0 ) {
			$dependencies = array_diff($dependencies, array('ga'));
		}

		if ( $instances === false ) {
			$search = wd_asp()->instances->get();
		} else {
			$search = array();
			foreach ( $instances as $instance ) {
				$search[] = wd_asp()->instances->get($instance);
			}
		}
		if (is_array($search) && count($search)>0) {
			foreach ($search as $s) {
				// $style and $id needed in the include
				$style = $s['data'];
				$id = $s['id'];

				// Calculate flags for the generated basic CSS
				// --- Results type
				$dependencies = array_diff($dependencies, array($s['data']['resultstype']));

				// --- Compact box
				if ( $s['data']['box_compact_layout'] ) {
					$dependencies = array_diff($dependencies, array('compact'));
				}

				// --- Auto populate
				if ( $s['data']['auto_populate'] != 'disabled' ) {
					$dependencies = array_diff($dependencies, array('autopopulate'));
				}

				// --- Autocomplete
				if ( $s['data']['autocomplete'] ) {
					$dependencies = array_diff($dependencies, array('autocomplete'));
				}

				// --- NOUI
				asp_parse_filters($id, $style, true, true);

				// --- Settings visibility
				/**
				 * DO NOT check the switch or if the settings are visible, because the user
				 * can still use the settings shortcode, and that is not possible to check
				 */
				if ( count(wd_asp()->front_filters->get()) > 0 ) {
					$dependencies = array_diff($dependencies, array('settings'));
				}

				foreach (wd_asp()->front_filters->get() as $filter) {
					if ($filter->display_mode == 'slider' || $filter->display_mode == 'range') {
						$dependencies = array_diff($dependencies, array('noui'));
						break;
					}
				}

				// --- Datepicker
				foreach (wd_asp()->front_filters->get() as $filter) {
					if ($filter->display_mode == 'date' || $filter->display_mode == 'datepicker') {
						$dependencies = array_diff($dependencies, array('datepicker'));
						break;
					}
				}
				// --- Scrollable filters
				foreach (wd_asp()->front_filters->get() as $filter) {
					if ( isset($filter->data['visible']) && $filter->data['visible'] == 0 ) {
						continue;
					}
					if ($filter->display_mode == 'checkboxes' || $filter->display_mode == 'radio') {
						break;
					}
				}
				// --- Autocomplete (not used yet)

				// --- Select2
				foreach (wd_asp()->front_filters->get() as $filter) {
					if ($filter->display_mode == 'dropdownsearch' || $filter->display_mode == 'multisearch') {
						$external_dependencies = array_diff($external_dependencies, array('select2'));
						break;
					}
				}

				// --- Isotope
				if ( $s['data']['resultstype'] == 'isotopic' || $s['data']['fss_column_layout'] == 'masonry' ) {
					$external_dependencies = array_diff($external_dependencies, array('isotope'));
				}
			}
		}

		// Store for the init script
		if ( $instances === false ) {
			update_site_option('asp_unused_assets', array(
				'internal' => $dependencies,
				'external' => $external_dependencies
			));
		}
		return array(
			'internal' => $dependencies,
			'external' => $external_dependencies
		);
	}
}