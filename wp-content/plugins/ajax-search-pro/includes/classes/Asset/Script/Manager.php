<?php
namespace WPDRMS\ASP\Asset\Script;

use stdClass;
use WPDRMS\ASP\Asset\ManagerInterface;
use WPDRMS\ASP\Patterns\SingletonTrait;
use WPDRMS\ASP\Utils\Html;
use WPDRMS\ASP\Utils\Script;

defined('ABSPATH') or die("You can't access this file directly.");

class Manager implements ManagerInterface {
	use SingletonTrait;
	private $prepared = array();
	private $media_query = '';
	private $inline = '';
	private $inline_instance = '';
	private $instances = false;

	private $args = array();


	private $scripts = array(
		'wd-asp-photostack' => array(
			'src' => 'js/{js_source}/external/photostack.js',
			'prereq' => false
		),
		'wd-asp-select2' => array(
			'src' => 'js/{js_source}/external/jquery.select2.js',
			'prereq' => array('jquery')
		),
		'wd-asp-lazy' => array(
			'src' => 'js/{js_source}/external/lazy.js',
			'prereq' => array('wd-asp-ajaxsearchpro')
		),
		'wd-asp-nouislider' => array(
			'src' => 'js/{js_source}/external/nouislider.all.js',
			'prereq' => false
		),
		'wd-asp-rpp-isotope' => array(
			'src' => 'js/{js_source}/external/isotope.js',
			'prereq' => false
		),
		'wd-asp-ajaxsearchpro' => array(
			'src' => 'js/{js_source}/plugin/merged/asp.js',
			'prereq' => false
		),
		'wd-asp-prereq-and-wrapper' => array(
			'src' => 'js/{js_source}/plugin/merged/asp-prereq-and-wrapper.js',
			'prereq' => false
		)
	);

	private $optimized_scripts = array(
		'wd-asp-ajaxsearchpro' => array(
			'wd-asp-ajaxsearchpro-prereq' => array(
				'handle' => 'wd-asp-ajaxsearchpro',	// Handle alias, for the enqueue
				'src' => 'js/{js_source}/plugin/optimized/asp-prereq.js',
			),
			'wd-asp-ajaxsearchpro-core' => array(
				'src' => 'js/{js_source}/plugin/optimized/asp-core.js',
			),
			'wd-asp-ajaxsearchpro-settings' => array(
				'src' => 'js/{js_source}/plugin/optimized/asp-settings.js',
				'prereq' => array('wd-asp-ajaxsearchpro'),
			),
			'wd-asp-ajaxsearchpro-compact' => array(
				'src' => 'js/{js_source}/plugin/optimized/asp-compact.js',
				'prereq' => array('wd-asp-ajaxsearchpro'),
			),
			'wd-asp-ajaxsearchpro-vertical' => array(
				'src' => 'js/{js_source}/plugin/optimized/asp-results-vertical.js',
				'prereq' => array('wd-asp-ajaxsearchpro'),
			),
			'wd-asp-ajaxsearchpro-horizontal' => array(
				'src' => 'js/{js_source}/plugin/optimized/asp-results-horizontal.js',
				'prereq' => array('wd-asp-ajaxsearchpro'),
			),
			'wd-asp-ajaxsearchpro-polaroid' => array(
				'src' => 'js/{js_source}/plugin/optimized/asp-results-polaroid.js',
				'prereq' => array('wd-asp-ajaxsearchpro'),
			),
			'wd-asp-ajaxsearchpro-isotopic' => array(
				'src' => 'js/{js_source}/plugin/optimized/asp-results-isotopic.js',
				'prereq' => array('wd-asp-ajaxsearchpro'),
			),
			'wd-asp-ajaxsearchpro-ga' => array(
				'src' => 'js/{js_source}/plugin/optimized/asp-ga.js',
				'prereq' => array('wd-asp-ajaxsearchpro'),
			),
			'wd-asp-ajaxsearchpro-live' => array(
				'src' => 'js/{js_source}/plugin/optimized/asp-live.js',
				'prereq' => array('wd-asp-ajaxsearchpro'),
			),
			'wd-asp-ajaxsearchpro-autocomplete' => array(
				'src' => 'js/{js_source}/plugin/optimized/asp-autocomplete.js',
				'prereq' => array('wd-asp-ajaxsearchpro'),
			),
			'wd-asp-ajaxsearchpro-load' => array(
				'src' => 'js/{js_source}/plugin/optimized/asp-load.js',
				'prereq' => true, // TRUE => previously loaded script
			),
			'wd-asp-ajaxsearchpro-wrapper' => array(
				'src' => 'js/{js_source}/plugin/optimized/asp-wrapper.js',
				'prereq' => true, // TRUE => previously loaded script
			),
			'wd-asp-ajaxsearchpro-addon-elementor' => array(
				'src' => 'js/{js_source}/plugin/optimized/asp-addons-elementor.js',
				'prereq' => true, // TRUE => previously loaded script
			),
			'wd-asp-ajaxsearchpro-addon-divi' => array(
				'src' => 'js/{js_source}/plugin/optimized/asp-addons-divi.js',
				'prereq' => true, // TRUE => previously loaded script
			)
		)
	);

