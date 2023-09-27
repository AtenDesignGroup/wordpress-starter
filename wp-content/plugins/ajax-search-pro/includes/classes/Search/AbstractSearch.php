<?php
namespace WPDRMS\ASP\Search;

use WPDRMS\ASP\Utils\MB;
use WPDRMS\ASP\Utils\Str;

defined('ABSPATH') or die("You can't access this file directly.");

abstract class AbstractSearch {

	// Total results count (unlimited)
	public $results_count = 0;

	// Actual results count, results which are returned
	public $return_count = 0;

	/**
	 * @var array of parameters
	 */
	protected $args;

	protected $pre_field = '';
	protected $suf_field = '';
	protected $pre_like  = '';
	protected $suf_like  = '';

	/**
	 * @var int the remaining limit (number of items to look for)
	 */
	protected $remaining_limit;
	/**
	 * @var int the start of the limit
	 */
	protected $limit_start = 0;
	/**
	 * @var int remaining limit modifier
	 */
	protected $remaining_limit_mod = 10;

	/**
	 * @var array of submitted options from the front end
	 */
	protected $options;
	/**
	 * @var int the ID of the current search instance
	 */
	protected $searchId;
	/**
	 * @var array of results
	 */
	protected $results;
	/**
	 * @var string the search phrase
	 */
	protected $s;
	/**
	 * @var array of each search phrase
	 */
	protected $_s;
	/**
	 * @var string the reversed search phrase
	 */
	protected $sr;
	/**
	 * @var array of each reversed search phrase
	 */
	protected $_sr;

	/**
	 * @var int the current blog ID
	 */
	protected $c_blogid;
	/**
	 * @var string the final search query
	 */
	protected $query;

	/**
	 * Create the class
	 */
	public function __construct($args) {
		$this->args = $args;

		if ( isset($args['_remaining_limit_mod']) )
			$this->remaining_limit_mod = $args['_remaining_limit_mod'];

		$this->c_blogid = get_current_blog_id();
	}

	/**
	 * Initiates the search operation
	 *
	 * @param $s
	 * @return array
	 * @noinspection PhpMissingParamTypeInspection
	 */
	public function search( $s = '' ): array {

		if ( MB::strlen($s) > 120 )
			$s = MB::substr($s, 0, 120);

		$this->prepareKeywords($s);
		$this->doSearch();
		$this->postProcess();

		return is_array($this->results) ? $this->results : array();
	}

	public function prepareKeywords($s) {

		if ( $s !== false )
			$keyword = $s;
		else
			$keyword = $this->args['s'];

		// This is the "japanese" ideographic space character, replaced by the regular space
		$keyword = preg_replace("@[ 　]@u", ' ', $keyword);

		$keyword = $this->compatibility($keyword);
		$keyword_rev = Str::reverse($keyword);

		$this->s = Str::escape( $keyword );
		$this->sr = Str::escape( $keyword_rev );

		/**
		 * Avoid double escape, explode the $keyword instead of $this->s
		 * Regex to match individual words and phrases between double quotes
		 **/
		if (
			preg_match_all( '/«.*?»/', $keyword, $m ) > 0 &&  // Only if there is at lease one complete «text» match
			preg_match_all( '/«.*?(»|$)|((?<=[\t «,+])|^)[^\t »,+]+/', $keyword, $matches )
		) {
			$this->_s = $this->parseSearchTerms(  $matches[0] );
		} else if (
			preg_match_all( '/".*?("|$)|((?<=[\t ",+])|^)[^\t ",+]+/', $keyword, $matches )
		) {
			$this->_s = $this->parseSearchTerms(  $matches[0] );
		} else {
			$this->_s = $this->parseSearchTerms( explode(" ", $keyword) );
		}
		if (
			preg_match_all( '/«.*?»/', $keyword_rev, $m ) > 0 &&  // Only if there is at lease one complete «text» match
			preg_match_all( '/«.*?(»|$)|((?<=[\t «,+])|^)[^\t »,+]+/', $keyword_rev, $matches )
		) {
			$this->_sr = $this->parseSearchTerms(  array_reverse($matches[0]) );
		} else if ( preg_match_all( '/".*?("|$)|((?<=[\t ",+])|^)[^\t ",+]+/', $keyword_rev, $matches ) ) {
			$this->_sr = $this->parseSearchTerms(  array_reverse($matches[0]) );
		} else {
			$this->_sr = $this->parseSearchTerms( array_reverse( explode(" ", $keyword_rev ) ) );
		}

		foreach ($this->_s as $k=>$w) {
			if ( MB::strlen($w) < $this->args['min_word_length'] ) {
				unset($this->_s[$k]);
			}
		}

		foreach ($this->_sr as $k=>$w) {
			if ( MB::strlen($w) < $this->args['min_word_length'] ) {
				unset($this->_sr[$k]);
			}
		}

		/**
		 * Reindex the arrays, in case there are missing keys from the previous removals
		 */
		if ( count($this->_s) > 0 ) {
			$this->_s = array_values($this->_s);
			$this->_sr = array_values($this->_sr);
		}
	}

