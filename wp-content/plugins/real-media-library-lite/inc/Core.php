<?php

namespace MatthiasWeb\RealMediaLibrary;

use MatthiasWeb\RealMediaLibrary\attachment\CountCache;
use MatthiasWeb\RealMediaLibrary\attachment\CustomField;
use MatthiasWeb\RealMediaLibrary\attachment\Filter;
use MatthiasWeb\RealMediaLibrary\attachment\Permissions;
use MatthiasWeb\RealMediaLibrary\attachment\Shortcut;
use MatthiasWeb\RealMediaLibrary\attachment\Upload;
use MatthiasWeb\RealMediaLibrary\base\Core as BaseCore;
use MatthiasWeb\RealMediaLibrary\comp\ExImport;
use MatthiasWeb\RealMediaLibrary\comp\ExportMediaLibrary;
use MatthiasWeb\RealMediaLibrary\comp\PageBuilders;
use MatthiasWeb\RealMediaLibrary\comp\PolyLang;
use MatthiasWeb\RealMediaLibrary\comp\WPML;
use MatthiasWeb\RealMediaLibrary\folder\Folder as FolderFolder;
use MatthiasWeb\RealMediaLibrary\lite\Core as LiteCore;
use MatthiasWeb\RealMediaLibrary\metadata\CoverImage;
use MatthiasWeb\RealMediaLibrary\metadata\Description;
use MatthiasWeb\RealMediaLibrary\metadata\Meta;
use MatthiasWeb\RealMediaLibrary\overrides\interfce\IOverrideCore;
use MatthiasWeb\RealMediaLibrary\rest\Attachment;
use MatthiasWeb\RealMediaLibrary\rest\Folder;
use MatthiasWeb\RealMediaLibrary\rest\Reset;
use MatthiasWeb\RealMediaLibrary\rest\Service;
use MatthiasWeb\RealMediaLibrary\usersettings\AllFilesShortcuts;
use MatthiasWeb\RealMediaLibrary\usersettings\DefaultFolder;
use MatthiasWeb\RealMediaLibrary\usersettings\InfiniteScrolling;
use MatthiasWeb\RealMediaLibrary\view\FolderShortcode;
use MatthiasWeb\RealMediaLibrary\view\Gutenberg;
use MatthiasWeb\RealMediaLibrary\view\Options;
use MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\Core as RealUtilsCore;
use MatthiasWeb\RealMediaLibrary\Vendor\MatthiasWeb\Utils\Service as UtilsService;
use MatthiasWeb\RealMediaLibrary\Vendor\MatthiasWeb\Utils\ServiceNoStore;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
require_once 'base/others/class-alias.php';
// @codeCoverageIgnoreEnd
/**
 * Singleton core class which handles the main system for plugin. It includes
 * registering of the autoload, all hooks (actions & filters) (see BaseCore class).
 * @internal
 */
