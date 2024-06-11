<?php

namespace YahnisElsts\AdminMenuEditor\Customizable;

abstract class HtmlHelper {
	/**
	 * Generate a HTML tag.
	 *
	 * @param string $tagName
	 * @param array<string,mixed> $attributes
	 * @param string|null $content
	 * @return string
	 */
	public static function tag($tagName, $attributes = array(), $content = null) {
		$html = '<' . $tagName;
		$charset = self::getCharset();

		//Convert array of CSS classes to a space-separated string.
		if ( isset($attributes['class']) && is_array($attributes['class']) ) {
			$attributes['class'] = implode(' ', $attributes['class']);
		}

		//Convert [property => value] to an inline style.
		if ( isset($attributes['style']) && is_array($attributes['style']) ) {
			if ( !empty($attributes['style']) ) {
				$styleAttr = '';
				foreach ($attributes['style'] as $name => $value) {
					$styleAttr .= $name . ':' . $value . ';';
				}
				$attributes['style'] = $styleAttr;
			} else {
				unset($attributes['style']);
			}
		}

		$filteredAttributes = array_filter($attributes, array(self::class, 'isNonEmptyAttributeValue'));

		//Special case: The empty string is a valid value for the "value" attribute.
		//For example, we want this for <option> tags where $('select').val() would otherwise return the option's text.
		if ( isset($attributes['value']) && ($attributes['value'] === '') ) {
			$filteredAttributes['value'] = $attributes['value'];
		}
		$attributes = $filteredAttributes;

		if ( !empty($attributes) ) {
			foreach ($attributes as $name => $value) {
				if ( is_scalar($value) ) {
					$stringValue = (string)$value;
				} else {
					$stringValue = wp_json_encode($value);
				}

				//esc_attr() doesn't double-encode entities and _wp_specialchars()
				//is marked as private, so let's use htmlspecialchars() instead.
				$html .= ' ' . $name . '="' . htmlspecialchars($stringValue, ENT_QUOTES, $charset) . '"';
			}
		}

		$html .= '>';
		if ( $content !== null ) {
			$html .= $content . '</' . $tagName . '>';
		}

		return $html;
	}

	private static function isNonEmptyAttributeValue($value) {
		return ($value !== null) && ($value !== false) && ($value !== '');
	}

	private static function getCharset() {
		static $charset = null;
		if ( $charset === null ) {
			$charset = get_option('blog_charset', '');
			if ( in_array($charset, array('utf8', 'utf-8', 'UTF8')) ) {
				$charset = 'UTF-8';
			}
		}
		return $charset;
	}
}