<?php /** @noinspection PhpUnused */

namespace WPDRMS\ASP\Hooks\Actions;

use WPDRMS\ASP\Hooks\Ajax\DeleteCache;
use WPDRMS\ASP\Index\Database;
use WPDRMS\ASP\Utils\Polylang\StringTranslations as PolylangStringTranslations;

if (!defined('ABSPATH')) die('-1');

class Other extends AbstractAction {
	public function handle() {}

	public function on_save_post() {
		// Clear all the cache just in case
		DeleteCache::getInstance()->handle(false);
	}

	/**
	 * Fix for 'WP External Links' plugin
	 * https://wordpress.org/plugins/wp-external-links/
	 *
	 * @param $link
	 */
	public function plug_WPExternalLinks_fix( $link ) {
		// ignore links with class "asp_showmore"
		if ( $link->has_attr_value( 'class', 'asp_showmore' ) ) {
			$link->set_ignore();
		}
	}

	public function pll_init_string_translations() {
		PolylangStringTranslations::init();
	}

	public function pll_save_string_translations() {
		// Save any possible PLL translation strings stack
		PolylangStringTranslations::save();
	}

	public function pll_register_string_translations() {
		PolylangStringTranslations::register();
	}

	/**
	 * Triggers when asp_scheduled_activation_events is triggered (during activation only)
	 */
	public function scheduledActivationEvents() {
		$index = new Database();
		$index->scheduled();
	}
}