	private $dev_scripts = array();


	// -------------------------------------------- PUBLIC ---------------------------------------------------------

	public function enqueue( $force = false ) {
		if ( $force || $this->args['method'] == 'classic' ) {
			// Do not allow async method in footer enqueue
			$this->args['method'] = $this->args['method'] == 'optimized_async' ? 'optimized' :$this->args['method'];
			$this->earlyFooterEnqueue();
			$this->initialize();
			echo $this->inline;
			foreach ($this->prepared as $script) {
				wp_enqueue_script($script['handle']);
			}
		}
	}

	public function printInline( $instances = array() ) {
		if ( $this->args['method'] != 'classic' ) {
			$this->initialize();
			$output = $this->inline_instance . $this->inline . $this->preparedToInline();
			if ( $output != '' ) {
				echo $output;
			}
		}
	}

	public function injectToBuffer($buffer, $instances) {
		if ( $this->args['method'] != 'classic' ) {
			$this->initialize($instances);
			$output = $this->inline_instance . $this->inline . $this->preparedToInline();
			if ( $output != '' ) {
				Html::inject($output, $buffer, array('</body>', '</html>', "<script"), false);
			}
		}
		return $buffer;
	}

	public function cleanup() {
		Generator::deleteFiles();
	}

	// ------------------------------------------- PRIVATE ---------------------------------------------------------

	private function preparedToInline() {
		$out = '';
		foreach ($this->prepared as $script) {
			$async = isset($script['async']) ? 'async ' : '';
			$out .= "<script " . $async . "type='text/javascript' src='" . $script['src'] ."' id='" . $script['handle'] ."-js'></script>";
		}
		return $out;
	}

	private function __construct() {
		if ( defined('ASP_DEBUG') && ASP_DEBUG == 1 ) {
			$dev_config = new DevConfig();
			$this->dev_scripts = $dev_config->getDevScripts();
		}

		$this->args = array(
			'method' => wd_asp()->o['asp_compatibility']['script_loading_method'],
			'source' => wd_asp()->o['asp_compatibility']['js_source'],
			'detect_ajax' => wd_asp()->o['asp_compatibility']['detect_ajax'],
			'init_only_in_viewport' => wd_asp()->o['asp_compatibility']['init_instances_inviewport_only'],
			'custom_ajax_handler' => wd_asp()->o['asp_compatibility']['usecustomajaxhandler'],
		);

		$this->adjustOptionsForCompatibility();
	}

	private function adjustOptionsForCompatibility() {
		if (
			isset($_GET, $_GET['et_fb']) || // Divi frontend editor
			isset($_GET, $_GET['vcv-ajax']) || // Visual Composer Frontend editor
			isset($_GET, $_GET['fl_builder']) || // Beaver Builder Frontend editor
			isset($_GET, $_GET['elementor-preview']) // Elementor Frontend
		) {
			$this->args['method'] = 'classic';
		}
	}