	/**
	 * Check if the terms are suitable for searching.
	 *
	 * @param array $terms Terms to check.
	 * @return array Terms
	 */
	protected function parseSearchTerms(array $terms ): array {
		$checked = array();

		foreach ( $terms as $term ) {
			// keep before/after spaces when term is for exact match
			if ( preg_match( '/^".+"$/', $term ) ) {
				$term = trim($term, "\"'");
			} else if ( preg_match( '/^«.+»$/', $term ) ) { // same for russian quotes
				$term = trim($term, "«»'");
			} else {
				$term = trim($term, "\"' ");
			}
			if ( $term != '' )
				$checked[] = $term;
		}

		if ( count($checked) > 0 )
			$checked = Str::escape(
				array_slice(array_unique($checked), 0, $this->args['_keyword_count_limit'])
			);

		return $checked;
	}

	/**
	 * The search function
	 */
	abstract protected function doSearch();

	/**
	 * Post-processing abstract
	 */
	protected function postProcess() {}

	/**
	 * Converts the keyword to the correct case and sets up the pre-suff fields.
	 *
	 * @param $s
	 * @return string
	 */
	protected function compatibility($s): string {
		/**
		 *  On forced case sensitivity: Let's add BINARY keyword before the LIKE
		 *  On forced case in-sensitivity: Append the lower() function around each field
		 */
		if ( $this->args['_db_force_case'] === 'sensitivity' ) {
			$this->pre_like = 'BINARY ';
		} else if ( $this->args['_db_force_case'] === 'insensitivity' ) {
			if ( function_exists( 'mb_convert_case' ) )
				$s = mb_convert_case( $s, MB_CASE_LOWER, "UTF-8" );
			else
				$s = strtoupper( $s );
			// if no mb_ functions :(

			$this->pre_field .= 'lower(';
			$this->suf_field .= ')';
		}

		/**
		 *  Check if utf8 is forced on LIKE
		 */
		if ( w_isset_def( $this->args['_db_force_utf8_like'], 0 ) == 1 )
			$this->pre_like .= '_utf8';

		/**
		 *  Check if unicode is forced on LIKE, but only apply if utf8 is not
		 */
		if ( w_isset_def( $this->args['_db_force_unicode'], 0 ) == 1
			&& w_isset_def( $this->args['_db_force_utf8_like'], 0 ) == 0
		)
			$this->pre_like .= 'N';

		return $s;
	}

	/**
	 * Builds the query from the parts
	 *
	 * @param $parts
	 *
	 * @return string query
	 */
	protected function buildQuery( $parts ): string {

		$args = &$this->args;
		$kw_logic = str_replace('EX', '', strtoupper( $args['keyword_logic'] ) );
		$kw_logic = $kw_logic != 'AND' && $kw_logic != 'OR' ? 'AND' : $kw_logic;

		$r_parts = array(); // relevance parts

		/*------------------------- Build like --------------------------*/
		$exact_query = '';
		$like_query_arr = array();
		foreach ( $parts as $k=>$part ) {
			if ( $k == 0 )
				$exact_query = '(' . implode(' OR ', $part[0]) . ')';
			else
				$like_query_arr[] = '(' . implode(' OR ', $part[0]) . ')';
		}
		$like_query = implode(' ' . $kw_logic . ' ', $like_query_arr);

		// When $exact query is empty, then surely $like_query must be empty too, see above
		if ( $exact_query == '' ) {
			$like_query = "(1)";
		} else {
			// Both $like_query and $exact_query set
			if ( $like_query != '' ) {
				$like_query = "( $exact_query OR $like_query )";
			} else {
				$like_query = "( $exact_query )";
			}
		}
		/*---------------------------------------------------------------*/

		/*---------------------- Build relevance ------------------------*/
		foreach ( $parts as $part ) {
			if ( isset($part[1]) && count($part[1]) > 0 )
				$r_parts = array_merge( $r_parts, $part[1] );
		}
		$relevance = implode( ' + ', $r_parts );
		if ( $args['_post_use_relevance'] != 1 || $relevance == "" ) {
			$relevance = "(1)";
		} else {
			$relevance = "($relevance)";
		}
		/*---------------------------------------------------------------*/

		if ( isset($this->remaining_limit) ) {
			if ($this->limit_start != 0)
				$limit = $this->limit_start . ", " . $this->remaining_limit;
			else
				$limit = $this->remaining_limit;
		} else {
			$limit = 10;
		}

		return str_replace(
			array( "{relevance_query}", "{like_query}", "{remaining_limit}" ),
			array( $relevance, $like_query, $limit ),
			$this->query
		);

	}

