<?php
namespace WPDRMS\ASP\Hooks\Actions;

use WPDRMS\ASP\Updates\Manager;

if (!defined('ABSPATH')) die('-1');

class Updates extends AbstractAction {
	function handle() {
		new Manager(ASP_PLUGIN_NAME, ASP_PLUGIN_SLUG, wd_asp()->updates);
	}
}