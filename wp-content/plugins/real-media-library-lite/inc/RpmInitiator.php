<?php

namespace MatthiasWeb\RealMediaLibrary;

use MatthiasWeb\RealMediaLibrary\base\UtilsProvider;
use MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealProductManagerWpClient\AbstractInitiator;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * Initiate real-product-manager-wp-client functionality.
 * @internal
 */
class RpmInitiator extends AbstractInitiator
{
    use UtilsProvider;
    /**
     * Documented in AbstractInitiator.
     *
     * @codeCoverageIgnore
     */
    public function getPluginBase()
    {
        return $this;
    }
    /**
     * Documented in AbstractInitiator.
     *
     * @codeCoverageIgnore
     */
    public function getProductAndVariant()
    {
        return [2, $this->isPro() ? 3 : 4];
    }
    /**
     * Documented in AbstractInitiator.
     *
     * @codeCoverageIgnore
     */
    public function getPluginAssets()
    {
        return $this->getCore()->getAssets();
    }
    /**
     * Documented in AbstractInitiator.
     *
     * @codeCoverageIgnore
     */
    public function getPrivacyPolicy()
    {
        return 'https://devowl.io/privacy-policy';
    }
    /**
     * Documented in AbstractInitiator.
     *
     * @codeCoverageIgnore
     */
    public function getLicenseKeyHelpUrl()
    {
        return \__('https://devowl.io/knowledge-base/codecanyon-where-can-i-find-my-license-key/', RML_TD);
    }
    /**
     * Documented in AbstractInitiator.
     *
     * @codeCoverageIgnore
     */
    public function getMigrationOption()
    {
        if ($this->isPro()) {
            $optionName = \sprintf('wpls_license_%s', $this->getPluginSlug());
            $old = \get_site_option($optionName);
            if (empty($old)) {
                return null;
            } else {
                \delete_site_option($optionName);
                return $old;
            }
        }
        return null;
    }
    /**
     * Documented in AbstractInitiator.
     *
     * @codeCoverageIgnore
     */
    public function isExternalUpdateEnabled()
    {
        return $this->isPro();
    }
    /**
     * Documented in AbstractInitiator.
     *
     * @codeCoverageIgnore
     */
    public function isAdminNoticeLicenseVisible()
    {
        return isset($GLOBALS['pagenow']) && $GLOBALS['pagenow'] === 'upload.php';
    }
    /**
     * Documented in AbstractInitiator.
     *
     * @codeCoverageIgnore
     */
    public function isLocalAnnouncementVisible()
    {
        return $this->isAdminNoticeLicenseVisible();
    }
}