	private function get( $handles = array(), $minified = true, $optimized = false, $except = array() ): array {
		$handles = is_string($handles) ? array($handles) : $handles;
		$handles = count($handles) == 0 ? array_keys($this->scripts) : $handles;
		$js_source = $minified ? "min" : "nomin";
		$return = array();

		foreach ( $handles as $handle ) {
			if ( in_array($handle, $except) || !Requirements::isRequired($handle, $this->instances) ) {
				continue;
			}
			if ( isset($this->scripts[$handle]) ) {
				if ( defined('ASP_DEBUG') &&
					ASP_DEBUG == 1 &&
					isset($this->dev_scripts[$handle]) &&
					wd_asp()->manager->getContext() != "backend"
				) {
					$src = $this->dev_scripts[$handle]['src'];
					$src = !is_array($src) ? array($src) : $src;
					$i = 0;
					foreach ( $src as $file_path ) {
						$_handle = $i == 0 ? $handle : $handle . '_' . $i;
						$url = esc_url_raw(str_replace(
							wp_normalize_path( untrailingslashit( ABSPATH ) ),
							site_url(),
							wp_normalize_path( $file_path )
						));
						$return[] = array(
							'handle' => $_handle,
							'src' => str_replace(
								array('{js_source}'),
								array($js_source),
								$url
							),
							'prereq' => $this->scripts[$handle]['prereq']
						);
						++$i;
					}
					continue;
				}

				if ( $optimized && isset($this->optimized_scripts[$handle]) ) {
					$prev_handle = '';
					foreach ( $this->optimized_scripts[$handle] as $optimized_script_handle => $optimized_script ) {
						if ( in_array($optimized_script_handle, $except) || !Requirements::isRequired($optimized_script_handle, $this->instances) ) {
							continue;
						}
						$prereq = !isset($optimized_script['prereq']) || $optimized_script['prereq'] === false ? array() : $optimized_script['prereq'];
						if ( $prereq === true ) {
							$prereq = array($prev_handle);
						}
						$return[] = array(
							'handle' => $optimized_script['handle'] ?? $optimized_script_handle,
							'path' => ASP_PATH . str_replace(
									array('{js_source}'),
									array($js_source),
									$optimized_script['src']
								),
							'src' => ASP_URL . str_replace(
									array('{js_source}'),
									array($js_source),
									$optimized_script['src']
								),
							'prereq' => $prereq
						);

						$prev_handle = $optimized_script_handle;
					}
					continue;
				}


				$return[] = array(
					'handle' => $handle,
					'path' => ASP_PATH . str_replace(
							array('{js_source}'),
							array($js_source),
							$this->scripts[$handle]['src']
						),
					'src' => ASP_URL . str_replace(
							array('{js_source}'),
							array($js_source),
							$this->scripts[$handle]['src']
						),
					'prereq' => $this->scripts[$handle]['prereq']
				);
			} else if ( $optimized && wd_in_array_r($handle, $this->optimized_scripts) ) {
				foreach ( $this->optimized_scripts as $scripts ) {
					if ( isset($scripts[$handle]) ) {
						$return[] = array(
							'handle' => $handle,
							'path' => ASP_PATH . str_replace(
									array('{js_source}'),
									array($js_source),
									$scripts[$handle]['src']
								),
							'src' => ASP_URL . str_replace(
									array('{js_source}'),
									array($js_source),
									$scripts[$handle]['src']
								),
							'prereq' => $scripts[$handle]['prereq']
						);
					}
				}
			}
		}

		return $return;
	}

	public function earlyFooterEnqueue( $instances = false ) {
		/**
		 * Internal WP Scripts have to be enqueued at this point
		 */
		if ( Requirements::isRequired('jquery', $instances) ) {
			wp_enqueue_script('jquery');
		}
		if ( Requirements::isRequired('jquery-ui-datepicker', $instances) ) {
			wp_enqueue_script('jquery-ui-datepicker');
		}

	}

