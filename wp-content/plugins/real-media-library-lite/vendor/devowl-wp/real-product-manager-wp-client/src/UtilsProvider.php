<?php

namespace MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealProductManagerWpClient;

use MatthiasWeb\RealMediaLibrary\Vendor\MatthiasWeb\Utils\Base;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * Let our package act as own "plugin".
 * @internal
 */
trait UtilsProvider
{
    use Base;
    /**
     * Get the prefix of this package so we can utils package natively.
     *
     * @return string
     */
    public function getPluginConstantPrefix()
    {
        self::setupConstants();
        return 'RPM_WP_CLIENT';
    }
    /**
     * Make sure the RPM_WP_CLIENT constants are available.
     */
    public static function setupConstants()
    {
        if (\defined('RPM_WP_CLIENT_SLUG')) {
            return;
        }
        \define('RPM_WP_CLIENT_SLUG', 'real-product-manager-wp-client');
        \define('RPM_WP_CLIENT_ROOT_SLUG', 'devowl-wp');
        \define('RPM_WP_CLIENT_TD', RPM_WP_CLIENT_ROOT_SLUG . '-' . RPM_WP_CLIENT_SLUG);
        \define('RPM_WP_CLIENT_SLUG_CAMELCASE', \lcfirst(\str_replace('-', '', \ucwords(RPM_WP_CLIENT_SLUG, '-'))));
        \define('RPM_WP_CLIENT_VERSION', \filemtime(__FILE__));
        // as we do serve assets through the consumer plugin we can safely use file modified time
        \define('RPM_WP_CLIENT_OPT_PREFIX', 'rpm-wpc');
    }
}
