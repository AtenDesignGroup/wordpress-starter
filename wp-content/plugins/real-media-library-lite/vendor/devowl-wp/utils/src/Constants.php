<?php

namespace MatthiasWeb\RealMediaLibrary\Vendor\MatthiasWeb\Utils;

/**
 * Common constants which cannot be hold in `trait`s as this is forbidden since PHP 8.1.
 * @internal
 */
class Constants
{
    const PLUGIN_CONST_FILE = 'FILE';
    const PLUGIN_CONST_INC = 'INC';
    const PLUGIN_CONST_PATH = 'PATH';
    const PLUGIN_CONST_ROOT_SLUG = 'ROOT_SLUG';
    const PLUGIN_CONST_SLUG = 'SLUG';
    const PLUGIN_CONST_TEXT_DOMAIN = 'TD';
    const PLUGIN_CONST_DEBUG = 'DEBUG';
    const PLUGIN_CONST_DB_PREFIX = 'DB_PREFIX';
    const PLUGIN_CONST_OPT_PREFIX = 'OPT_PREFIX';
    const PLUGIN_CONST_VERSION = 'VERSION';
    const PLUGIN_CONST_NS = 'NS';
    const PLUGIN_CLASS_ACTIVATOR = 'Activator';
    const PLUGIN_CLASS_ASSETS = 'Assets';
    const PLUGIN_CLASS_LOCALIZATION = 'Localization';
    const LOCALIZATION_FRONTEND = 'frontend';
    const LOCALIZATION_BACKEND = 'backend';
    /**
     * Used in frontend localization to detect the i18n files.
     */
    const LOCALIZATION_PUBLIC_JSON_I18N = 'public/languages/json';
    const LOCALIZATION_MO_CACHE_FOLDER = 'mo-cache';
    /**
     * Enqueue scripts and styles in admin pages.
     */
    const ASSETS_TYPE_ADMIN = 'admin_enqueue_scripts';
    /**
     * Enqueue scripts and styles in frontend pages.
     */
    const ASSETS_TYPE_FRONTEND = 'wp_enqueue_scripts';
    /**
     * Enqueue scripts and styles in login page.
     */
    const ASSETS_TYPE_LOGIN = 'login_enqueue_scripts';
    /**
     * Enqueue scripts and styles in customize page.
     */
    const ASSETS_TYPE_CUSTOMIZE = 'customize_controls_print_scripts';
    const ASSETS_HANDLE_REACT = 'react';
    const ASSETS_HANDLE_REACT_DOM = 'react-dom';
    const ASSETS_HANDLE_REACT_JSX_RUNTIME = 'react-jsx-runtime';
    const ASSETS_HANDLE_MOBX = 'mobx';
    const ASSETS_ADVANCED_ENQUEUE_FEATURE_DEFER = 'defer';
    const ASSETS_ADVANCED_ENQUEUE_FEATURE_ASYNC = 'async';
    const ASSETS_ADVANCED_ENQUEUE_FEATURE_PRELOADING = 'preloading';
    const ASSETS_ADVANCED_ENQUEUE_FEATURE_PRIORITY_QUEUE = 'priority-queue';
}
