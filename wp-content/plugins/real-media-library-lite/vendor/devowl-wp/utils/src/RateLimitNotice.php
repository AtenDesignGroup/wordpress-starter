<?php

namespace MatthiasWeb\RealMediaLibrary\Vendor\MatthiasWeb\Utils;

// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * Show a notice about rate limit when contacting a devowl.io external service.
 *
 * @see https://app.clickup.com/t/86939q6ce
 * @internal
 */
class RateLimitNotice
{
    private $core;
    const NOTICE_ID = 'notice-devowl-rate-limit';
    /**
     * C'tor.
     *
     * @param PluginReceiver $core
     * @codeCoverageIgnore
     */
    private function __construct($core)
    {
        $this->core = $core;
    }
    /**
     * Create hooks to obtain rate limit requests and show the notice accordingly.
     */
    public function hooks()
    {
        // Only do this once per plugin
        if (!isset($GLOBALS[self::NOTICE_ID])) {
            $GLOBALS[self::NOTICE_ID] = \true;
            // Temporarily disabled due to https://app.clickup.com/t/8694uj43d?comment=90120048782289
            //add_action('admin_notices', [$this, 'admin_notices']);
            \add_filter('http_response', [$this, 'http_response'], 10, 3);
        }
    }
    /**
     * Output the notice about rate limit when we catched a failed request.
     */
    public function admin_notices()
    {
        if (!\current_user_can('administrator')) {
            return;
        }
        $urlOption = $this->getFailedUrl();
        if (isset($_GET[$urlOption->getName()])) {
            \check_admin_referer($urlOption->getName());
            $urlOption->set('');
        }
        $url = $urlOption->get();
        if (!empty($url)) {
            $devowlPlugins = Utils::joinWithAndSeparator(Utils::getActivePluginsMap(\false, function ($data) {
                return \strpos($data['AuthorName'] ?? '', 'devowl.io') !== \false;
            }), \__(' and ', 'devowl-wp-utils'));
            echo \sprintf('<div class="notice notice-warning"><p>%s</p><p><a href="%s">%s</a></p></div>', \sprintf(
                // translators:
                \__('Your WordPress unexpectedly requests the license server and cloud services of <strong>%1$s</strong> exceptionally often (URL: <code>%3$s</code>). This indicates a misconfiguration of your WordPress system, which can also affect the loading speed of your website or cause malfunctions. Please check with your technical contact what is configured incorrectly! Alternatively, you can <a href="%2$s" target="_blank">open a support ticket</a> at the plugin manufacturer support.', 'devowl-wp-utils'),
                $devowlPlugins,
                \esc_url(\__('https://devowl.io/support/', 'devowl-wp-utils')),
                $url
            ), \esc_url(\add_query_arg([$urlOption->getName() => \true, '_wpnonce' => \wp_create_nonce($urlOption->getName())])), \__('I have solved the problem (hide the message until the next occurrence of the error)', 'devowl-wp-utils'));
        }
    }
    /**
     * Filters the response of a HTTP request and check if it is to our devowl.io server and if it
     * failed with 429 error code.
     *
     * @param array $response
     * @param array $parsed_args
     * @param string $url
     */
    public function http_response($response, $parsed_args, $url)
    {
        if (\is_array($response) && \wp_remote_retrieve_response_code($response) === 429 && !empty(\wp_remote_retrieve_header($response, 'X-Devowl-Env'))) {
            $url = \explode('?', $url, 2)[0];
            $this->getFailedUrl()->set($url);
        }
        return $response;
    }
    /**
     * Get the latest failed URL which run into "Too many requests" error.
     */
    public function getFailedUrl()
    {
        $option = new ExpireOption(self::NOTICE_ID, \false, 60 * 60 * 3);
        $option->enableAutoload();
        return $option;
    }
    /**
     * Get core instance.
     *
     * @return PluginReceiver
     * @codeCoverageIgnore
     */
    public function getCore()
    {
        return $this->core;
    }
    /**
     * Get a new instance of RateLimitNotice.
     *
     * @param PluginReceiver $core
     * @return RateLimitNotice
     * @codeCoverageIgnore Instance getter
     */
    public static function instance($core)
    {
        return new RateLimitNotice($core);
    }
}
