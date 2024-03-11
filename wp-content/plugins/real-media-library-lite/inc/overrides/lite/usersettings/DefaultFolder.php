<?php

namespace MatthiasWeb\RealMediaLibrary\lite\usersettings;

// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/** @internal */
trait DefaultFolder
{
    // Documented in IOverrideDefaultFolder
    public function overrideConstruct()
    {
        // Silence is golden.
    }
    // Documented in IMetadata
    public function overrideSave($response, $user, $request)
    {
        return $response;
    }
}
