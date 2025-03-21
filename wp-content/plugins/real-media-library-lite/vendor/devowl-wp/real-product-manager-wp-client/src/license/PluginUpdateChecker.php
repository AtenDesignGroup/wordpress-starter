<?php

namespace MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealProductManagerWpClient\license;

use MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealProductManagerWpClient\client\ClientUtils;
use MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealProductManagerWpClient\PluginUpdate;
use MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealProductManagerWpClient\UtilsProvider;
use WP_Error;
use MatthiasWeb\RealMediaLibrary\Vendor\YahnisElsts\PluginUpdateChecker\v5\PucFactory;
use MatthiasWeb\RealMediaLibrary\Vendor\YahnisElsts\PluginUpdateChecker\v5p3\UpdateChecker;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * Enable external updates with the help of PUC. This should be only done in non-free plugins!
 *
 * @see https://github.com/YahnisElsts/plugin-update-checker
 * @internal
 */
class PluginUpdateChecker
{
    use UtilsProvider;
    const ENDPOINT = '1.0.0/wp/product/version';
    /**
     * PluginUpdate instance.
     *
     * @var PluginUpdate
     */
    private $pluginUpdate;
    /**
     * Plugin Update Checker instance.
     *
     * @var UpdateChecker|null
     * @see https://github.com/YahnisElsts/plugin-update-checker
     */
    private $puc;
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
     * Enable it, probably!
     */
    public function probablyEnableExternalUpdates()
    {
        $initiator = $this->getPluginUpdate()->getInitiator();
        if (!$initiator->isExternalUpdateEnabled()) {
            return;
        }
        $puc = PucFactory::buildUpdateChecker($initiator->getHost() . self::ENDPOINT, $initiator->getPluginFile(), $initiator->getPluginSlug());
        // Add our license key to the external request
        $puc->addQueryArgFilter([$this, 'queryArg']);
        // Validate response against license expiration
        $puc->addFilter('request_metadata_http_result', [$this, 'validateResult']);
        $this->puc = $puc;
    }
    /**
     * The metadata JSON can also return error codes in case of license expiration / revoke.
     * Let's check against this and save the error code to our WP instance.
     *
     * @param WP_Error|array $response
     */
    public function validateResult($response)
    {
        $license = $this->getLicense();
        if ($license !== \false) {
            $parsedError = ClientUtils::parseResponse($response);
            $license->validateRemoteResponse($parsedError);
        }
        return $response;
    }
    /**
     * Add the license key to the update request.
     *
     * @param array $queryArgs
     */
    public function queryArg($queryArgs)
    {
        $license = $this->getLicense();
        $product = $this->getPluginUpdate()->getInitiator()->getProductAndVariant();
        $queryArgs['product_id'] = $product[0];
        $queryArgs['product_variant_id'] = $product[1];
        if ($license !== \false) {
            $queryArgs['license_key'] = $license->getActivation()->getCode();
            $queryArgs['client_uuid'] = $license->getUuid();
        }
        return $queryArgs;
    }
    /**
     * Check if Plugin Update Checker is currently active.
     */
    public function isEnabled()
    {
        return $this->getPuc() !== null;
    }
    /**
     * Get the first found license as we do not differ between multiple licenses in
     * a multisite environment. WordPress does not allow to update per-site because a plugin
     * is installed network-wide.
     */
    public function getLicense()
    {
        return $this->getPluginUpdate()->getFirstFoundLicense();
    }
    /**
     * Generate a "Check for Updates" link so users can trigger this manually.
     */
    public function getCheckUpdateLink()
    {
        return $this->isEnabled() ? \add_query_arg(['puc_check_for_updates' => 1, 'puc_slug' => $this->getPluginUpdate()->getInitiator()->getPluginSlug(), '_wpnonce' => \wp_create_nonce('puc_check_for_updates')], \self_admin_url('plugins.php')) : \false;
    }
    /**
     * Get the instance of `Puc_v4_Factory`.
     */
    public function getPuc()
    {
        return $this->puc;
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
        return new PluginUpdateChecker($pluginUpdate);
    }
}
