<?php

namespace MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealProductManagerWpClient\client;

use MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealProductManagerWpClient\license\License;
use MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealProductManagerWpClient\UtilsProvider;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * Handle Real Product Manager API calls.
 * @internal
 */
class TelemetryData
{
    use UtilsProvider;
    const ENDPOINT_TELEMETRY = '1.0.0/telemetry';
    /**
     * License instance.
     *
     * @var License
     */
    private $license;
    /**
     * C'tor.
     *
     * @param License $license
     * @codeCoverageIgnore
     */
    private function __construct($license)
    {
        $this->license = $license;
    }
    /**
     * `PUT` to the REST API of Real Product Manager.
     *
     * @param License $license
     */
    public function put()
    {
        $license = $this->getLicense();
        $pluginUpdate = $license->getPluginUpdate();
        $built = $license->getTelemetryData()->build(\true);
        // Nothing to send, skip request and return simulated "valid" response
        if (\count($built) === 0) {
            return [];
        }
        $body = ['licenseActivation' => ['license' => ['licenseKey' => $license->getActivation()->getCode()], 'client' => ['uuid' => $license->getUuid()]], 'telemetries' => $built];
        return ClientUtils::request($pluginUpdate->getInitiator(), self::ENDPOINT_TELEMETRY, $body, 'PUT');
    }
    /**
     * Get plugin update instance.
     *
     * @codeCoverageIgnore
     */
    public function getLicense()
    {
        return $this->license;
    }
    /**
     * New instance.
     *
     * @param License $license
     * @codeCoverageIgnore
     */
    public static function instance($license)
    {
        return new TelemetryData($license);
    }
}
