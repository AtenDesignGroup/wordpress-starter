<?php

namespace YahnisElsts\AdminMenuEditor\Customizable\Controls;

use YahnisElsts\AdminMenuEditor\Customizable\HtmlHelper;
use YahnisElsts\AdminMenuEditor\Customizable\Rendering\Renderer;

//TODO: Could this conceivably be a subclass of ControlGroup? It can generate the controls dynamically.
class RadioGroup extends ChoiceControl implements ControlContainer {
	const WRAP_LINE_BREAK = 'LineBreak';
	const WRAP_PARAGRAPH = 'Paragraph';
	const WRAP_NONE = 'None';
	const INPUT_ID_PREFIX = 'ame-rg-input_';

	protected $type = 'radio';
	protected $koComponentName = 'ame-radio-group';
	protected $declinesExternalLineBreaks = true;

	protected $beforeOption = '';
	protected $afterOption = '';
	protected $wrapStyle;

	protected $descriptionsAsTooltips = false;

	/**
	 * @var Control[]
	 */
	protected $choiceChildren = [];

	public function __construct($settings = [], $params = []) {
		parent::__construct($settings, $params);

		if ( isset($params['choiceChildren']) ) {
			$this->choiceChildren = $params['choiceChildren'];
		}

		$this->wrapStyle = isset($params['wrap']) ? $params['wrap'] : self::WRAP_PARAGRAPH;
		switch ($this->wrapStyle) {
			case self::WRAP_LINE_BREAK:
				//A few WordPress settings pages use this.
				$this->beforeOption = '';
				$this->afterOption = '<br>';
				break;
			case self::WRAP_PARAGRAPH:
				//"Settings -> Reading" uses this, and AME used it in the "Settings" tab.
				$this->beforeOption = '<p>';
				$this->afterOption = '</p>';
				break;
			default:
				throw new \InvalidArgumentException("Invalid option wrap style: " . $this->wrapStyle);
		}

		if ( isset($params['descriptionsAsTooltips']) ) {
			$this->descriptionsAsTooltips = (bool)$params['descriptionsAsTooltips'];
		}
	}

	public function renderContent(Renderer $renderer) {
		$fieldName = $this->getFieldName();
		$currentValue = $this->mainSetting->getValue();

		$classes = $this->classes;
		$hasNestedControls = !empty($this->choiceChildren);
		if ( $hasNestedControls ) {
			$classes[] = 'ame-rg-has-nested-controls';
		}

		$beforeOption = $this->beforeOption;
		$afterOption = $this->afterOption;
		if ( $hasNestedControls ) {
			//Layout will be handled by CSS grid, so we don't need line breaks,
			//and wrapping the options in <p> tags would mess up the grid.
			$beforeOption = $afterOption = '';
		}

		//buildTag() is safe, and we intentionally allow HTML in the label and description.
		//phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->buildTag(
			'fieldset',
			[
				'class'     => $classes,
				'style'     => $this->styles,
				'disabled'  => !$this->isEnabled(),
				'data-bind' => $this->makeKoDataBind($this->getKoEnableBinding()),
			]
		);
		foreach ($this->options as $option) {
			$isChecked = ($currentValue === $option->value);

			echo $beforeOption;
			$labelClasses = ['ame-rg-option-label'];
			if ( isset($this->choiceChildren[$option->value]) ) {
				$labelClasses[] = 'ame-rg-has-choice-child';
			}
			echo $this->buildTag('label', ['class' => $labelClasses]);

			echo $this->buildTag(
				'input',
				array_merge([
					'type'      => 'radio',
					'name'      => $fieldName,
					'value'     => $this->mainSetting->encodeForForm($option->value),
					'class'     => $this->inputClasses,
					'checked'   => $isChecked,
					'disabled'  => !$option->enabled,
					'id'        => $this->getRadioInputId($option),
					'data-bind' => $this->makeKoDataBind([
						'checked'      => $this->getKoObservableExpression($option->value),
						'checkedValue' => wp_json_encode($option->value),
					]),
				], $this->inputAttributes)
			);
			echo ' ', $option->label;

			if ( !empty($option->description) ) {
				if ( $this->descriptionsAsTooltips ) {
					echo ' ';
					$renderer->renderTooltipTrigger(new Tooltip(
						$option->description,
						Tooltip::INFO,
						['ame-understated-tooltip']
					));
				} else {
					echo self::formatNestedDescription($option->description);
				}
			}
			echo '</label>';
			echo $afterOption;

			if ( isset($this->choiceChildren[$option->value]) ) {
				$childControl = $this->choiceChildren[$option->value];
				echo HtmlHelper::tag('span', ['class' => 'ame-rg-nested-control']);
				$renderer->renderControl($childControl);
				echo '</span>';
			}
		}
		echo '</fieldset>';
		//phpcs:enable
	}

	/**
	 * @param ChoiceControlOption $option
	 * @return string
	 */
	protected function getRadioInputId($option) {
		return $this->getRadioInputPrefix() . sanitize_key(strval($option->value));
	}

	protected function getRadioInputPrefix() {
		return self::INPUT_ID_PREFIX . $this->instanceNumber . '-';
	}

	public function serializeForJs() {
		$result = parent::serializeForJs();
		if ( !isset($result['children']) ) {
			$result['children'] = [];
			foreach ($this->choiceChildren as $child) {
				$result['children'][] = $child->serializeForJs();
			}
		}
		return $result;
	}

	protected function getKoComponentParams() {
		$params = parent::getKoComponentParams();

		$hasNestedControls = !empty($this->choiceChildren);
		$params['wrapStyle'] = $hasNestedControls ? self::WRAP_NONE : $this->wrapStyle;
		$params['radioInputPrefix'] = $this->getRadioInputPrefix();

		if ( $hasNestedControls ) {
			//Values can be things that aren't valid JS identifiers, so we'll serialize
			//the value-to-child relationship as an array of value + child index pairs.
			$valueChildIndexes = [];
			$i = 0;
			foreach ($this->choiceChildren as $value => $child) {
				$valueChildIndexes[] = [$value, $i];
				$i++;
			}
			$params['valueChildIndexes'] = $valueChildIndexes;
		}

		return $params;
	}

	public function getChildren() {
		return $this->choiceChildren;
	}

	public function getAllDescendants() {
		foreach ($this->choiceChildren as $child) {
			yield $child;
			if ( $child instanceof ControlContainer ) {
				yield from $child->getAllDescendants();
			}
		}
	}
}