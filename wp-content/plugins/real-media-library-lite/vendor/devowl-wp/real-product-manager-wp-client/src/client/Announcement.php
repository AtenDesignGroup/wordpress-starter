<?php

namespace MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealProductManagerWpClient\client;

use MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealProductManagerWpClient\PluginUpdate;
use MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealProductManagerWpClient\UtilsProvider;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * Handle Real Product Manager API calls.
 * @internal
 */
class Announcement
{
    use UtilsProvider;
    const ENDPOINT_ANNOUNCEMENT = '1.0.0/announcement';
    const ENDPOINT_ANNOUNCEMENT_VIEW = '1.0.0/announcement/view';
    /**
     * PluginUpdate instance.
     *
     * @var PluginUpdate
     */
    private $pluginUpdate;
    /**
     * C'tor.
     *
     * @param PluginUpdate $pluginUpdate
     * @codeCoverageIgnore
     */
    private function __construct($pluginUpdate)
    {
        $this->pluginUpdate = $pluginUpdate;
    }
    /**
     * `GET` to the REST API of Real Product Manager.
     */
    public function get()
    {
        $initiator = $this->getPluginUpdate()->getInitiator();
        return ClientUtils::request($initiator, self::ENDPOINT_ANNOUNCEMENT, ['productVariantId' => $initiator->getProductAndVariant()[1], 'productVariantVersion' => $initiator->getPluginVersion()], 'GET');
    }
    /**
     * `POST` to the REST API of Real Product Manager.
     *
     * @param int $id Announcement id
     * @param string $uuid User IDs
     */
    public function postView($id, $uuid)
    {
        $initiator = $this->getPluginUpdate()->getInitiator();
        return ClientUtils::request($initiator, self::ENDPOINT_ANNOUNCEMENT_VIEW, ['announcement' => ['id' => $id], 'client' => ['uuid' => $uuid]], 'POST');
    }
    /**
     * Get plugin update instance.
     *
     * @codeCoverageIgnore
     */
    public function getPluginUpdate()
    {
        return $this->pluginUpdate;
    }
    /**
     * New instance.
     *
     * @param PluginUpdate $pluginUpdate
     * @codeCoverageIgnore
     */
    public static function instance($pluginUpdate)
    {
        return new Announcement($pluginUpdate);
    }
}
