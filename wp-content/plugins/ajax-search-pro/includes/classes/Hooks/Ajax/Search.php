<?php /** @noinspection PhpMissingParamTypeInspection */

/** @noinspection PhpComposerExtensionStubsInspection */

namespace WPDRMS\ASP\Hooks\Ajax;

use WPDRMS\ASP\Cache\TextCache;
use WPDRMS\ASP\Misc\Performance;
use WPDRMS\ASP\Query\SearchQuery;
use WPDRMS\ASP\Utils\Ajax;
use WPDRMS\ASP\Utils\Search as SearchUtils;

if (!defined('ABSPATH')) die('-1');


class Search extends AbstractAjax {
	private $cache;

	/**
	 * Oversees and handles the search request
	 *
	 * @param bool $dontGroup
	 * @return array|mixed|void
	 * @noinspection PhpIncludeInspection
	 */
	public function handle($dontGroup = false) {

		$perf_options = wd_asp()->o['asp_performance'];

		if (w_isset_def($perf_options['enabled'], 1)) {
			$performance = new Performance('asp_performance_stats');
			$performance->start_measuring();
		}
		$s = $_POST['aspp'];

		if (is_array($_POST['options']))
			$options = $_POST['options'];
		else
			parse_str($_POST['options'], $options);

		$id = (int)$_POST['asid'];
		$instance = wd_asp()->instances->get($id);
		$sd = &$instance['data'];

		if (
			wd_asp()->o['asp_caching']['caching'] == 1
		) {
			$this->printCache($options, $s, $id);
		}

		// If previewed, we need the details
		if ( isset($_POST['asp_preview_options']) && (current_user_can("activate_plugins") || ASP_DEMO) ) {
			require_once(ASP_PATH . "backend" . DIRECTORY_SEPARATOR . "settings" . DIRECTORY_SEPARATOR . "types.inc.php");
			parse_str( $_POST['asp_preview_options'], $preview_options );
			$_POST['asp_preview_options'] = wpdreams_parse_params($preview_options);
			$_POST['asp_preview_options'] = wd_asp()->instances->decode_params($_POST['asp_preview_options']);
			$sd = $_POST['asp_preview_options'];
		}

		$asp_query = new SearchQuery(array(
			"s"    => $s,
			"_id"  => $id,
			"_ajax_search"  => true,
			"_call_num"     => $_POST['asp_call_num'] ?? 0
		), $id, $options);
		$results = $asp_query->posts;

		if ( count($results) > 0 ) {
			$results = apply_filters('asp_only_non_keyword_results', $results, $id, $s, $asp_query->getArgs());
		} else {
			if ( $sd['result_suggestions'] ) {
				$results = $asp_query->resultsSuggestions( $sd['keywordsuggestions'] );
			} else if ( $sd['keywordsuggestions'] ) {
				$results = $asp_query->kwSuggestions();
			}
		}

		if (count($results) <= 0 && $sd['keywordsuggestions']) {
			$results = $asp_query->resultsSuggestions();
		} else if (count($results) > 0) {
			$results = apply_filters('asp_only_non_keyword_results', $results, $id, $s, $asp_query->getArgs());
		}

		$results = apply_filters('asp_ajax_results', $results, $id, $s, $sd);

		do_action('asp_after_search', $s, $results, $id);

		if ( isset($performance) ) {
			$performance->stop_measuring();
		}

		$html_results = SearchUtils::generateHTMLResults($results, $sd, $id, $s);

		// Override from hooks
		if (isset($_POST['asp_get_as_array'])) {
			return $results;
		}

		$html_results = apply_filters('asp_before_ajax_output', $html_results, $id, $results, $asp_query->getArgs());

		$final_output = "";
		/* Clear output buffer, possible warnings */
		$final_output .= "___ASPSTART_HTML___" . $html_results . "___ASPEND_HTML___";
		$final_output .= "___ASPSTART_DATA___";
		if ( defined('JSON_INVALID_UTF8_IGNORE') ) {
			$final_output .= json_encode(array(
				'results_count' => isset($results["keywords"]) ? 0 : $asp_query->returned_posts,
				'full_results_count' => $asp_query->found_posts,
				'results' => $results
			), JSON_INVALID_UTF8_IGNORE);
		} else {
			$final_output .= json_encode(array(
				'results_count' => isset($results["keywords"]) ? 0 : $asp_query->returned_posts,
				'full_results_count' => $asp_query->found_posts,
				'results' => $results
			));
		}
		$final_output .= "___ASPEND_DATA___";

		$this->setCache($final_output);
		Ajax::prepareHeaders();
		print_r($final_output);
		die();
	}

	private function printCache($options, $s, $id) {
		$o = $options;
		$this->cache = new TextCache(wd_asp()->cache_path, "z_asp", wd_asp()->o['asp_caching']['cachinginterval'] * 60);
		$call_num = $_POST['asp_call_num'] ?? 0;

		unset($o['filters_initial'], $o['filters_changed']);
		$file_name = md5(json_encode($o) . $call_num . $s . $id);

		if ( wd_asp()->o['asp_caching']['caching_method'] == 'file' || wd_asp()->o['asp_caching']['caching_method'] == 'sc_file' ) {
			$cache_content = $this->cache->getCache($file_name);
		} else {
			$cache_content = $this->cache->getDBCache($file_name);
		}
		if ( $cache_content !== false ) {
			$cache_content = apply_filters('asp_cached_content', $cache_content, $s, $id);
			do_action('asp_after_search', $cache_content, $s, $id);
			print "cached(" . date("F d Y H:i:s.", $this->cache->getLastFileMtime()) . ")";
			print_r($cache_content);
			die;
		}
	}

	private function setCache($content) {
		if ( isset($this->cache) ) {
			if (
				wd_asp()->o['asp_caching']['caching_method'] == 'file' ||
				wd_asp()->o['asp_caching']['caching_method'] == 'sc_file'
			) {
				$this->cache->setCache('!!ASPSTART!!' . $content . "!!ASPEND!!");
			} else {
				$this->cache->setDBCache('!!ASPSTART!!' . $content . "!!ASPEND!!");
			}
		}
	}
}