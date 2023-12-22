<?php
namespace WPDRMS\ASP\Misc;

use WPDRMS\ASP\Patterns\SingletonTrait;

if ( !defined('ABSPATH') ) die('-1');

class PriorityGroups {
	use SingletonTrait;

	/**
	 * @var string
	 */
	private $key = '_asp_priority_groups';
	/**
	 * Array of instances (arrays)
	 *
	 * @var array
	 */
	private $instances;

	/**
	 * Ordering in which the instances can be requested: priority, name
	 *
	 * @var string
	 */
	private $order = 'priority';
	/**
	 * Order by: asc or desc
	 *
	 * @var string
	 */
	private $orderBy = 'desc';

	/**
	 * Default data structure for one priority ruleset
	 *
	 * @var array
	 */
	private $default = array(
		'priority' => 100,
		'name' => 'Priority group',
		'phrase' => '',                  // phrase that affects the logic
		'phrase_logic' => 'disabled',          // disabled, exact, any, start, end
		'instance' => 0,                   // search instance ID to affect, default 0 (all)
		'logic' => 'and',               // logic to connect the rules with
		'rules' => array(/*
			array(
				'name'          => 'Rule name',
				'field'         => 'tax',       // 'tax', 'cf', 'title' (disabled for now)
				'operator'      => 'in',        // in, not in, like, not like, elike, =, <>, >, <, >=, <=, BETWEEN
				'values'        => array(),
				'_values'       => array()      // additional data for the values
			)
			*/
		)
	);

	/**
	 * A sample default rule, used in the rule array in a ruleset
	 *
	 * @var array
	 */
	private $default_rule = array(
		'name' => 'Rule name',
		'field' => 'tax',       // 'tax', 'cf', 'title'
		'operator' => 'in',        // in, not in, like, not like, elike, =, <>, >, <, >=, <=, BETWEEN
		'values' => array(),
		'_values' => array()      // additional data for the values
	);


	/**
	 * Get a rule by ID, or all rules
	 *
	 * @param bool|int $id
	 * @param bool|int $force_refresh
	 * @return array
	 */
	public function get($id = false, $force_refresh = false): array {
		if ( !isset($this->instances) || !is_array($this->instances) || $force_refresh )
			$this->load();

		if ( $id === false ) {
			return $this->instances;
		} else {
			return $this->instances[$id] ?? array();
		}
	}

	/**
	 * Get all rules sorted
	 *
	 * @param string $order
	 * @param string $orderby
	 * @return array
	 */
	public function getSorted(string $order = 'priority', string $orderby = 'desc'): array {
		$inst = $this->get();
		if ( !empty($inst) ) {
			$this->order = $order;
			$this->orderBy = $orderby;
			usort($inst, array($this, 'sortInstances'));
		}
		return $inst;
	}

	/**
	 * Get a rule by ID, or all rules
	 * Includes the taxonomy term labels.
	 *
	 * @param bool|int $id
	 * @param bool|int $force_refresh
	 * @return array
	 */
	public function getForDisplay($id = false, $force_refresh = false): array {
		$inst = $this->get($id, $force_refresh);
		if ( empty($inst) )
			return $inst;
		if ( $id !== false ) {
			// For easier use, put a single instance into an array
			$inst = array($inst);
		}
		/**
		 * Expected structure:
		 * $inst = array(
		 *      ...,
		 *      'values' => array(
		 *          array(
		 *              'field' => 'tax',
		 *              'values' => array(
		 *                  'taxonomy_name' => array(1, 2, 3),
		 *                  ...
		 *           ),
		 *          array(
		 *              'field' => 'cf',
		 *              'values' => array( 'field_name' => array('val1', 'val2') )
		 *           ),
		 *          array(
		 *              'field'  => 'title'
		 *              'values' => 'value'
		 *          )
		 *          ...
		 *      ),
		 *      ....
		 * );
		 */
		foreach ($inst as $ik => $instance) {
			foreach ($instance['rules'] as $rk => $rule) {
				if ( $rule['field'] == 'tax' ) {
					foreach ($rule['values'] as $rfk => $rfv) {
						$terms = wpd_get_terms(array(
							'taxonomy' => $rfk,
							'include' => $rule['values'][$rfk],
							'fields' => 'id=>name',
							'hide_empty' => false
						));
						if ( !isset($inst[$ik]['rules'][$rk]['_values']) )
							$inst[$ik]['rules'][$rk]['_values'] = array();
						if ( !is_wp_error($terms) ) {
							$inst[$ik]['rules'][$rk]['_values'][$rfk] = $terms;
						} else {
							// Something went wrong (taxonomy deleted?)
							unset($inst[$ik]['rules'][$rk]['values'][$rfk]);
						}
					}
				}
			}
		}

		return $inst;
	}

