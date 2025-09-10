<?php

namespace MatthiasWeb\RealMediaLibrary\Vendor\MatthiasWeb\Utils;

use MO;
use Translation_Entry;
use WP_Error;
use WP_Textdomain_Registry;
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
/**
 * Base i18n management for backend and frontend.
 * @internal
 */
trait Localization
{
    private $remoteMeta = null;
    /**
     * Get the directory where the languages folder exists.
     *
     * The returned string array should look like this:
     * [0] = Full path to the "languages" folder
     * [1] = Used textdomain
     * [2]? = Use different prefix domain in filename
     *
     * @param string $type
     * @return string[]
     */
    protected abstract function getPackageInfo($type);
    /**
     * Clear the MO cache directory for a given plugin.
     *
     * @param string $slug
     * @param string $domain
     */
    public function clearMoCacheDir($slug, $domain)
    {
        $cacheDir = self::getMoCacheDir($slug);
        if ($cacheDir !== \false) {
            // @codeCoverageIgnoreStart
            if (!\defined('PHPUNIT_FILE')) {
                require_once ABSPATH . 'wp-admin/includes/file.php';
            }
            // @codeCoverageIgnoreEnd
            $files = \list_files($cacheDir);
            if (\is_array($files)) {
                foreach ($files as $file) {
                    @\unlink($file);
                }
            }
            $this->getDownloadLanguagePackError($slug, $domain)->delete();
        }
    }
    /**
     * Obtain language key from a file name.
     *
     * @param string $file
     */
    public function getLanguageFromFile($file)
    {
        $availableLanguages = \get_available_languages();
        $availableLanguages[] = 'en_US';
        \preg_match_all('/-(' . \join('|', $availableLanguages) . ')-/m', \basename($file), $matches, \PREG_SET_ORDER, 0);
        if (\count($matches) === 0) {
            return \false;
        }
        return $matches[0][1];
    }
    /**
     * Cache the results of the `MO` parser in a file in `wp-content/languages/mo-cache` because it is extremely
     * slow on some WordPress installations.
     *
     * @param boolean $plugin_override
     * @param string $domain
     * @param string $mofile
     * @see https://core.trac.wordpress.org/ticket/32052
     * @see https://core.trac.wordpress.org/ticket/17268
     * @see https://app.clickup.com/t/861m3qqb7
     */
    public function override_load_textdomain($plugin_override, $domain, $mofile)
    {
        /**
         * Var.
         *
         * @var WP_Textdomain_Registry $wp_textdomain_registry Check if null as only available since WP 6.1: https://github.com/WordPress/WordPress/commit/bb9f57429a8d3d8b08935f26dc547dcf947b0704
         */
        global $l10n, $l10n_unloaded, $wp_textdomain_registry;
        // Do not override other mechanism
        if ($plugin_override === \false) {
            list($slug, $newmofile) = $this->getMofilePath($mofile, $domain);
            if ($newmofile === \false) {
                return $plugin_override;
            }
            $mofile = $newmofile;
            // Check if folder is writable for this plugin
            $destinationFolder = self::getMoCacheDir($slug);
            if ($destinationFolder === \false) {
                return $plugin_override;
            }
            // Read from existing cache
            $moFileTime = \filemtime($mofile);
            $cachedMoJsonPath = \trailingslashit($destinationFolder) . \md5($mofile . $moFileTime) . '.json';
            $data = \false;
            if (\is_readable($cachedMoJsonPath)) {
                $data = \json_decode(\file_get_contents($cachedMoJsonPath), ARRAY_A);
            }
            // First of all, we need to get the parent `$locale` variable so we can "recall" it
            // See https://github.com/WordPress/wordpress-develop/blob/28f10e4af559c9b4dbbd1768feff0bae575d5e78/src/wp-includes/l10n.php#L718-L733
            // phpcs:disable
            $backtrace = @\debug_backtrace(\DEBUG_BACKTRACE_PROVIDE_OBJECT, 5);
            // phpcs:enable
            $locale = null;
            foreach ($backtrace as $bt) {
                if (isset($bt['function'], $bt['args']) && $bt['function'] === 'load_textdomain' && \count($bt['args']) > 2 && isset($bt['args'][2])) {
                    $locale = $bt['args'][2];
                    break;
                }
            }
            if (!$locale) {
                $locale = \determine_locale();
            }
            // On some environments, `trailingslashit` function is not loaded correctly. Let's require it.
            // See also: https://stackoverflow.com/q/74653740/5506547
            if (!\function_exists('trailingslashit')) {
                require_once ABSPATH . WPINC . '/formatting.php';
            }
            $mo = new MO();
            if (!$data || !isset($data['mtime']) || $moFileTime > $data['mtime']) {
                // Parse MO file, prepare cache and write it
                if (!$mo->import_from_file($mofile)) {
                    if ($wp_textdomain_registry) {
                        $wp_textdomain_registry->set($domain, $locale, \false);
                    }
                    return \false;
                }
                $data = ['mtime' => $moFileTime, 'file' => $mofile, 'entries' => $mo->entries, 'headers' => $mo->headers];
                \file_put_contents($cachedMoJsonPath, \json_encode($data));
            } else {
                // Load from cache, and keep `Translation_Entry` instances intact
                // See https://github.com/WordPress/wordpress-develop/blob/28f10e4af559c9b4dbbd1768feff0bae575d5e78/src/wp-includes/pomo/entry.php
                $mo->entries = [];
                $mo->headers = $data['headers'];
                foreach ($data['entries'] as $key => $entry) {
                    $mo->entries[$key] = new Translation_Entry($entry);
                }
            }
            if (isset($l10n[$domain])) {
                $mo->merge_with($l10n[$domain]);
            }
            unset($l10n_unloaded[$domain]);
            $l10n[$domain] =& $mo;
            if ($wp_textdomain_registry) {
                $wp_textdomain_registry->set($domain, $locale, \dirname($mofile));
            }
            return \true;
        }
        return $plugin_override;
    }
    /**
     * Reuse JSON files from other plugins in the `mo-cache` folder.
     *
     * Why? The download of remote files is currently only triggered for PHP textdomains, but not JSON translations.
     * That means, Real Media Library "requests" the `devowl-wp-real-product-manager-wp-client` textdomain and downloads
     * it to `mo-cache/real-media-library`, so no translations (in this case `fr_FR`) are downloaded for `real-physical-media`.
     * But, in WordPress, frontend packages are not loaded "isolated" and per plugin, they get loaded for the last enqueued plugin.
     *
     * @see https://app.clickup.com/t/2gfb42y?comment=90040016362143
     * @param string $file
     * @param string $handle
     * @param string $domain
     */
    public function load_script_translation_file($file, $handle, $domain)
    {
        if ($file && \is_readable($file)) {
            return $file;
        }
        list(, $packageDomain) = $this->getPackageInfo(Constants::LOCALIZATION_FRONTEND);
        if ($packageDomain === $domain) {
            $cacheDir = $this->getMoCacheDir();
            if ($cacheDir !== \false && \strpos($file, $cacheDir) === 0) {
                $glob = \glob($cacheDir . '/*/' . \basename($file));
                if (\count($glob) > 0) {
                    return $glob[0];
                }
            }
        }
        return $file;
    }
    /**
     * Get the path to the MO file within our plugin, the `wp-content/languages/plugins` or `wp-content/languages/mo-cache` folder.
     * Additionally, it returns the plugin slug which is associated to the passed mofile path.
     *
     * @param string $mofile
     * @param string $domain
     */
    public function getMofilePath($mofile, $domain)
    {
        // Do not run this caching mechanism for other plugins, only ours
        list($packagePath, $packageDomain) = $this->getPackageInfo(Constants::LOCALIZATION_BACKEND);
        if ($domain !== $packageDomain) {
            return [\false, \false];
        }
        // Extract locale from mofile
        $mofileLocale = \explode('-', \basename($mofile));
        $mofileLocale = \explode('.', $mofileLocale[\count($mofileLocale) - 1], 2)[0];
        // Never load `.mo` files from `wp-content/plugins/languages` as we do manage all our translations ourself.
        $avoidPath = \constant('WP_LANG_DIR') . '/plugins/';
        // Path to the language file within our plugin
        $packageFilePath = \trailingslashit($packagePath) . \basename($mofile);
        // Detect plugin slug
        $pluginDir = \constant('WP_PLUGIN_DIR') . \DIRECTORY_SEPARATOR;
        $slug = \strrev(\basename(\strrev(\substr($packageFilePath, \strlen($pluginDir)))));
        // Download offloaded language packs
        $downloaded = $this->downloadLanguagePackForMofile($packageFilePath, $slug, $packageDomain, $mofileLocale);
        if (\is_string($downloaded)) {
            $packageFilePath = $downloaded;
        }
        /**
         * By default, we do not allow to retrieve translations from the official WordPress translation hub as this could
         * lead to issues with freemium software (e.g. PRO-version strings are not translated). All the translation is
         * managed via translate.devowl.io.
         *
         * @hook DevOwl/Utils/Localization/AllowExternalMofile
         * @param {boolean} $allow
         * @param {string} $mofile
         * @param {string} $mofileLocale
         * @since 1.12.29
         */
        if ((\substr($mofile, 0, \strlen($avoidPath)) === $avoidPath || \strpos($mofile, \constant('WP_PLUGIN_DIR') . \DIRECTORY_SEPARATOR . $slug) !== \false) && \is_readable($packageFilePath) && !\apply_filters('DevOwl/Utils/Localization/AllowExternalMofile', \false, $mofile, $mofileLocale)) {
            // Always use our internal translation instead of the `wp-content/languages` folder
            $mofile = $packageFilePath;
        }
        if (!\is_readable($mofile) || \strpos($mofile, $pluginDir) !== 0 && \strpos($mofile, Constants::LOCALIZATION_MO_CACHE_FOLDER) === \false) {
            return [$slug, \false];
        }
        return [$slug, $mofile];
    }
    /**
     * Try to download language pack from a remote server for a given language when our build of the plugin does not hold the translations.
     *
     * @param string $mofile
     * @param string $slug
     * @param string $domain
     * @param string $locale
     * @return WP_Error|false|string
     */
    protected function downloadLanguagePackForMofile($mofile, $slug, $domain, $locale)
    {
        if ($this instanceof PackageLocalization) {
            // Only plugins support remote language packs, but not packages
            // In the build step, all package languages are moved to the plugins root `languages/` folder
            // See also `PackageLocalization::getParentLanguageFolder`
            return \false;
        }
        if (\method_exists($this, 'getPluginConstant')) {
            $downloadError = $this->getDownloadLanguagePackError($slug, $domain);
            $downloadPreviousError = $downloadError->get();
            if ($downloadPreviousError !== \false) {
                return new WP_Error($downloadPreviousError['code'], $downloadPreviousError['message']);
            }
            $remoteMeta = $this->getRemoteMeta();
            if ($remoteMeta === \false) {
                return \false;
            }
            $translations = $remoteMeta['translations'];
            $isPrereleaseVersion = \strpos($this->getPluginConstant(Constants::PLUGIN_CONST_VERSION), '-') !== \false;
            $cacheDir = self::getMoCacheDir($slug);
            if ($cacheDir === \false) {
                return \false;
            }
            // Check if the file is part of the downloaded ZIP file
            $expectedFilename = \basename($mofile);
            $filesInZip = $remoteMeta['zipFiles'][$locale] ?? [];
            if (!\in_array($expectedFilename, $filesInZip, \true)) {
                return \false;
            }
            // Check if it is already downloaded and short circuit
            $expectedFile = \trailingslashit($cacheDir) . $expectedFilename;
            if (\is_readable($expectedFile)) {
                return $expectedFile;
            }
            // Build a list of URLs which should be tried to download the file
            $checksum = $translations[$locale] ?? null;
            if ($checksum === null) {
                return \false;
            }
            $filename = \sprintf('%s-%s.zip', $locale, $checksum);
            $urls = ['prerelease' => [10, $isPrereleaseVersion, \trailingslashit($remoteMeta['prereleaseEndpoint']) . $filename], 'devowl' => [10, \false, \sprintf('https://assets.devowl.io/wp-language-packs/%s/%s', $domain, $filename)], 'wp-org-svn' => [20, \false, \sprintf('https://plugins.svn.wordpress.org/%s/language-packs/%s', $domain, $filename)], 'wp-org-svn-alternative' => [30, \false, \sprintf('https://ps.w.org/%s/language-packs/%s', $domain, $filename)]];
            /**
             * Try to download language pack from a remote server for a given language when our build of the plugin does not hold the translations.
             * This hooks allows you to configure predefined endpoints for the language packs or your own additional language pack endpoint.
             *
             * The `$urls` scheme:
             *
             * ```
             * [
             *   'wp-org-svn' => [
             *      10, // priority
             *      true, // should this endpoint be considered?
             *      "https://plugins.svn.wordpress.org/my-plugin/language-packs/de_DE.zip"
             *   ]
             * ]
             * ```
             *
             * @hook DevOwl/Utils/Localization/LanguagePacks/$slug
             * @param {array} $urls
             * @param {boolean} $isPrereleaseVersion
             * @param {string} $slug
             * @param {string} $domain
             * @param {string} $locale
             * @param {array} $remoteMeta
             * @since 1.16.0
             */
            $urls = \apply_filters('DevOwl/Utils/Localization/LanguagePacks/' . $slug, $urls, $isPrereleaseVersion, $slug, $domain, $locale, $remoteMeta);
            \usort($urls, function ($a, $b) {
                return $a[0] - $b[0];
            });
            if (!\function_exists('download_url')) {
                require_once ABSPATH . 'wp-admin/includes/file.php';
            }
            /**
             * Error.
             *
             * @var WP_Error
             */
            $lastError = null;
            $lastEndpoint = null;
            foreach ($urls as $url) {
                list(, $active, $endpoint) = $url;
                if (!$active) {
                    continue;
                }
                $lastEndpoint = $endpoint;
                $tmp_archive_path = \download_url($endpoint);
                if (\is_wp_error($tmp_archive_path)) {
                    $lastError = $tmp_archive_path;
                    continue;
                }
                // Download successful, do a checksum test
                if (\md5_file($tmp_archive_path) !== $checksum) {
                    \unlink($tmp_archive_path);
                    // Archive no longer needed
                    $lastError = new WP_Error('devowl_download_language_pack_checksum_failed', 'Something went wrong.');
                    continue;
                }
                $unzip = Utils::runDirectFilesystem(function () use($tmp_archive_path, $cacheDir) {
                    return \unzip_file($tmp_archive_path, $cacheDir);
                }, function () use($tmp_archive_path) {
                    \unlink($tmp_archive_path);
                    // Archive no longer needed
                });
                if (\is_wp_error($unzip)) {
                    $lastError = $unzip;
                    continue;
                } elseif (\is_readable($expectedFile)) {
                    // Files are now available in $cacheDir
                    return $expectedFile;
                }
            }
            if ($lastError !== null) {
                $downloadError->set(['code' => $lastError->get_error_code(), 'message' => \sprintf(
                    // translators:
                    \__('Some language packs could not be downloaded for the textdomain %1$s (Error: %2$s, Endpoint: %3$s).', 'devowl-wp-utils'),
                    $domain,
                    $lastError->get_error_message(),
                    $lastEndpoint
                )]);
                return $lastError;
            }
        }
        return \false;
    }
    /**
     * Add filters to WordPress runtime.
     */
    public function hooks()
    {
        \add_action('admin_notices', [$this, 'admin_notices']);
        \add_filter('override_load_textdomain', [$this, 'override_load_textdomain'], 1, 3);
        \add_filter('load_script_translation_file', [$this, 'load_script_translation_file'], 11, 3);
    }
    /**
     * Show an admin notice about failed downloads of remote language packs.
     */
    public function admin_notices()
    {
        if (\method_exists($this, 'getPluginConstant')) {
            $slug = $this->getPluginConstant(Constants::PLUGIN_CONST_SLUG);
            $td = $this->getPluginConstant(Constants::PLUGIN_CONST_TEXT_DOMAIN);
            $downloadError = $this->getDownloadLanguagePackError($slug, $td);
            if (isset($_GET[$downloadError->getName()])) {
                \check_admin_referer($downloadError->getName());
                $downloadError->delete();
            }
            $downloadPreviousError = $downloadError->get();
            if ($downloadPreviousError !== \false) {
                echo \sprintf('<div class="notice notice-warning"><p>%s &bull; <a href="%s">%s</a></p></div>', $downloadPreviousError['message'], \esc_url(\add_query_arg([$downloadError->getName() => \true, '_wpnonce' => \wp_create_nonce($downloadError->getName())])), \__('Retry', 'devowl-wp-utils'));
            }
        }
    }
    /**
     * Get the last error which happened during download of a given mofile.
     *
     * The scheme for the `ExpireOption` matches `[code => string, message => string]`.
     *
     * @param string $slug
     * @param string $domain
     */
    public function getDownloadLanguagePackError($slug, $domain)
    {
        $downloadErrorTransientName = \sprintf('%s_language_pack_error-%s', $slug, $domain);
        $downloadError = new ExpireOption($downloadErrorTransientName, \true, 60 * 60 * 3);
        $downloadError->enableAutoload();
        return $downloadError;
    }
    /**
     * Get the languages which are available in the POT file. Why multiple? Imagine you want to
     * use the pot file for `en_US` and `en_GB`. This can be useful for the `@devowl-wp/multilingual`
     * package, especially the `TemporaryTextDomain` feature.
     */
    public function getPotLanguages()
    {
        return ['en_US', 'en_GB', 'en_CA', 'en_NZ', 'en_AU', 'en'];
    }
    /**
     * Get the remote metadata saved in `languages/meta.json`.
     *
     * @return array|false `false` when meta file cannot be read
     */
    public function getRemoteMeta()
    {
        if ($this->remoteMeta !== null) {
            return $this->remoteMeta;
        }
        list($path) = $this->getPackageInfo(Constants::LOCALIZATION_BACKEND);
        $path = \trailingslashit($path) . 'meta.json';
        if (\is_readable($path)) {
            $this->remoteMeta = \json_decode(\file_get_contents($path), ARRAY_A);
        } else {
            $this->remoteMeta = \false;
        }
        return $this->remoteMeta;
    }
    /**
     * Get the cache directory for cached MO files after parsing. It also checks, if the directory
     * is writable and create the path for a given plugin slug.
     *
     * This function is expensive, so we cached it to `$GLOBALS`.
     *
     * The returned path can be either `wp-content/languages/...` or `wp-includes/languages/...`.
     *
     * @see https://github.com/WordPress/WordPress/blob/34dd52dea760b8a0e81860b010d9f5057fa3c38e/wp-includes/load.php#L617-L651
     * @param string $slug
     */
    public static function getMoCacheDir($slug = '')
    {
        global $devowl_mo_cache_dir;
        $devowl_mo_cache_dir = $devowl_mo_cache_dir ?? [];
        if (!isset($devowl_mo_cache_dir[$slug])) {
            $path = \defined('WP_LANG_DIR') ? \constant('WP_LANG_DIR') : '';
            if (empty($path) || !\is_dir($path)) {
                return \false;
            }
            $path = \trailingslashit($path) . Constants::LOCALIZATION_MO_CACHE_FOLDER . (empty($slug) ? '' : '/' . $slug);
            if (\wp_mkdir_p($path) && \wp_is_writable($path)) {
                $devowl_mo_cache_dir[$slug] = $path;
            } else {
                $devowl_mo_cache_dir[$slug] = '';
            }
        }
        return empty($devowl_mo_cache_dir[$slug]) ? \false : $devowl_mo_cache_dir[$slug];
    }
    /**
     * Enables the download of wordpress.org language packs endpoints.
     *
     * @param string $slug
     * @param string $dotOrgSlug
     */
    public static function enableWordPressDotOrgLanguagePacksDownload($slug, $dotOrgSlug)
    {
        \add_filter('DevOwl/Utils/Localization/LanguagePacks/' . $slug, function ($urls, $isPrereleaseVersion) use($dotOrgSlug) {
            if (!$isPrereleaseVersion) {
                $filename = \basename($urls['wp-org-svn'][2]);
                $urls['wp-org-svn'][1] = \true;
                $urls['wp-org-svn-alternative'][1] = \true;
                $urls['wp-org-svn'][2] = \sprintf('https://plugins.svn.wordpress.org/%s/language-packs/%s', $dotOrgSlug, $filename);
                $urls['wp-org-svn-alternative'][2] = \sprintf('https://ps.w.org/%s/language-packs/%s', $dotOrgSlug, $filename);
            }
            return $urls;
        }, 10, 2);
    }
    /**
     * Enables the download of assets.devowl.io language packs endpoints.
     *
     * @param string $slug
     */
    public static function enableAssetsDotDevowlIoLanguagePacksDownload($slug)
    {
        \add_filter('DevOwl/Utils/Localization/LanguagePacks/' . $slug, function ($urls, $isPrereleaseVersion) {
            if (!$isPrereleaseVersion) {
                $urls['devowl'][1] = \true;
            }
            return $urls;
        }, 10, 2);
    }
}
