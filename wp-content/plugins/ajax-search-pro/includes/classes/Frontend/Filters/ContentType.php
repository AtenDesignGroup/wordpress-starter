<?php
namespace WPDRMS\ASP\Frontend\Filters;

if (!defined('ABSPATH')) die('-1');

class ContentType extends Filter {
	public $data = array(
		"field" => "",
		"required" => false,
		"invalid_input_text" => "Please select one!"
	);

	protected $default = array(
		'label' => '',
		'selected' => false,
		'value' => '',
		'level' => 0,
		'default' => false
	);
	protected $key = 'value';
	protected $type = 'content_type';
}