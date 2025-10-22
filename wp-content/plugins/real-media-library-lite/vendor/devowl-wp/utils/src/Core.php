<?php

namespace MatthiasWeb\RealMediaLibrary\Vendor\MatthiasWeb\Utils;

\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
/**
 * Base class for the applications Core class.
 * @internal
 */
trait Core
{
    /**
     * The stored plugin data.
     */
    private $plugin_data;
    /**
     * The plugins activator class.
     *
     * @var Activator
     */
    private $activator;
    /**
     * The plugins asset class.
     *
     * @var Assets
     */
    private $assets;
    /**
     * The utils service class.
     *
     * @var Service
     */
    private $service;
    /**
     * The constructor handles the core startup mechanism.
     *
     * The constructor is protected because a factory method should only create
     * a Core object.
     */
    protected function construct()
    {
        // Define lazy constants
        $pluginFile = $this->getPluginConstant(Constants::PLUGIN_CONST_FILE);
        // Register immediate actions and filters
        $this->activator = $this->getPluginClassInstance(Constants::PLUGIN_CLASS_ACTIVATOR);
        $this->assets = $this->getPluginClassInstance(Constants::PLUGIN_CLASS_ASSETS);
        $this->service = Service::instance($this);
        \add_action('after_setup_theme', [$this, 'i18n']);
        // `after_setup_theme` cause `functions.php` can add additional hooks for i18n
        \add_action('plugins_loaded', [$this, 'updateDbCheck']);
        \add_action('init', [$this, 'init']);
        \add_action('rest_api_init', [$this->getService(), 'rest_api_init']);
        \add_action('admin_notices', [$this->getService(), 'admin_notices']);
        \add_action('request', [$this->getService(), 'request']);
        // Localize the plugin and package itself
        $this->getPluginClassInstance(Constants::PLUGIN_CLASS_LOCALIZATION)->hooks();
        PackageLocalization::instance($this->getPluginConstant(Constants::PLUGIN_CONST_ROOT_SLUG), \dirname(__DIR__))->hooks();
        \register_activation_hook($pluginFile, [$this->getActivator(), 'registerCapabilities']);
        \register_activation_hook($pluginFile, [$this->getActivator(), 'install']);
        \register_activation_hook($pluginFile, [$this->getActivator(), 'activate']);
        \register_deactivation_hook($pluginFile, [$this->getActivator(), 'deactivate']);
        RateLimitNotice::instance($this)->hooks();
        $this->persistEncodedHttpRefererInUrl();
    }
    /**
     * For apache2 servers we need to send the URL without any `?` characters, so we simply remove the query arguments, as this is
     * disallowed by a fix for a [CVE](https://www.cve.org/CVERecord?id=CVE-2024-38474). Additionally, we send another `_wp_http_referer_b64`
     * with base64-encoded URL so we can get the full URL in WordPress.
     *
     * In general, this is bad practice to modify `$_REQUEST` directly, but we do not yet know another way of doing other.
     *
     * @see https://app.clickup.com/t/86954236z
     */
    protected function persistEncodedHttpRefererInUrl()
    {
        if (isset($_GET['_wp_http_referer_b64'])) {
            $parsedUrl = Utils::parseEncodedRawRefererEncoded($_GET['_wp_http_referer_b64']);
            if ($parsedUrl !== \false) {
                // $_REQUEST['_wp_http_referer'] = $parsedUrl; -> automatically receives on update of `$_GET`
                $_GET['_wp_http_referer'] = $parsedUrl;
                unset($_GET['_wp_http_referer_b64']);
            }
        }
    }
    /**
     * The plugin is loaded. Start to register the localization (i18n) files.
     * Also respect packages in vendor dir.
     */
    public function i18n()
    {
        \load_plugin_textdomain($this->getPluginConstant(Constants::PLUGIN_CONST_TEXT_DOMAIN), \false, \dirname(\plugin_basename($this->getPluginConstant(Constants::PLUGIN_CONST_FILE))) . $this->getPluginData('DomainPath'));
        // Include text domains of packages
        $packages = $this->getInternalPackages();
        $locale = \determine_locale();
        foreach ($packages as $package => $path) {
            $textdomain = $this->getPluginConstant(Constants::PLUGIN_CONST_ROOT_SLUG) . '-' . $package;
            $locale = \apply_filters('plugin_locale', $locale, $textdomain);
            $mofile = $path . '/languages/backend/' . $package . '-' . $locale . '.mo';
            \load_textdomain($textdomain, PackageLocalization::getParentLanguageFolder($mofile, \true));
        }
    }
    /**
     * Updates the database version in the options table.
     * It also installs the needed database tables.
     */
    public function updateDbCheck()
    {
        $activator = $this->getActivator();
        $installed = $activator->getDatabaseVersion();
        if ($installed !== $this->getPluginConstant(Constants::PLUGIN_CONST_VERSION) && !$activator->isMigrationLocked() && $activator->isMigrationLocked(\time())) {
            $slug = $this->getPluginConstant(Constants::PLUGIN_CONST_SLUG);
            $textdomain = $this->getPluginConstant(Constants::PLUGIN_CONST_TEXT_DOMAIN);
            $this->debug('(Re)install the database tables', __FUNCTION__);
            // Clear localization cache for JSON MO files
            $this->getPluginClassInstance(Constants::PLUGIN_CLASS_LOCALIZATION)->clearMoCacheDir($slug, $textdomain);
            $activator->registerCapabilities();
            if ($activator->install()) {
                /**
                 * A new version got installed for this plugin. Consider to use the [`versionCompareOlderThan()`](../php/classes/MatthiasWeb-Utils-Core.html#method_versionCompareOlderThan)
                 * method from the Core class.
                 *
                 * @hook DevOwl/Utils/NewVersionInstallation/$slug
                 * @param {string} $installed Previously version, can be also `null` for new installations
                 */
                \do_action('DevOwl/Utils/NewVersionInstallation/' . $slug, $installed);
                /**
                 * A new version got installed for a plugin. Please use `DevOwl/Utils/NewVersionInstallation/$slug`.
                 *
                 * @hook DevOwl/Utils/NewVersionInstallation
                 * @param {string} $installed Previously version, can be also `null` for new installations
                 * @param {string} $slug
                 */
                \do_action('DevOwl/Utils/NewVersionInstallation', $installed, $slug);
                $activator->isMigrationLocked(0);
            }
        }
    }
    /**
     * Get a list of internal packages (our own, symlinked from the monorepo).
     *
     * @return object
     */
    public function getInternalPackages()
    {
        $result = [];
        $globPath = \trailingslashit($this->getPluginConstant(Constants::PLUGIN_CONST_PATH)) . 'vendor/' . $this->getPluginConstant(Constants::PLUGIN_CONST_ROOT_SLUG) . '/*';
        foreach (\glob($globPath) as $path) {
            $package = \pathinfo($path, \PATHINFO_FILENAME);
            $result[$package] = $path;
        }
        return $result;
    }
    /**
     * Gets the plugin data.
     *
     * @param string $key The key of the data to return
     * @return string[]|string|null
     * @see https://developer.wordpress.org/reference/functions/get_plugin_data/
     */
    public function getPluginData($key = null)
    {
        // @codeCoverageIgnoreStart
        if (!\defined('PHPUNIT_FILE')) {
            require_once ABSPATH . '/wp-admin/includes/plugin.php';
        }
        // @codeCoverageIgnoreEnd
        $data = isset($this->plugin_data) ? $this->plugin_data : ($this->plugin_data = \get_plugin_data($this->getPluginConstant(Constants::PLUGIN_CONST_FILE), \false, \false));
        return $key === null ? $data : (isset($data[$key]) ? $data[$key] : null);
    }
    /**
     * Getter.
     *
     * @return Activator
     * @codeCoverageIgnore
     */
    public function getActivator()
    {
        return $this->activator;
    }
    /**
     * Getter
     *
     * @return Assets
     * @codeCoverageIgnore
     */
    public function getAssets()
    {
        return $this->assets;
    }
    /**
     * Getter
     *
     * @return Service
     * @codeCoverageIgnore
     */
    public function getService()
    {
        return $this->service;
    }
    /**
     * Checks if a previously installed version is lower than an expected version.
     * Additionally, we can check for a prerelease version, too. The `$allowedPrerelease` needs
     * to be an array (e.g. `["2.15.1", "2.16.0"]`), and if the installed version is a prerelease
     * (e.g. `2.15.1-9507`), the version `2.15.1` gets extracted and checked for existence in the array.
     *
     * Usage with `$prereleaseAdditionalCheck`: You can pass an additional callback which is executed before
     * returning `true` when a prerelease got found. An example scenario for this callable is to check for
     * a specific database table if you drop a column. Why is this needed? Imagine you are sending multiple
     * prerelease to a customer.
     *
     * @param string|false $installed
     * @param string $version
     * @param string[] $allowedPrerelease
     * @param callable $prereleaseAdditionalCheck `($prereleaseVersion, $prereleaseCiIid) => boolean`
     */
    public static function versionCompareOlderThan($installed, $version, $allowedPrerelease = [], $prereleaseAdditionalCheck = null)
    {
        if ($installed) {
            $prerelease = \explode('-', $installed, 2);
            $prereleaseVersion = \count($prerelease) > 1 ? $prerelease[0] : null;
            $prereleaseCiIid = \count($prerelease) > 1 && \is_numeric($prerelease[1]) ? \intval($prerelease[1]) : null;
            if (\version_compare($installed, $version, '<=') || $prereleaseVersion === $version) {
                return \true;
            }
            if (\count($allowedPrerelease) > 0 && $prereleaseVersion !== null) {
                if (\in_array($prereleaseVersion, $allowedPrerelease, \true)) {
                    if (\is_callable($prereleaseAdditionalCheck)) {
                        return $prereleaseAdditionalCheck($prereleaseVersion, $prereleaseCiIid);
                    }
                    return \true;
                }
            }
        }
        return \false;
    }
}