	/**
	 * Returns the context of a phrase within a text.
	 * Uses preg_split method to iterate through strings.
	 *
	 * @param $str - string context
	 * @param $needle - string context
	 * @param $context - int length of the context
	 * @param $maxlength - int maximum length of the string in characters
	 * @param $str_length_limit - source string maximum length
	 * @return string
	 * @noinspection PhpMissingParamTypeInspection
	 */
	public function contextFind($str, $needle, $context, $maxlength, $str_length_limit = 10000, $false_on_no_match = false) {
		$haystack = remove_accents(' '.trim($str).' ');

		// To prevent memory overflow, we need to limit the hay to relatively low count
		$haystack = wd_substr_at_word(MB::strtolower($haystack), $str_length_limit, '');
		$needle = remove_accents(MB::strtolower($needle));

		if ( $needle == "" ) {
			if ( MB::strlen($str) > $maxlength) {
				return wd_substr_at_word($str, $maxlength);
			} else {
				return $str;
			}
		}

		/**
		 * This is an interesting issue. Turns out mb_substr($hay, $start, 1) is very ineffective.
		 * the preg_split(...) method is far more efficient in terms of speed, however it needs much more
		 * memory. In our case speed is the top priority. However, to prevent memory overflow, the haystack
		 * is reduced to 10000 characters (roughly 1500 words) first.
		 *
		 * Reference ticket: https://wp-dreams.com/forums/topic/search-speed/
		 * Speed tests: http://stackoverflow.com/questions/3666306/how-to-iterate-utf-8-string-in-php
		 */
		$chrArray = preg_split('//u', $haystack, -1, PREG_SPLIT_NO_EMPTY);
		$hay_length = count($chrArray) - 1;

		if ( $i = MB::strpos($haystack, $needle) ) {
			$start=$i;
			$end=$i;
			$spaces=0;

			while ($spaces < ((int) $context/2) && $start > 0) {
				$start--;
				if ($chrArray[$start] == ' ') {
					$spaces++;
				}
			}

			while ($spaces < ($context +1) && $end < $hay_length) {
				$end++;
				if ($chrArray[$end] == ' ') {
					$spaces++;
				}
			}

			while ($spaces < ($context +1) && $start > 0) {
				$start--;
				if ($chrArray[$start] == ' ') {
					$spaces++;
				}
			}

			$str_start = ($start - 1) < 0 ? 0 : ($start -1);
			$str_end = ($end - 1) < 0 ? 0 : ($end -1);

			$result = trim( MB::substr($str, $str_start, ($str_end - $str_start)) );

			// Somewhere inbetween..
			if ( $start != 0 && $end < $hay_length )
				return "... " . $result . " ...";

			// Beginning
			if ( $start == 0 && $end < $hay_length )
				return $result . " ...";

			// End
			if ( $start != 0 && $end == $hay_length )
				return "... " . $result;

			// If it is too long, strip it
			if ( MB::strlen($result) > $maxlength)
				return wd_substr_at_word( $result, $maxlength );

			// Else, it is the whole
			return $result;

		} else {
			if ( $false_on_no_match )
				return false;

			// If it is too long, strip it
			if ( MB::strlen($str) > $maxlength)
				return wd_substr_at_word( $str, $maxlength );

			return $str;
		}
	}
}