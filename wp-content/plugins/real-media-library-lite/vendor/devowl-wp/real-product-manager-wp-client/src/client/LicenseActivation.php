<?php

namespace MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealProductManagerWpClient\client;

use MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealProductManagerWpClient\PluginUpdate;
use MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealProductManagerWpClient\Utils;
use MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealProductManagerWpClient\UtilsProvider;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * Handle Real Product Manager API calls.
 * @internal
 */
class LicenseActivation
{
    use UtilsProvider;
    const ENDPOINT_LICENSE_ACTIVATION = '1.0.0/license/activation';
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
     * `POST` to the REST API of Real Product Manager.
     *
     * @param string $code
     * @param string $uuid
     * @param string $installationType
     * @param boolean $telemetry
     * @param boolean $newsletterOptIn
     * @param string $firstName
     * @param string $email
     */
    public function post($code = '', $uuid = '', $installationType = 'prod', $telemetry = \false, $newsletterOptIn = \false, $firstName = '', $email = '')
    {
        $ip = Utils::getIpAddress();
        $ip = empty($ip) ? '127.0.0.1' : $ip;
        $product = $this->getPluginUpdate()->getInitiator()->getProductAndVariant();
        $body = ['licenseActivation' => ['license' => ['product' => ['id' => $product[0]], 'productVariant' => ['id' => $product[1]], 'licenseKey' => $code], 'client' => ['uuid' => $uuid, 'properties' => $this->getClientProperties()], 'type' => $installationType, 'telemetryDataSharingOptIn' => $telemetry, 'newsletterOptIn' => $newsletterOptIn, 'ip' => $ip, 'properties' => \array_filter([['key' => 'pluginVersion', 'value' => $this->getPluginUpdate()->getInitiator()->getPluginVersion()], $firstName ? ['key' => 'firstName', 'value' => $firstName] : \false, $email ? ['key' => 'email', 'value' => $email] : \false])]];
        return ClientUtils::request($this->getPluginUpdate()->getInitiator(), self::ENDPOINT_LICENSE_ACTIVATION, $body, 'POST');
    }
    /**
     * `PATCH` to the REST API of Real Product Manager.
     *
     * @param string $code
     * @param string $uuid
     * @param boolean $telemetry
     */
    public function patch($code, $uuid, $telemetry)
    {
        $body = ['licenseActivation' => ['license' => ['licenseKey' => $code], 'client' => ['uuid' => $uuid, 'properties' => $this->getClientProperties()], 'telemetryDataSharingOptIn' => $telemetry, 'properties' => [['key' => 'pluginVersion', 'value' => $this->getPluginUpdate()->getInitiator()->getPluginVersion()]]]];
        return ClientUtils::request($this->getPluginUpdate()->getInitiator(), self::ENDPOINT_LICENSE_ACTIVATION, $body, 'PATCH');
    }
    /**
     * `GET` to the REST API of Real Product Manager.
     *
     * @param string $code
     * @param string $uuid
     */
    public function get($code, $uuid)
    {
        return ClientUtils::request($this->getPluginUpdate()->getInitiator(), self::ENDPOINT_LICENSE_ACTIVATION, ['licenseKey' => $code, 'clientUuid' => $uuid], 'GET');
    }
    /**
     * `DELETE` to the REST API of Real Product Manager.
     *
     * @param string $code
     * @param string $uuid
     */
    public function delete($code, $uuid)
    {
        return ClientUtils::request($this->getPluginUpdate()->getInitiator(), self::ENDPOINT_LICENSE_ACTIVATION, ['licenseActivation' => ['license' => ['licenseKey' => $code], 'client' => ['uuid' => $uuid]]], 'DELETE');
    }
    /**
     * Client properties.
     */
    protected function getClientProperties()
    {
        return [['key' => 'hostname', 'value' => Utils::getCurrentHostname()], ['key' => 'wpVersion', 'value' => \get_bloginfo('version')], ['key' => 'wpLanguage', 'value' => \get_locale()], ['key' => 'phpVersion', 'value' => \phpversion()]];
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
        return new LicenseActivation($pluginUpdate);
    }
}
