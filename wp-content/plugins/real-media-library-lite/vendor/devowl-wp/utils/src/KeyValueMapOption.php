<?php

namespace MatthiasWeb\RealMediaLibrary\Vendor\MatthiasWeb\Utils;

use WP_Error;
use WP_REST_Response;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * Adds an option via `add_option` which represents a simple map / array of key value pairs.
 *
 * It also provides some methods to generate notices with dismiss-functionality (best use-case
 * for a key-value map), and allowing to modify the value via REST API.
 * @internal
 */
class KeyValueMapOption
{
    const REST_NAME = 'key-value-map';
    private $name;
    private $siteWide;
    private $core;
    /**
     * See `registerMigrationForKey`.
     *
     * @var callable[]
     */
    private $migrationsForKey;
    /**
     * See `registerMigration`.
     *
     * @var callable[]
     */
    private $migrations;
    /**
     * See `registerModifier`.
     *
     * @var callable[]
     */
    private $modifiers;
    /**
     * C'tor.
     *
     * @param string $name The option name
     * @param Core $core Pass a `Core` instance to enable helpers for REST API requests
     * @param boolean $siteWide
     * @param boolean $autoload
     * @codeCoverageIgnore
     */
    public function __construct($name, $core = null, $siteWide = \false, $autoload = \true)
    {
        $this->name = $name;
        $this->siteWide = $siteWide;
        $this->core = $core;
        if ($autoload) {
            $this->enableAutoload();
        }
    }
    /**
     * Allows to register a modifier for a value of. The passed callable is executed, when you `$this->set()`
     * a new value for a key. The callable is called with the key name as first argument and the value as
     * the second argument. The callable needs to return the new value.
     *
     * @param callable $callable
     */
    public function registerModifier($callable)
    {
        $this->modifiers[] = $callable;
        return $this;
    }
    /**
     * Allows to register a migration for non-existing keys. The passed callable is executed,
     * when you try to `$this->get()` a specific key which does not yet exist in the map. The
     * return value of the callable is used for the map value and automatically persisted to database.
     *
     * @param string $key
     * @param callable $callable
     */
    public function registerMigrationForKey($key, $callable)
    {
        $this->migrationsForKey[$key] = $callable;
        return $this;
    }
    /**
     * Allows to register a migration for non-existing map. The passed callable is executed,
     * when the map does not yet exist in database. The callable gets called with the newly created
     * map so you can modify and return it.
     *
     * @param callable $callable
     */
    public function registerMigration($callable)
    {
        $this->migrations[] = $callable;
        return $this;
    }
    /**
     * Registers a REST route to your existing REST Service which allows updating the value of a given key via `PATCH`,
     * e.g. `wp-json/real-cookie-banner/v1/key-value-map/{name}/{key}` and send a `value` as data.
     *
     * @param string $capability Minimum required capability to call this route
     * @param string $key Pass a regular expression with leading and trailing slashes e.g. `/[a-zA-Z0-9_-]+/`
     * @param string $args Argument validation for `value` (see also https://developer.wordpress.org/rest-api/extending-the-rest-api/routes-and-endpoints/#arguments)
     */
    public function registerRestForKey($capability, $key, $args)
    {
        \add_action('rest_api_init', function () use($capability, $key, $args) {
            $namespace = Service::getNamespace($this->core);
            $isKeyRegex = \strpos($key, '/') === 0;
            if ($isKeyRegex) {
                $key = \trim($key, '/');
            }
            $endpoint = '/' . $this->getRestEndpointForKey($isKeyRegex ? \sprintf('(?P<key>%s)', $key) : $key);
            \register_rest_route($namespace, $endpoint, ['methods' => 'PATCH', 'callback' => function ($request) use($key, $isKeyRegex) {
                $key = $isKeyRegex ? $request->get_param('key') : $key;
                $value = $request->get_param('value');
                if (!$this->set($key, $value)) {
                    return new WP_Error('key_value_map_update_failed', 'Update for given key failed.', ['key' => $key, 'value' => $value]);
                }
                return new WP_REST_Response(['key' => $key, 'value' => $this->get($key)]);
            }, 'permission_callback' => function () use($capability) {
                return \current_user_can($capability);
            }, 'args' => ['value' => \array_merge(['required' => \true], $args)]]);
        });
        return $this;
    }
    /**
     * Generate a `onclick` JavaScript coding which automatically hides the notice, sends a request via
     * REST API and optionally redirects to a given page.
     *
     * @param string $key
     * @param string $value
     * @param string $redirect
     */
    public function noticeDismissOnClickHandler($key, $value, $redirect = \false)
    {
        $rest_url = Service::getUrl($this->core);
        $redirectStr = $redirect ? \sprintf('window.location.href= "%s";', $redirect) : '';
        return \join('', ['jQuery(this).parents(".notice").remove();', \sprintf('window.fetch("%s").then(function(response){ %s });', \add_query_arg(['_method' => 'PATCH', '_wpnonce' => \wp_create_nonce('wp_rest'), 'value' => $value], \sprintf('%s%s', $rest_url, $this->getRestEndpointForKey($key))), $redirectStr)]);
    }
    /**
     * Generate the REST endpoint to a given key.
     *
     * @param string $key
     */
    public function getRestEndpointForKey($key)
    {
        return \sprintf('%s/%s/%s', self::REST_NAME, $this->getName(), $key);
    }
    /**
     * Get value by key.
     *
     * @param string $key
     * @param mixed $default
     */
    public function get($key, $default = \false)
    {
        $map = $this->getMap();
        if (!isset($map[$key]) && isset($this->migrationsForKey[$key])) {
            $this->set($key, $this->migrationsForKey[$key]());
            // Never run again
            unset($this->migrationsForKey[$key]);
            return $this->get($key, $default);
        }
        return $map[$key] ?? $default;
    }
    /**
     * Set value by key.
     *
     * @param string $key
     * @param mixed $value Use `null` to remove the key from the map
     */
    public function set($key, $value)
    {
        $map = $this->getMap();
        $changed = \false;
        if ($value === null) {
            if (isset($map[$key])) {
                $changed = \true;
                unset($map[$key]);
            }
        } elseif (isset($map[$key]) && $map[$key] !== $value || !isset($map[$key])) {
            $changed = \true;
            foreach ($this->modifiers as $modifier) {
                $value = $modifier($key, $value);
            }
            $map[$key] = $value;
        }
        if (!$changed) {
            return \true;
        }
        return $this->persistMap($map);
    }
    /**
     * Registers the option as autoload in database. This is not supported for site-wide options.
     */
    protected function enableAutoload()
    {
        Utils::enableOptionAutoload($this->name, ['__created' => \true]);
    }
    /**
     * Persist map to `wp_options`.
     *
     * @param array $map
     */
    protected function persistMap($map)
    {
        return $this->isSiteWide() ? \update_site_option($this->getName(), $map) : \update_option($this->getName(), $map);
    }
    /**
     * Get the map as array.
     *
     * @return array
     */
    public function getMap()
    {
        $map = $this->isSiteWide() ? \get_site_option($this->getName(), []) : \get_option($this->getName(), []);
        if (isset($map['__created']) && $map['__created']) {
            foreach ($this->migrations as $migration) {
                $map = $migration($map);
            }
            unset($map['__created']);
            $this->persistMap($map);
        }
        return $map;
    }
    /**
     * Get an array with keys starting with a given string.
     *
     * @param string $prefix
     */
    public function getKeysStartingWith($prefix)
    {
        $map = $this->getMap();
        $result = [];
        foreach ($map as $key => $value) {
            if (\strpos($key, $prefix) === 0) {
                $result[\substr($key, \strlen($prefix))] = $value;
            }
        }
        return $result;
    }
    /**
     * Get option name.
     *
     * @codeCoverageIgnore
     */
    public function getName()
    {
        return $this->name;
    }
    /**
     * Get if option is site-wide.
     *
     * @codeCoverageIgnore
     */
    public function isSiteWide()
    {
        return $this->siteWide;
    }
}
