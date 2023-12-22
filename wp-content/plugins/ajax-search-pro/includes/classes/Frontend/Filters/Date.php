<?php
namespace WPDRMS\ASP\Frontend\Filters;

if (!defined('ABSPATH')) die('-1');


class Date extends Filter {
	public $data = array();

	protected $default = array(
		'label' => '',
		'value' => '',
		'name'  => '',
		'format' => 0,
		'default' => false,
		'placeholder' => ''
	);

	protected $key = 'value';
	protected $type = 'date';
}