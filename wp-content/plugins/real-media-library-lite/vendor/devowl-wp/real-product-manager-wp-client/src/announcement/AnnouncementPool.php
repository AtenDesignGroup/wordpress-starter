<?php

namespace MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealProductManagerWpClient\announcement;

use MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealProductManagerWpClient\PluginUpdate;
use MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealProductManagerWpClient\UtilsProvider;
use MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealProductManagerWpClient\view\AnnouncementView;
use MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealProductManagerWpClient\client\Announcement as ClientAnnouncement;
use MatthiasWeb\RealMediaLibrary\Vendor\MatthiasWeb\Utils\ExpireOption;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * Represent a set of announcements synced to the backend (for a given plugin update).
 * A announcement pool is a dependency of the plugin update because they should only be shown,
 * when a valid license is active.
 * @internal
 */
class AnnouncementPool
{
    use UtilsProvider;
    const OPTION_NAME = RPM_WP_CLIENT_OPT_PREFIX . '-announcements';
    const OPTION_EXPIRE = 60 * 60 * 6;
    // 6 hours
    const OPTION_NAME_ENABLED = RPM_WP_CLIENT_OPT_PREFIX . '-announcements-active_';
    const DEFAULT_ENABLED = \true;
    /**
     * Plugin update instance.
     *
     * @var PluginUpdate
     */
    private $pluginUpdate;
    /**
     * View handler.
     *
     * @var AnnouncementView
     */
    private $view;
    /**
     * Expired option for announcement cache.
     *
     * @param ExpireOption
     */
    private $option;
    /**
     * Announcement client.
     *
     * @param ClientAnnouncement
     */
    private $client;
    /**
     * List of all announcements fetched from remote.
     *
     * @var Announcement[]
     */
    private $items;
    /**
     * List of all viewed announcement IDs of remote.
     *
     * @var int[]
     */
    private $viewed = [];
    /**
     * List of all dismissed announcement IDs of remote.
     *
     * @var int[]
     */
    private $dismissed = [];
    /**
     * C'tor.
     *
     * @param PluginUpdate $pluginUpdate
     * @codeCoverageIgnore
     */
    private function __construct($pluginUpdate)
    {
        $this->pluginUpdate = $pluginUpdate;
        $this->view = AnnouncementView::instance($this);
        $this->option = new ExpireOption(self::OPTION_NAME . '_' . $pluginUpdate->getInitiator()->getPluginSlug(), \is_multisite(), self::OPTION_EXPIRE, \true);
        $this->client = ClientAnnouncement::instance($pluginUpdate);
    }
    /**
     * This function should only be called when a valid license was found. This is before the `init` hook was called!
     */
    public function initialize()
    {
        $slug = $this->getPluginUpdate()->getInitiator()->getPluginSlug();
        // Automatically refetch announcements for new updates
        \add_action('DevOwl/Utils/NewVersionInstallation/' . $slug, function () {
            $this->sync(\true);
        });
        // Add an option which represents the active state
        \add_option(self::OPTION_NAME_ENABLED . $slug, self::DEFAULT_ENABLED);
        \add_action('admin_notices', [$this->getView(), 'admin_notices']);
    }
    /**
     * Sync all current available announcements and fetch from remote all x hours.
     * This method may only be called when the user has a valid license!
     *
     * @param boolean $force
     */
    public function sync($force = \false)
    {
        $option = $this->getOption();
        $previousValue = $option->get(\false, \false);
        $value = $option->get();
        if (($value === \false || $force) && $this->isActive()) {
            $remoteItems = $this->getClient()->get();
            $value = [
                // Always reset to no items (we ignore errors for the first)
                'items' => \is_wp_error($remoteItems) ? [] : $remoteItems['announcements'],
                // Restore viewed and dismissed stats always from previous value
                'viewed' => $previousValue === \false ? [] : $previousValue['viewed'],
                'dismissed' => $previousValue === \false ? [] : $previousValue['dismissed'],
            ];
            // Update
            $option->set($value);
            // Models should be recreated
            $this->items = null;
        }
        if ($this->items === null) {
            $this->items = [];
            if (isset($value['items'])) {
                foreach ($value['items'] as $row) {
                    $this->items[] = Announcement::fromResponse($this, $row);
                }
            }
        }
        $this->viewed = $value['viewed'] ?? [];
        $this->dismissed = $value['dismissed'] ?? [];
    }
    /**
     * Sync the view status to the remote server. It automatically uses all
     * valid licenses in a multisite installation.
     *
     * @param Announcement $announcement
     */
    public function syncViewStatus($announcement)
    {
        $id = $announcement->getId();
        $viewed = $this->getViewed();
        // Already viewed? Do nothing.
        if (\in_array($id, $viewed, \true)) {
            return;
        }
        foreach ($this->getPluginUpdate()->getUniqueLicenses() as $license) {
            if (!empty($license->getActivation()->getCode())) {
                $this->getClient()->postView($id, $license->getUuid());
            }
        }
        // Save status in option
        $viewed[] = $id;
        $option = $this->getOption();
        $value = $option->get();
        $value['viewed'] = $viewed;
        $option->set($value);
    }
    /**
     * Dismiss an announcement by ID.
     *
     * @param int $id Announcement ID
     */
    public function dismiss($id)
    {
        $dismissed = $this->getDismissed();
        // Already dismissed? Do nothing.
        if (\in_array($id, $dismissed, \true)) {
            return;
        }
        // Save status in option
        $dismissed[] = $id;
        $option = $this->getOption();
        $value = $option->get();
        $value['dismissed'] = $dismissed;
        $option->set($value);
    }
    /**
     * Check if announcements are currently active (announcements can be disabled by the user).
     *
     * @param boolean $set
     */
    public function isActive($set = null)
    {
        $optionName = self::OPTION_NAME_ENABLED . $this->getPluginUpdate()->getInitiator()->getPluginSlug();
        if ($set !== null) {
            return \update_option($optionName, $set);
        }
        return \boolval(\get_option($optionName, self::DEFAULT_ENABLED)) && $this->getPluginUpdate()->getCurrentBlogLicense()->isFulfilled();
    }
    /**
     * Self-explanatory.
     *
     * @codeCoverageIgnore
     */
    public function getPluginUpdate()
    {
        return $this->pluginUpdate;
    }
    /**
     * Self-explanatory.
     *
     * @codeCoverageIgnore
     */
    public function getView()
    {
        return $this->view;
    }
    /**
     * Self-explanatory.
     *
     * @codeCoverageIgnore
     */
    public function getOption()
    {
        return $this->option;
    }
    /**
     * Self-explanatory.
     *
     * @codeCoverageIgnore
     */
    public function getClient()
    {
        return $this->client;
    }
    /**
     * Self-explanatory.
     *
     * @codeCoverageIgnore
     */
    public function getItems()
    {
        $this->sync();
        return $this->items;
    }
    /**
     * Self-explanatory.
     *
     * @codeCoverageIgnore
     */
    public function getViewed()
    {
        $this->sync();
        return $this->viewed;
    }
    /**
     * Self-explanatory.
     *
     * @codeCoverageIgnore
     */
    public function getDismissed()
    {
        $this->sync();
        return $this->dismissed;
    }
    /**
     * New instance.
     *
     * @param PluginUpdate $pluginUpdate The associated plugin to the announcements
     * @codeCoverageIgnore
     */
    public static function instance($pluginUpdate)
    {
        return new AnnouncementPool($pluginUpdate);
    }
}
