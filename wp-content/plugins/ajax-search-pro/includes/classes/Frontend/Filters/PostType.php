<?php
namespace WPDRMS\ASP\Frontend\Filters;

if (!defined('ABSPATH')) die('-1');

class PostType extends Filter {
	public $data = array(
		'required' => false,
		'invalid_input_text' => 'This is required!'
	);

	protected $default = array(
		'label' => '',
		'selected' => false,
		'value' => '',
		'level' => 0,
		'default' => false
	);
	protected $key = 'value';
	protected $type = 'post_type';
}