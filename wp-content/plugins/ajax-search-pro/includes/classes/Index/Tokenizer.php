<?php
namespace WPDRMS\ASP\Index;

use WPDRMS\ASP\Utils\Html;
use WPDRMS\ASP\Utils\MB;
use WPDRMS\ASP\Utils\Str;

defined('ABSPATH') or die("You can't access this file directly.");


class Tokenizer {
	/**
	 * @var string unique random string for special replacements
	 */
	private
		$randstr = "wpivdny3htnydqd6mlyg",
		$args;

	public static $additional_keywords_pattern = array(
		'"', "'", "`", '’', '‘', '”', '“', '«', '»', "+",
		'.', ',', ':', '-', '_', "=", "%", '(', ')', '{',
		'}', '*', '[', ']', '|', "&", "/"
	);

	function __construct( $args ) {
		$defaults = array(
			'min_word_length' => 2,
			'use_stopwords' => false,
			'stopwords' => array(),
			'synonyms_as_keywords' => false
		);
		$this->args = wp_parse_args( $args, $defaults );
	}

	/**
	 * Performs a simple trimming, character removal on a string
	 *
	 * @param $str
	 * @param $post
	 * @return string
	 */
	function tokenizeSimple( $str, $post ): string {
		if ( function_exists( 'mb_internal_encoding' ) ) {
			mb_internal_encoding( "UTF-8" );
		}

		$str = Str::anyToString($str);
		$str = Html::toTxt( $str );
		$str = strip_tags( $str );
		$str = stripslashes( $str );
		// Non breakable spaces to regular spaces
		$str = preg_replace('/\xc2\xa0/', ' ', $str);
		$str = preg_replace( '/[[:space:]]+/', ' ', $str );

		$str = str_replace( array( "\n", "\r", "  " ), " ", $str );
		// Turkish uppercase I does not lowercase correctly
		$str = str_replace( 'İ', 'i', $str );
		$str = MB::strtolower( $str );
		$str = trim($str);

		$str = Content::hebrewUnvocalize($str);
		$str = Content::arabicRemoveDiacritics($str);

		$stop_words = $this->getStopWords($post);
		foreach ( $stop_words as $stop_word ) {
			// If there is a stopword within, this case is over
			if ( strpos($str, $stop_word) !== false ) {
				return '';
			}
		}

		return $str;
	}

	/**
	 * Performs a simple trimming, character removal on a string, but returns array of keywords
	 * by the separator character
	 *
	 * @param $str
	 * @param $post
	 * @return array
	 */
	function tokenizePhrases( $str, $post, $word_separator = ',' ): array {
		if ( function_exists( 'mb_internal_encoding' ) ) {
			mb_internal_encoding( "UTF-8" );
		}

		$args = $this->args;

		$str = Str::anyToString($str);
		$str = Html::toTxt( $str );
		$str = strip_tags( $str );
		$str = stripslashes( $str );
		// Non breakable spaces to regular spaces
		$str = preg_replace('/\xc2\xa0/', ' ', $str);
		$str = preg_replace( '/[[:space:]]+/', ' ', $str );

		$str = str_replace( array( "\n", "\r", "  " ), " ", $str );
		// Turkish uppercase I does not lowercase correctly
		$str = str_replace( 'İ', 'i', $str );
		$str = MB::strtolower( $str );
		$str = trim($str);

		$str = Content::hebrewUnvocalize($str);
		$str = Content::arabicRemoveDiacritics($str);

		$words = explode($word_separator, $str);
		$words = array_map('trim', $words);
		$words = array_filter($words, function($word){
			return \WPDRMS\ASP\Utils\MB::strlen($word);
		});

		$keywords = array();

		while (($c_word = array_shift($words)) !== null) {
			$c_word = trim($c_word);

			if ( $c_word == '' || MB::strlen($c_word) < $args['min_word_length'] ) {
				continue;
			}

			// Numerics won't work otherwise, need to trim that later
			if ( is_numeric($c_word) ) {
				$c_word = " " . $c_word;
			}

			if ( array_key_exists($c_word, $keywords) ) {
				$keywords[$c_word][1]++;
			} else {
				$keywords[$c_word] = array($c_word, 1);
			}
		}
		unset($c_word);

		return $keywords;
	}

