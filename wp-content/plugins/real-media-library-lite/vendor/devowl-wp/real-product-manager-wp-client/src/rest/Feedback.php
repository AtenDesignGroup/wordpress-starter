<?php

namespace MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealProductManagerWpClient\rest;

use MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealProductManagerWpClient\client\Feedback as ClientFeedback;
use MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealProductManagerWpClient\Core;
use MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealProductManagerWpClient\UtilsProvider;
use MatthiasWeb\RealMediaLibrary\Vendor\MatthiasWeb\Utils\Service as UtilsService;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * Create feedback REST service.
 * @internal
 */
class Feedback
{
    use UtilsProvider;
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
     * Register endpoints.
     */
    public function rest_api_init()
    {
        $namespace = UtilsService::getNamespace($this);
        \register_rest_route($namespace, '/feedback/(?P<slug>[a-zA-Z0-9_-]+)', ['methods' => 'POST', 'callback' => [$this, 'routePost'], 'permission_callback' => [$this, 'permission_callback'], 'args' => ['skip' => ['type' => 'boolean', 'default' => \false], 'reason' => ['type' => 'string', 'required' => \true], 'note' => ['type' => 'string', 'default' => ''], 'email' => ['type' => 'string', 'default' => '', 'validate_callback' => function ($param) {
            return empty($param) || \is_email($param);
        }], 'name' => ['type' => 'string', 'default' => ''], 'deactivateLicense' => ['type' => 'boolean', 'default' => \false]]]);
    }
    /**
     * Check if user is allowed to call this service requests.
     */
    public function permission_callback()
    {
        return \current_user_can('activate_plugins');
    }
    /**
     * See API docs.
     *
     * @param WP_REST_Request $request
     * @api {post} /real-product-manager-wp-client/v1/feedback/:slug Create a feedback
     * @apiHeader {string} X-WP-Nonce
     * @apiParam {string} slug
     * @apiParam {string} reason
     * @apiParam {boolean} [skip] If `true`, use `other` in `reason` parameter
     * @apiParam {string} [note]
     * @apiParam {string} [email]
     * @apiParam {string} [name]
     * @apiParam {boolean} [deactivateLicense]
     * @apiName Create
     * @apiPermission activate_plugins
     * @apiGroup Feedback
     * @apiVersion 1.0.0
     */
    public function routePost($request)
    {
        $slug = $request->get_param('slug');
        $initiator = Core::getInstance()->getInitiator($slug);
        if ($initiator === null) {
            return new WP_Error('rest_not_found', 'Not found', ['status' => 404]);
        } else {
            $skip = $request->get_param('skip');
            $reason = $request->get_param('reason');
            $note = $request->get_param('note');
            $email = $request->get_param('email');
            $name = $request->get_param('name');
            $deactivateLicense = $request->get_param('deactivateLicense');
            $response = ['sentFeedback' => \false, 'deactivatedLicense' => []];
            if ($deactivateLicense) {
                $licenses = [];
                if (\is_multisite() && \is_plugin_active_for_network(\plugin_basename($initiator->getPluginFile()))) {
                    $licenses = $initiator->getPluginUpdater()->getUniqueLicenses(\true);
                } else {
                    $licenses[] = $initiator->getPluginUpdater()->getCurrentBlogLicense();
                }
                foreach ($licenses as $license) {
                    if (!empty($license->getActivation()->getCode())) {
                        $response['deactivatedLicense'][$license->getBlogId()] = $license->getActivation()->deactivate(\true);
                    }
                }
            }
            if (!$skip) {
                $result = ClientFeedback::instance($initiator->getPluginUpdater())->post($reason, $note, $email, $name);
                if (\is_wp_error($result)) {
                    return $result;
                }
                $response['sentFeedback'] = $result;
            }
            return new WP_REST_Response($response);
        }
    }
    /**
     * New instance.
     *
     * @codeCoverageIgnore
     */
    public static function instance()
    {
        return new Feedback();
    }
}
