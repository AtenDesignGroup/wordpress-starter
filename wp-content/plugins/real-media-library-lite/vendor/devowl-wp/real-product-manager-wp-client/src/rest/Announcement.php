<?php

namespace MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealProductManagerWpClient\rest;

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
 * Create announcement REST service.
 * @internal
 */
class Announcement
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
        \register_rest_route($namespace, '/announcement/(?P<slug>[a-zA-Z0-9_-]+)/active', ['methods' => 'POST', 'callback' => [$this, 'routeActive'], 'permission_callback' => [$this, 'permission_callback'], 'args' => ['state' => ['type' => 'boolean', 'required' => \true]]]);
        \register_rest_route($namespace, '/announcement/(?P<slug>[a-zA-Z0-9_-]+)/(?P<id>[0-9_-]+)/view', ['methods' => 'DELETE', 'callback' => [$this, 'routeDeleteView'], 'permission_callback' => [$this, 'permission_callback']]);
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
     * @api {post} /real-product-manager-wp-client/v1/announcement/:slug/active Set the announcement active status for this plugin
     * @apiHeader {string} X-WP-Nonce
     * @apiParam {string} slug
     * @apiParam {boolean} state
     * @apiName SetActiveStatus
     * @apiPermission activate_plugins
     * @apiGroup Announcement
     * @apiVersion 1.0.0
     */
    public function routeActive($request)
    {
        $slug = $request->get_param('slug');
        $initiator = Core::getInstance()->getInitiator($slug);
        if ($initiator === null) {
            return new WP_Error('rest_not_found', 'Not found', ['status' => 404]);
        } else {
            return new WP_REST_Response(['success' => $initiator->getPluginUpdater()->getAnnouncementPool()->isActive($request->get_param('state'))]);
        }
    }
    /**
     * See API docs.
     *
     * @param WP_REST_Request $request
     * @api {delete} /real-product-manager-wp-client/v1/announcement/:slug/:id/view Dismiss an announcement notice
     * @apiHeader {string} X-WP-Nonce
     * @apiParam {string} slug
     * @apiParam {number} id
     * @apiName DeleteView
     * @apiPermission activate_plugins
     * @apiGroup Announcement
     * @apiVersion 1.0.0
     */
    public function routeDeleteView($request)
    {
        $id = \intval($request->get_param('id'));
        $slug = $request->get_param('slug');
        $initiator = Core::getInstance()->getInitiator($slug);
        if ($initiator === null) {
            return new WP_Error('rest_not_found', 'Not found', ['status' => 404]);
        } else {
            $initiator->getPluginUpdater()->getAnnouncementPool()->dismiss($id);
            return new WP_REST_Response(['success' => \true]);
        }
    }
    /**
     * New instance.
     *
     * @codeCoverageIgnore
     */
    public static function instance()
    {
        return new Announcement();
    }
}
