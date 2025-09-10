<?php

namespace MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealProductManagerWpClient\license;

use MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealProductManagerWpClient\Utils;
use MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealProductManagerWpClient\UtilsProvider;
use MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealProductManagerWpClient\view\PluginUpdateView;
use MatthiasWeb\RealMediaLibrary\Vendor\MatthiasWeb\Utils\ExpireOption;
use WP_Error;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * Handle license activation.
 * @internal
 */
class LicenseActivation
{
    use UtilsProvider;
    const ERROR_CODE_ALREADY_ACTIVATED = 'rpm_wpc_already_exists';
    /**
     * License instance.
     *
     * @var License
     */
    private $license;
    /**
     * C'tor.
     *
     * @param License $license
     * @codeCoverageIgnore
     */
    public function __construct($license)
    {
        $this->license = $license;
    }
    /**
     * Mark the license as "ever seen form once", see also `#hasInteractedWithFormOnce`.
     * You should use the REST API endpoint `plugin-update/:slug/skip` for this.
     *
     * `$noUsage = true`:
     *
     * Mark the license as "no usage". That means, the license code is not needed and not
     * considered for "fully licensed" or "partial licensed".
     *
     * Use case: User has installed the plugin network wide, but want to use the premium plugin
     * only on one sub site.
     *
     * @param boolean $noUsage
     */
    public function skip($noUsage = \false)
    {
        $license = $this->getLicense();
        $slug = $license->getSlug();
        $license->switch();
        \update_option(License::OPTION_NAME_TELEMETRY_PREFIX . $slug, \false);
        \update_option(License::OPTION_NAME_INSTALLATION_TYPE_PREFIX . $slug, License::INSTALLATION_TYPE_PRODUCTION);
        \update_option(License::OPTION_NAME_NO_USAGE_PREFIX . $slug, $noUsage);
        $result = \update_option(License::OPTION_NAME_CODE_PREFIX . $slug, '');
        $license->restore();
        return $result;
    }
    /**
     * Activate this license with a given code. It returns a `WP_Error` if a code is already active.
     *
     * @param string $code
     * @param string $installationType
     * @param boolean $telemetry
     * @param boolean $newsletterOptIn
     * @param string $firstName
     * @param string $email
     */
    public function activate($code, $installationType, $telemetry, $newsletterOptIn, $firstName, $email)
    {
        $license = $this->getLicense();
        $license->switch();
        $slug = $license->getSlug();
        if (!empty($this->getCode())) {
            $result = new WP_Error(self::ERROR_CODE_ALREADY_ACTIVATED, \__('You have already activated a license for this plugin. Please deactivate it first!', RPM_WP_CLIENT_TD), ['blog' => $license->getBlogId(), 'slug' => $slug]);
        } else {
            $uuid = $license->getUuid();
            $result = $license->getClient()->post($code, $uuid, $installationType, $telemetry, $newsletterOptIn, $firstName, $email);
            if (!\is_wp_error($result)) {
                // No error occurred, let's save the license key and UUID
                $licenseKey = $result['licenseActivation']['license']['licenseKey'];
                $uuid = $result['licenseActivation']['client']['uuid'];
                \update_option(License::OPTION_NAME_CODE_PREFIX . $slug, $licenseKey);
                \update_option(License::OPTION_NAME_UUID_PREFIX . $slug, $uuid);
                \update_option(License::OPTION_NAME_HOST_NAME . $slug, \base64_encode(Utils::getCurrentHostname()));
                // base64 encoded to avoid search & replace of migration tools
                \update_option(License::OPTION_NAME_TELEMETRY_PREFIX . $slug, \intval($telemetry));
                \update_option(License::OPTION_NAME_INSTALLATION_TYPE_PREFIX . $slug, $installationType);
                \update_option(License::OPTION_NAME_NO_USAGE_PREFIX . $slug, 0);
                \delete_option(License::OPTION_NAME_HINT_PREFIX . $slug);
                // The notice for license activation should never be shown again
                $initiator = $this->getLicense()->getInitiator();
                if ($initiator->isExternalUpdateEnabled()) {
                    \update_option(PluginUpdateView::OPTION_NAME_ADMIN_NOTICE_LICENSE_DISMISSED_DAY_PREFIX . $initiator->getPluginSlug(), \PHP_INT_MAX);
                }
                $this->getLicense()->receivedRemoteLicenseActivation($result['licenseActivation']);
                $this->getLicense()->getTelemetryData()->probablyTransmit();
                $this->getLicense()->getPluginUpdate()->getLicensedBlogIds(\true);
                /**
                 * License activation for a given plugin got changed.
                 *
                 * Note: You are running in the context of the activated blog if you are in a multisite!
                 *
                 * @hook DevOwl/RealProductManager/LicenseActivation/StatusChanged/$slug
                 * @param {boolean} $status
                 * @param {string} $licenseKey
                 * @param {string} $uuid
                 * @since 1.6.4
                 */
                \do_action('DevOwl/RealProductManager/LicenseActivation/StatusChanged/' . $slug, \true, $licenseKey, $uuid);
            }
        }
        $license->restore();
        return $result;
    }
    /**
     * Deactivate the license for this blog and plugin.
     *
     * @param boolean $remote If `true`, the license is also deactivate remotely
     * @param string $validateStatus
     * @param string $help
     * @return WP_Error|true
     */
    public function deactivate($remote = \false, $validateStatus = null, $help = '')
    {
        $license = $this->getLicense();
        $license->switch();
        $code = $this->getCode();
        $uuid = $this->getLicense()->getUuid();
        // We need to ensure, the license activation is removed from remote server (only when not already detected remotely)
        if ($remote) {
            $delete = $this->getLicense()->getClient()->delete($code, $uuid);
            if (\is_wp_error($delete)) {
                return $delete;
            }
        }
        // Let's remove locally...
        $slug = $license->getSlug();
        \update_option(License::OPTION_NAME_CODE_PREFIX . $slug, '');
        \update_option(License::OPTION_NAME_HOST_NAME . $slug, '');
        \update_option(License::OPTION_NAME_TELEMETRY_PREFIX . $slug, \false);
        \update_option(License::OPTION_NAME_NO_USAGE_PREFIX . $slug, \false);
        \update_option(License::OPTION_NAME_INSTALLATION_TYPE_PREFIX . $slug, License::INSTALLATION_TYPE_NONE);
        // For feature-flags compatibility we do never delete the last contact to our license server
        //delete_option(License::OPTION_NAME_LICENSE_ACTIVATION_PREFIX . $slug);
        if ($validateStatus !== null) {
            \update_option(License::OPTION_NAME_HINT_PREFIX . $slug, ['validateStatus' => $validateStatus, 'hasFeedback' => \true, 'help' => $help]);
        }
        $this->getLicense()->getPluginUpdate()->getLicensedBlogIds(\true);
        // Documented in `activate`
        \do_action('DevOwl/RealProductManager/LicenseActivation/StatusChanged/' . $slug, \false, '', $uuid);
        $license->restore();
        return \true;
    }
    /**
     * Pass any timestamp and this method returns the day of the timestamp with the time of
     * the license activation. This allows us to defer API calls.
     *
     * @param int $timestamp
     */
    public function applyTimeOfLicenseActivationToTimestamp($timestamp)
    {
        $received = $this->getReceived();
        if (\is_array($received)) {
            $activatedAt = \strtotime($received['activatedAt']);
            return \strtotime(\gmdate('H:i:s', $activatedAt), $timestamp);
        }
        return $timestamp;
    }
    /**
     * Schedules an action (callable) for deferred execution. The action is called only once
     * for the given period in days. The action gets called at the time of license activation
     * so we can scutter e.g. API calls to our license server independent of configured time and
     * timezone of current server.
     *
     * @param string $actionName
     * @param callable|int $actionOrExpire If you pass `int` the action will be scheduled after this expiration in seconds
     * @param int $periodInDays
     * @param string $minimumWpHook
     */
    public function executeDeferredAction($actionName, $actionOrExpire, $periodInDays = 1, $minimumWpHook = 'init')
    {
        $license = $this->getLicense();
        $license->switch();
        $transientName = \sprintf('%s-licenseDeferred%s_%s', RPM_WP_CLIENT_OPT_PREFIX, \ucfirst($actionName), $license->getSlug());
        $now = \time();
        $usePassedExpiration = \is_numeric($actionOrExpire);
        $expire = $usePassedExpiration ? \intval($actionOrExpire) : $this->applyTimeOfLicenseActivationToTimestamp($now + $periodInDays * DAY_IN_SECONDS) - $now;
        $expireOption = new ExpireOption($transientName, \false, $expire);
        if ($usePassedExpiration) {
            return $expireOption->set('1');
        }
        $transientValue = $expireOption->get();
        $result = \false;
        if (!$transientValue && $expireOption->set('1')) {
            $result = \true;
            if (\did_action($minimumWpHook)) {
                \call_user_func($actionOrExpire);
            } else {
                \add_action($minimumWpHook, $actionOrExpire);
            }
        }
        $license->restore();
        return $result;
    }
    /**
     * Check if the form for this license was shown the user once. This allows you e.g.
     * show a form of the license activation directly after using the plugin for the first time.
     *
     * When the current user cannot activate plugins, a license activation form should never be loaded.
     */
    public function hasInteractedWithFormOnce()
    {
        return \current_user_can('activate_plugins') ? $this->getCode() !== \false : \true;
    }
    /**
     * Get a hint for this license activation. This can happen e.g. the remote status changed (revoked, expired)
     * and we want to user show a notice for this. Can be `false` if none given.
     *
     * @return string
     */
    public function getHint()
    {
        // Mixed state due to conflicts? We cannot fix this currently, but we can check if there is a active
        // license activation code.
        if (!empty($this->getCode())) {
            return \false;
        }
        $license = $this->getLicense();
        $license->switch();
        $result = \get_option(License::OPTION_NAME_HINT_PREFIX . $license->getSlug(), \false);
        $license->restore();
        return $result;
    }
    /**
     * Get the license activation we received from our remote license server. This returns also
     * a result if you have deactivated your license already.
     *
     * @return false|array
     */
    public function getReceived()
    {
        $license = $this->getLicense();
        $license->switch();
        $result = \get_option(License::OPTION_NAME_LICENSE_ACTIVATION_PREFIX . $license->getSlug());
        $license->restore();
        return $result;
    }
    /**
     * Get the received client properties from the last contact to our license server for this activation.
     * This returns also a result if you have deactivated your license already.
     *
     * @return false|array
     */
    public function getReceivedClientProperties()
    {
        $received = $this->getReceived();
        if (\is_array($received)) {
            $properties = $received['client']['properties'];
            $properties = \array_combine(\array_column($properties, 'key'), \array_column($properties, 'value'));
            return $properties;
        }
        return \false;
    }
    /**
     * Get a received client property by key from the last contact to our license server for this activation.
     * This returns also a result if you have deactivated your license already.
     *
     * @param string $key
     * @param mixed $default
     */
    public function getReceivedClientProperty($key, $default = \false)
    {
        $properties = $this->getReceivedClientProperties();
        return $properties[$key] ?? $default;
    }
    /**
     * Get entered license code for this activation. Can be `false` if none given. If it is
     * an empty string, the form got skipped through `#skip()`.
     *
     * @return string|false
     */
    public function getCode()
    {
        $license = $this->getLicense();
        $license->switch();
        $result = \get_option(License::OPTION_NAME_CODE_PREFIX . $license->getSlug());
        $license->restore();
        return $result;
    }
    /**
     * See `License#INSTALLATION_TYPE_*` constants.
     */
    public function getInstallationType()
    {
        $license = $this->getLicense();
        $license->switch();
        $result = \get_option(License::OPTION_NAME_INSTALLATION_TYPE_PREFIX . $license->getSlug());
        $license->restore();
        return empty($result) ? License::INSTALLATION_TYPE_NONE : $result;
    }
    /**
     * Check if this license activation has enabled telemetry data sharing.
     *
     * **Attention**: When you provide a `$set` variable, please also call `License#syncWithRemote` so the external update
     * server gets noticed about this change!
     *
     * @param boolean $set
     * @return boolean When `$set` is a `boolean`, it will return `true` if telemetry data sharing was successfully enabled
     */
    public function isTelemetryDataSharingOptIn($set = null)
    {
        $license = $this->getLicense();
        $license->switch();
        $optionName = License::OPTION_NAME_TELEMETRY_PREFIX . $license->getSlug();
        $result = \get_option($optionName);
        if (\is_bool($set)) {
            if ($license->isNoUsage() || empty($this->getCode())) {
                return \false;
            }
            if ($set === $result) {
                return \true;
            }
            return \update_option($optionName, $set);
        }
        $license->restore();
        return \boolval($result);
    }
    /**
     * Get license instance.
     *
     * @codeCoverageIgnore
     */
    public function getLicense()
    {
        return $this->license;
    }
}
