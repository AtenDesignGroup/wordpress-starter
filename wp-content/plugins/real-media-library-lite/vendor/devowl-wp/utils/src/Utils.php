<?php

namespace MatthiasWeb\RealMediaLibrary\Vendor\MatthiasWeb\Utils;

use Exception;
use WP_Error;
use WP_Filesystem_Direct;
use WP_Hook;
use WP_Rewrite;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * Utility helpers.
 * @internal
 */
class Utils
{
    /**
     * Run $callback with the $handler disabled for the $hook action/filter
     *
     * @param string $hook filter name
     * @param callable $callback function execited while filter disabled
     * @return mixed value returned by $callback
     * @see https://gist.github.com/westonruter/6647252#gistcomment-2668616
     */
    public static function withoutFilters($hook, $callback)
    {
        global $wp_filter;
        $wp_hook = null;
        // Remove and cache the filter
        if (isset($wp_filter[$hook]) && $wp_filter[$hook] instanceof WP_Hook) {
            $wp_hook = $wp_filter[$hook];
            unset($wp_filter[$hook]);
        }
        $retval = \call_user_func($callback);
        // Add back the filter
        if ($wp_hook instanceof WP_Hook) {
            $wp_filter[$hook] = $wp_hook;
        }
        return $retval;
    }
    /**
     * Checks if the current request is a WP REST API request.
     *
     * Case #1: After WP_REST_Request initialisation
     * Case #2: Support "plain" permalink settings
     * Case #3: It can happen that WP_Rewrite is not yet initialized,
     *          so do this (wp-settings.php)
     * Case #4: URL Path begins with wp-json/ (your REST prefix)
     *          Also supports WP installations in subfolders
     *
     * @returns boolean
     * @author matzeeable
     * @see https://gist.github.com/matzeeable/dfd82239f48c2fedef25141e48c8dc30
     */
    public static function isRest()
    {
        if (\defined('REST_REQUEST') && \constant('REST_REQUEST') || isset($_GET['rest_route']) && \strpos(\trim($_GET['rest_route'], '\\/'), \rest_get_url_prefix(), 0) === 0) {
            return \true;
        }
        // (#3)
        global $wp_rewrite;
        if ($wp_rewrite === null) {
            $wp_rewrite = new WP_Rewrite();
        }
        // (#4)
        $rest_url = \wp_parse_url(\trailingslashit(\rest_url()));
        $current_url = \wp_parse_url(\add_query_arg([]));
        // no `esc_url` needed as only used for checking purposes
        return \strpos($current_url['path'] ?? '/', $rest_url['path'], 0) === 0;
    }
    /**
     * Check if passed string is JSON.
     *
     * @param string $string
     * @param mixed $default
     * @see https://stackoverflow.com/a/6041773/5506547
     * @return array|false
     */
    public static function isJson($string, $default = \false)
    {
        if (\is_array($string)) {
            return $string;
        }
        if (!\is_string($string)) {
            return $default;
        }
        $result = \json_decode($string, ARRAY_A);
        return \json_last_error() === \JSON_ERROR_NONE ? $result : $default;
    }
    /**
     * Get the nonce salt of the current WordPress installation. This one can be used to hash data unique to the WordPress instance.
     *
     * @return string
     */
    public static function getNonceSalt()
    {
        $salt = '';
        /**
         * In some cases, hosting providers generate the salts with lower case constant names.
         * I do not know if this works correctly, as PHP's method to obtain a constant is case-
         * sensitive and e.g. `wp_salt()` also expects an uppercase constant name.
         *
         * If a lowercase constant exists, use it, instead try the uppercase one and throw an error
         * if needed (PHP > 8).
         */
        foreach (['nonce_salt', 'NONCE_SALT'] as $constant) {
            if (\defined($constant)) {
                $salt = \constant($constant);
                break;
            }
        }
        /**
         * For older WordPress versions, WordPress did not have salts (https://api.wordpress.org/secret-key/1.1/).
         * They came with newer version of WordPress (https://api.wordpress.org/secret-key/1.1/salt/). But fortunately,
         * `wp_salt` generates a salt in database if not yet given:
         *
         * https://github.com/WordPress/WordPress/blob/1553e3fa008d331adab1c26d221035fbe1876d1f/wp-includes/pluggable.php#L2455-L2459
         */
        if (empty($salt)) {
            \wp_salt('nonce');
            // Call once to ensure `nonce_salt` is in database
            $salt = \get_site_option('nonce_salt', '');
        }
        return $salt;
    }
    /**
     * Add an option to autoloading with default, and additionally a filter like `boolval`.
     *
     * @param string $optionName
     * @param mixed $default
     * @param callable $filter
     */
    public static function enableOptionAutoload($optionName, $default, $filter = null)
    {
        $doIt = function () use($optionName, $default, $filter) {
            // Avoid overwriting and read current
            $currentValue = \get_option($optionName, $default);
            $newValue = $filter === null ? $currentValue : \call_user_func($filter, $currentValue);
            \add_option($optionName, $newValue);
            if ($filter !== null) {
                \add_filter('option_' . $optionName, $filter);
            }
        };
        if (\did_action('init')) {
            $doIt();
        } else {
            \add_action('init', $doIt);
        }
    }
    /**
     * Allows you to find a hook by criteria and suspend the filter. In general, it returns
     * two closures, the first one allows you to suspend and the second one to continue.
     *
     * @param string $hook The hook name.
     * @param callable $criteriaFilter Arguments: function($function, $acceptedArgs, $priority)
     */
    public static function suspenseHook($hook, $criteriaFilter)
    {
        global $wp_filter;
        $found = [];
        if (isset($wp_filter[$hook])) {
            foreach ($wp_filter[$hook] as $priority => $hook_callbacks) {
                foreach ($hook_callbacks as $callback) {
                    $function = $callback['function'];
                    $acceptedArgs = $callback['accepted_args'] ?? 1;
                    if ($criteriaFilter($function, $acceptedArgs, $priority)) {
                        $found[] = [$hook, $function, $acceptedArgs, $priority];
                    }
                }
            }
        }
        return new class($found)
        {
            private $found;
            // C'tor.
            public function __construct($found)
            {
                $this->found = $found;
            }
            /**
             * Suspense the hook.
             */
            public function suspense()
            {
                $result = [];
                foreach ($this->found as $found) {
                    list($hook, $function, $acceptedArgs, $priority) = $found;
                    $result[] = \remove_filter($hook, $function, $priority);
                }
                return $result;
            }
            /**
             * Continue the hook.
             */
            public function continue()
            {
                foreach ($this->found as $found) {
                    list($hook, $function, $acceptedArgs, $priority) = $found;
                    \add_filter($hook, $function, $priority, $acceptedArgs);
                }
            }
        };
    }
    /**
     * Run a command temporarily in `direct` filesystem mode. This is helpful when you want
     * to e.g. `unzip_file` a file to the `wp-content/uploads` folder.
     *
     * **Attention**: Make sure that the folder you want to interact with is writable by the
     * PHP FPM user.
     *
     * @param callback $callback Arguments: WP_Filesystem_Direct $fs
     * @param callback $teardown This is always called, e.g. to remove a temporary archive file
     * @return mixed|WP_Error The result of the callback or `WP_Error` when exception occurs
     */
    public static function runDirectFilesystem($callback, $teardown = null)
    {
        global $wp_filesystem;
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
        require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';
        \WP_Filesystem();
        $currentFilesystem = $wp_filesystem;
        $result = null;
        try {
            if (!$currentFilesystem instanceof WP_Filesystem_Direct) {
                // Set the permission constants if not already set.
                // Why? At this time, it is not ensured, that `FS_CHMOD_DIR` and `FS_CHMOD_FILE` is
                // defined as it could be "prevent" by `$wp_filesystem->errors->has_errors()` and the
                // associated `return false;`.
                // See https://github.com/WordPress/WordPress/blob/8ce9f7e74a983832494aff578c222e09d85351e6/wp-admin/includes/file.php#L2134
                // Defaults: https://github.com/WordPress/WordPress/blob/8ce9f7e74a983832494aff578c222e09d85351e6/wp-admin/includes/file.php#L2142-L2148
                if (!\defined('FS_CHMOD_DIR')) {
                    \define('FS_CHMOD_DIR', \fileperms(ABSPATH) & 0777 | 0755);
                }
                if (!\defined('FS_CHMOD_FILE')) {
                    \define('FS_CHMOD_FILE', \fileperms(ABSPATH . 'index.php') & 0777 | 0644);
                }
                $wp_filesystem = new WP_Filesystem_Direct(null);
            }
            $result = $callback($wp_filesystem);
        } catch (Exception $e) {
            $result = new WP_Error('run_direct_filesystem_error', $e->getMessage());
        } finally {
            // Reset to original
            $wp_filesystem = $currentFilesystem;
            if (\is_callable($teardown)) {
                $teardown();
            }
        }
        return $result;
    }
    /**
     * Get the list of active plugins in a map: File => Name. This is needed for the config and the
     * notice for `skip-if-active` attribute in cookie opt-in codes.
     *
     * @param boolean $includeSlugs
     * @param callable $filter
     */
    public static function getActivePluginsMap($includeSlugs = \true, $filter = null)
    {
        $result = [];
        $plugins = \array_merge(\get_option('active_plugins'), \is_multisite() ? \array_keys(\get_site_option('active_sitewide_plugins')) : []);
        foreach ($plugins as $pluginFile) {
            $pluginFilePath = \constant('WP_PLUGIN_DIR') . '/' . $pluginFile;
            if (\file_exists($pluginFilePath)) {
                $data = \get_plugin_data($pluginFilePath);
                if ($filter !== null && $filter($data) === \false) {
                    continue;
                }
                $name = \wp_specialchars_decode($data['Name']);
                $result[$pluginFile] = $name;
                if ($includeSlugs) {
                    $slug = \explode('/', $pluginFile)[0];
                    $result[$slug] = $name;
                }
            }
        }
        return $result;
    }
    /**
     * Join an array of strings together with comma and the last one with `and`.
     *
     * @param string[] $array
     * @param string $andSeparator
     */
    public static function joinWithAndSeparator($array, $andSeparator)
    {
        if (\count($array) > 1) {
            \array_splice($array, \count($array) - 1, 0, ['{{andSeparator}}']);
        }
        return \str_replace(', {{andSeparator}}, ', $andSeparator, \join(', ', $array));
    }
    /**
     * Allows to set an array value by passing a key path like `my.awesome.key`.
     *
     * @param array $array
     * @param string $keyPath
     * @param callable $callback
     */
    public static function arrayModifyByKeyPath(&$array, $keyPath, $callback)
    {
        $keys = \explode('.', $keyPath);
        $current =& $array;
        $pathExists = \true;
        foreach ($keys as $i => $key) {
            if (!isset($current[$key])) {
                $pathExists = \false;
                break;
            }
            if ($i < \count($keys) - 1) {
                $current =& $current[$key];
            }
        }
        // If the path exists, use the callback to set the new value.
        if ($pathExists) {
            $lastKey = \end($keys);
            if (isset($current[$lastKey])) {
                $current[$lastKey] = $callback($current[$lastKey]);
            }
        }
    }
    /**
     * This hash function is used to generate a simple hash from a given string. This is very simple
     * so it can be used in frontend (e.g. Webpack chunk loading).
     *
     * @param string $s
     */
    public static function simpleHash($s)
    {
        $a = 0;
        foreach (\str_split($s) as $char) {
            $charCode = \ord($char);
            // Force PHP to perform integer arithmetic by using bitwise operations.
            // Use & to ensure the result stays within PHP's integer size.
            $a = ($a << 5 & \PHP_INT_MAX) - $a + $charCode;
            // Use a bitwise AND with a large prime number to ensure the result stays within 64-bit bounds
            // and to avoid negative numbers on systems where PHP ints are 64 bits.
            $a = $a & 0x7fffffff;
            // This is the largest 31-bit positive integer
        }
        return $a;
    }
    /**
     * This obfuscate function is used to generate a simple encrypted string from a text and secret. This is very
     * simple so it can be used in frontend (e.g. URL obfuscating). This is not a real encryption as it uses
     * the Vignere Cipher implementation.
     *
     * @param string $input
     * @param string $key The key needs to contain only alphanumeric values, e.g. no spaces
     * @param boolean $encipher
     * @see https://www.programmingalgorithms.com/algorithm/vigenere-cipher/php/
     */
    public static function simpleObfuscate($input, $key, $encipher)
    {
        $keyLen = \strlen($key);
        if (!\ctype_alnum($key)) {
            return '';
        }
        $output = '';
        $nonAlphaCharCount = 0;
        $inputLen = \strlen($input);
        for ($i = 0; $i < $inputLen; ++$i) {
            if (\ctype_alpha($input[$i])) {
                $cIsUpper = \ctype_upper($input[$i]);
                $offset = \ord($cIsUpper ? 'A' : 'a');
                $keyIndex = ($i - $nonAlphaCharCount) % $keyLen;
                $keyChar = $key[$keyIndex];
                if (\is_numeric($keyChar)) {
                    $k = \intval($keyChar);
                } else {
                    $k = \ord($cIsUpper ? \strtoupper($keyChar) : \strtolower($keyChar)) - $offset;
                }
                $k = $encipher ? $k : -$k;
                $ch = \chr(((\ord($input[$i]) + $k - $offset) % 26 + 26) % 26 + $offset);
                $output .= $ch;
            } else {
                $output .= $input[$i];
                ++$nonAlphaCharCount;
            }
        }
        return $output;
    }
    /**
     * For apache2 servers we need to send the URL without any `?` characters, so we simply remove the query arguments, as this is
     * disallowed by a fix for a [CVE](https://www.cve.org/CVERecord?id=CVE-2024-38474). Additionally, we send another `_wp_http_referer_b64`
     * with base64-encoded URL so we can get the full URL in WordPress.
     *
     * Additionally it adds a trailing `-` to avoid removal of `==` through the browser. You can parse the URL with `parseEncodedRawRefererEncoded()`.
     *
     * @see https://app.clickup.com/t/86954236z
     */
    public static function getRawRefererEncodedForUrl()
    {
        $referer = \wp_get_raw_referer();
        return \is_string($referer) ? \base64_encode($referer) . '-' : \false;
    }
    /**
     * Parse the URL generated by `getRawRefererEncodedForUrl()`.
     *
     * @param string $url
     */
    public static function parseEncodedRawRefererEncoded($url)
    {
        $b64 = \trim($url, '-');
        $b64 = \base64_decode($b64);
        return $b64 !== \false && \filter_var($b64, \FILTER_VALIDATE_URL) ? $b64 : \false;
    }
}
