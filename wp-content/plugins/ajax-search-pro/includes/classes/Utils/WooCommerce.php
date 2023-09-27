<?php
namespace WPDRMS\ASP\Utils;

defined('ABSPATH') or die("You can't access this file directly.");

class WooCommerce {
	/**
	 * Gets the WooCommerce formatted currency, supporting multiple currencies WPML, WCML
	 *
	 * @param $id - Product or variation ID
	 * @param $field - Field label
	 * @param $args - Search arguments
	 */
	public static function formattedPriceWithCurrency($id, $field, $args) {
		global $woocommerce_wpml;
		global $sitepress;

		$currency = $args['woo_currency'] ?? (function_exists('get_woocommerce_currency') ?
				get_woocommerce_currency() : '');

		$p = wc_get_product( $id );

		// WCML Section, copied and modified from
		// ..\wp-content\plugins\wpml-woocommerce\inc\currencies\class-wcml-multi-currency-prices.php
		// line 139, function product_price_filter(..)
		if ( isset($sitepress, $woocommerce_wpml, $woocommerce_wpml->multi_currency) ) {
			$original_object_id = apply_filters( 'translate_object_id', $id, get_post_type($id), false, $sitepress->get_default_language() );
			$ccr = get_post_meta($original_object_id, '_custom_conversion_rate', true);
			if( in_array($field, array('_price', '_regular_price', '_sale_price', '_price_html')) && !empty($ccr) && isset($ccr[$field][$currency]) ){
				if ( $field == '_price_html' ) {
					$field = '_price';
				}
				$price_original = get_post_meta($original_object_id, $field, true);
				$price = $price_original * $ccr[$field][$currency];
			} else {
				$manual_prices = $woocommerce_wpml->multi_currency->custom_prices->get_product_custom_prices($id, $currency);
				if ( $field == '_price_html' ) {
					$field = '_price';
				}
				if( $manual_prices && !empty($manual_prices[$field]) ){
					$price = $manual_prices[$field];
				} else {
					// 2. automatic conversion
					$price = get_post_meta($id, $field, true);
					$price = apply_filters('wcml_raw_price_amount', $price, $currency );
				}
			}

			if ( $price != '') {
				$price = wc_price($price, array('currency' => $currency));
			}
		} else {
			// For variable products _regular_price, _sale_price are not defined
			// ..however are most likely used together. So in case of _regular_price display the range,
			// ..but do not deal with _sale_price at all
			if ( $p->is_type('variable') && $field != '_sale_price' ) {
				$price = $p->get_price_html();
			} else {
				switch ($field) {
					case '_regular_price':
						$price = $p->get_regular_price();
						break;
					case '_sale_price':
						$price = $p->get_sale_price();
						break;
					case '_tax_price':
						$price = wc_get_price_including_tax($p);
						break;
					case '_price_html':
						$price = $p->get_price_html();
						break;
					default:
						$price = $p->get_price();
						break;
				}
				if ( $field != '_price_html' && $price != '' ) {
					if ($currency != '')
						$price = wc_price($price, array('currency' => $currency));
					else
						$price = wc_price($price);
				}
			}
		}

		return $price;
	}

	public static function isShop(): bool {
		// is_archive() required, otherwise warnings are thrown
		return function_exists('is_shop') && is_archive() && is_shop();
	}
}