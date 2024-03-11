<?php

namespace YahnisElsts\AdminMenuEditor\Customizable\Controls;

use YahnisElsts\AdminMenuEditor\Customizable\Rendering\Renderer;

class RadioButtonBar extends ChoiceControl {
	protected $type = 'radio-bar';
	protected $koComponentName = 'ame-radio-button-bar';

	protected $declinesExternalLineBreaks = true;

	protected $controlClass = 'ame-radio-button-bar-control';

	public function renderContent(Renderer $renderer) {
		$fieldName = $this->getFieldName();
		$currentValue = $this->mainSetting->getValue();

		//phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->buildTag(
			'fieldset',
			[
				'class'     => array_merge([$this->controlClass], $this->classes),
				'style'     => $this->styles,
				'disabled'  => !$this->isEnabled(),
				'data-bind' => $this->makeKoDataBind($this->getKoEnableBinding()),
			]
		);
		foreach ($this->options as $option) {
			$isChecked = ($currentValue === $option->value);

			echo $this->buildTag('label', array(
				'class' => 'ame-radio-bar-item',
				'title' => $option->description,
			));

			echo $this->buildTag(
				'input',
				array_merge(array(
					'type'      => 'radio',
					'name'      => $fieldName,
					'value'     => $this->mainSetting->encodeForForm($option->value),
					'class'     => $this->inputClasses,
					'checked'   => $isChecked,
					'disabled'  => !$option->enabled,
					'data-bind' => $this->makeKoDataBind([
						'checked'                   => $this->getKoObservableExpression($option->value),
						'checkedValue'              => wp_json_encode($option->value),
						'ameObservableChangeEvents' => 'true',
					]),
				), $this->inputAttributes)
			);

			$buttonContent = esc_html($option->label);
			if ( is_string($option->icon) && (strpos($option->icon, 'dashicons-') !== false) ) {
				$buttonContent = sprintf(
					'<span class="dashicons %s"></span>  %s',
					esc_attr($option->icon),
					$buttonContent
				);
			}

			$buttonClasses = ['button', 'ame-radio-bar-button'];
			if ( !empty($option->label) ) {
				$buttonClasses[] = 'ame-rb-has-label';
			}

			//Note that we can't use a "button" element because then the label
			//won't correctly select the radio input when clicked. It's probably
			//because a label can't be associated with two elements.
			echo $this->buildTag(
				'span',
				['class' => $buttonClasses],
				$buttonContent
			);

			echo '</label>';
		}
		echo '</fieldset>';
		//phpcs:enable
	}
}