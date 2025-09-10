<?php

namespace MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\cross;

use MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\UtilsProvider;
use MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\Core;
use MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\TransientHandler;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * An abstract cross-selling implementation which can be used for each pro product of devowl.io.
 * Do not use any constants as they are not available when the plugin is not active.
 * @internal
 */
abstract class AbstractCrossSelling
{
    use UtilsProvider;
    const NEXT_POPUP = '+7 days';
    const NEXT_POPUP_IN_PRO = '+14 days';
    /**
     * Get the slug for this plugin.
     *
     * @return string
     */
    public abstract function getSlug();
    /**
     * Get available popup types. See CrossRealMediaLibrary as example implementation.
     *
     * @return string
     */
    public abstract function getMeta();
    /**
     * Check if the plugin is already installed so the ad can be skipped.
     *
     * @return boolean
     */
    public abstract function skip();
    /**
     * Get the external URL to assets.
     *
     * @param string $path
     */
    public function getAssetsUrl($path = '')
    {
        return Core::getInstance()->getBaseAssetsUrl(\sprintf('wp-%s/%s', $this->getSlug(), $path));
    }
    /**
     * Get or update the action counter for a given action.
     *
     * @param string $action
     * @param boolean $increment
     * @return int
     */
    public function actionCounter($action, $increment = \false)
    {
        $optionName = TransientHandler::TRANSIENT_CROSS_COUNTER . '.' . $this->getSlug() . '.' . $action;
        $cnt = TransientHandler::get(TransientHandler::TRANSIENT_INITIATOR_CROSS, $optionName, 0);
        if ($increment) {
            ++$cnt;
            TransientHandler::set(TransientHandler::TRANSIENT_INITIATOR_CROSS, $optionName, $cnt);
        }
        return $cnt;
    }
    /**
     * Get or update the hidden action status for a given action. This can not be undone if once set.
     *
     * @param string $action
     * @param boolean $force
     * @return boolean
     */
    public function forceHide($action, $force = \false)
    {
        $skip = TransientHandler::get(TransientHandler::TRANSIENT_INITIATOR_CROSS, TransientHandler::TRANSIENT_CROSS_SKIP, []);
        if ($force) {
            $skip[] = $this->getSlug() . '.' . $action;
            TransientHandler::set(TransientHandler::TRANSIENT_INITIATOR_CROSS, TransientHandler::TRANSIENT_CROSS_SKIP, $skip);
        }
        return \in_array($this->getSlug() . '.' . $action, $skip, \true);
    }
    /**
     * Dismiss a cross popup for a product.
     *
     * @param string $action
     * @param boolean $force
     * @return boolean
     */
    public function dismiss($action, $force)
    {
        // Increment dismisses
        $this->actionCounter($action, \true);
        $this->forceHide($action, $force);
        // Update next timestamp
        $ts = \strtotime(Core::getInstance()->getCrossSellingHandler()->isAnyProInstalled() ? self::NEXT_POPUP_IN_PRO : self::NEXT_POPUP);
        if (TransientHandler::set(TransientHandler::TRANSIENT_INITIATOR_CROSS, TransientHandler::TRANSIENT_NEXT_CROSS_SELLING, $ts)) {
            return $ts;
        }
        return \false;
        // @codeCoverageIgnoreStart
    }
    // @codeCoverageIgnoreEnd
}