class Core extends BaseCore implements IOverrideCore
{
    use LiteCore;
    /**
     * Singleton instance.
     */
    private static $me;
    /**
     * See RpmInitiator.
     *
     * @var RpmInitiator
     */
    private $rpmInitiator;
    /**
     * Application core constructor.
     */
    protected function __construct()
    {
        parent::__construct();
        // Enable `no-store` for our relevant WP REST API endpoints
        ServiceNoStore::hook('/' . UtilsService::getNamespace($this));
        ServiceNoStore::hook('/' . Service::LEGACY_NAMESPACE);
        \MatthiasWeb\RealMediaLibrary\Localization::enableWordPressDotOrgLanguagePacksDownload(RML_SLUG, RML_SLUG_LITE);
        // Register creatables
        \wp_rml_register_creatable(FolderFolder::class, RML_TYPE_FOLDER);
        // Register all your before init hooks here
        \add_action('admin_init', [Options::getInstance(), 'register_fields']);
        \add_filter('RML/Validate/Insert', [Permissions::getInstance(), 'insert'], 10, 3);
        \add_filter('RML/Validate/Create', [Permissions::getInstance(), 'create'], 10, 4);
        \add_filter('RML/Validate/Rename', [Permissions::getInstance(), 'setName'], 10, 3);
        \add_filter('RML/Validate/Delete', [Permissions::getInstance(), 'deleteFolder'], 10, 3);
        \add_filter('wp_die_ajax_handler', [$this, 'update_count'], 1);
        \add_filter('wp_die_handler', [$this, 'update_count'], 1);
        \add_filter('rest_post_dispatch', [$this, 'update_count']);
        \add_filter('wp_die_xmlrpc_handler', [$this, 'update_count'], 1);
        \add_filter('wp_redirect', [$this, 'update_count'], 1);
        $this->rpmInitiator = new \MatthiasWeb\RealMediaLibrary\RpmInitiator();
        $this->rpmInitiator->start();
        $this->overrideConstruct();
        $this->overrideConstructFreemium();
        $this->compatibilities(\false);
        (new \MatthiasWeb\RealMediaLibrary\AdInitiator())->start();
    }
    /**
     * Define constants which relies on i18n localization loaded.
     */
    public function i18n()
    {
        parent::i18n();
        $translatedUrl = \__('https://devowl.io/go/real-media-library?source=rml-lite', RML_TD);
        \define('RML_PRO_VERSION', $translatedUrl);
    }
    /**
     * The init function is fired even the init hook of WordPress. If possible
     * it should register all hooks to have them in one place.
     */
    public function init()
    {
        // Add our folder shortcode
        global $shortcode_tags;
        \add_shortcode('folder-gallery', $shortcode_tags['gallery']);
        FolderShortcode::getInstance();
        $restService = new Service();
        $restAttachment = new Attachment();
        // Register all your hooks here
        \add_action('rest_api_init', [$restService, 'rest_api_init']);
        \add_action('rest_api_init', [new Folder(), 'rest_api_init']);
        \add_action('rest_api_init', [$restAttachment, 'rest_api_init']);
        \add_action('rest_api_init', [new Reset(), 'rest_api_init']);
        \add_action('rest_attachment_collection_params', [$restAttachment, 'rest_attachment_collection_params']);
        \add_action('rest_attachment_query', [$restAttachment, 'rest_attachment_query'], 10, 2);
        \add_action('admin_enqueue_scripts', [$this->getAssets(), 'admin_enqueue_scripts']);
        \add_action('wp_enqueue_scripts', [$this->getAssets(), 'wp_enqueue_scripts']);
        \add_action('customize_controls_print_scripts', [$this->getAssets(), 'customize_controls_print_scripts']);
        \add_action('pre_get_posts', [Filter::getInstance(), 'pre_get_posts'], 998);
        \add_action('delete_attachment', [Shortcut::getInstance(), 'delete_attachment']);
        \add_action('delete_attachment', [Filter::getInstance(), 'delete_attachment']);
        \add_action('plugin_row_meta', [$this->getAssets(), 'plugin_row_meta'], 10, 2);
        \add_action('pre-upload-ui', [Upload::getInstance(), 'pre_upload_ui']);
        \add_action('add_attachment', [Upload::getInstance(), 'add_attachment']);
        \add_action('wp_prepare_attachment_for_js', [Filter::getInstance(), 'wp_prepare_attachment_for_js'], 10, 3);
        \add_action('RML/Options/Register', [ExImport::getInstance(), 'options_register']);
        \add_action('RML/Folder/Deleted', [Meta::getInstance(), 'folder_deleted'], 10, 2);
        \add_action('RML/Scripts', [RealUtilsCore::getInstance()->getAssets(), 'admin_enqueue_scripts']);
        \add_filter('posts_clauses', [Filter::getInstance(), 'posts_clauses'], 10, 2);
        \add_filter('media_view_strings', [$this->getAssets(), 'media_view_strings']);
        \add_filter('media_row_actions', [$this->getAssets(), 'media_row_actions'], 10, 2);
        \add_filter('add_post_metadata', [Shortcut::getInstance(), 'add_post_metadata'], 999999, 5);
        \add_filter('update_post_metadata', [Shortcut::getInstance(), 'update_post_metadata'], 999999, 5);
        \add_filter('get_post_metadata', [Shortcut::getInstance(), 'get_post_metadata'], 999999, 4);
        \add_filter('attachment_fields_to_edit', [CustomField::getInstance(), 'attachment_fields_to_edit'], 10, 2);
        \add_filter('attachment_fields_to_save', [CustomField::getInstance(), 'attachment_fields_to_save'], 10, 2);
        \add_filter('restrict_manage_posts', [Filter::getInstance(), 'restrict_manage_posts']);
        \add_filter('ajax_query_attachments_args', [Filter::getInstance(), 'ajax_query_attachments_args']);
        \add_filter('mla_media_modal_query_final_terms', [Filter::getInstance(), 'ajax_query_attachments_args']);
        \add_filter('shortcode_atts_gallery', [FolderShortcode::getInstance(), 'shortcode_atts_gallery'], 1, 3);
        \add_filter('superpwa_sw_never_cache_urls', [$restService, 'superpwa_exclude_from_cache']);
        // Predefined meta boxes
        \add_rml_meta_box('general', Meta::getInstance(), \false, 0);
        \add_rml_meta_box('description', new Description(), \false, 0);
        \add_rml_meta_box('coverImage', new CoverImage(), \false, 0);
        \add_rml_user_settings_box('allFilesShortcuts', new AllFilesShortcuts(), \false, 0);
        $infiniteScrolling = new InfiniteScrolling();
        if ($infiniteScrolling->isAvailable()) {
            \add_rml_user_settings_box('infiniteScrolling', $infiniteScrolling, \false, 0);
        }
        \add_rml_user_settings_box('defaultFolder', new DefaultFolder(), \false, 0);
        //add_rml_user_settings_box('demo', new \MatthiasWeb\RealMediaLibrary\usersettings\Demo(), false, 0);
        // Gutenberg blocks
        if (\function_exists('register_block_type')) {
            Gutenberg::getInstance();
            \add_action('enqueue_block_editor_assets', [$this->getAssets(), 'enqueue_block_editor_assets']);
        }
        $this->compatibilities(\true);
        $this->overrideInit();
    }
    /**
     * Allow a better compatibility for other plugins.
     *
     * Have a look at the class' constructors for all needed filters and actions.
     *
     * @param boolean $init
     */
    private function compatibilities($init)
    {
        if ($init) {
            // @see https://wordpress.org/plugins/export-media-library/
            if (\defined('MASSEDGE_WORDPRESS_PLUGIN_EXPORT_MEDIA_LIBRARY_PLUGIN_PATH')) {
                $data = \get_plugin_data(\constant('MASSEDGE_WORDPRESS_PLUGIN_EXPORT_MEDIA_LIBRARY_PLUGIN_PATH'), \true, \false);
                if (\version_compare($data['Version'], '2.0.0', '>=')) {
                    new ExportMediaLibrary();
                }
            }
        } else {
            \add_action('init', [PolyLang::getInstance(), 'init']);
            \add_action('init', [WPML::getInstance(), 'init'], 9);
            \add_action('init', [PageBuilders::getInstance(), 'init']);
        }
    }
    /**
     * Use the wp die filter to make the last update count;
     *
     * @param mixed $result
     * @return mixed
     */
    public function update_count($result)
    {
        CountCache::getInstance()->wp_die();
        /**
         * This function is called at the end of: AJAX Handler, WP Handler, REST Handler.
         * You can collect for example batch actions and merge it to one SQL query.
         *
         * @hook RML/Die
         * @since 4.0.2
         */
        \do_action('RML/Die');
        return $result;
    }
    /**
     * Static method to get a RML table name.
     *
     * @param string $name
     * @return string
     * @see Core::getTableName
     */
    public static function tableName($name = '')
    {
        return self::getInstance()->getTableName($name);
    }
    /**
     * Get ad initiator from `real-product-manager-wp-client`.
     *
     * @codeCoverageIgnore
     */
    public function getRpmInitiator()
    {
        return $this->rpmInitiator;
    }
    /**
     * Get singleton core class.
     *
     * @return Core
     */
    public static function getInstance()
    {
        return !isset(self::$me) ? self::$me = new \MatthiasWeb\RealMediaLibrary\Core() : self::$me;
    }
}
// can not be placed in class_alias.php because for Core the class must exist (e. g. Justified Image Grid)
\class_alias(\MatthiasWeb\RealMediaLibrary\Core::class, RML_NS . '\\general\\Core');
/**
 * See API docs.
 *
 * @api {get} /real-media-library/v1/plugin Get plugin information
 * @apiHeader {string} X-WP-Nonce
 * @apiName GetPlugin
 * @apiGroup Plugin
 *
 * @apiSuccessExample {json} Success-Response:
 * {
 *     Name: "My plugin",
 *     PluginURI: "https://example.com/my-plugin",
 *     Version: "0.1.0",
 *     Description: "This plugin is doing something.",
 *     Author: "<a href="https://example.com">John Smith</a>",
 *     AuthorURI: "https://example.com",
 *     TextDomain: "my-plugin",
 *     DomainPath: "/languages",
 *     Network: false,
 *     Title: "<a href="https://example.com">My plugin</a>",
 *     AuthorName: "John Smith"
 * }
 * @apiVersion 0.1.0
 */
