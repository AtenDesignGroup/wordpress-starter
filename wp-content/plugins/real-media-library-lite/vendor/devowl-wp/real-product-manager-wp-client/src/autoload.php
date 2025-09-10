<?php

namespace MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealProductManagerWpClient;

// Simply check for defined constants, we do not need to `die` here
if (\defined('ABSPATH')) {
    Utils::setupConstants();
    Localization::instanceThis()->hooks();
}
