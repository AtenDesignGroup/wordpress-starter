<?php /** @noinspection PhpComposerExtensionStubsInspection */

namespace WPDRMS\ASP\Suggest;

use Exception;
use WPDRMS\ASP\Utils\MB;

defined('ABSPATH') or die("You can't access this file directly.");


class SuggestGooglePlaces extends AbstractSuggest {
	private $args, $url;

	public function __construct( $args = array() ) {
		$defaults = array(
			'maxCount' => 10,
			'maxCharsPerWord' => 25,
			'lang' => "en",
			'overrideUrl' => '',
			'match_start' => false,
			'api_key' => ''
		);
		$args = wp_parse_args( $args, $defaults );
		$this->args = $args;

		if ($args['overrideUrl'] != '') {
			$this->url = $args['overrideUrl'];
		} else {
			$this->url = 'https://maps.googleapis.com/maps/api/place/autocomplete/json?types=geocode&language=' . $args['lang'] . '&key='.$args['api_key'] . "&input=";
		}

		$this->url = apply_filters('asp/suggestions/google_places/url', $this->url, $args);
	}


	public function getKeywords(string $q): array {
		if ( $this->args['api_key'] == "" )
			return array();

		$qf = str_replace(' ', '+', $q);

		$response = wp_remote_get( $this->url . rawurlencode($qf), array(
			'timeout' => 1,
			'user-agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/103.0.0.0 Safari/537.36'
		) );

		if ( is_wp_error( $response ) || !isset($response['body']) ) {
			return array();
		} else {
			$data = $response['body'];
		}

		if (function_exists('mb_convert_encoding'))
			$data = mb_convert_encoding($data, "UTF-8");
		try {
			$array = json_decode($data, TRUE);
			$res = array();

			foreach ($array['predictions']  as $keyword) {
				$t = MB::strtolower($keyword['description']);
				if (
					$t != $q &&
					('' != $str = wd_substr_at_word($keyword['description'], $this->args['maxCharsPerWord'], ''))
				) {
					if ($this->args['match_start'] && strpos($t, MB::strtolower($q)) === 0)
						$res[] = $str;
					elseif (!$this->args['match_start'])
						$res[] = $str;
				}
			}
			$res = array_slice($res, 0, $this->args['maxCount']);
			if (count($res) > 0) {
				return apply_filters('asp/suggestions/google_places/results', $res, $q);
			} else {
				return apply_filters('asp/suggestions/google_places/results', array(), $q);
			}
		} catch( Exception $e) {
			return apply_filters('asp/suggestions/google_places/results', array(), $q);
		}
	}
}