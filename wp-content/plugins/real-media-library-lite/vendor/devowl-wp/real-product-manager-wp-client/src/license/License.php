<?php

namespace MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealProductManagerWpClient\license;

use MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealProductManagerWpClient\client\ClientUtils;
use MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealProductManagerWpClient\Core;
use MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealProductManagerWpClient\PluginUpdate;
use MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealProductManagerWpClient\Utils;
use MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealProductManagerWpClient\UtilsProvider;
use WP_Error;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * Handle license information for a given plugin and associated blog id.
 * @internal
 */
class License
{
    use UtilsProvider;
    const OPTION_NAME_CODE_PREFIX = RPM_WP_CLIENT_OPT_PREFIX . '-code_';
    const OPTION_NAME_UUID_PREFIX = RPM_WP_CLIENT_OPT_PREFIX . '-uuid_';
    const OPTION_NAME_HOST_NAME = RPM_WP_CLIENT_OPT_PREFIX . '-hostname_';
    const OPTION_NAME_TELEMETRY_PREFIX = RPM_WP_CLIENT_OPT_PREFIX . '-telemetry_';
    const OPTION_NAME_INSTALLATION_TYPE_PREFIX = RPM_WP_CLIENT_OPT_PREFIX . '-installationType_';
    const OPTION_NAME_HINT_PREFIX = RPM_WP_CLIENT_OPT_PREFIX . '-hint_';
    const OPTION_NAME_LICENSE_ACTIVATION_PREFIX = RPM_WP_CLIENT_OPT_PREFIX . '-licenseActivation_';
    const OPTION_NAME_NO_USAGE_PREFIX = RPM_WP_CLIENT_OPT_PREFIX . '-noUsage_';
    const INSTALLATION_TYPE_NONE = \false;
    const INSTALLATION_TYPE_DEVELOPMENT = 'development';
    const INSTALLATION_TYPE_PRODUCTION = 'production';
    const ERROR_CODE_NOT_ACTIVATED = 'rpm_wpc_not_activated';
    /**
     * In some cases, the new host gets validated and RPM automatically deactivates the
     * license of the users without any changes to the host. For some reason, the detected
     * hostname is an invalid "URL" like `http:`.
     */
    const VALIDATE_NEW_HOSTNAME_SKIP = ['http:', 'https:'];
    /**
     * Skip the license deactivation for some exceptions. For example, AWS Lightsail does
     * not automatically redirect the `ec2-192-18[...]` domain to the WordPress domain URL.
     *
     * @see https://regex101.com/r/OxkZVE/2
     */
    const VALIDATE_NEW_HOSTNAME_SKIP_BY_REGEXP = '/^(?:\\w+-\\d+-\\d+-\\d+-\\d+\\.[^\\.]+\\.(?:[\\w-]+[-.])?amazonaws\\.com|.*temp\\.domains)$/m';
    private $initialized = \false;
    /**
     * Plugin slug.
     *
     * @var string
     */
    private $slug;
    /**
     * Blog id for this license.
     *
     * @var int
     */
    private $blogId;
    /**
     * Hostname at time of creation of this instance.
     *
     * @var string
     */
    private $currentHostname;
    /**
     * Plugin update handler.
     *
     * @var PluginUpdate
     */
    private $pluginUpdate;
    /**
     * License activation handler.
     *
     * @var LicenseActivation
     */
    private $activation;
    /**
     * License telemetry data handler.
     *
     * @var TelemetryData
     */
    private $telemetryData;
    /**
     * Remote status of the activation.
     *
     * @var WP_Error|array
     */
    private $remoteStatus;
    /**
     * C'tor.
     *
     * @param PluginUpdate $pluginUpdate
     * @param string $currentHostname
     * @param int $blogId
     * @codeCoverageIgnore
     */
    public function __construct($pluginUpdate, $currentHostname, $blogId)
    {
        $this->pluginUpdate = $pluginUpdate;
        $this->slug = $pluginUpdate->getInitiator()->getPluginSlug();
        $this->currentHostname = $currentHostname;
        $this->blogId = $blogId;
        $this->activation = new LicenseActivation($this);
        $this->telemetryData = new TelemetryData($this);
        $newVersionHook = 'DevOwl/Utils/NewVersionInstallation/' . $this->slug;
        \add_action($newVersionHook, [$this, 'newVersionInstalled']);
        if (\did_action($newVersionHook)) {
            $this->newVersionInstalled();
        }
        \add_action('update_option_siteurl', [$this, 'update_option_siteurl']);
        if ($this->getInitiator()->isExternalUpdateEnabled() && \is_admin()) {
            \add_action('shutdown', [$this, 'validateNewHostName']);
        }
    }
    /**
     * Initalizes the license once.
     */
    public function initialize()
    {
        if ($this->initialized) {
            return;
        }
        $this->getTelemetryData()->probablyTransmit();
        $this->probablySyncWithRemote();
        $this->probablyMigrate();
        $this->initialized = \true;
    }
    /**
     * A new version of the plugin got installed.
     */
    public function newVersionInstalled()
    {
        // Generate random seconds between 15 minutes and 3 hours
        $delayApiCalls = \mt_rand(15, 3 * 60) * 60;
        $this->getActivation()->executeDeferredAction('licenseActivation', $delayApiCalls);
    }
    /**
     * Switch to this blog.
     *
     * @see https://developer.wordpress.org/reference/functions/switch_to_blog/
     */
    public function switch()
    {
        if (\is_multisite()) {
            \switch_to_blog($this->getBlogId());
        }
    }
    /**
     * Restore to previous blog.
     *
     * @see https://developer.wordpress.org/reference/functions/restore_current_blog/
     */
    public function restore()
    {
        if (\is_multisite()) {
            \restore_current_blog();
        }
    }
    /**
     * If given, read the old license key from the previous updater and give it back as hint.
     */
    public function probablyMigrate()
    {
        $activation = $this->getActivation();
        if ($activation->getCode() !== \false) {
            // We already have a license key, do nothing
            return;
        }
        $this->switch();
        $oldValue = $this->getInitiator()->getMigrationOption();
        $this->restore();
        if ($oldValue !== null) {
            $activation->deactivate(\false, 'warning', \sprintf(
                // translators:
                \__('The plugin has a new update server. Therefore, you need to reactivate your license (%s) to continue receiving updates.', RPM_WP_CLIENT_TD),
                $oldValue
            ) . (\is_multisite() ? ' ' . \__('You are using a WordPress multisite. According to the plugin\'s licence agreement, you need one license per website. If you have only used one licence for all websites in your WordPress multisite, this was only possible because it was not technically prevented. We ask for your understanding if this causes you any inconvenience!', RPM_WP_CLIENT_TD) : ''));
        }
    }
    /**
     * Sync our plugin version, PHP version and WordPress version with our remote system.
     */
    public function syncWithRemote()
    {
        $activation = $this->getActivation();
        $code = $activation->getCode();
        if (empty($code)) {
            return new WP_Error(License::ERROR_CODE_NOT_ACTIVATED, \__('You have not yet activated a license for this plugin on your website.', RPM_WP_CLIENT_TD), ['blog' => $this->getBlogId(), 'slug' => $this->getSlug()]);
        }
        $this->switch();
        $response = $this->getClient()->patch($code, $this->getUuid(), $activation->isTelemetryDataSharingOptIn());
        $this->restore();
        $this->validateRemoteResponse($response);
        return $response;
    }
    /**
     * The same as `syncWithRemote`, but surely synced only once a day.
     */
    public function probablySyncWithRemote()
    {
        $this->getActivation()->executeDeferredAction('licenseActivation', [$this, 'syncWithRemote']);
    }
    /**
     * Fetch remote status from the Real Product Manager server. Automatically
     * validates with `#validateRemoteResponse`, too.
     *
     * @param boolean $force
     */
    public function fetchRemoteStatus($force = \false)
    {
        // Not yet activated, it's an error when asking for remote result
        $code = $this->getActivation()->getCode();
        if (empty($code)) {
            return new WP_Error(self::ERROR_CODE_NOT_ACTIVATED, \__('You have not yet activated a license for this plugin on your website.', RPM_WP_CLIENT_TD), ['blog' => $this->getBlogId(), 'slug' => $this->getSlug()]);
        }
        if ($this->remoteStatus === null || $force) {
            $this->remoteStatus = $this->getClient()->get($code, $this->getUuid());
            $this->validateRemoteResponse($this->remoteStatus);
        }
        return $this->remoteStatus;
    }
    /**
     * If the `site_url` got updated through e.g. the UI, persist the host name as known
     * so that `self::validateNewHost` does not automatically deactivate the license - its
     * still the same WordPress installation.
     */
    public function update_option_siteurl()
    {
        $currentHostname = Utils::getCurrentHostname();
        \update_option(self::OPTION_NAME_HOST_NAME . $this->getSlug(), \base64_encode($currentHostname));
    }
    /**
     * Check if the plugin got migrated to another host and deactivate the license automatically.
     */
    public function validateNewHostName()
    {
        $this->switch();
        $currentHostname = Utils::getCurrentHostname();
        $persistedHostname = $this->getKnownHostname();
        $code = $this->getActivation()->getCode();
        $isLicensed = !empty($code);
        $dynamic = \defined('RPM_WP_CLIENT_SKIP_DYNAMIC_HOST_CHECK') && \constant('RPM_WP_CLIENT_SKIP_DYNAMIC_HOST_CHECK');
        $isWpCli = \defined('WP_CLI') && \constant('WP_CLI');
        if (!$isWpCli && !Utils::isRedirected() && $isLicensed && !empty($currentHostname) && \filter_var(\preg_replace('/:[0-9]+/', '', $currentHostname), \FILTER_VALIDATE_IP) === \false && \parse_url($currentHostname) !== \false && !$dynamic && !\in_array($currentHostname, self::VALIDATE_NEW_HOSTNAME_SKIP, \true) && !\preg_match(self::VALIDATE_NEW_HOSTNAME_SKIP_BY_REGEXP, $currentHostname)) {
            // Backwards-compatibility, save option of current host
            if (empty($persistedHostname)) {
                \update_option(self::OPTION_NAME_HOST_NAME . $this->getSlug(), \base64_encode($currentHostname));
                $persistedHostname = $currentHostname;
            }
            // Automatically deactivate
            if ($currentHostname !== $persistedHostname) {
                $this->getActivation()->deactivate(\false, 'warning', \__('The license has been automatically deactivated because your website is running on a new domain. Please activate the license again!', RPM_WP_CLIENT_TD) . \sprintf(' "%s" -> "%s"', $persistedHostname, $currentHostname) . ($this->getInitiator()->isExternalUpdateEnabled() ? \sprintf(' %s: %s', \__('License key', RPM_WP_CLIENT_TD), $code) : ''));
                // It might be a clone of the website, let's delete also the UUID
                \update_option(License::OPTION_NAME_UUID_PREFIX . $this->getSlug(), '');
                // Is there a chance the new host is configured programmatically?
                $this->activateProgrammatically(\true);
            }
        }
        $this->restore();
    }
    /**
     * Validate a remote response against their body and probably an error code.
     * It automatically revokes the license if expired/revoked remotely.
     *
     * @param WP_Error|array $response
     */
    public function validateRemoteResponse($response)
    {
        $isError = \is_wp_error($response);
        if ($isError && $response->get_error_code() === ClientUtils::ERROR_CODE_REMOTE) {
            $errorCodes = $response->get_error_codes();
            $errors = $response->get_error_messages();
            foreach ($errorCodes as $index => $errorCode) {
                switch ($errorCode) {
                    case 'ClientNotFound':
                    case 'LicenseActivationNotFound':
                    case 'LicenseHasBeenExpired':
                    case 'LicenseHasBeenRevoked':
                    case 'LicenseNotFound':
                        $this->getActivation()->deactivate(\false, 'warning', \sprintf('%s (%s)', $errors[$index], $this->getActivation()->getCode()));
                        return \false;
                    default:
                        break;
                }
            }
        }
        if (!$isError && isset($response['licenseActivation'])) {
            $this->receivedRemoteLicenseActivation($response['licenseActivation']);
        }
        return \true;
    }
    /**
     * Check if there is a programmatically configured license key and activate it if not already.
     * If there is already an error, it will return that error (expect you pass `$force = true`).
     *
     * @param boolean $force
     */
    public function activateProgrammatically($force = \false)
    {
        $prog = $this->getProgrammaticActivation();
        $activation = $this->getActivation();
        if ($prog === \false) {
            return new WP_Error('rpm_wpc_programmatic_activation_not_found', \__('No programmatic activation found for this plugin.'));
        }
        // If already activated, check if something changed in our filter and reforce
        $code = $activation->getCode();
        $isLicensed = !empty($code);
        if ($isLicensed) {
            $installationType = $activation->getInstallationType();
            if ($code !== $prog['code'] || $installationType !== $prog['type']) {
                $force = \true;
            } else {
                return $activation->getReceived();
            }
        }
        if (!$force) {
            $hint = $activation->getHint();
            if (\is_array($hint)) {
                return new WP_Error('rpm_wpc_programmatic_activation_already_error', $hint['help']);
            }
        }
        // Deactivate old license
        if ($isLicensed) {
            $activation->deactivate(\true);
        }
        $result = $activation->activate($prog['code'], $prog['type'], $prog['telemetry'], \false, '', '');
        // Save as hint so our user can see the error message within the license UI
        if (\is_wp_error($result)) {
            \update_option(self::OPTION_NAME_HINT_PREFIX . $this->getSlug(), ['validateStatus' => 'error', 'hasFeedback' => \true, 'help' => \trim(\join(' ', $result->get_error_messages()))]);
        }
        return $result;
    }
    /**
     * We received the remote license activation data. Let's save this to our database
     * so other plugins can act on them.
     *
     * Use cases:
     *
     * - Enable/Disable functionality depending on client properties (Feature flags)
     *
     * @param array $licenseActivation
     */
    public function receivedRemoteLicenseActivation($licenseActivation)
    {
        $this->switch();
        $result = \update_option(License::OPTION_NAME_LICENSE_ACTIVATION_PREFIX . $this->getSlug(), $licenseActivation);
        $this->restore();
        return $result;
    }
    /**
     * Get initiator.
     */
    public function getInitiator()
    {
        return Core::getInstance()->getInitiator($this->getSlug());
    }
    /**
     * Get blog name for this license.
     */
    public function getBlogName()
    {
        $this->switch();
        $result = \sprintf('%s (%s)', \get_bloginfo('name'), Utils::getCurrentHostname());
        $this->restore();
        return $result;
    }
    /**
     * Get known UUID. Can be empty if none given. The UUID will be set with the first
     * license activation.
     *
     * @return string
     */
    public function getUuid()
    {
        $this->switch();
        $result = \get_option(self::OPTION_NAME_UUID_PREFIX . $this->getSlug(), '');
        $this->restore();
        return $result;
    }
    /**
     * Get known hostname. Can be empty if none given. The hostname will be set with the first
     * license activation. The value itself is base64-encoded in database to avoid search & replace
     * mechanism to replace the persisted URL.
     *
     * @return string
     */
    public function getKnownHostname()
    {
        $this->switch();
        $result = \get_option(self::OPTION_NAME_HOST_NAME . $this->getSlug(), '');
        $this->restore();
        return empty($result) ? $result : \base64_decode($result);
    }
    /**
     * Get plugin update handler.
     *
     * @codeCoverageIgnore
     */
    public function getPluginUpdate()
    {
        return $this->pluginUpdate;
    }
    /**
     * Get license activation handler.
     *
     * @codeCoverageIgnore
     */
    public function getActivation()
    {
        return $this->activation;
    }
    /**
     * Get license telemetry data handler.
     *
     * @codeCoverageIgnore
     */
    public function getTelemetryData()
    {
        return $this->telemetryData;
    }
    /**
     * Get plugin slug.
     *
     * @codeCoverageIgnore
     */
    public function getSlug()
    {
        return $this->slug;
    }
    /**
     * Get license client.
     */
    public function getClient()
    {
        return $this->getInitiator()->getPluginUpdater()->getLicenseActivationClient();
    }
    /**
     * Get the license as array, useful for frontend needs or REST API.
     */
    public function getAsArray()
    {
        $remote = $this->fetchRemoteStatus();
        $this->switch();
        $host = Utils::getCurrentHostName();
        $this->restore();
        return ['uuid' => $this->getUuid(), 'blog' => $this->getBlogId(), 'host' => $host, 'programmatically' => $this->getProgrammaticActivation(), 'blogName' => $this->getBlogName(), 'installationType' => $this->getActivation()->getInstallationType(), 'telemetryDataSharingOptIn' => $this->getActivation()->isTelemetryDataSharingOptIn(), 'code' => $this->getActivation()->getCode(), 'hint' => $this->getActivation()->getHint(), 'remote' => \is_wp_error($remote) ? null : $remote, 'noUsage' => $this->isNoUsage()];
    }
    /**
     * See filter `DevOwl/RealProductManager/License/Programmatic/$slug`.
     */
    public function getProgrammaticActivation()
    {
        /**
         * Activate a license programmatically.
         *
         * @hook DevOwl/RealProductManager/License/Programmatic/$slug
         * @example <caption>Example usage for Real Cookie Banner plugin</caption>
         * add_filter(
         *     'DevOwl/RealProductManager/License/Programmatic/real-cookie-banner-pro',
         *     function ($activation, $blogId, $siteUrlHostname) {
         *         if (
         *              // Check if there is already a programmatic activation so we do not overwrite
         *              $activation === false &&
         *              // Check the blog ID within a multisite installation, for non-multisite installation this is always 1
         *              $blogId === 1 &&
         *              // The hostname of your WordPress installation (including subdomain, without path and protocol)
         *              $siteUrlHostname === 'example.your-host.com'
         *         ) {
         *             return [
         *                 // You must pass `true` for the activation to work; you thereby agree to privacy policies of the plugin to be activated
         *                 'optInPrivacyPolicy' => true,
         *                 'key' => 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX',
         *                 'environment' => 'production', // optional, default: production, can also be `development`
         *                 'telemetry' => true // optional
         *             ];
         *         }
         *         return $activation;
         *     },
         *     10,
         *     3
         * );
         * @param {array|false} $activation The activation for this blog and host, `false` if no filter provides a programmatic activation yet
         * @param {int} $blogId Blog ID within a multisite installation, for non-multisite installation this is always 1
         * @param {string} $siteUrlHostname The hostname of your WordPress installation (including subdomain, without path and protocol)
         * @since 1.11.0
         * @return {array|false}
         */
        $result = \apply_filters('DevOwl/RealProductManager/License/Programmatic/' . $this->getSlug(), \false, $this->getBlogId(), Utils::getCurrentHostname());
        if (\is_array($result) && isset($result['key'])) {
            $key = $result['key'];
            $environment = 'production';
            if (isset($result['environment'])) {
                if (\in_array($environment, ['development', 'production'], \true)) {
                    $environment = $result['environment'];
                } else {
                    // No valid environment
                    return \false;
                }
            }
            $telemetry = \false;
            if (isset($result['telemetry'])) {
                if (\is_bool($result['telemetry'])) {
                    $telemetry = $result['telemetry'];
                } else {
                    // No valid telemetry
                    return \false;
                }
            }
            $optInPrivacyPolicy = $result['optInPrivacyPolicy'] ?? \false;
            if (!$optInPrivacyPolicy) {
                return \false;
            }
            return ['code' => $key, 'type' => $environment, 'telemetry' => $telemetry];
        }
        return \false;
    }
    /**
     * Check if this license needs to have a license key (no usage) and if yes, check if one is given.
     *
     * If you want to check strictly if a license is active do something like `!empty($license->getActivation()->getCode())`.
     */
    public function isFulfilled()
    {
        return $this->isNoUsage() ? \true : !empty($this->getActivation()->getCode());
    }
    /**
     * Check if this license should not be used. It is mostly useful within Multisite when a non-free plugin
     * should be only used on one sub site.
     *
     * @return boolean
     */
    public function isNoUsage()
    {
        // "No usage" is only allowed when more than one unique license is available
        if (\is_multisite() && !$this->getInitiator()->isMultisiteLicensed()) {
            return \false;
        }
        $this->switch();
        $result = \get_option(self::OPTION_NAME_NO_USAGE_PREFIX . $this->getSlug());
        $this->restore();
        return \boolval($result);
    }
    /**
     * Make it work with `array_unique`.
     *
     * @see https://stackoverflow.com/a/2426579/5506547
     */
    public function __toString()
    {
        return \json_encode([$this->getSlug(), $this->getBlogId()]);
    }
    /**
     * Get blog id.
     *
     * @codeCoverageIgnore
     */
    public function getBlogId()
    {
        return $this->blogId;
    }
    /**
     * Getter. See also `getKnownHostname`.
     */
    public function getCurrentHostname()
    {
        return $this->currentHostname;
    }
}
