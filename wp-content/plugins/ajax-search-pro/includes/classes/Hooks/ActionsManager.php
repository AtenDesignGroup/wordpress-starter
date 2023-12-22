<?php
namespace WPDRMS\ASP\Hooks;
if (!defined('ABSPATH')) die('-1');


class ActionsManager {
	const NAMESPACE = "WPDRMS\\ASP\\Hooks\\Actions\\";
	/**
	 * Array of internal known actions
	 *
	 * @var array
	 */
	private static $actions = array(
		array(
			"action" => "init",
			"handler" => "Cookies",
			"priority"    => 10,
			"args"  => 0
		),
		array(
			"action" => "template_redirect",
			"handler" => array("OutputBuffer", 'obStart'),
			"priority"    => 9999,
			"args"  => 0
		),
		array(
			"action" => "shutdown",
			"handler" => array('OutputBuffer', 'obClose'),
			"priority"    => -101,
			"args"  => 0
		),
		array(
			"action" => "wp_footer",
			"handler" => "Footer",
			"priority"    => 10,
			"args"  => 0
		),
		array(
			"action" => "init",
			"handler" => "Updates",
			"priority"    => 10,
			"args"  => 0
		),
		array(
			"action" => "widgets_init",
			"handler" => "Widgets",
			"priority"    => 10,
			"args"  => 0
		),
		array(
			"action" => "edit_attachment",
			"handler" => array("IndexTable", "update"),
			"priority"    => 999999998,
			"args"  => 3
		),
		array(
			"action" => "add_attachment",
			"handler" => array("IndexTable", "update"),
			"priority"    => 999999998,
			"args"  => 3
		),
		array(
			"action" => "save_post",
			"handler" => array("IndexTable", "update"),
			"priority"    => 999999998,
			"args"  => 3
		),
		array(
			"action" => "added_post_meta",
			"handler" => array("IndexTable", "update_post_meta"),
			"priority"    => 999999999,
			"args"  => 4
		),
		array(
			"action" => "updated_post_meta",
			"handler" => array("IndexTable", "update_post_meta"),
			"priority"    => 999999999,
			"args"  => 4
		),
		array(
			"action" => "wp_insert_post",
			"handler" => array("IndexTable", "update"),
			"priority"    => 999999999,
			"args"  => 3
		),
		array(
			"action" => "new_to_publish",
			"handler" => array("Other", "on_save_post"),
			"priority"    => 999999999,
			"args"  => 0
		),
		array(
			"action" => "draft_to_publish",
			"handler" => array("Other", "on_save_post"),
			"priority"    => 999999999,
			"args"  => 0
		),
		array(
			"action" => "pending_to_publish",
			"handler" => array("Other", "on_save_post"),
			"priority"    => 999999999,
			"args"  => 0
		),
		array(
			"action" => "delete_post",
			"handler" => array("IndexTable", "delete"),
			"priority"    => 10,
			"args"  => 1
		),
		array(
			"action" => "asp_cron_it_extend",
			"handler" => array("IndexTable", "extend"),
			"priority"    => 10,
			"args"  => 0,
			"cron"  => true
		),
		array(
			"action" => "wpel_before_apply_link",
			"handler" => array("Other", "plug_WPExternalLinks_fix"),
			"priority"    => 9999,
			"args"  => 1
		),
		array(
			"action" => "wp_footer",
			"handler" => array("Other", "pll_save_string_translations"),
			"priority" => 9999,
			"args" => 0
		),
		array(
			"action" => "init",
			"handler" => array("Other", "pll_init_string_translations"),
			"priority" => 9998,
			"args" => 0
		),
		array(
			"action" => "init",
			"handler" => array("Other", "pll_register_string_translations"),
			"priority" => 9999,
			"args" => 0
		),
		array(
			"action" => "asp_scheduled_activation_events",
			"handler" => array("Other", "scheduledActivationEvents"),
			"priority" => 9999,
			"args" => 0
		)
	);

	/**
	 * Array of already registered handlers
	 *
	 * @var array
	 */
	private static $registered = array();

	/**
	 * Registers all the handlers from the $actions variable
	 */
	public static function registerAll() {

		foreach (self::$actions as $data) {
			self::register($data['action'], $data['handler'], $data['priority'], $data['args']);

			if ( !empty($data['cron']) )
				self::registerCron( $data['handler'] );
		}
	}

	/**
	 * Get all the queued handlers
	 *
	 * @return array
	 */
	public static function getAll( ): array {
		return array_keys(self::$actions);
	}

	/**
	 * Get all the already registered handlers
	 *
	 * @return array
	 */
	public static function getRegistered(): array {
		return self::$registered;
	}

	/**
	 * Registers an action with the handler class name.
	 *
	 * @param $action
	 * @param $handler
	 * @param int $priority
	 * @param int $accepted_args
	 * @return bool
	 */
	public static function register($action, $handler, int $priority = 10, int $accepted_args = 0): bool {

		if ( is_array($handler) ) {
			$class = self::NAMESPACE . $handler[0];
			$handle = $handler[1];
		} else {
			$class = self::NAMESPACE . $handler;
			$handle = "handle";
		}

		if ( !class_exists($class) ) return false;

		add_action($action, array(call_user_func(array($class, 'getInstance')), $handle), $priority, $accepted_args);

		self::$registered[] = $action;

		return true;
	}

	public static function registerCron( $handler ) {
		if ( is_array($handler) ) {
			$class = self::NAMESPACE . $handler[0];
			$handle = "cron_".$handler[1];
		} else {
			$class = self::NAMESPACE . $handler;
			$handle = "cron_handle";
		}

		$o = call_user_func(array($class, 'getInstance'));
		// Run the handler, that will register the event
		$o->$handle();
	}

	/**
	 * Deregisters an action handler.
	 *
	 * @param $action
	 * @param $handler
	 * @noinspection PhpMissingParamTypeInspection
	 */
	public static function deregister( $action, $handler = false ) {
		if ( $handler === false ) {
			foreach ( self::$actions as $a ) {
				if ( $a['action'] == $action ) {
					if ( is_array($a['handler']) ) {
						remove_action($action, array(call_user_func(array(self::NAMESPACE . $a['handler'][0], 'getInstance')), $a['handler'][1]));
					} else {
						remove_action($action, array(call_user_func(array(self::NAMESPACE . $a['handler'], 'getInstance')), 'handle'));
					}
				}
			}
		} else if ( is_array($handler) ) {
			remove_action($action, array(call_user_func(array(self::NAMESPACE . $handler[0], 'getInstance')), $handler[1]));
		} else if ( is_string($handler) ) {
			remove_action($action, array(call_user_func(array(self::NAMESPACE . $handler, 'getInstance')), 'handle'));
		}
	}

}