	/**
	 * Prints the scripts
	 */
	public function initialize( $instances = false ): bool {
		$this->instances = $instances;
		$analytics = wd_asp()->o['asp_analytics'];
		$load_in_footer = true;
		$media_query = ASP_DEBUG == 1 ? asp_gen_rnd_str() : get_site_option("asp_media_query", "defn");
		if ( wd_asp()->manager->getContext() == "backend" ) {
			$js_minified = false;
			$js_optimized = true;
			$js_async_load = false;
			$js_aggregate = false;
		} else {
			$js_minified = $this->args['source'] == 'jqueryless-min';
			$js_optimized = $this->args['method']  != 'classic';
			$js_async_load = $this->args['method']  == 'optimized_async';
			$js_aggregate = !$js_async_load && !ASP_DEBUG && !(defined('WP_ASP_TEST_ENV') && is_user_logged_in());
		}

		$single_highlight = false;
		$single_highlight_arr = array();

		// Search results page && keyword highlighter

		if (
			isset($_GET['asp_highlight'], $_GET['p_asid']) && intval($_GET['p_asid']) > 0 &&
			wd_asp()->instances->exists($_GET['p_asid'])
		) {
			$phrase = $_GET['s'] ?? $_GET['asp_highlight'];
			if ( $phrase != '' ) {
				$search = wd_asp()->instances->get(intval($_GET['p_asid']));
				if ( $search['data']['single_highlight'] == 1 || $search['data']['result_page_highlight'] == 1 ) {
					$single_highlight = true;
					$single_highlight_arr = array(
						'id' => $search['id'],
						'selector' => $search['data']['single_highlight_selector'],
						'scroll' => $search['data']['single_highlight_scroll'] == 1,
						'scroll_offset' => intval($search['data']['single_highlight_offset']),
						'whole' => $search['data']['single_highlightwholewords'] == 1,
					);
				}
			}
		}

		$ajax_url = admin_url('admin-ajax.php');
		if ( !is_admin() ) {
			if (  $this->args['custom_ajax_handler'] == 1) {
				$ajax_url = ASP_URL . 'ajax_search.php';
			}
			if ( wd_asp()->o['asp_caching']['caching'] == 1 && wd_asp()->o['asp_caching']['caching_method'] == 'sc_file' ) {
				$ajax_url = ASP_URL . 'sc-ajax.php';
			}
		}

		$handle = 'wd-asp-ajaxsearchpro';
		if ( !$js_async_load ) {
			$handle = $this->prepare(
				$this->get(array(), $js_minified, $js_optimized, array(
					'wd-asp-prereq-and-wrapper'
				)),
				array(
					'media_query' => $media_query,
					'in_footer' => $load_in_footer,
					'aggregate' => $js_aggregate,
					'handle' => $handle
				)
			);
			$additional_scripts = $this->get(array(), $js_minified, $js_optimized,
				array('wd-asp-prereq-and-wrapper', 'wd-asp-ajaxsearchpro-wrapper')
			);
		} else {
			$handle = 'wd-asp-prereq-and-wrapper';
			$this->prepare(
				$this->get($handle, $js_minified, $js_optimized),
				array(
					'media_query' => $media_query,
					'in_footer' => $load_in_footer
				)
			);
			$additional_scripts = $this->get(array(), $js_minified, $js_optimized,
				array(
					'wd-asp-prereq-and-wrapper',
					'wd-asp-ajaxsearchpro-wrapper',
					'wd-asp-ajaxsearchpro-prereq'
				)
			);
		}

		// The new variable is ASP
		$this->inline = Script::objectToInlineScript($handle, 'ASP', array(
			'wp_rocket_exception' => 'DOMContentLoaded',	// WP Rocket hack to prevent the wrapping of the inline script: https://docs.wp-rocket.me/article/1265-load-javascript-deferred
			'ajaxurl' => $ajax_url,
			'backend_ajaxurl' => admin_url('admin-ajax.php'),
			'asp_url' => ASP_URL,
			'upload_url' => wd_asp()->upload_url,
			'detect_ajax' => $this->args['detect_ajax'],
			'media_query' => get_site_option("asp_media_query", "defn"),
			'version' => ASP_CURR_VER,
			'pageHTML' => '',
			'additional_scripts' => $additional_scripts,
			'script_async_load' => $js_async_load,
			'font_url' => str_replace('http:', "", plugins_url()). '/ajax-search-pro/css/fonts/icons/icons2.woff2',
			'init_only_in_viewport' => $this->args['init_only_in_viewport'] == 1,
			'highlight' => array(
				'enabled' => $single_highlight,
				'data' => $single_highlight_arr
			),
			'debug' => ASP_DEBUG == 1 || defined('WP_ASP_TEST_ENV'),
			'instances' => new stdClass(),
			'analytics' => array(
				'method' => $analytics['analytics'],
				'tracking_id' => $analytics['analytics_tracking_id'],
				'event' => array(
					'focus' => array(
						'active' => $analytics['gtag_focus'],
						'action' => $analytics['gtag_focus_action'],
						"category" => $analytics['gtag_focus_ec'],
						"label" =>  $analytics['gtag_focus_el'],
						"value" => $analytics['gtag_focus_value']
					),
					'search_start' => array(
						'active' => $analytics['gtag_search_start'],
						'action' => $analytics['gtag_search_start_action'],
						"category" => $analytics['gtag_search_start_ec'],
						"label" =>  $analytics['gtag_search_start_el'],
						"value" => $analytics['gtag_search_start_value']
					),
					'search_end' => array(
						'active' => $analytics['gtag_search_end'],
						'action' => $analytics['gtag_search_end_action'],
						"category" => $analytics['gtag_search_end_ec'],
						"label" =>  $analytics['gtag_search_end_el'],
						"value" => $analytics['gtag_search_end_value']
					),
					'magnifier' => array(
						'active' => $analytics['gtag_magnifier'],
						'action' => $analytics['gtag_magnifier_action'],
						"category" => $analytics['gtag_magnifier_ec'],
						"label" =>  $analytics['gtag_magnifier_el'],
						"value" => $analytics['gtag_magnifier_value']
					),
					'return' => array(
						'active' => $analytics['gtag_return'],
						'action' => $analytics['gtag_return_action'],
						"category" => $analytics['gtag_return_ec'],
						"label" =>  $analytics['gtag_return_el'],
						"value" => $analytics['gtag_return_value']
					),
					'try_this' => array(
						'active' => $analytics['gtag_try_this'],
						'action' => $analytics['gtag_try_this_action'],
						"category" => $analytics['gtag_try_this_ec'],
						"label" =>  $analytics['gtag_try_this_el'],
						"value" => $analytics['gtag_try_this_value']
					),
					'facet_change' => array(
						'active' => $analytics['gtag_facet_change'],
						'action' => $analytics['gtag_facet_change_action'],
						"category" => $analytics['gtag_facet_change_ec'],
						"label" =>  $analytics['gtag_facet_change_el'],
						"value" => $analytics['gtag_facet_change_value']
					),
					'result_click' => array(
						'active' => $analytics['gtag_result_click'],
						'action' => $analytics['gtag_result_click_action'],
						"category" => $analytics['gtag_result_click_ec'],
						"label" =>  $analytics['gtag_result_click_el'],
						"value" => $analytics['gtag_result_click_value']
					)
				)
			)
		), 'before',true, false);

		// Instance data
		$script_data = wd_asp()->instances->get_script_data();
		if ( count($script_data) > 0 ) {
			if ( $instances === false ) {
				$script = "window.ASP_INSTANCES = [];";
				foreach ($script_data as $id => $data) {
					$script .= "window.ASP_INSTANCES[$id] = $data;";
				}

				$script_id = 'wd-asp-instances-' . substr(md5($script), 0, 8);
				$this->inline_instance .= "<script id='$script_id'>$script</script>";
			} else {
				$script = '';
				foreach ( $instances as $id ) {
					if ( isset($script_data[$id]) ) {
						$script .= "window.ASP_INSTANCES[$id] = $script_data[$id];";
					}
				}
				if ( $script != '' ) {
					$script = "window.ASP_INSTANCES = [];" . $script;
				}
			}

			/**
			 * Why not wp_add_inline_script(), why this way?
			 *
			 * Because the script ID needs to be different for each different output, to signify difference
			 * for cache plugin. Otherwise caches like wp-optimize will cache the same output for the same
			 * script ID - and then the search instances will be missing.
			 *
			 * WordPress prints scripts at priority: 10 in this hook
			 */
			if ( $script != '' ) {
				$script_id = 'wd-asp-instances-' . substr(md5($script), 0, 8);
				$this->inline_instance .= "<script id='$script_id'>$script</script>";
			}
		}
		return true;
	}

