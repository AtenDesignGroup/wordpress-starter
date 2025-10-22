<?php

namespace MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\Freemium;

use MatthiasWeb\RealMediaLibrary\Vendor\MatthiasWeb\Utils\Base;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request.
// @codeCoverageIgnoreEnd
/**
 * Extends the UtilsProvider with freemium provider.
 * @internal
 */
trait FreemiumProvider
{
    /**
     * Is the current using plugin Pro version?
     *
     * @return boolean
     */
    public function isPro()
    {
        /**
         * This trait always needs to be used along with base trait.
         *
         * @var Base
         */
        $base = $this;
        return $base->getPluginConstant(Constants::PLUGIN_CONST_IS_PRO);
    }
}
