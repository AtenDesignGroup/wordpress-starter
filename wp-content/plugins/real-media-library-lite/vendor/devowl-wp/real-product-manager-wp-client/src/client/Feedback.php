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
class Feedback
{
    use UtilsProvider;
    const ENDPOINT_DEACTIVATION_FEEDBACK = '1.0.0/deactivation-feedback';
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
     * @param string $reason
     * @param string $note
     * @param string $email
     * @param string $name
     */
    public function post($reason, $note, $email, $name)
    {
        $initiator = $this->getPluginUpdate()->getInitiator();
        $product = $initiator->getProductAndVariant();
        $version = $initiator->getPluginVersion();
        return ClientUtils::request($initiator, self::ENDPOINT_DEACTIVATION_FEEDBACK, ['deactivationFeedback' => [
            'product' => ['id' => $product[0]],
            'productVariant' => ['id' => $product[1]],
            'productVersion' => $version,
            'reason' => $reason,
            'note' => $note,
            'email' => $email,
            'hasAnswerOptIn' => !empty($email),
            // Only send the IP if the user has accepted our privacy policy
            'ip' => empty($email) ? null : Utils::getIpAddress(),
            'name' => $name,
        ]], 'POST');
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
        return new Feedback($pluginUpdate);
    }
}
