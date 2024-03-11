<?php

namespace MatthiasWeb\RealMediaLibrary\overrides\interfce;

use MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\Freemium\ICore;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/** @internal */
interface IOverrideCore extends ICore
{
    /**
     * Additional constructor.
     */
    public function overrideConstruct();
    /**
     * Additional init.
     */
    public function overrideInit();
}
