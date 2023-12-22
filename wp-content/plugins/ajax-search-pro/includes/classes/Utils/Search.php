<?php
namespace WPDRMS\ASP\Utils;

use WPDRMS\ASP\Utils\Polylang\StringTranslations as PolylangStringTranslations;

defined('ABSPATH') or die("You can't access this file directly.");

class Search {
	/** @noinspection PhpIncludeInspection */
	public static function generateHTMLResults($results, $s_options, $id, $phrase = '', $subdir = '') {
		$html = "";
		if ( $s_options === false ) {
			$search = wd_asp()->instances->get($id);
			$s_options = $search['data'];
		}
		$theme = $s_options['resultstype'];
		$subdir = !empty($subdir) ? $subdir . '/' : '';
		$theme_path = get_stylesheet_directory() . "/asp/" . $subdir;

		$phrase = strip_tags( Str::escape( Str::clear($phrase) ) );

		$comp_settings = wd_asp()->o['asp_compatibility'];
		$load_lazy = w_isset_def($comp_settings['load_lazy_js'], 0);

		PolylangStringTranslations::init();

		if (empty($results) || !empty($results['nores'])) {
			if (!empty($results['keywords'])) {
				$s_keywords = $results['keywords'];
				// Get the keyword suggestions template
				ob_start();
				if ( file_exists( $theme_path . "keyword-suggestions.php" ) )
					include( $theme_path . "keyword-suggestions.php" );
				else
					include( ASP_INCLUDES_PATH . "views/results/".$subdir."keyword-suggestions.php" );
			} else {
				// No results at all.
				ob_start();
				if ( file_exists( $theme_path . "no-results.php" ) )
					include( $theme_path . "no-results.php" );
				else
					include( ASP_INCLUDES_PATH . "views/results/".$subdir."no-results.php" );
			}
			$html .= ob_get_clean();
			if ( isset($results['suggested']) ) {
				$results = $results['suggested'];
			}
		}

		if ( !empty($results) && !isset($results['nores']) ) {
			if (isset($results['grouped'])) {
				foreach($results['groups'] as $k=>$g) {
					$group_name = esc_html($g['title']);
					$group_class = "asp_results_group_" . esc_attr($k);
					// Get the group headers
					ob_start();
					if ( file_exists( $theme_path . "group-header.php" ) )
						include( $theme_path . "group-header.php" );
					else
						include(ASP_INCLUDES_PATH . "views/results/group-header.php");
					$html .= ob_get_clean();

					// Get the item HTML
					foreach($g['items'] as $kk=>$r) {
						switch ($r->content_type) {
							case 'term':
								$show_description = $s_options['tax_res_showdescription'];
								break;
							case 'user':
								$show_description = $s_options['user_res_showdescription'];
								break;
							default:
								$show_description = $s_options['showdescription'];
						}
						$asp_res_css_class = ' asp_r_' . $r->content_type . ' asp_r_' . $r->content_type . '_' .$r->id;
						if ( isset($r->post_type) ) {
							$asp_res_css_class .= ' asp_r_' . $r->post_type;
						} else if ( isset($r->taxonomy) ) {
							$asp_res_css_class .= ' asp_r_' . $r->taxonomy;
						}
						ob_start();
						if ( file_exists( $theme_path . $theme . ".php" ) )
							include( $theme_path . $theme . ".php" );
						else
							include( ASP_INCLUDES_PATH . "views/results/" . $theme . ".php" );
						$html .= ob_get_clean();
					}

					// Display no results in group where no items are present
					if ( empty($g['items']) ) {
						ob_start();
						if ( file_exists( $theme_path . "no-results.php" ) )
							include( $theme_path . "no-results.php" );
						else
							include( ASP_INCLUDES_PATH . "views/results/no-results.php" );
						$html .= ob_get_clean();
					}

					// Get the group footers
					ob_start();
					if ( file_exists( $theme_path . "group-footer.php" ) )
						include( $theme_path . "group-footer.php" );
					else
						include( ASP_INCLUDES_PATH . "views/results/group-footer.php" );
					$html .= ob_get_clean();
				}
			} else {
				// Get the item HTML
				foreach($results as $k=>$r) {
					switch ($r->content_type) {
						case 'term':
							$show_description = $s_options['tax_res_showdescription'];
							break;
						case 'user':
							$show_description = $s_options['user_res_showdescription'];
							break;
						default:
							$show_description = $s_options['showdescription'];
					}
					$asp_res_css_class = ' asp_r_' . $r->content_type . ' asp_r_' . $r->content_type . '_' .$r->id;
					if ( isset($r->post_type) ) {
						$asp_res_css_class .= ' asp_r_' . $r->post_type;
					} else if ( isset($r->taxonomy) ) {
						$asp_res_css_class .= ' asp_r_' . $r->taxonomy;
					}
					ob_start();
					if ( file_exists( $theme_path . $theme . ".php" ) )
						include( $theme_path . $theme . ".php" );
					else
						include(ASP_INCLUDES_PATH . "views/results/" . $theme . ".php");
					$html .= ob_get_clean();
				}
			}
		}


		PolylangStringTranslations::save();

		return preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $html);
	}



	/**
	 * Returns the search options after redirection to the results page
	 *
	 * @return array|false
	 */
	public static function getOptions() {
		$options = false;

		// If the get method is used, then the cookies are not present or not used
		if ( isset($_GET['p_asp_data']) ) {
			if ( $_GET['p_asp_data'] == 1 ) {
				$options = $_GET;
			} else {
				// Legacy support
				parse_str(base64_decode($_GET['p_asp_data']), $options);
			}
		} else if (
			isset($_GET['s'], $_COOKIE['asp_data'], $_COOKIE['asp_phrase']) &&
			$_COOKIE['asp_phrase'] == $_GET['s']
		) {
			parse_str($_COOKIE['asp_data'], $options);
		}
		return $options;
	}
}