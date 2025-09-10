<?php

namespace MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealProductManagerWpClient;

use MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealProductManagerWpClient\announcement\AnnouncementPool;
use MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealProductManagerWpClient\license\PluginUpdateChecker;
use MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealProductManagerWpClient\license\PluginUpdateLicensePool;
use MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealProductManagerWpClient\view\PluginUpdateView;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * Plugin update and license handling. If the plugin is hosted on wordpress.org, this
 * class never does anything. But notice, that wordpress.org plugins also needs to "Complete
 * the setup" and get a "free license" from the RPM license server.
 * @internal
 */
class PluginUpdate
{
    use UtilsProvider;
    use PluginUpdateLicensePool;
    const OPTION_NAME_FIRST_INITIALIZATION_PREFIX = RPM_WP_CLIENT_OPT_PREFIX . '-puFirst_';
    const ERROR_CODE_INVALID_LICENSES = 'rpm_wpc_invalid_licenses';
    const ERROR_CODE_INVALID_NEWSLETTER = 'rpm_wpc_invalid_newsletter';
    const ERROR_CODE_BLOG_NOT_FOUND = 'rpm_wpc_blog_not_found';
    const ERROR_CODE_NONE_IN_USAGE = 'rpm_wpc_none_in_usage';
    const ERROR_CODE_INVALID_KEYS = 'rpm_wpc_invalid_keys';
    /**
     * View handler.
     *
     * @var PluginUpdateView
     */
    private $view;
    /**
     * Initiator for this plugin.
     *
     * @var AbstractInitiator
     */
    private $initiator;
    /**
     * Announcement pool
     *
     * @var AnnouncementPool
     */
    private $announcementPool;
    /**
     * Plugin Update Checker instance.
     *
     * @var PluginUpdateChecker
     */
    private $pluginUpdateChecker;
    /**
     * C'tor.
     *
     * @param AbstractInitiator $initiator
     * @codeCoverageIgnore
     */
    private function __construct($initiator)
    {
        $this->initiator = $initiator;
        $this->view = PluginUpdateView::instance($this);
        $this->announcementPool = AnnouncementPool::instance($this);
        $this->pluginUpdateChecker = PluginUpdateChecker::instance($this);
        $this->constructPluginUpdateLicensePool();
    }
    /**
     * Initialize PRO updates for this plugin. This is before the `init` hook was called!
     */
    public function initialize()
    {
        $initiator = $this->getInitiator();
        // Add an option which represents the existence of the first time this plugin updater was used
        \add_option(self::OPTION_NAME_FIRST_INITIALIZATION_PREFIX . $initiator->getPluginSlug(), \time());
        \add_action('init', [$this, 'init']);
        \add_filter('auto_update_plugin', [$this, 'auto_update_plugin'], 10, 2);
        \add_filter('DevOwl/Utils/Localization/LanguagePacks/' . $initiator->getPluginSlug(), [$this, 'language_packs'], 10, 2);
        //wp_maybe_auto_update(); // For testing purposes, trigger auto update
        $this->getAnnouncementPool()->initialize();
    }
    /**
     * `init` hook which does e.g. automatically activate licenses depending on a WordPress hook.
     */
    public function init()
    {
        $initiator = $this->getInitiator();
        $basename = \plugin_basename($initiator->getPluginFile());
        \add_filter('plugin_action_links_' . $basename, [$this->getView(), 'plugin_action_links']);
        \add_filter('network_admin_plugin_action_links_' . $basename, [$this->getView(), 'plugin_action_links']);
        if ($initiator->isExternalUpdateEnabled()) {
            \add_action('after_plugin_row_' . $basename, [$this->getView(), 'after_plugin_row']);
            \add_action('admin_notices', [$this->getView(), 'admin_notices_not_licensed']);
        }
        \add_action('admin_notices', [$this->getView(), 'admin_notices_license_hint']);
        $this->getPluginUpdateChecker()->probablyEnableExternalUpdates();
        // We do not handle the response as the activation automatically creates a warning for our user
        $this->getCurrentBlogLicense()->activateProgrammatically();
    }
    /**
     * For licensed plugin installations, endable the download from assets.devowl.io for language packs.
     *
     * @param array $urls
     * @param boolean $isPrereleaseVersion
     */
    public function language_packs($urls, $isPrereleaseVersion)
    {
        if (!$isPrereleaseVersion && !empty($this->getCurrentBlogLicense()->getActivation()->getCode())) {
            $urls['devowl'][1] = \true;
        }
        return $urls;
    }
    /**
     * Check for auto updates and do not update major versions.
     *
     * @param boolean|null $update
     * @param object|array $item
     * @return boolean|null
     */
    public function auto_update_plugin($update, $item)
    {
        // Do only update if the feature is enabled
        if ($update !== \true) {
            return $update;
        }
        $initiator = $this->getInitiator();
        // e.g. Google Site Kit plugin modifies this to an `array`
        if (\is_array($item)) {
            $item = (object) $item;
        }
        if (isset($item->slug) && $item->slug === $initiator->getPluginSlug()) {
            $currentVersion = $initiator->getPluginVersion();
            $currentMajorVersion = \intval(\explode('.', $currentVersion)[0]);
            $newMajorVersion = \intval(\explode('.', $item->new_version)[0]);
            return $currentMajorVersion === $newMajorVersion;
        }
        return $update;
    }
    /**
     * Enable auto updates for this plugin.
     */
    public function enableAutoUpdates()
    {
        $initiator = $this->getInitiator();
        if (!$initiator->isAutoUpdatesEnabled()) {
            return \false;
        } else {
            $autoUpdates = \get_option('auto_update_plugins');
            // e.g. WPtimecapsule writes `""` to the option, but it needs to be an array
            if (!\is_array($autoUpdates)) {
                $autoUpdates = [];
            }
            $autoUpdates[] = \plugin_basename($initiator->getPluginFile());
            return \update_option('auto_update_plugins', $autoUpdates);
        }
    }
    /**
     * Get all blog ids for this WordPress instance (Multisite) which needs a license.
     *
     * @param int[] $inBlogIds If set only return the list of this blog IDs
     * @return int[]
     */
    protected function getPotentialBlogIds($inBlogIds = null)
    {
        // Only needs a license for the main site
        if (\is_multisite() && !$this->getInitiator()->isMultisiteLicensed()) {
            return [\get_network()->site_id];
        }
        $currentBlogId = \get_current_blog_id();
        // Multisite (all blog IDs)
        $blogIds = [];
        if (\function_exists('get_sites') && \class_exists('WP_Site_Query')) {
            $sites = \get_sites(['number' => 0, 'fields' => 'ids']);
            foreach ($sites as $site) {
                $blogId = \intval($site);
                // We do not use `site__in` to cache only a single query
                if (\is_array($inBlogIds) && !\in_array($blogId, $inBlogIds, \true)) {
                    continue;
                }
                $blogIds[] = $blogId;
            }
        } else {
            $blogIds[] = $currentBlogId;
        }
        return $blogIds;
    }
    /**
     * Get the timestamp which represents the existence of the first time this plugin updater was used.
     *
     * @return int
     */
    public function getFirstInitializationTimestamp()
    {
        return \get_option(self::OPTION_NAME_FIRST_INITIALIZATION_PREFIX . $this->getInitiator()->getPluginSlug(), \time());
    }
    /**
     * Get the days since the first initialization. See `#getFirstInitializationTimestamp`. Imagine, you
     * install the plugin today, you will get `1` for today, `2` for tomorrow, and so on.
     */
    public function getDaysSinceFirstInitialization()
    {
        $start = $this->getFirstInitializationTimestamp();
        return \intval(\ceil(\abs(\time() - $start) / 86400));
    }
    /**
     * Get view.
     *
     * @codeCoverageIgnore
     */
    public function getView()
    {
        return $this->view;
    }
    /**
     * Get initiator.
     *
     * @codeCoverageIgnore
     */
    public function getInitiator()
    {
        return $this->initiator;
    }
    /**
     * Get announcement pool.
     *
     * @codeCoverageIgnore
     */
    public function getAnnouncementPool()
    {
        return $this->announcementPool;
    }
    /**
     * Get Plugin Update Checker.
     *
     * @codeCoverageIgnore
     */
    public function getPluginUpdateChecker()
    {
        return $this->pluginUpdateChecker;
    }
    /**
     * New instance.
     *
     * @param AbstractInitiator $initiator
     * @codeCoverageIgnore
     */
    public static function instance($initiator)
    {
        return new PluginUpdate($initiator);
    }
}
