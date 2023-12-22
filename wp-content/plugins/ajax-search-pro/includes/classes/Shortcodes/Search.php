<?php
namespace WPDRMS\ASP\Shortcodes;

use WPDRMS\ASP\Frontend\FiltersManager;
use WPDRMS\ASP\Hooks\AjaxManager;
use WPDRMS\ASP\Patterns\SingletonTrait;
use WPDRMS\ASP\Utils\MobileDetect;

if (!defined('ABSPATH')) die('-1');


class Search extends AbstractShortcode {
	use SingletonTrait;
	/**
	 * Overall instance count
	 *
	 * @var int
	 */
	private static $instanceCount = 0;

	/**
	 * Instance count per search ID
	 *
	 * @var array
	 */
	private static $perInstanceCount = array();

	/**
	 * Does the search shortcode stuff
	 *
	 * @param array|null $atts
	 * @return string|void
	 */
	public function handle($atts) {
		$style = null;
		$mdetectObj = new MobileDetect();

		extract(shortcode_atts(array(
			'id' => 'something',
			'extra_class' => '',
			'include_styles' => 0,
			'display_on_mobile' => 1
		), $atts));

		// If disabled on mobile exit
		if ( $display_on_mobile == 0 && $mdetectObj->isMobile() ) return;

		// Disable back-end display on taxonomy list pages
		if ( is_admin() && isset($_GET['taxonomy']) ) return;

		if ( AjaxManager::doingAjax("ajaxsearchpro_preview") ) {
			require_once(ASP_PATH . "backend" . DIRECTORY_SEPARATOR . "settings" . DIRECTORY_SEPARATOR . "types.inc.php");
			parse_str($_POST['formdata'], $style);
			$search = wd_asp()->instances->get($id);
			$style = wpdreams_parse_params($style);
			$style = wd_asp()->instances->decode_params($style);
		} else {
			// Visual composer, first selected row, no data, so select the first one
			if ($id == 99999) {
				$_instances = wd_asp()->instances->get();
				if ( empty($_instances) )
					return "There are no search instances to display. Please create one.";

				$search = reset($_instances);
			} else {
				$_instance = wd_asp()->instances->get($id);
				if ( empty($_instance) )
					return "This search form (with id $id) does not exist!";
				$search = $_instance;
			}

			// Parse the id again for correction
			$id = $search['id'] + 0;
			$wpdreams_ajaxsearchpros[$id] = 1;
			$style = $search['data'];
		}

		// If disabled on mobile from the back-end
		if ( $style['mob_display_search'] == 0 && $mdetectObj->isMobile() ) return;
		if ( $style['desktop_display_search'] == 0 && !$mdetectObj->isMobile() ) return;

		// Don't move this above any return statements!
		self::$instanceCount++;
		if (isset(self::$perInstanceCount[$id]))
			self::$perInstanceCount[$id]++;
		else
			self::$perInstanceCount[$id] = 1;

		$style = array_merge(wd_asp()->options['asp_defaults'], $style);

		global $post;
		if ( !empty($post->ID) ) {
			$asp_metadata = get_post_meta( $post->ID, '_asp_metadata', true );
			if ( is_array($asp_metadata) ) {
				if (
					!empty($asp_metadata['asp_suggested_phrases']) &&
					(
						empty($asp_metadata['asp_suggested_instances']) ||
						$asp_metadata['asp_suggested_instances'] == $id
					)
				)
					$style['frontend_suggestions_keywords'] = $asp_metadata['asp_suggested_phrases'];
			}
		}

		if ( $style['box_compact_layout'] ) {
			/**
			 * Do the tablet check first, as some tablets are also returned as mobiles
			 */
			if ( $mdetectObj->isTablet() ) {
				if ( !$style['box_compact_layout_tablet'] ) {
					$style['box_compact_layout'] = 0;
				}
			} else if ( $mdetectObj->isMobile() && !$mdetectObj->isTablet() ) {
				// Check excplicitly if this is not a tablet, as some tablets are also mobiles
				if ( !$style['box_compact_layout_mobile'] ) {
					$style['box_compact_layout'] = 0;
				}
			} else {
				if ( !$style['box_compact_layout_desktop'] ) {
					$style['box_compact_layout'] = 0;
				}
			}
		}

		// Disabled compact layout if the box is hidden anyways
		if ( $style['box_sett_hide_box'] == 1 ) {
			$style['box_compact_layout'] = 0;
			$style['frontend_search_settings_visible'] = 1;
			$style['show_frontend_search_settings'] = 1;
			$style['frontend_search_settings_position'] = "block";
			$style['resultsposition'] = "block";
			$style['charcount'] = 0;
			if ( $style['trigger_on_facet'] == 0 && $style['fe_search_button'] == 0 )
				$style['trigger_on_facet'] = 1;
		}

		// Triggered by URL
		if ( isset($_GET['asp_s']) ) {
			if ( empty($_GET['p_asid']) || (!empty($_GET['p_asid']) && $_GET['p_asid']==$id) ) {
				$style['auto_populate'] = "phrase";
				$style['auto_populate_phrase'] = $_GET['asp_s'];
			}
		}

		$style['redirect_elementor'] = !empty($style['redirect_elementor']) ? get_permalink($style['redirect_elementor']) : home_url("/");
		$style['redirect_elementor'] = add_query_arg('asp_ls', '{phrase}', $style['redirect_elementor']);
		$style['mob_redirect_elementor'] = !empty($style['mob_redirect_elementor']) ? get_permalink($style['mob_redirect_elementor']) : home_url("/");
		$style['mob_redirect_elementor'] = add_query_arg('asp_ls', '{phrase}', $style['mob_redirect_elementor']);
		$style['fe_sb_redirect_elementor'] = !empty($style['fe_sb_redirect_elementor']) ? get_permalink($style['fe_sb_redirect_elementor']) : home_url("/");
		$style['fe_sb_redirect_elementor'] = add_query_arg('asp_ls', '{phrase}', $style['fe_sb_redirect_elementor']);
		$style['more_redirect_elementor'] = !empty($style['more_redirect_elementor']) ? get_permalink($style['more_redirect_elementor']) : home_url("/");
		$style['more_redirect_elementor'] = add_query_arg('asp_ls', '{phrase}', $style['more_redirect_elementor']);

		$style['resultstype'] = $style['resultstype'] == '' ? 'vertical' : $style['resultstype'];

		// Hidden, but might be possible to show it
		$settingsHidden = w_isset_def($style['show_frontend_search_settings'], 1) != 1 ? true : false;
		// Hidden, as well as the switch, so not possible to show
		$settingsFullyHidden =
			$style['show_frontend_search_settings']!= 1 &&
			$style['frontend_search_settings_visible'] != 1 ? true : false;

		// Initialize the filters global
		wd_asp()->front_filters = FiltersManager::getInstance();


		// If images are removed the results count is unpredictable, thus disable ajax loader on more results
		if (
			($style['resultstype'] == "isotopic" && $style['i_ifnoimage'] == 'removeres') ||
			($style['resultstype'] == 'polaroid' && $style['pifnoimage'] == 'removeres')
		){
			$style['more_results_action'] = "redirect";
		}

		// IMPORTANT for Perfromace: If the index table is enabled, the generics are forced to be enabled
		// ..if unchecked, it will be overwritten excplicitly anyways
		if ( $style['search_engine'] == 'index') {
			$style['searchintitle'] = 1;
			$style['searchincontent'] = 1;
			$style['searchinexcerpt'] = 1;
		}

		$style['_id'] = $id;

		$style = apply_filters("asp_shortcode_search_options", $style);
		$_st = &$style; // Shorthand
		$out = '';

		// Finally make preview changes after option changes
		if ( AjaxManager::doingAjax("ajaxsearchpro_preview") || $include_styles == 1 ) {
			ob_start();
			include(ASP_PATH . "/css/style.basic.css.php");
			include(ASP_PATH . "/css/style.css.php");
			$out = ob_get_clean();
			ob_start();
			?>
			<div style='display: none;' id="asp_preview_options"><?php echo base64_encode(serialize($style)); ?></div>
			<style>
				<?php echo $out; ?>
			</style>
			<?php
			$out = ob_get_clean();
		}

		if (isset($_POST['p_asp_data']) || isset($_POST['np_asp_data'])) {
			$_p_data = $_POST['p_asp_data'] ?? $_POST['np_asp_data'];
			$_p_id = $_POST['p_asid'] ?? $_POST['np_asid'];
			if ( $_p_id == $id ) {
				parse_str($_p_data, $style['_fo']);
			}
		} else if ( isset($_GET['p_asp_data']) ) {
			$_p_id = $_GET['p_asid'] ?? $_GET['np_asid'];
			if ( $_GET['p_asp_data'] == 1 ) {
				if ( $_p_id == $id ) {
					$style['_fo'] = $_GET;
				}
			} else {
				// Legacy support
				if ( $_p_id == $id ) {
					parse_str(base64_decode($_GET['p_asp_data']), $style['_fo']);
				}
			}
		}

		// Parse the filters for this shortcode ID
		asp_parse_filters($id, $_st, true);

		do_action('asp_layout_before_shortcode', $id);

		ob_start();
		include(ASP_PATH."includes/views/asp.shortcode.php");
		$out .= ob_get_clean();

		do_action('asp_layout_after_shortcode', $id);

		return apply_filters('asp_shortcode_output', $out, $id);
	}

	public static function instanceCount(): int {
		return self::$instanceCount;
	}
}