	/**
	 * Performs a keyword extraction on the given content string.
	 *
	 *
	 * @return array of keywords $keyword = array( 'keyword', {count} )
	 */
	function tokenize( $str, $post = false, $lang = '' ): array {

		if ( is_array( $str ) ) {
			$str = Str::anyToString( $str );
		}
		if ( function_exists("mb_strlen") )
			$fn_strlen = "mb_strlen";
		else
			$fn_strlen = "strlen";

		$args = $this->args;

		if ( function_exists( 'mb_internal_encoding' ) ) {
			mb_internal_encoding( "UTF-8" );
		}

		$str = apply_filters( 'asp_indexing_string_pre_process', $str );

		$str = Html::toTxt( $str );
		$str = wp_specialchars_decode( $str );


		$str = strip_tags( $str );
		$str = stripslashes( $str );

		// Replace non-word boundary dots with a unique string + 'd'
		/** @noinspection RegExpRedundantEscape */
		$str = preg_replace("/([0-9])[\.]([0-9])/", "$1".$this->randstr."d$2", $str);

		// Remove potentially dangerous or unusable characters
		$str = str_replace( array(
			"Â·", "â€¦", "â‚¬", "&shy;", '·', '…', '®', '©', '™', "\xC2\xAD"
		), "", $str );
		$str = str_replace( array(
			". ", // dot followed by space as boundary, otherwise it might be a part of the word
			", ", // comma followed by space only, otherwise it might be a word part
			"<", ">", "†", "‡", "‰", "‹", "™", "¡", "¢", "¤", "¥", "¦", "§", "¨", "©", "ª", "«", "¬",
			"®", "¯", "°", "±", "¹", "²", "³", "¶", "·", "º", "»", "¼", "½", "¾", "¿", "÷", "•", "…", "←",
			"←", "↑", "→", "↓", "↔", "↵", "⇐", "⇑", "⇒", "⇓", "⇔", "√", "∝", "∞", "∠", "∧", "∨", "∂", "∃", "∅",
			"∗", "∩", "∪", "∫", "∴", "∼", "≅", "≈", "≠", "≡", "≤", "≥", "⊂", "⊃", "⊄", "⊆", "⊇", "⊕", "⊗", "⊥",
			"◊", "♠", "♣", "♥", "♦", "🔴", "​", "◊", "〈", "〉", "⌊", "⌋", "⌈", "⌉", "⋅",
			"Ă‹â€ˇ", "Ă‚Â°", "~", "Ă‹â€ş", "Ă‹ĹĄ", "Ă‚Â¸", "Ă‚Â§", "Ă‚Â¨", "â€™", "â€", "â€ť",
			"â€ś", "â€ž", "Â´", "â€”", "â€“", "Ă—", '&#8217;', "&#128308;", "&nbsp;", "\n", "\r",
			"& ", "\\", "^", "?", "!", ";",
			chr( 194 ) . chr( 160 )
		), " ", $str );
		$str = str_replace( 'Ăź', 'ss', $str );

		// Turkish uppercase I does not lowercase correctly
		$special_replace = array(
			'İ' => 'i',
			'—' => '-'
		);
		$str = str_replace( array_keys($special_replace), array_values($special_replace), $str );

		// Any yet undefined punctuation
		//$str = preg_replace( '/[[:punct:]]+/u', ' ', $str );
		// Non breakable spaces to regular spaces
		$str = preg_replace('/\xc2\xa0/', ' ', $str);
		// Any remaining multiple space characters
		$str = preg_replace( '/[[:space:]]+/', ' ', $str );

		$str = MB::strtolower($str);

		$str = Content::hebrewUnvocalize($str);
		$str = Content::arabicRemoveDiacritics($str);

		//$str = preg_replace('/[^\p{L}0-9 ]/', ' ', $str);
		$str = str_replace( "\xEF\xBB\xBF", '', $str );

		$str = trim( preg_replace( '/\s+/', ' ', $str ) );

		// Set back the non-word boundary dots
		$str = str_replace( $this->randstr."d", '.', $str );

		$str = apply_filters( 'asp_indexing_string_post_process', $str );

		$words = explode( ' ', $str );

		// Remove punctuation marks + some extra from the start and the end of words

		// Characters, which should not be standalone (but can be in start on end)
		$non_standalone_strings = array("$", "€", "£", "%");
		// Additional keywords, should not be standalone
		$additional_keywords_string = implode('', array_diff(self::$additional_keywords_pattern, $non_standalone_strings));
		foreach ( $words as $wk => &$ww ) {
			$ww = MB::trim($ww, $additional_keywords_string);
			if ( $ww == '' || in_array($ww, $non_standalone_strings ) ) {
				unset($words[$wk]);
			}
		}
		unset($wk);
		unset($ww);

		// Get additional words if available
		$additional_words = array();
		foreach ($words as $ww) {

			// ex.: 123-45-678 to 123, 45, 678
			$ww1 = str_replace(self::$additional_keywords_pattern, ' ', $ww);
			$wa = explode(" ", $ww1);
			if (count($wa) > 1) {
				foreach ( $wa as $wak => $wav ) {
					$wav = trim(preg_replace( '/[[:space:]]+/', ' ', $wav ));
					if ( $wav != '' && !in_array($wav, $words) ) {
						$wa[$wak] = $wav;
					} else {
						unset($wa[$wak]);
					}
				}
				$additional_words = array_merge($additional_words, $wa);
			}
			// ex.: 123-45-678 to 12345678
			$ww2 = str_replace(self::$additional_keywords_pattern, '', $ww);
			if ( $ww2 != '' && $ww2 != $ww && !in_array($ww2, $words) && !in_array($ww2, $additional_words) ) {
				$additional_words[] = $ww2;
			}
		}

		// Append them after the words array
		$words = array_merge($words, $additional_words);

		/**
		 * Apply synonyms for the whole string instead of the words, because
		 * synonyms can be multi-keyword phrases too
		 */
		$syn_inst = \WPDRMS\ASP\Synonyms\Manager::getInstance();
		if ( $syn_inst->exists() ) {
			if ( $this->args['synonyms_as_keywords'] == 1 ) {
				$syn_inst->synonymsAsKeywords();
			}
			$additional_words_by_synonyms = array();
			$synonyms = $syn_inst->get();

			// If the langauge is set
			if ( $lang != '' && isset($synonyms[$lang]) ) {
				foreach ( $synonyms[$lang] as $keyword => $synonyms_arr ) {
					if ( preg_match('/\b'.preg_quote($keyword).'\b/u', $str) ) {
						$additional_words_by_synonyms = array_merge($additional_words_by_synonyms, $synonyms_arr);
					}
				}
			}
			unset($keyword, $synonyms_arr);

			// Also for the "default" aka "any"
			if ( isset($synonyms['default']) ) {
				foreach ( $synonyms['default'] as $keyword => $synonyms_arr ) {
					if ( preg_match('/\b'.preg_quote($keyword).'\b/u', $str) ) {
						$additional_words_by_synonyms = array_merge($additional_words_by_synonyms, $synonyms_arr);
					}
				}
			}

			if ( count($additional_words_by_synonyms) > 0 ) {
				$words = array_merge($words, $additional_words_by_synonyms);
			}
		}

		$stopWords = $this->getStopWords($post);
		$keywords = array();

		while (($c_word = array_shift($words)) !== null) {
			$c_word = trim($c_word);

			if ( $c_word == '' || $fn_strlen($c_word) < $args['min_word_length'] ) {
				continue;
			}
			if ( !empty($stopWords) && in_array($c_word, $stopWords) ) {
				continue;
			}
			// Numerics won't work otherwise, need to trim that later
			if ( is_numeric($c_word) ) {
				$c_word = " " . $c_word;
			}

			if ( array_key_exists($c_word, $keywords) ) {
				$keywords[$c_word][1]++;
			} else {
				$keywords[$c_word] = array($c_word, 1);
			}
		}
		unset($c_word);

		return apply_filters( 'asp_indexing_keywords', $keywords );
	}


	/**
	 * Returns the stop words, including the negative keywords for the current post object
	 */
	private function getStopWords( $post ): array {
		$stopWords = array();
		// Only compare to common words if $restrict is set to false
		if ( $this->args['use_stopwords'] == 1 && $this->args['stopwords'] != "" ) {
			$this->args['stopwords'] = str_replace(" ", "", $this->args['stopwords']);
			$stopWords = explode( ',', $this->args['stopwords'] );
		}
		// Post level stop-words, negative keywords
		if ( $post !== false ) {
			$negative_keywords = get_post_meta($post->ID, '_asp_negative_keywords', true);
			if ( !empty($negative_keywords) ) {
				$negative_keywords = trim( preg_replace('/\s+/', ' ',$negative_keywords) );
				$negative_keywords = explode(' ', $negative_keywords);
				$stopWords = array_merge($stopWords, $negative_keywords);
			}
		}
		$stopWords = array_unique( $stopWords );
		foreach ( $stopWords as $sk => &$sv ) {
			$sv = trim($sv);
			if ( $sv == '' ) {
				unset($stopWords[$sk]);
			}
		}

		return $stopWords;
	}
}