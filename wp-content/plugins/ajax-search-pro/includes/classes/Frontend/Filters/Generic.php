<?php
namespace WPDRMS\ASP\Frontend\Filters;

if (!defined('ABSPATH')) die('-1');

class Generic extends Filter {
	public $data = array(
		"field" => ""
	);

	protected $default = array(
		'label' => '',
		'selected' => false,
		'value' => '',
		'level' => 0,
		'default' => false
	);
	protected $key = 'value';
	protected $type = 'generic';
}