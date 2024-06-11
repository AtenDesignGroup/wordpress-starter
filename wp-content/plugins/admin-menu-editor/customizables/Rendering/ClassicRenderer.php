<?php

namespace YahnisElsts\AdminMenuEditor\Customizable\Rendering;

use YahnisElsts\AdminMenuEditor\Customizable\Controls\Section;
use YahnisElsts\AdminMenuEditor\Customizable\Controls\Tooltip;

abstract class ClassicRenderer extends Renderer {
	protected $needsTooltipDependencies = false;

	public function renderTooltipTrigger(Tooltip $tooltip) {
		$this->needsTooltipDependencies = true;

		$linkClasses = array('ws_tooltip_trigger');
		$dashiconClasses = array('dashicons');

		switch ($tooltip->getType()) {
			case Tooltip::EXPERIMENTAL:
				$linkClasses[] = 'ame-warning-tooltip';
				$dashiconClasses[] = 'dashicons-admin-tools';
				break;
			case Tooltip::INFO:
			default:
				$dashiconClasses[] = 'dashicons-info';
		}

		$linkClasses = array_merge($linkClasses, $tooltip->getExtraClasses());

		printf(
			'<a class="%s" title="%s"><span class="%s"></span></a>',
			esc_attr(implode(' ', $linkClasses)),
			esc_attr($tooltip->getHtmlContent()),
			esc_attr(implode(' ', $dashiconClasses))
		);
	}

	public function enqueueDependencies($containerSelector = '') {
		static $done = false;
		if ( $done ) {
			return;
		}
		$done = true;

		parent::enqueueDependencies($containerSelector);

		if ( $this->needsTooltipDependencies ) {
			if ( !(wp_script_is('jquery-qtip', 'done') || wp_script_is('jquery-qtip')) ) {
				wp_enqueue_script('jquery-qtip');
			}

			$tooltipInitScript = /** @lang JavaScript */
				'(function(containerSelector) {
					jQuery(function ($) {
						$(containerSelector + \' .ws_tooltip_trigger\').qtip({
							style: {
								classes: \'qtip qtip-rounded ws_tooltip_node ws_wide_tooltip\'
							}
						});
					});
				})';

			$tooltipInitScript .= "('" . esc_js($containerSelector) . "');";
			$initScriptTag = '<script type="text/javascript">' . $tooltipInitScript . '</script>';

			//Adding inline scripts only works if the script is in queue
			//and not already done.
			if ( wp_script_is('jquery-qtip') && !wp_script_is('jquery-qtip', 'done') ) {
				wp_add_inline_script('jquery-qtip', $tooltipInitScript);
			} else {
				$fallbackAction = 'admin_print_footer_scripts';
				if ( !did_action($fallbackAction) ) {
					add_action($fallbackAction, function () use ($initScriptTag) {
						// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Outputs generated JS.
						echo $initScriptTag;
					});
				} else {
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo $initScriptTag;
				}
			}
		}
	}

	/**
	 * @param \YahnisElsts\AdminMenuEditor\Customizable\Controls\ControlGroup $group
	 * @return void
	 */
	protected function renderGroupTitleContent($group) {
		$tooltip = $group->getTooltip();
		$title = $group->getTitle();
		if ( !empty($title) ) {
			$labelTargetId = $group->getLabelFor();
			if ( !empty($labelTargetId) ) {
				printf(
					'<label for="%s">%s</label>',
					esc_attr($labelTargetId),
					esc_html($title)
				);
			} else {
				echo esc_html($title);
			}
		}

		if ( $tooltip ) {
			echo ' '; //Add a space between the title and the tooltip.
			$this->renderTooltipTrigger($tooltip);
		}
	}

	protected function renderSectionTitleContent(Section $section) {
		if ( !$section->hasTitle() ) {
			return;
		}

		$title = $section->getTitle();
		echo esc_html($title);

		$tooltip = $section->getTooltip();
		if ( $tooltip ) {
			echo ' ';
			$this->renderTooltipTrigger($tooltip);
		}
	}
}