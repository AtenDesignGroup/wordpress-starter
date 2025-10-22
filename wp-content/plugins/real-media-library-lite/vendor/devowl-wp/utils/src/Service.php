<?php

namespace MatthiasWeb\RealMediaLibrary\Vendor\MatthiasWeb\Utils;

use WP_REST_Response;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * Create a base REST Service needed for boilerplate development.
 * @internal
 */
class Service
{
    private $core;
    const NOTICE_CORRUPT_REST_API_ID = 'notice-corrupt-rest-api';
    const SECURITY_PLUGINS_BLOCKING_REST_API = ['better-wp-security', 'all-in-one-wp-security-and-firewall', 'sucuri-scanner', 'anti-spam', 'wp-cerber', 'wp-simple-firewall', 'wp-hide-security-enhancer', 'bulletproof-security', 'disable-json-api', 'ninjafirewall', 'hide-my-wp', 'perfmatters', 'swift-performance', 'clearfy', 'password-protected', 'wp-rest-api-controller'];
    /**
     * C'tor.
     *
     * @param PluginReceiver $core
     * @codeCoverageIgnore
     */
    private function __construct($core)
    {
        $this->core = $core;
    }
    /**
     * Register endpoints.
     */
    public function rest_api_init()
    {
        $namespace = Service::getNamespace($this->getCore());
        \register_rest_route($namespace, '/plugin', ['methods' => 'GET', 'callback' => [$this, 'routePlugin'], 'permission_callback' => '__return_true']);
    }
    /**
     * Response for /plugin route.
     */
    public function routePlugin()
    {
        return new WP_REST_Response($this->getCore()->getPluginData());
    }
    /**
     * Get core instance.
     *
     * @return PluginReceiver
     * @codeCoverageIgnore
     */
    public function getCore()
    {
        return $this->core;
    }
    /**
     * Enable obfuscate for REST API routes. This is useful when an ad-blocker blocks a route.
     *
     * @param array $queryVars
     * @see https://regex101.com/r/fx1BfD/1
     */
    public function request($queryVars)
    {
        $offset = self::getObfuscateOffset();
        if (!empty($offset) && isset($queryVars['rest_route']) && \preg_match('/(.*\\/)([01])(\\w{16})[\\/](.*)$/m', $queryVars['rest_route'], $match) && $match[3] === $offset) {
            $beforeOffset = $match[1];
            $mode = $match[2];
            $afterOffset = $match[4];
            if ($mode === '1') {
                $afterOffset = self::deObfuscatePath($offset, $afterOffset);
            }
            $queryVars['rest_route'] = self::deObfuscatePath($offset, $beforeOffset) . $afterOffset;
        }
        return $queryVars;
    }
    /**
     * Show a notice for `corruptRestApi.tsx`.
     */
    public function admin_notices()
    {
        $screen = \get_current_screen();
        $isUpdateCorePage = $screen->id === 'update-core';
        if (!isset($GLOBALS[self::NOTICE_CORRUPT_REST_API_ID]) && !$isUpdateCorePage) {
            $GLOBALS[self::NOTICE_CORRUPT_REST_API_ID] = \true;
            $securityPlugins = $this->getSecurityPlugins();
            echo \sprintf('<div id="notice-corrupt-rest-api" class="hidden notice notice-warning" style="display:none;"><p>%s</p><ul style="list-style:circle;margin-left:30px;"></ul><p>%s</p><p>%s</p><textarea readonly="readonly" style="width:100%%;margin-bottom:5px;" rows="4"></textarea></div>', \sprintf(
                // translators:
                \__('<strong>An unexpected network error has occurred!</strong> One or more WordPress plugins tried to call the WordPress REST API, which failed. Most likely a <strong>security plugin%s</strong>, a web <strong>server configuration</strong> or active <strong>ad-blocker extension</strong> installed on your browser disabled the REST API. Please make sure that the following REST API namespaces are reachable to use your plugin without problems:', 'devowl-wp-utils'),
                \count($securityPlugins) > 0 ? ' (' . \join(', ', $securityPlugins) . ')' : ''
            ), \sprintf(
                // translators:
                \__('What is the WordPress REST API and how to enable it? %1$sLearn more%2$s.', 'devowl-wp-utils'),
                '<a href="' . \esc_attr(\__('https://devowl.io/knowledge-base/wordpress-rest-api-does-not-respond/', 'devowl-wp-utils')) . '" target="_blank" rel="noreferrer">',
                '</a>'
            ), \__('In the text box below you will find a technical listing of the last 10 failed network requests:', 'devowl-wp-utils'));
        }
    }
    /**
     * Get all active security plugins which can limit the WP REST API.
     *
     * @return string[]
     */
    public function getSecurityPlugins()
    {
        $result = [];
        $plugins = \get_option('active_plugins');
        // @codeCoverageIgnoreStart
        if (!\defined('PHPUNIT_FILE')) {
            require_once ABSPATH . '/wp-admin/includes/plugin.php';
        }
        // @codeCoverageIgnoreEnd
        foreach ($plugins as $pluginFile) {
            foreach (self::SECURITY_PLUGINS_BLOCKING_REST_API as $slug) {
                if (\strpos($pluginFile, $slug, 0) === 0) {
                    $result[] = \get_plugin_data(\constant('WP_PLUGIN_DIR') . '/' . $pluginFile)['Name'];
                }
            }
        }
        return $result;
    }
    /**
     * Get the wp-json URL for a defined REST service.
     *
     * @param string $instance The plugin class instance, so we can determine the slug from
     * @param string $namespace The prefix for REST service
     * @param string $endpoint The path appended to the prefix
     * @return string Example: https://wordpress.org/wp-json
     */
    public static function getUrl($instance, $namespace = null, $endpoint = '')
    {
        $path = ($namespace === null ? Service::getNamespace($instance) : $namespace) . '/' . $endpoint;
        return \rest_url($path);
    }
    /**
     * Get the default namespace of this plugin generated from the slug.
     *
     * @param mixed $instance The plugin class instance, so we can determine the slug from
     * @param string $version The version used for this namespace
     * @return string
     */
    public static function getNamespace($instance, $version = 'v1')
    {
        $slug = $instance->getPluginConstant(Constants::PLUGIN_CONST_SLUG);
        $slug = \preg_replace('/-(lite|pro)$/', '', $slug);
        return $slug . '/' . $version;
    }
    /**
     * Get the (backend) API URL for the current running environment for another container.
     *
     * @param string $serviceName E.g. commerce
     * @codeCoverageIgnore Ignored due to the fact that it depends on too much server variables
     */
    public static function getExternalContainerUrl($serviceName)
    {
        $host = 'devowl.io';
        // In our development / review environment we always have prefixed our WordPress container as `wordpress.` subdomain
        if (\defined('DEVOWL_WP_DEV') && \constant('DEVOWL_WP_DEV')) {
            $host = \parse_url(\site_url(), \PHP_URL_HOST);
            $host = \substr($host, \strlen('WordPress.'));
        }
        // Internal hook
        return \apply_filters('DevOwl/Utils/Service/ExternalContainerUrl', \trailingslashit(\sprintf('https://%s.%s', $serviceName, $host)), $serviceName, $host);
    }
    /**
     * Get the offset for the obfuscate mechanism. This can be a string or a number. This allows
     * uniqueness of obfuscated strings between WordPress instances.
     *
     * @param mixed $instance The plugin class instance, so we can determine the slug from
     */
    public static function getObfuscateOffset()
    {
        $salt = Utils::getNonceSalt();
        if (empty($salt)) {
            return '';
        }
        /**
         * This filter allows to enable or disable the obfuscated `wp-json/...` pathes.
         *
         * Currently, the obfuscating is disabled by default for the following plugins:
         *
         * ### WP Cerber (https://wordpress.org/plugins/wp-cerber/)
         *
         * Technically, WP Cerber uses `$_REQUEST['rest_route']` instead of
         * https://github.com/WordPress/WordPress/blob/fe6b65c44b929ee5583f6146d92388ef43061f9a/wp-includes/rest-api.php#L390,
         * which is the default WordPress API to do so. Also, the `cerber_access_control()` function, which blocks the REST API requests,
         * is executed at `init` time, instead of https://developer.wordpress.org/reference/hooks/rest_api_init/ or even better:
         * https://developer.wordpress.org/reference/hooks/rest_authentication_errors/
         *
         * ### mqTranslate (https://wordpress.org/plugins/mqtranslate/)
         *
         * It modifies the way how the `request` hook works to rewrite translation URLs. As it is an outdated plugin, we simply deactivate
         * the obfuscation for this plugin.
         *
         * @hook DevOwl/Utils/RestObfuscatePath
         * @example <caption>Disable REST obfuscation</caption>
         * <?php
         * add_filter('DevOwl/Utils/RestObfuscatePath', '__return_false');
         * @param {boolean} $enabled
         * @since 1.13.3
         */
        $enableObfuscation = \apply_filters('DevOwl/Utils/RestObfuscatePath', !\defined('CERBER_PLUGIN_ID') && !\is_plugin_active('mqtranslate/mqtranslate.php'));
        if (!$enableObfuscation) {
            return '';
        }
        // As the salt should not be exposed to public, but the obfuscate-offset will be,
        // we take only the first x characters and hash it. It should be enough for uniqueness.
        return \substr(\md5(\substr($salt, 0, 6)), 0, 16);
    }
    /**
     * A helper function which obfuscates a given path string with a given offset.
     *
     * @param string $offset
     * @param string $str
     * @param string $mode Can be `full` or `keep-last-part`
     */
    public static function obfuscatePath($offset, $str, $mode = 'keep-last-part')
    {
        $parts = \explode('/', $str);
        $result = [];
        foreach ($parts as $i => $part) {
            if ($mode === 'keep-last-part' && $i === \count($parts) - 1) {
                $result[] = $part;
                continue;
            }
            $result[] = Utils::simpleObfuscate($part, $offset, \true);
        }
        $modeInt = $mode === 'full' ? 1 : 0;
        \array_splice($result, \count($result) - 1, 0, "{$modeInt}{$offset}");
        return \implode('/', $result);
    }
    /**
     * A helper function which obfuscates a given path string with a given offset.
     *
     * @param string $offset
     * @param string $str
     */
    public static function deObfuscatePath($offset, $str)
    {
        $result = \explode('/', $str);
        foreach ($result as &$part) {
            if (!empty($part)) {
                $part = Utils::simpleObfuscate($part, $offset, \false);
            }
        }
        return \join('/', $result);
    }
    /**
     * When saving JSON in a registered post meta we need to convert the JavaScript `JSON.stringify`ied result
     * to a valid PHP encoded JSON string with backslashes:
     *
     * ```
     * json_encode("https://")      // -> "https:\/\/"
     * JSON.stringify("https://)    // -> "https://"
     * ```
     *
     * You only need to use this `sanitize_callback` when your meta value contains slashes `/`, otherwise you could
     * get error messages like `Could not update the meta value of %s in database.`.
     *
     * Example usage:
     *
     * ```php
     * register_meta('post', self::META_NAME_TECHNICAL_DEFINITIONS, [
     *     'object_subtype' => self::CPT_NAME,
     *     'type' => 'string',
     *     'single' => true,
     *     'sanitize_callback' => [Service::class, 'sanitizePostMetaJson'],
     *     'show_in_rest' => true
     * ]);
     * ```
     *
     * @param string $value
     * @see https://github.com/WordPress/WordPress/blob/99366f31d23cdbad3296fa78d7813e2d3664790a/wp-includes/rest-api/fields/class-wp-rest-meta-fields.php#L372-L379
     * @see https://app.clickup.com/t/861n4602e
     * @deprecated Do not use this, use `register_meta` with `type=object|array` instead!
     */
    public static function sanitizePostMetaJson($value)
    {
        if (Utils::isRest() && Utils::isJson($value)) {
            return \json_encode(\json_decode($value));
        }
        return $value;
    }
    /**
     * Get a new instance of Service.
     *
     * @param PluginReceiver $core
     * @return Service
     * @codeCoverageIgnore Instance getter
     */
    public static function instance($core)
    {
        return new Service($core);
    }
}
