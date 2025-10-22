<?php

namespace MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealProductManagerWpClient;

use WP_User;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * Utils functionality.
 * @internal
 */
class Utils
{
    use UtilsProvider;
    /**
     * Check if a string starts with a given needle.
     *
     * @param string $haystack The string to search in
     * @param string $needle The starting string
     * @see https://stackoverflow.com/a/834355/5506547
     */
    public static function startsWith($haystack, $needle)
    {
        if ($haystack === null || $needle === null) {
            return \false;
        }
        $length = \strlen($needle);
        return \substr($haystack, 0, $length) === $needle;
    }
    /**
     * Check if the current page request gets redirected.
     */
    public static function isRedirected()
    {
        foreach (\headers_list() as $line) {
            $header = \strtolower($line);
            if (self::startsWith($header, 'location:')) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * Get the IP address of the current request.
     */
    public static function getIpAddress()
    {
        if (isset($_SERVER['HTTP_X_REAL_IP'])) {
            return \sanitize_text_field(\wp_unslash($_SERVER['HTTP_X_REAL_IP']));
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            // `HTTP_X_FORWARDED_FOR` can contain multiple IPs
            return (string) \rest_is_ip_address(\trim(\current(\preg_split('/,/', \sanitize_text_field(\wp_unslash($_SERVER['HTTP_X_FORWARDED_FOR']))))));
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            return \sanitize_text_field(\wp_unslash($_SERVER['REMOTE_ADDR']));
        }
        return null;
    }
    /**
     * Get the full name for the currently logged in user.
     *
     * @param WP_User $user Fall back to current user
     */
    public static function getUserFullName($user = null)
    {
        $user = $user === null ? \wp_get_current_user() : $user;
        if ($user instanceof WP_User) {
            $names = [];
            if (!empty($user->first_name)) {
                $names[] = $user->first_name;
            }
            if (!empty($user->last_name)) {
                $names[] = $user->last_name;
            }
            return \count($names) > 0 ? \join(' ', $names) : '';
        }
        return '';
    }
    /**
     * Get the raw value of a `wp_options` value by respecting the object cache. It is not modified
     * through option-filters.
     *
     * @param string $optionName
     * @param mixed $default
     */
    public static function getOptionRaw($optionName, $default = \false)
    {
        // Force so the options cache is filled
        \get_option($optionName);
        // Directly read from our cache cause we want to skip `site_url` / `option_site_url` filters (https://git.io/JOnGV)
        // Why `alloptions`? Due to the fact that `siteurl` is `autoloaded=yes`, it is loaded via `wp_load_alloptions` and filled
        // to the cache key `alloptions`. The filters are used by WPML and PolyLang but we do not care about them. Non-autoloaded
        // options are read from `notoptions`.
        foreach (['alloptions', 'notoptions'] as $cacheKey) {
            $cache = \wp_cache_get($cacheKey, 'options');
            if (\is_array($cache) && isset($cache[$optionName])) {
                return \maybe_unserialize($cache[$optionName]);
            }
        }
        // Fallback to directly read the option from the `options` cache
        $directFromCache = \wp_cache_get($optionName, 'options');
        if ($directFromCache !== \false) {
            return \maybe_unserialize($directFromCache);
        }
        return $default;
    }
    /**
     * Get current home url, normalized without schema, `www` subdomain and path.
     * This avoids general conflicts for situations, when customers move their
     * HTTP site to HTTPS.
     *
     * @return string Can be empty, e.g. for WP CLI and WP Cronjob when Object Cache is active
     */
    public static function getCurrentHostname()
    {
        // Multisite subdomain installations are forced to use the `home_url` option
        // See also https://github.com/WordPress/WordPress/blob/4e4016f61fa40abda4c0a0711496f2ba50a10563/wp-includes/ms-blogs.php#L249
        $isMultisiteSubdomainInstallation = \is_multisite() && \defined('SUBDOMAIN_INSTALL') && \constant('SUBDOMAIN_INSTALL');
        if (!$isMultisiteSubdomainInstallation && \defined('WP_SITEURL')) {
            // Constant is defined (https://wordpress.org/support/article/changing-the-site-url/#edit-wp-config-php)
            $site_url = \constant('WP_SITEURL');
        } else {
            $site_url = self::getOptionRaw('siteurl', \site_url());
        }
        $url = \parse_url($site_url, \PHP_URL_HOST);
        $url = \preg_replace('/^www\\./', '', $url);
        // Remove default ports (https://regex101.com/r/eyxvPE/1)
        $url = \preg_replace('/:(80|443)$/', '', $url);
        $url = \strtolower($url);
        /**
         * This filter allows you to connect multiple hosts / subdomains to one main host and reduce the number of needed license keys.
         *
         * This filter is only allowed to link thematically, related subdomains / domains, so that one license key is valid for these subdomains / domains.
         *
         * ### Use case example: Multilingual through multisite
         *
         * You have a multisite and each subsite represents a language. When opening the license activation in dialog and you need to
         * activate a license for each language:
         *
         * - English (example.com)
         * - German (de.example.com)
         * - Spanish (es.example.com)
         *
         * You can add the following filter to your `functions.php` to map `de.` and `es.` to the main host `example.com`
         *
         * ```php
         * <?php
         * add_filter('DevOwl/RealProductManager/HostMap/ConnectThematic', function($host) {
         *   return "example.com";
         * });
         * ```
         *
         * Afterward, only one license is requested for `example.com`.
         *
         * ### Use case example: Shop and blog
         *
         * You have a multisite and there you maintain pages for different organizations. Each organization has a blog and a store.
         * When opening the license activation in dialog and you need to activate a license for each language:
         *
         * - Main (example.com)
         * - Organization 1 (org1.com)
         * - Organization 1 shop (shop.org1.com)
         * - Organization 2 (org2.com)
         * - Organization 2 shop (shop.org2.com)
         *
         * Now, instead of needed two license keys for each organization you can connect it thematically:
         *
         * ```php
         * <?php
         * add_filter('DevOwl/RealProductManager/HostMap/ConnectThematic', function($host) {
         *   if ($host === 'shop.org1.com') {
         *     return 'org1.com';
         *   }
         *
         *   if ($host === 'shop.org2.com') {
         *     return 'org2.com';
         *   }
         *
         *   return $host;
         * });
         * ```
         *
         * Afterward, only three licenses are requested for `example.com`, `org1.com` and `org2.com`.
         *
         * @hook DevOwl/RealProductManager/HostMap/ConnectThematic
         * @param {string} $host
         * @param {int} $blogId
         * @since 1.7.9
         * @return {string}
         * @see https://devowl.io/knowledge-base/licensing-of-multiple-websites/
         */
        return \apply_filters('DevOwl/RealProductManager/HostMap/ConnectThematic', $url, \get_current_blog_id());
    }
    /**
     * To avoid issues with multisites without own domains, we need to map blog ids
     * to their `site_url`'s host so we can determine the used license for a given blog.
     *
     * @param int[] $blogIds
     */
    public static function mapBlogsToHosts($blogIds)
    {
        // Map blog ids to potential hostnames and reverse
        $hostnames = ['blog' => [], 'host' => []];
        $isMu = \is_multisite();
        foreach ($blogIds as $blogId) {
            if ($isMu) {
                \switch_to_blog($blogId);
            }
            $host = self::getCurrentHostname();
            $hostnames['blog'][$blogId] = $host;
            $hostnames['host'][$host][] = $blogId;
            if ($isMu) {
                \restore_current_blog();
            }
        }
        return $hostnames;
    }
}
