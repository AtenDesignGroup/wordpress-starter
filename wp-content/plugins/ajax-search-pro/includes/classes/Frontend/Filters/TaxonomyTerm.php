<?php
namespace WPDRMS\ASP\Frontend\Filters;

if (!defined('ABSPATH')) die('-1');


class TaxonomyTerm extends Filter {
	public $data = array(
		"type" => "checkboxes",
		"required" => false,
		"invalid_input_text" => "Please select one!",
		"default" => "checked",
		"placeholder" => "",
		"taxonomy" => "category",
		"allow_empty" => '',    // true or false, or '' for inherit
		"logic" => ''           // and, or, andex or '' for inherit
	);

	protected $default = array(
		'label' => '',
		'selected' => false,
		'id' => 0,
		'level' => 0,
		'default' => false,
		'parent' => 0,
		'taxonomy' => 'category'
	);

	protected $type = 'taxonomy';

	public function field(): string {
		return $this->data['taxonomy'];
	}

	public function isMixed(): bool {
		$taxonomies = array();
		foreach ( $this->values as $value ) {
			if ( $value->id != 0 && isset($value->taxonomy) ) {
				$taxonomies[] = $value->taxonomy;
				$taxonomies = array_unique($taxonomies);
				if (count($taxonomies) > 1)
					return true;
			}
		}
		return false;
	}
}