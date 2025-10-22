<?php

namespace MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealProductManagerWpClient;

use MatthiasWeb\RealMediaLibrary\Vendor\MatthiasWeb\Utils\Assets as UtilsAssets;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * Assets handling.
 * @internal
 */
class Assets
{
    use UtilsProvider;
    use UtilsAssets;
    /**
     * C'tor.
     *
     * @codeCoverageIgnore
     */
    private function __construct()
    {
        // Silence is golden.
    }
    /**
     * Localize the plugin with additional options.
     *
     * @param string $context
     * @return array
     */
    public function overrideLocalizeScript($context)
    {
        // Get names of plugins
        $names = [];
        foreach (Core::getInstance()->getInitiators() as $initiator) {
            $names[$initiator->getPluginSlug()] = ['name' => $initiator->getPluginName(), 'plugin' => \plugin_basename($initiator->getPluginFile()), 'privacyProvider' => $initiator->getPrivacyProvider(), 'privacyPolicy' => $initiator->getPrivacyPolicy(), 'isPluginActiveForNetwork' => \is_multisite() ? \is_plugin_active_for_network(\plugin_basename($initiator->getPluginFile())) : \false, 'hasAtLeastOneActiveLicense' => $initiator->getPluginUpdater()->getFirstFoundLicense() !== \false];
        }
        // Do not localize too much, instead work with REST queries!
        return ['currentUserFullName' => Utils::getUserFullName(), 'names' => $names];
    }
    /**
     * Enqueue scripts and styles depending on the type. This function is called
     * from both admin_enqueue_scripts and wp_enqueue_scripts. You can check the
     * type through the $type parameter. In this function you can include your
     * external libraries from public/lib, too.
     *
     * Note: The scripts are loaded only on backend (`admin_enqueue_scripts`). If your plugin
     * is also loaded on frontend you need to make sure to enqueue via `wp_enqueue_scripts`, too.
     * See also https://app.clickup.com/t/4rknyh for more information about this (commits).
     *
     * @param string $type The type (see Assets constants)
     * @param string $hook_suffix The current admin page
     */
    public function enqueue_scripts_and_styles($type, $hook_suffix = null)
    {
        $isPluginsPage = \function_exists('get_current_screen') ? \in_array(\get_current_screen()->id, ['plugins', 'plugins-network'], \true) : \false;
        if (!$isPluginsPage) {
            return;
        }
        $this->enqueue();
    }
    /**
     * Enqueue scripts and styles for this library.
     *
     * @param UtilsAssets $assets
     */
    public function enqueue($assets = null)
    {
        $assets = $assets ? $assets : $this->getFirstAssetsToEnqueueComposer();
        $scriptDeps = $assets->enqueueUtils();
        $handle = $assets->enqueueComposerScript(RPM_WP_CLIENT_SLUG, $scriptDeps);
        $assets->enqueueComposerStyle(RPM_WP_CLIENT_SLUG, []);
        \wp_localize_script($handle, RPM_WP_CLIENT_SLUG_CAMELCASE, $this->localizeScript($this));
        return $handle;
    }
    /**
     * Get first found instance of utils' Assets class. This is needed to we can enqueue assets from their.
     */
    public function getFirstAssetsToEnqueueComposer()
    {
        foreach (Core::getInstance()->getInitiators() as $initiator) {
            $assets = $initiator->getPluginAssets();
            if (isset($assets::$ASSETS_BUMP) && $assets::$ASSETS_BUMP >= 4) {
                return $assets;
            }
        }
    }
    /**
     * New instance.
     *
     * @codeCoverageIgnore
     */
    public static function instance()
    {
        return new Assets();
    }
}
