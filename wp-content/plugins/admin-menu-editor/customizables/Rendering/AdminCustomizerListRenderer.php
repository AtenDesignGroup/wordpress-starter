<?php

namespace YahnisElsts\AdminMenuEditor\Customizable\Rendering;

use YahnisElsts\AdminMenuEditor\Customizable\HtmlHelper;
use YahnisElsts\AdminMenuEditor\Customizable\Controls\Section;
use YahnisElsts\AdminMenuEditor\Customizable\Controls\Tooltip;

class AdminCustomizerListRenderer extends Renderer {
	protected $pendingSections = [];

	public function renderStructure($structure) {
		$rootSection = new Section(
			$structure->getTitle() ?: 'Root',
			$structure->getAsSections(),
			['id' => 'structure-root']
		);
		$this->renderSection($rootSection);
	}

	protected function renderPendingSections() {
		while (!empty($this->pendingSections)) {
			$section = array_shift($this->pendingSections);
			$this->renderSection($section);
		}
	}

	public function renderSection($section) {
		echo HtmlHelper::tag('ul', [
			'class' => 'ame-ac-section',
			'id'    => $this->getSectionElementId($section),
		]);

		?>
		<li class="ame-ac-section-meta">
			<div class="ame-ac-section-header">
				<button class="ame-ac-section-back-button">
					<span class="screen-reader-text">Back</span>
				</button>
				<h3 class="ame-ac-section-title"><?php
					echo esc_html($section->getTitle());
					?></h3>
			</div>
		</li>
		<?php

		$this->renderSectionChildren($section);
		echo '</ul>';

		$this->renderPendingSections();
	}

	protected function renderChildSection($section) {
		if ( $section->hasChildren() ) {
			$this->pendingSections[] = $section;
		}

		echo HtmlHelper::tag('li', [
			'class'          => 'ame-ac-section-link',
			'data-target-id' => $this->getSectionElementId($section),
		]);
		printf(
			'<h3 class="ame-ac-section-title">%s</h3>',
			esc_html($section->getTitle())
		);
		echo '</li>';
	}

	protected function renderControlGroup($group) {
		$title = $group->getTitle();
		if ( !empty($title) ) {
			echo '<li class="ame-ac-control ame-ac-control-group">';
			echo '<h4 class="ame-ac-group-title">';
			echo esc_html($title);
			echo '</h4>';
			echo '</li>';
		}

		$this->renderGroupChildren($group);
	}

	protected function renderUngroupedControl($control) {
		$this->renderControl($control);
	}

	public function renderControl($control, $parentContext = null) {
		$settings = $control->getSettings();
		$settingIds = array_map(function ($setting) {
			return $setting->getId();
		}, $settings);

		//The collection of settings IDs should be an associative array. If it's not,
		//assign the first setting to the "default" key.
		$isAssociative = false;
		foreach ($settingIds as $key => $value) {
			if ( is_string($key) ) {
				$isAssociative = true;
				break;
			}
		}
		if ( !$isAssociative ) {
			$firstId = array_shift($settingIds);
			$settingIds['default'] = $firstId;
		}

		echo HtmlHelper::tag('li', [
			'class'                => 'ame-ac-control',
			'data-ac-setting-ids'  => !empty($settingIds) ? wp_json_encode($settingIds) : null,
			'data-ac-control-type' => $control->getType(),
		]);
		if ( !$control->includesOwnLabel() ) {
			$this->renderControlLabel($control);
		}

		parent::renderControl($control, $parentContext);
		echo '</li>';
	}

	public function renderTooltipTrigger(Tooltip $tooltip) {
		// TODO: Implement renderTooltipTrigger() method.
		echo '[tooltip here]';
	}

	protected function getSectionElementId($section) {
		return 'ame-ac-section-' . $section->getId();
	}

	/**
	 * @param \YahnisElsts\AdminMenuEditor\Customizable\Controls\Control $control
	 * @return void
	 */
	protected function renderControlLabel($control) {
		$label = $control->getLabel();
		if ( empty($label) ) {
			return;
		}

		$labelForId = $control->getLabelTargetId();
		if ( $control->supportsLabelAssociation() && !empty($labelForId) ) {
			printf(
				'<label for="%s" class="ame-ac-control-label">%s</label>',
				esc_attr($labelForId),
				esc_html($label)
			);
		} else {
			printf(
				'<span class="ame-ac-control-label">%s</span>',
				esc_html($label)
			);
		}
	}
}