	/**
	 * JSON and Base64 encoded version of get()
	 *
	 * @param bool|int $id
	 * @param bool|int $force_refresh
	 * @return string
	 */
	public function getEncoded($id = false, $force_refresh = false): string {
		return base64_encode(json_encode($this->get($id, $force_refresh)));
	}

	/**
	 * JSON and Base64 encoded version of getForDisplay()
	 *
	 * @param bool|int $id
	 * @param bool|int $force_refresh
	 * @return string
	 */
	public function getForDisplayEncoded($id = false, $force_refresh = false): string {
		return base64_encode(json_encode($this->getForDisplay($id, $force_refresh)));
	}

	/**
	 * Get the rules count
	 *
	 * @param bool $force_refresh
	 * @return int
	 */
	public function getCount(bool $force_refresh = false): int {
		if ( !isset($this->instances) || !is_array($this->instances) || $force_refresh )
			$this->load();

		return count($this->instances);
	}


	/**
	 * Set all instances by data
	 *
	 * @param $data
	 * @param bool|int $save
	 */
	public function set($data, $save = true) {
		$this->instances = $data;
		if ( $save )
			$this->save();
	}

	/**
	 * JSON and Base64 encoded version of set()
	 *
	 * @param $data
	 * @param bool|int $save
	 */
	public function setEncoded($data, $save = true) {
		$this->set(json_decode(base64_decode($data), true), $save);
	}

	/**
	 * Add a group to the instances
	 *
	 * @param $data
	 * @param bool|int $save
	 */
	public function add($data, $save = true) {
		$this->instances[] = $data;
		if ( $save )
			$this->save();
	}

	/**
	 * Load up the instances from the database
	 *
	 * @return mixed
	 */
	public function load() {
		$this->instances = get_option($this->key, array());
		if ( $this->cleanInstances() ) {
			// Something changed, save the new, consistent data
			$this->save(false);
		}

		return $this->instances;
	}

	/**
	 * Saves instances to the database
	 *
	 * @param bool|int $clean
	 */
	public function save($clean = true) {
		if ( $clean )
			$this->cleanInstances();
		update_option($this->key, $this->instances);
	}

	/**
	 * Used for usort() to sort by key
	 *
	 * @param $a
	 * @param $b
	 * @return int
	 */
	public function sortInstances($a, $b): int {
		if ( isset($a[$this->order], $b[$this->order]) ) {
			if ( $a[$this->order] == $b[$this->order] ) {
				return 0;
			}
			if ( $this->orderBy == 'asc' ) {
				return ($a[$this->order] < $b[$this->order]) ? -1 : 1;
			} else {
				return ($a[$this->order] < $b[$this->order]) ? 1 : -1;
			}
		} else {
			return 0;
		}
	}

	/**
	 *
	 */
	public function debug() {}

	// ------------------------------------------------------------
	//       ---------------- PRIVATE --------------------
	// ------------------------------------------------------------

	/**
	 * Just calls init
	 */
	private function __construct() {
		$this->init();
	}

	private function init() {
		$this->load();
	}

	/**
	 * Performs a cleaning process on group instances
	 *
	 * @return bool
	 */
	private function cleanInstances(): bool {
		$changed = false;

		foreach ($this->instances as $k => &$v) {
			// Merge with the default first
			$v = wp_parse_args($v, $this->default);
			// Clean up the unwanted keys, if any
			$instance_keys = array_keys($this->default);
			foreach ($v as $kk => $vv) {
				if ( !in_array($kk, $instance_keys) )
					unset($this->instances[$k][$kk]);
			}
			// ..also, the unwanted rule keys, if any
			$rule_keys = array_keys($this->default_rule);
			foreach ($v['rules'] as $rk => $rule) {
				foreach ($rule as $kk => $vv) {
					if ( !in_array($kk, $rule_keys) )
						unset($this->instances[$k]['rules'][$rk][$kk]);
				}
			}

			// Skip these tests on the front-end and on any ajax request
			if ( !wp_doing_ajax() && is_admin() ) {
				// Clean up non-existent taxonomies and taxonomy terms
				foreach ($v['rules'] as $rk => $rule) {
					if ( $rule['field'] == 'tax' ) {
						foreach ($rule['values'] as $rtax => $rterms) {
							$terms = wpd_get_terms(array(
								'taxonomy' => $rtax,
								'include' => $rterms,
								'fields' => 'ids',
								'hide_empty' => false
							));
							if ( !is_wp_error($terms) ) {
								// Some category might got deleted?
								if ( count($terms) == 0 ) {
									unset($this->instances[$k]['rules'][$rk]['values'][$rtax]);
									$changed = true;
								} else if ( count($terms) < count($rule['values'][$rtax]) ) {
									$v['rules'][$rk]['values'][$rtax] = $terms;
									$changed = true;
								}
							} else {
								// Something went wrong (taxonomy deleted?)
								unset($this->instances[$k]['rules'][$rk]['values'][$rtax]);
								$changed = true;
							}
						}
					}
				}
			}

		}

		return $changed;
	}
}