	private function prepare( $scripts = array(), $args = array() ) {
		$defaults = array(
			'media_query' => '',
			'in_footer'	=> true,
			'prereq'	=> array(),
			'aggregate' => false,
			'handle'    => ''
		);
		$args = wp_parse_args($args, $defaults);
		$register_scripts = $scripts;
		if ( $args['aggregate'] ) {
			$generator = new Generator($scripts);
			$filename = $generator->get();
			if ( $filename != '' ) {
				$handle = $generator->fileHandle();
				$prereq = $args['prereq'];
				foreach ($scripts as $script) {
					if ( is_array($script['prereq']) ) {
						$prereq = array_merge($prereq, $script['prereq']);
					}
				}
				$prereq = array_unique($prereq);
				$prereq = array_filter($prereq, function($p){
					return strpos($p, 'asp-') === false;
				});
				$register_scripts = array(
					array(
						'handle' => $handle,
						'src'	 => wd_asp()->cache_url . $filename,
						'prereq' => $prereq,
						'async'  => true
					)
				);
			}
		}

		foreach ( $register_scripts as $script ) {
			if ( isset($script['prereq']) ) {
				if ( $script['prereq'] === false ) {
					$script['prereq'] = array();
				}
			} else {
				$script['prereq'] = $args['prereq'];
			}
			wp_register_script(
				$script['handle'],
				$script['src'],
				$script['prereq'],
				$args['media_query'],
				$args['in_footer']
			);
			$this->prepared[] = $script;
		}

		return $handle ?? $args['handle'];
	}
}