<?php
namespace WPDRMS\ASP\Suggest;

defined('ABSPATH') or die("You can't access this file directly.");


abstract class AbstractSuggest {
	/**
	 * This should always return an array of keywords or an empty array
	 *
	 * @param string $q search keyword
	 * @return array keywords
	 */
	abstract function getKeywords(string $q): array;
}