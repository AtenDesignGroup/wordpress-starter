<?php
namespace WPDRMS\ASP\Hooks\Actions;

if (!defined('ABSPATH')) die('-1');

class Footer extends AbstractAction {
	public function handle() {
		$exit1 = apply_filters('asp_load_css_js', false);
		$exit2 = apply_filters('asp_load_css', false);
		if ($exit1 || $exit2)
			return;

		// Blur for isotopic
		?>
		<div class='asp_hidden_data' id="asp_hidden_data" style="display: none !important;">
			<svg style="position:absolute" height="0" width="0">
				<filter id="aspblur">
					<feGaussianBlur in="SourceGraphic" stdDeviation="4"/>
				</filter>
			</svg>
			<svg style="position:absolute" height="0" width="0">
				<filter id="no_aspblur"></filter>
			</svg>
		</div>
		<?php
	}
}