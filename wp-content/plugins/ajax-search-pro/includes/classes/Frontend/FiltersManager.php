<?php /** @noinspection PhpMissingParamTypeInspection */

/** @noinspection PhpMissingReturnTypeInspection */

namespace WPDRMS\ASP\Frontend;

use WPDRMS\ASP\Frontend\Filters\Filter;
use WPDRMS\ASP\Patterns\SingletonTrait;

if ( !defined('ABSPATH') ) die('-1');

class FiltersManager {
	use SingletonTrait;

	const NAMESPACE = "WPDRMS\\ASP\\Frontend\\Filters\\";

	/**
	 * Filters array
	 *
	 * @var array
	 */
	private $filters = array();

	/**
	 * The current search ID
	 *
	 * @var int
	 */
	private $search_id = 0;

	/**
	 * Types to class names array
	 *
	 * @var array
	 */
	private $classes = array(
		'generic' => 'Generic',
		'content_type' => 'ContentType',
		'post_type' => 'PostType',
		'taxonomy' => 'TaxonomyTerm',
		'post_tags' => 'PostTags',
		'custom_field' => 'CustomField',
		'date' => 'Date',
		'button' => 'Button'
	);

	/**
	 * Sets the currenty search ID
	 *
	 * @param $id - search ID
	 */
	public function setSearchId($id) {
		$this->search_id = $id + 0;
	}

	/**
	 * Creates and returns a new filter object, depending on the type
	 *
	 * @param $type - Type of the filter (generic, content_type, taxonomy, custom_field, date, search, reset)
	 * @param $label
	 * @param $display_mode - checkboxes, input, slider, range, dropdown, radio, dropdownsearch, multisearch
	 * @param $data - Other related information for the given filter type
	 * @return Filter
	 */
	public function create($type, $label = '', $display_mode = '', $data = array()) {
		$class = !empty($type) && isset($this->classes[$type]) ? $this->classes[$type] : $this->classes['taxonomy'];
		$class = self::NAMESPACE . $class;
		return new $class($label, $display_mode, $data);
	}

	/**
	 * Adds an existing filter to the end of the filters list
	 *
	 * @param Filter $filter
	 * @return Filter mixed
	 */
	public function add( $filter ) {
		if ( !isset($this->filters[$this->search_id]) )
			$this->filters[$this->search_id] = array();
		$this->filters[$this->search_id][] = $filter;
		$this->organize();
		return $filter;
	}

	/**
	 * Removes a filter by name or array key (id)
	 *
	 * @param int|string $key
	 * @return bool
	 */
	public function remove( $key ) {
		$k = $this->find( $key, true );
		if ( $k !== false ) {
			unset($this->filters[$this->search_id][$k]);
			$this->organize();
			return true;
		}
		return false;
	}

	/**
	 * Clears the filters array, removing every filter, and resetting every position etc...
	 *
	 * @param int $id the search ID
	 */
	public function clear($id ) {
		if ( isset($this->filters[$id]) )
			$this->filters[$id] = array();
		Filter::reset();
	}

	/**
	 * Finds a filter by array key (id) or label
	 *
	 * @param $key
	 * @param bool $key_only
	 * @return bool|int|Filter
	 */
	public function find($key, $key_only = false ) {
		if ( is_numeric($key) ) {
			if ( isset($this->filters[$this->search_id][$key]) ) {
				return $key_only ? $key : $this->filters[$this->search_id][$key];
			}
		} else {
			foreach ( $this->filters[$this->search_id] as $k => $v ) {
				if ( isset($v->label) && $v->label == $key ) {
					return $key_only ? $k : $this->filters[$this->search_id][$k];
				}
			}
		}

		return false;
	}


	/**
	 * Gets the filters
	 *
	 * @param string $order 'position' or 'added'
	 * @param bool|string $type false, or the filter type
	 * @return Filter[]
	 */
	public function get($order = 'position', $type = false ) {
		if ( !isset($this->filters[$this->search_id]) )
			return array();
		if ( $order == 'position' ) {
			$return = array();
			$added = array();
			while ( count($return) != count($this->filters[$this->search_id]) ) {
				$key = -1;
				$lowest_position = 999999999;
				foreach ( $this->filters[$this->search_id] as $k => $v ) {
					if ( !in_array($k, $added) ) {
						if ( $v->position <= $lowest_position ) {
							$lowest_position = $v->position;
							$key = $k;
						}
					}
				}
				if ( $key > -1 && !in_array($key, $added) ) {
					$return[] = $this->filters[$this->search_id][$key];
					$added[] = $key;
				}
			}
		} else {
			$return = $this->filters[$this->search_id];
		}
		if ( $type !== false ) {
			foreach ( $return as $k => $filter ) {
				if ( $filter->type() != $type ) {
					unset($return[$k]);
				}
			}
		}

		return apply_filters('asp_pre_get_front_filters', $return, $type);
	}

	/**
	 * Sets a filter attribute by array key (id) or by label
	 *
	 * @param int|string $key array key (id)
	 * @param $attribute
	 * @param $value
	 * @return bool
	 */
	public function set($key, $attribute, $value ) {
		$k = $this->find( $key, true );
		if ( $k !== false ) {
			$this->filters[$this->search_id][$k]->{$attribute} = $value;
			return true;
		}
		return false;
	}

	/**
	 * Reorganizes the filter IDs
	 */
	private function organize() {
		foreach ( $this->filters as $k => $v ) {
			if ( is_array($v) ) {
				foreach ( $this->filters[$k] as $kk => $vv ) {
					$this->filters[$k][$kk]->id = $kk + 1;
				}
			}
		}
	}
}