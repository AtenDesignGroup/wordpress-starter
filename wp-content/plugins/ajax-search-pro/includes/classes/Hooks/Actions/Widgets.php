<?php
namespace WPDRMS\ASP\Hooks\Actions;

if (!defined('ABSPATH')) die('-1');

class Widgets extends AbstractAction {
	public function handle() {
		register_widget("\\WPDRMS\\ASP\\Widgets\\Search");
		register_widget("\\WPDRMS\\ASP\\Widgets\\LastSearches");
		register_widget("\\WPDRMS\\ASP\\Widgets\\TopSearches");
	}
}