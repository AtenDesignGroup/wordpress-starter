<?php
namespace WPDRMS\ASP\Frontend\Filters;

if (!defined('ABSPATH')) die('-1');

class CustomField extends Filter {
	public $data = array(
		"field" => "",
		"source" => "postmeta",
		"type" => "checkboxes",
		"required" => false,
		"invalid_input_text" => "Please select one!",
		"logic" => 'and',
		/*
		 * Apply the AND logic as if fields are separately stored with the same name ex.:
		 * field_name => value1,
		 * field_name => value2
		 * .. instead of field_name => 'value1, value2 etc..'
		 */
		"logic_and_separate_custom_fields" => false,
		"operator" => '=',
		"acf_type" => false
	);

	protected $default = array(
		'label' => '',
		'selected' => false,
		'value' => '',
		'level' => 0,
		'default' => false,
		'parent' => 0,
		'option_group' => false
		/**
		 * Other possible keys here, depending on the type:
		 *  'slider_from', 'slider_to', 'placeholder'
		 */
	);
	protected $special_args = array(
		"slider" => array(
			'slider_prefix' => '-,',
			'slider_suffix' => '.',
			'slider_step' => 1,
			'slider_from' => 1,
			'slider_to'   => 1000,
			'slider_decimals' => 0,
			'slider_t_separator' => ' ',
			'operator' => 'let'
		),
		"number_range" => array(
			'range_step' => 1,
			'range_from' => 1,
			'placeholder1' => '',
			'placeholder2' => '',
			'range_to'   => 1000,
			'range_t_separator' => ' '
		),
		"range" => array(
			'range_prefix' => '-,',
			'range_suffix' => '.',
			'range_step' => 1,
			'range_from' => 1,
			'range_to'   => 1000,
			'range_decimals' => 0,
			'range_t_separator' => ' '
		),
		"datepicker" => array(
			'placeholder' => '',
			'date_format' => 'dd/mm/yy',
			'date_store_format' => 'datetime' // datetime, acf, timestamp
		),
		"dropdown" => array(
			'placeholder' => '',
			'multiple' => false
		),
		"dropdownsearch" => array(
			'placeholder' => ''
		),
		"multisearch" => array(
			'placeholder' => ''
		)
	);
	protected $key = 'value';
	protected $type = 'custom_field';

	public function __construct($label = '', $display_mode = 'checkboxes', $data = array(), $position = -1) {
		parent::__construct($label, $display_mode, $data, $position);
		if ( isset($this->special_args[$this->display_mode]) ) {
			$this->data = array_merge($this->data, $this->special_args[$this->display_mode], $data);
		} else {
			$this->data = array_merge($this->data, $data);
		}
		if ( function_exists('get_field') ) {
			$this->data['acf_type'] = asp_acf_get_field_type($this->data['field']);
		}
	}

	public function field():string {
		return $this->data['field'];
	}

	public function getUniqueFieldName( $to_display = false ) {
		if ( isset($this->data['field']) ) {
			$field = $this->data['field'];
			if ( $to_display ) {
				$field = str_replace(
					array('[', ']'),
					array('!_brktl_!', '!_brktr_!'),
					$field
				);
			}
			return $field . '__' . $this->id;
		} else {
			return $this->id;
		}
	}
}