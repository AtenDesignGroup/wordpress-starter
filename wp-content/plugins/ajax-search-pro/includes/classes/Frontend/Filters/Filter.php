<?php
namespace WPDRMS\ASP\Frontend\Filters;

if (!defined('ABSPATH')) die('-1');


class Filter {
	private static $last_position = 0;
	private static $last_id = 0;

	public $label = '';
	public $display_mode = 'checkboxes';
	public $data = array();
	public $position = 0;
	public $id = 0;
	public $is_api = true;  // Filter added via API, set to false for parsers

	protected $values = array();
	// Default value for the $values array
	protected $default = array(
		'label' => '',
		'selected' => false,
		'id' => 0,  // Can be numeric, as well as a field name etc..
		'level' => 0,
		'default' => false
	);
	protected $key = 'id';  // The main distinctive field
	protected $type = '';

	protected $option_path = array(
		'taxonomy' => 'termset',
		'custom_field' => 'aspf'
	);

	function __construct($label = '', $display_mode = 'checkboxes', $data = array(), $position = -1) {
		$this->label = $label;
		$this->display_mode = $display_mode;
		$data = is_array($data) ? $data : (array)$data;
		$this->data = array_merge($this->data, $data);
		$this->id = ++self::$last_id;

		if ( isset($data['is_api']) )
			$this->is_api = $data['is_api'];

		if ( $position > -1 ) {
			$this->position = $position;
			if ( $position > self::$last_position ) {
				self::$last_position = $position;
			}
		} else {
			$this->position = self::$last_position;
			++self::$last_position;
		}
	}

	public function isEmpty(): bool {
		return empty($this->values);
	}

	public function add($filter, $position = false) {
		$new = (object)array_merge($this->default, $filter);
		if ( $position === false ) {
			$this->values[] = $new;
		} else {
			$position = intval($position);
			array_splice( $this->values, $position, 0, array($new) );
		}
		return $new;
	}

	public function get($ids = array()) {
		$key = $this->key;
		if ( is_array($ids) ) {
			if (empty($ids)) {
				return $this->values;
			} else {
				$ret = array();
				foreach ($this->values as $v) {
					if (in_array($v->{$key}, $ids)) {
						$ret[] = $v;
					}
				}
				return $ret;
			}
		} else {
			foreach ($this->values as $v) {
				if ($v->{$key} == $ids) {
					return $v;
				}
			}
		}
		return array();
	}

	public function remove($ids = array(), $by_id = false) {
		if ( $by_id ) {
			$i = is_array($ids) ? $ids : array($ids);
			foreach ( $i as $ii ) {
				if (isset($this->values[$ii])) {
					unset($this->values[$ii]);
				}
			}
		} else {
			$key = $this->key;
			if ( is_array($ids) ) {
				if (empty($ids)) {
					$this->values = array();
				} else {
					foreach ($this->values as $k => $v) {
						if (in_array($v->{$key}, $ids)) {
							unset($this->values[$k]);
						}
					}
				}
			} else {
				foreach ($this->values as $k => $v) {
					if ($v->{$key} == $ids) {
						unset($this->values[$k]);
					}
				}
			}
		}
	}

	public function attr($ids = array(), $att = '', $val = '', $by_id = false) {
		if ( $by_id ) {
			$i = is_array($ids) ? $ids : array($ids);
			foreach ( $i as $ii ) {
				if ( isset($this->values[$ii]) ) {
					$this->values[$ii]->{$att} = $val;
				}
			}
		} else {
			$key = $this->key;
			if ( is_array($ids) ) {
				if (empty($ids)) {
					foreach ($this->values as $k => $v) {
						$this->values[$k]->{$att} = $val;
					}
				} else {
					foreach ($this->values as $k => $v) {
						if (in_array($v->{$key}, $ids)) {
							$this->values[$k]->{$att} = $val;
						}
					}
				}
			} else {
				foreach ($this->values as $k => $v) {
					if ($v->{$key} == $ids) {
						$this->values[$k]->{$att} = $val;
					}
				}
			}
		}
	}

	public function select($ids = array(), $unselect = false) {
		if ($unselect) {
			$this->unselect();
		}
		$this->attr($ids, 'selected', true);
	}

	public function unselect($ids = array(), $select = false) {
		if ($select) {
			$this->select();
		}
		$this->attr($ids, 'selected', false);
	}

	/** @noinspection PhpUnused */
	public function selectByOptions($options ) {
		if ( $this->is_api && $this->type != '' && isset($this->option_path[$this->type], $options['_fo']) ) {
			$path = $this->option_path[$this->type];
			$key = $this->key;
			$o = $options['_fo'];
			foreach( $this->values as &$value ) {
				if ( $this->type == 'taxonomy' ) {
					if ( isset($o[$path], $o[$path][$value->taxonomy]) ) {
						$posted = $o[$path][$value->taxonomy];
						if ( is_array($posted) ) {
							$value->selected = in_array($value->{$key}, $posted);
						} else {
							$value->selected = $value->{$key} == $posted;
						}
					} else {
						$value->selected = false;
					}
				} else if ( $this->type == 'custom_field' ) {
					if ( method_exists($this, 'getUniqueFieldName') ) {
						$unique_field_name = $this->getUniqueFieldName();
						if ( isset($o[$path], $o[$path][$unique_field_name]) ) {
							$posted = $o[$path][$unique_field_name];
							if ( is_array($posted) ) {
								if ( !is_array($value->{$key}) ) {
									$value->selected = in_array($value->{$key} . '', $posted);
								} else {
									$value->value = array_values($posted);
								}
							} else {
								$value->selected = $value->{$key} == $posted;
								if ( $this->display_mode == 'datepicker' && isset($o[$path][$unique_field_name]) ) {
									$value->value = $o[$path][$unique_field_name];
								} else if ( in_array($this->display_mode, array('hidden', 'text', 'slider')) ) {
									$value->value = $posted;
								}
							}
						} else {
							$value->selected = false;
						}
					}
				}
			}
		}
	}

	public function type(): string {
		return $this->type;
	}

	public function field(): string {
		return $this->type();
	}

	public static function getLastId(): int {
		return self::$last_id;
	}

	/** @noinspection PhpUnused */
	public static function getLastPosition() {
		return self::$last_position;
	}

	public static function reset() {
		self::$last_id = 0;
		self::$last_position = 0;
	}
}