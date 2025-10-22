<?php

namespace MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealProductManagerWpClient\license;

use MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealProductManagerWpClient\client\LicenseActivation as ClientLicenseActivation;
use MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealProductManagerWpClient\PluginUpdate;
use MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealProductManagerWpClient\Utils;
use MatthiasWeb\RealMediaLibrary\Vendor\MatthiasWeb\Utils\ExpireOption;
use WP_Error;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * Use this trait together in `PluginUpdate`.
 * @internal
 */
trait PluginUpdateLicensePool
{
    /**
     * License instances of the complete multisite.
     *
     * @var License[]
     */
    private $licenses;
    /**
     * License instances cached, can be also partial within a multisite (not all subsites).
     *
     * @var License[]
     */
    private $licenseCache = [];
    private $suppressGetLicensesWpDie = \false;
    /**
     * License activation client.
     *
     * @var ClientLicenseActivation
     */
    private $licenseActivationClient;
    /**
     * C'tor.
     */
    protected function constructPluginUpdateLicensePool()
    {
        $this->licenseActivationClient = ClientLicenseActivation::instance($this);
    }
    /**
     * Update license settings for this plugin.
     *
     * @param array $licenses Pass `null` to activate all unlicensed, free sites
     * @param boolean $telemetry
     * @param boolean $newsletterOptIn
     * @param string $firstName
     * @param string $email
     */
    public function updateLicenseSettings($licenses, $telemetry = \false, $newsletterOptIn = \false, $firstName = '', $email = '')
    {
        // Validate free products
        if ($licenses === null) {
            if ($this->getInitiator()->isExternalUpdateEnabled()) {
                return new WP_Error(PluginUpdate::ERROR_CODE_INVALID_LICENSES, \__('You need to provide at least one license!', RPM_WP_CLIENT_TD), ['status' => 400]);
            }
            // Fallback to use free licenses
            $licenses = [];
            foreach ($this->getUniqueLicenses() as $license) {
                if (empty($license->getActivation()->getCode())) {
                    $licenses[] = ['blog' => $license->getBlogId(), 'installationType' => License::INSTALLATION_TYPE_PRODUCTION, 'code' => $license->getActivation()->getCode(), 'noUsage' => $license->isNoUsage()];
                }
            }
        }
        // Validate newsletter input
        if ($newsletterOptIn && (empty($firstName) || empty($email))) {
            return new WP_Error(PluginUpdate::ERROR_CODE_INVALID_NEWSLETTER, \__('You must provide an email address and a name if you want to subscribe to the newsletter!', RPM_WP_CLIENT_TD), ['status' => 400]);
        }
        $validateKeys = $this->validateLicenseCodes($licenses, $telemetry, $newsletterOptIn, $firstName, $email);
        if (\is_wp_error($validateKeys)) {
            return $validateKeys;
        }
        // Synchronize announcements
        $this->getAnnouncementPool()->sync(\true);
        return \true;
    }
    /**
     * Validate license codes.
     *
     * @param array $licenses
     * @param boolean $telemetry
     * @param boolean $newsletterOptIn
     * @param string $firstName
     * @param string $email
     */
    protected function validateLicenseCodes($licenses, $telemetry, $newsletterOptIn, $firstName, $email)
    {
        $invalidKeys = [];
        $currentLicenses = $this->getLicenses(\true);
        $provider = $this->getInitiator()->getPrivacyProvider();
        // At least one license need to be used
        if ($this->getFirstFoundLicense() === \false && $this->getInitiator()->isExternalUpdateEnabled()) {
            $noUsageLicenses = \array_filter($licenses, function ($noUsageLicense) {
                return $noUsageLicense['noUsage'];
            });
            if (\count($noUsageLicenses) === \count($licenses)) {
                return new WP_Error(PluginUpdate::ERROR_CODE_NONE_IN_USAGE, \__('You must have at least one license of a site in use within your multisite.', RPM_WP_CLIENT_TD));
            }
        }
        // Validate license keys
        foreach ($licenses as $value) {
            $blogId = $value['blog'];
            $installationType = $value['installationType'];
            $code = $value['code'];
            $noUsage = $value['noUsage'];
            if (isset($currentLicenses[$blogId])) {
                $activation = $currentLicenses[$blogId]->getActivation();
                if ($noUsage) {
                    $activation->skip(\true);
                } else {
                    $result = $activation->activate($code, $installationType, $telemetry, $newsletterOptIn, $firstName, $email);
                    // Ignore already existing activations as they should not lead to UI errors
                    if (\is_wp_error($result) && $result->get_error_code() !== LicenseActivation::ERROR_CODE_ALREADY_ACTIVATED) {
                        $errorText = \join(' ', $result->get_error_messages());
                        switch ($result->get_error_code()) {
                            case 'http_request_failed':
                                $errorText = \sprintf(
                                    // translators:
                                    \__('The license server for checking the license cannot be reached. Please check if you are blocking access to %s e.g. by a firewall.', RPM_WP_CLIENT_TD),
                                    $provider
                                );
                                break;
                            default:
                                break;
                        }
                        $invalidKeys[$blogId] = ['validateStatus' => 'error', 'hasFeedback' => \true, 'help' => $errorText, 'debug' => $result];
                    }
                }
            } else {
                return new WP_Error(PluginUpdate::ERROR_CODE_BLOG_NOT_FOUND, '', ['blog' => $blogId]);
            }
        }
        return empty($invalidKeys) ? \true : new WP_Error(PluginUpdate::ERROR_CODE_INVALID_KEYS, $invalidKeys[\array_keys($invalidKeys)[0]]['help'], ['invalidKeys' => $invalidKeys]);
    }
    /**
     * Get the license for the current blog id.
     *
     * @return License
     */
    public function getCurrentBlogLicense()
    {
        $blogId = \get_current_blog_id();
        $licenses = $this->getLicenses(\false, [$blogId]);
        return \array_shift($licenses);
    }
    /**
     * Get first found license as we can not update per-site in multisite (?).
     */
    public function getFirstFoundLicense()
    {
        $licensedBlogIds = $this->getLicensedBlogIds(\false, \true)['_'];
        if (\count($licensedBlogIds) === 0) {
            return \false;
        }
        foreach ($licensedBlogIds as $blogId) {
            $licenses = $this->getLicenses(\false, [$blogId]);
            foreach ($licenses as $license) {
                if ($license->isFulfilled()) {
                    return $license;
                }
            }
        }
        return \false;
    }
    /**
     * Get a list of licensed blog IDs.
     *
     * @param boolean|string $invalidate If `true` it will invalidate the transient, or `"never"` to get the value independent of cache expiration
     * @param boolean $returnCurrentOnNonMultisite
     * @return array|false Array with hostname as key and blog IDs as value. If it is not multisite, it returns `false` (see also `$returnCurrentOnNonMultisite`)
     */
    public function getLicensedBlogIds($invalidate = \false, $returnCurrentOnNonMultisite = \false)
    {
        if (\is_multisite()) {
            $transientName = \sprintf('%s-licensedBlogIds_v2_%s', RPM_WP_CLIENT_OPT_PREFIX, $this->getInitiator()->getPluginSlug());
            $expireOption = new ExpireOption($transientName, \true, 1 * DAY_IN_SECONDS, \true);
            if ($invalidate === 'never') {
                return $expireOption->get(['_' => []], \false);
            }
            $transientValue = $invalidate ? \false : $expireOption->get(\false);
            if (!\is_array($transientValue)) {
                $transientValue = [
                    // A collection of all blog ids
                    '_' => [],
                ];
                $this->suppressGetLicensesWpDie = \true;
                foreach ($this->getUniqueLicenses(\true) as $license) {
                    if (!empty($license->getActivation()->getCode())) {
                        $hostname = $license->getCurrentHostname();
                        $transientValue['_'][] = $license->getBlogId();
                        $transientValue[$hostname] = $transientValue[$hostname] ?? [];
                        $transientValue[$hostname][] = $license->getBlogId();
                    }
                }
                $this->suppressGetLicensesWpDie = \false;
                $expireOption->set($transientValue);
            }
            return $transientValue;
        } elseif ($returnCurrentOnNonMultisite) {
            $license = $this->getCurrentBlogLicense();
            $currentHostname = $license->getCurrentHostname();
            $currentBlogId = $license->getBlogId();
            return $this->getCurrentBlogLicense()->isFulfilled() ? ['_' => [$currentBlogId], $currentHostname => [$currentBlogId]] : ['_' => []];
        }
        return \false;
    }
    /**
     * Get all licenses for each blog (when multisite is enabled). Attention: If a blog
     * uses the same hostname as a previous known blog, they share the same `License` instance.
     *
     * @param boolean $checkRemoteStatus
     * @param int[] $inBlogIds If set only return the list of this blog IDs
     * @return License[]
     */
    public function getLicenses($checkRemoteStatus = \false, $inBlogIds = null)
    {
        global $pagenow;
        /**
         * Short circuit when getting only a set of blogs.
         *
         * @var License[]
         */
        $inBlogIdsLicenses = [];
        if ($this->licenses === null || $checkRemoteStatus || \is_array($inBlogIds)) {
            $blogIds = $this->getPotentialBlogIds($inBlogIds);
            $hostnames = Utils::mapBlogsToHosts($blogIds);
            /**
             * Create licenses per hostname and prefer the blog ID which holds an active license,
             * otherwise take the first one.
             *
             * @var License[]
             */
            $licensedBlogIds = $this->getLicensedBlogIds('never', \false);
            $hostLicenses = [];
            foreach ($hostnames['host'] as $host => $hostBlogIds) {
                $hostBlogId = $hostBlogIds[0];
                // Prefer an active license within a multisite for this host
                if ($licensedBlogIds !== \false && isset($licensedBlogIds[$host])) {
                    $hostBlogId = $licensedBlogIds[$host][0];
                }
                $cacheKey = $host . ':' . $hostBlogId;
                $hostLicenses[$host] = $this->licenseCache[$cacheKey] ?? new License($this, $host, $hostBlogId);
                $this->licenseCache[$cacheKey] = $hostLicenses[$host];
            }
            // Create licenses per blog ID and point to hostname-license
            if ($inBlogIds === null) {
                $this->licenses = [];
                $isDevEnv = \defined('DEVOWL_WP_DEV') && \constant('DEVOWL_WP_DEV');
                if ($isDevEnv && \is_admin() && $pagenow === 'index.php' && !$this->suppressGetLicensesWpDie) {
                    \wp_die(\sprintf('You are calling <code>%s</code> at page load which leads to performance issues on multisite. Backtrace:<br><br><pre>%s</pre>', __METHOD__, \json_encode(\debug_backtrace(\DEBUG_BACKTRACE_IGNORE_ARGS, 10), \JSON_PRETTY_PRINT)));
                }
            }
            foreach ($blogIds as $blogId) {
                $host = $hostnames['blog'][$blogId];
                $license = $hostLicenses[$host];
                if ($checkRemoteStatus) {
                    $license->fetchRemoteStatus(\true);
                }
                $license->initialize();
                if ($inBlogIds === null) {
                    $this->licenses[$blogId] = $license;
                } else {
                    $inBlogIdsLicenses[$blogId] = $license;
                }
            }
        }
        return $inBlogIds === null ? $this->licenses : $inBlogIdsLicenses;
    }
    /**
     * The same as `getLicenses`, but only get unique `License` instances.
     *
     * Use this with caution as it leads to iterate all subsites within your multisite!
     *
     * @param boolean $skipNoUsage Pass `true` to skip licenses which are not in usage
     * @return License[]
     */
    public function getUniqueLicenses($skipNoUsage = \false)
    {
        $result = [];
        foreach ($this->getLicenses() as $license) {
            if ($skipNoUsage && $license->isNoUsage()) {
                continue;
            }
            $result[$license->getBlogId()] = $license;
        }
        return \array_values($result);
    }
    /**
     * Get license activation client.
     *
     * @codeCoverageIgnore
     */
    public function getLicenseActivationClient()
    {
        return $this->licenseActivationClient;
    }
}
