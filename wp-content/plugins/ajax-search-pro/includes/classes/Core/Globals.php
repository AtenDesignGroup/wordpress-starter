<?php
namespace WPDRMS\ASP\Core;

if (!defined('ABSPATH')) die('-1');

class Globals {

    /**
     * The plugin options and defaults
     *
     * @var array
     */
    public $options;

    /**
     * The plugin options and defaults (shorthand)
     *
     * @var array
     */
    public $o;

    /**
     * Instance of the init class
     *
     * @var Init
     */
    public $init;

    /**
     * Instance of the instances class
     *
     * @var Instances
     */
    public $instances;

    /**
     * Instance of the instances class
     *
     * @var \WPDRMS\ASP\Misc\PriorityGroups
     */
    public $priority_groups;


    /**
     * Instance of the updates manager
     *
     * @var \asp_updates
     */
    public $updates;

    /**
     * Instance of the database manager
     *
     * @var \WPDRMS\ASP\Database\Manager
     */
    public $db;

    /**
     * Instance of the REST API manager
     *
     * @var \WPDRMS\ASP\API\REST0\Rest
     */
    public $rest_api;

    /**
     * Instance of the manager
     *
     * @var Manager
     */
    public $manager;

    /**
     * Instance of the manager
     *
     * @var WPDRMS\ASP\Frontend\FiltersManager
     */
    public $front_filters;

    /**
     * Instance of the manager
     *
     * @var \WD_ASP_Instant
     */
    public $instant;

	/**
	 * Instance of the scripts manager
	 *
	 * @var \WPDRMS\ASP\Asset\Script\Manager
	 */
	public $script_manager;

	/**
	 * Instance of the scripts manager
	 *
	 * @var \WPDRMS\ASP\Asset\Css\Manager
	 */
	public $css_manager;

	/**
	 * Instance of the legacy scripts manager
	 *
	 * @var ScriptsLegacy
	 */
	public $scripts_legacy;

    /**
     * Array of ASP tables
     *
     * @var array
     */
    public $tables;

    /**
     * Holds the correct table prefix for ASP tables
     *
     * @var string
     */
    public $_prefix;

    /**
     * The upload directory for the plugin
     *
     * @var string
     */
    public $upload_dir = "asp_upload";

    /**
     * The upload directory for the BFI thumb library
     *
     * @var string
     */
    public $bfi_dir = "bfi_thumb";

    /**
     * The upload path
     *
     * @var string
     */
    public $upload_path;

    /**
     * The BFI lib upload path
     *
     * @var string
     */
    public $bfi_path;

    /**
     * The upload URL
     *
     * @var string
     */
    public $upload_url;

	/**
	 * Cache subdirectory name for CSS/JS assets
	 *
	 * @var string
	 */
	public $global_cache_path;

	/**
	 * Cache path for CSS/JS assets
	 *
	 * @var string
	 */
	public $cache_path;

	/**
	 * Cache url for CSS/JS assets
	 *
	 * @var string
	 */
	public $cache_url;
}