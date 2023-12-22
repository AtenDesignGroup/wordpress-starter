<?php /** @noinspection PhpComposerExtensionStubsInspection */

namespace WPDRMS\ASP\Suggest;

use Exception;
use WPDRMS\ASP\Utils\MB;

defined('ABSPATH') or die("You can't access this file directly.");

class SuggestGoogle extends AbstractSuggest {
	protected $args, $url;

	function __construct( $args = array() ) {
		$defaults = array(
			'maxCount' => 10,
			'maxCharsPerWord' => 25,
			'lang' => "en",
			'overrideUrl' => '',
			'match_start' => false
		);
		$args = wp_parse_args( $args, $defaults );
		$this->args = $args;

		if ($args['overrideUrl'] != '') {
			$this->url = $args['overrideUrl'];
		} else {
			$this->url = 'https://suggestqueries.google.com/complete/search?output=toolbar&oe=utf-8&client=toolbar&hl=' . $args['lang'] . '&q=';
		}

		$this->url = apply_filters('asp/suggestions/google/url', $this->url, $args);
	}

	public function getKeywords(string $q): array {
		$qf = str_replace(' ', '+', $q);
		$response = wp_remote_get( $this->url . rawurlencode($qf), array(
			'timeout' => 1,
			'user-agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/103.0.0.0 Safari/537.36'
		) );
		if ( is_wp_error( $response ) || !isset($response['body']) ) {
			return apply_filters('asp/suggestions/google/results', array(), $q);
		} else {
			$data = $response['body'];
		}

		if (function_exists('mb_convert_encoding'))
			$data = mb_convert_encoding($data, "UTF-8");
		try {
			$suggestions = simplexml_load_string($data);
			if (
				$suggestions === false ||
				!isset($suggestions->{'CompleteSuggestion'})
			) {
				return apply_filters('asp/suggestions/google/results', array(), $q);
			}

			$suggestions = json_decode(
				json_encode($suggestions->{'CompleteSuggestion'}),
				true
			);
			$res = array();
			$keywords = array();
			foreach ($suggestions as $v) {
				if (isset($v['suggestion']))
					$keywords[] = $v['suggestion']['@attributes']['data'];
				elseif (isset($v[0]))
					$keywords[] = $v[0]['@attributes']['data'];
			}
			foreach ($keywords as $keyword) {
				$t = MB::strtolower($keyword);
				if (
					$t != $q &&
					('' != $str = wd_substr_at_word($keyword, $this->args['maxCharsPerWord'], ''))
				) {
					if ($this->args['match_start'] && strpos($t, MB::strtolower($q)) === 0)
						$res[] = $str;
					elseif (!$this->args['match_start'])
						$res[] = $str;
				}
			}
			$res = array_slice($res, 0, $this->args['maxCount']);
			if ( count($res) > 0 ) {
				return apply_filters('asp/suggestions/google/results', $res, $q);
			} else {
				return apply_filters('asp/suggestions/google/results', array(), $q);
			}
		} catch(Exception $e) {
			return apply_filters('asp/suggestions/google/results', array(), $q);
		}
	}
}