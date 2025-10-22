<?php

namespace MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealProductManagerWpClient\license;

use MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealProductManagerWpClient\client\TelemetryData as ClientTelemetryData;
use MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealProductManagerWpClient\UtilsProvider;
use MatthiasWeb\RealMediaLibrary\Vendor\MatthiasWeb\Utils\Utils;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * Handle telemetry data.
 * @internal
 */
class TelemetryData
{
    use UtilsProvider;
    /**
     * License instance.
     *
     * @var License
     */
    private $license;
    /**
     * Telemetry data which gets filled with `appendData`.
     */
    private $data = [];
    /**
     * Determines if the built data expects encoded data (e.g. `json_encode`).
     */
    private $encode = \false;
    /**
     * HTTP client.
     *
     * @var ClientTelemetryData
     */
    private $client;
    /**
     * C'tor.
     *
     * @param License $license
     * @codeCoverageIgnore
     */
    public function __construct($license)
    {
        $this->license = $license;
        $this->client = ClientTelemetryData::instance($license);
    }
    /**
     * Add telemetry data to our existing data set.
     *
     * @param string $key
     * @param mixed $data
     */
    public function add($key, $data)
    {
        $this->data[] = ['key' => $key, 'value' => $this->encode ? ($data === \false ? '0' : \is_scalar($data)) ? \strval($data) : \json_encode($data) : $data];
        return $this;
    }
    /**
     * Get telemetry data (also when deactivated, you need to check this on your own).
     *
     * @param boolean $encode
     */
    public function build($encode = \false)
    {
        $this->encode = $encode;
        $this->getLicense()->getInitiator()->buildTelemetryData($this);
        return $this->data;
    }
    /**
     * Transmit the telemetry data to the license server depending on the state if allowed.
     * This includes in general if user has accepted telemetry data sharing and only once a day.
     */
    public function probablyTransmit()
    {
        $activation = $this->getLicense()->getActivation();
        if ($activation->isTelemetryDataSharingOptIn() && !empty($activation->getCode())) {
            $activation->executeDeferredAction('telemetry', [$this->client, 'put']);
        }
    }
    /**
     * Get a compact list of active plugins.
     */
    public function getActivePlugins()
    {
        $res = [];
        foreach (Utils::getActivePluginsMap() as $file => $name) {
            $res[] = ['slug' => \explode('/', $file)[0], 'file' => $file, 'name' => $name];
        }
        return $res;
    }
    /**
     * Get a compact list of active plugins.
     */
    public function getActiveTheme()
    {
        $theme = \wp_get_theme();
        $file = $theme->get_stylesheet();
        $parent = $theme->parent();
        return \array_merge(['slug' => $file, 'name' => \strval($theme)], $parent !== \false ? ['parentSlug' => $parent->get_stylesheet(), 'parentName' => \strval($parent)] : ['parentSlug' => '', 'parentName' => '']);
    }
    /**
     * Get license instance.
     *
     * @codeCoverageIgnore
     */
    public function getLicense()
    {
        return $this->license;
    }
}
