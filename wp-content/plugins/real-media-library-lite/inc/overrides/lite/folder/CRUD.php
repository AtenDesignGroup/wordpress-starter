<?php

namespace MatthiasWeb\RealMediaLibrary\lite\folder;

use MatthiasWeb\RealMediaLibrary\exception\OnlyInProVersionException;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/** @internal */
trait CRUD
{
    // Documented in wp_rml_create_p()
    public function createRecursively($name, $parent, $type, $restrictions = [], $supress_validation = \false)
    {
        return new OnlyInProVersionException(__METHOD__);
    }
}
