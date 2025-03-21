<?php

namespace MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealProductManagerWpClient;

use MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealProductManagerWpClient\license\TelemetryData;
use MatthiasWeb\RealMediaLibrary\Vendor\MatthiasWeb\Utils\Base;
use MatthiasWeb\RealMediaLibrary\Vendor\MatthiasWeb\Utils\Assets;
use MatthiasWeb\RealMediaLibrary\Vendor\MatthiasWeb\Utils\Service;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * This is the main class. You need to create an own class extending from
 * this one to initiate the client. The configuration is done by an
 * abstract schema. That means, all configurations need to be implemented through
 * methods.
 * @internal
 */
abstract class AbstractInitiator
{
    // use UtilsProvider; Never do this, because the extended class needs to use their plugins' UtilsProvider
    /**
     * Plugin update instance.
     *
     * @var PluginUpdate
     */
    private $pluginUpdate;
    /**
     * Get the plugin's base instance. It is needed so our initiator can
     * access dynamically constants and configurations.
     *
     * @return Base
     */
    public abstract function getPluginBase();
    /**
     * Get the plugin's product ID and product variant ID.
     *
     * @return int[]
     */
    public abstract function getProductAndVariant();
    /**
     * Get the plugin's assets instance. It is need to enqueue scripts and styles.
     *
     * @return Assets
     */
    public abstract function getPluginAssets();
    /**
     * Get the link to the privacy policy.
     *
     * @return string
     */
    public abstract function getPrivacyPolicy();
    /**
     * Return `true` if you want to enable external updates instead of wordpress.org.
     *
     * @return boolean
     */
    public abstract function isExternalUpdateEnabled();
    /**
     * Returns `true` if the current WordPress installations allows automatic updates for this plugin.
     * Returns `false` if the current plugin is already enabled for auto updates.
     *
     * Attention: We have implemented to only update minor and patch versions, no major versions!
     *
     * @see https://wordpress.org/support/wordpress-version/version-5-5/#security
     * @see https://developer.wordpress.org/reference/hooks/auto_update_type/
     * @see https://wordpress.org/support/article/configuring-automatic-background-updates/#plugin-theme-updates-via-filter
     * @return boolean
     */
    public function isAutoUpdatesEnabled()
    {
        if (!\is_wp_version_compatible('5.5.0') || !\current_user_can('activate_plugins')) {
            return \false;
        }
        $autoUpdates = \get_option('auto_update_plugins');
        if ($autoUpdates === \false) {
            return \true;
        }
        // e.g. WPtimecapsule writes `""` to the option, but it needs to be an array
        if (!\is_array($autoUpdates)) {
            $autoUpdates = [];
        }
        $basename = \plugin_basename($this->getPluginFile());
        return !\in_array($basename, $autoUpdates, \true);
    }
    /**
     * Return `false` if you want to disable sending telemetry data.
     *
     * @return boolean
     */
    public function isTelemetryEnabled()
    {
        return \true;
    }
    /**
     * Return `true` if you want to show a notice in the current admin page when the
     * plugin is not fully licensed. There are multiple texts depending on the day since
     * the first initialization. See also `PluginUpdateView#getAdminNoticeLicenseText`.
     *
     * @return boolean
     */
    public function isAdminNoticeLicenseVisible()
    {
        return \false;
    }
    /**
     * Return `true` if you want to show a local announcement on the current page.
     *
     * @return boolean
     */
    public function isLocalAnnouncementVisible()
    {
        return \false;
    }
    /**
     * Return `false` if you want to disable sending newsletter signup.
     *
     * @return boolean
     */
    public function isNewsletterEnabled()
    {
        return \true;
    }
    /**
     * Allows you to build telemetry data.
     *
     * @param TelemetryData $telemetry
     */
    public function buildTelemetryData($telemetry)
    {
        // Silence is golden.
    }
    /**
     * Allows to configure additional checkboxes in the license activation form.
     *
     * It should return an array in form of:
     *
     * ```
     * [
     *   [
     *     'id' => 'my-checkbox',
     *     'text' => 'This is my awesome checkbox',
     *     'stateFn' => function($state) {
     *       // Do anything with $state
     *       // You can also return `WP_Error`
     *     }
     *   ]
     * ]
     * ```
     *
     * @return array
     */
    public function formAdditionalCheckboxes()
    {
        return [];
    }
    /**
     * Return `false` if you want to disable license per site in multisite.
     * For wordpress.org plugins (free) the multisite-license is disabled.
     *
     * @return boolean
     */
    public function isMultisiteLicensed()
    {
        return $this->isExternalUpdateEnabled();
    }
    /**
     * Get the plugin updater instance.
     *
     * @return PluginUpdate
     */
    public function getPluginUpdater()
    {
        if ($this->pluginUpdate === null) {
            $this->pluginUpdate = PluginUpdate::instance($this);
        }
        return $this->pluginUpdate;
    }
    /**
     * Get the option value if we want to migrate a plugin. Please override with your
     * implementation and simply return the "old" used license key.
     *
     * @return null|string
     */
    public function getMigrationOption()
    {
        return null;
    }
    // Self-explaining
    public function getHost()
    {
        return Service::getExternalContainerUrl('license');
    }
    // Self-explaining
    public function getAccountSiteUrl()
    {
        return \__('https://devowl.io/account', RPM_WP_CLIENT_TD);
    }
    // Self-explaining
    public function getLicenseKeyHelpUrl()
    {
        return \__('https://devowl.io/knowledge-base/devowl-where-can-i-find-my-license-key/', RPM_WP_CLIENT_TD);
    }
    /**
     * Get the privacy provider.
     *
     * @return string
     */
    public function getPrivacyProvider()
    {
        return \parse_url($this->getPrivacyPolicy(), \PHP_URL_HOST);
    }
    /**
     * Initialize all available things depending on the configuration.
     */
    public function start()
    {
        Core::getInstance()->addInitiator($this);
    }
    // Self-explaining
    public function getPluginSlug()
    {
        return $this->getPluginBase()->getPluginConstant('SLUG');
    }
    // Self-explaining
    public function getPluginFile()
    {
        return $this->getPluginBase()->getPluginConstant('FILE');
    }
    // Self-explaining
    public function getPluginVersion()
    {
        return $this->getPluginBase()->getPluginConstant('VERSION');
    }
    // Self-explaining
    public function getPluginName()
    {
        return \get_plugin_data($this->getPluginFile())['Name'];
    }
}
