<?php
namespace WPDRMS\ASP\Frontend\Filters;

if (!defined('ABSPATH')) die('-1');

class Button extends Filter {
	protected $default = array(
		'label' => '',
		'type' => '',
		'container_class' => '',
		'button_class' => ''
	);
	protected $key = 'type';
	protected $type = 'button';
}