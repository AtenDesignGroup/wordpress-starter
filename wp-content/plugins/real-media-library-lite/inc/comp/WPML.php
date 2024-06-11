<?php

namespace MatthiasWeb\RealMediaLibrary\comp;

use MatthiasWeb\RealMediaLibrary\api\IFolder;
use MatthiasWeb\RealMediaLibrary\attachment\CountCache;
use MatthiasWeb\RealMediaLibrary\base\UtilsProvider;
use MatthiasWeb\RealMediaLibrary\lite\comp\WPML as CompWPML;
use MatthiasWeb\RealMediaLibrary\overrides\interfce\comp\IOverrideWPML;
use MatthiasWeb\RealMediaLibrary\Util;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * This class handles the compatibility for WPML.
 * @internal
 */
class WPML implements IOverrideWPML
{
    use CompWPML;
    use UtilsProvider;
    private static $me = null;
    /**
     * Avoid duplicate call of move action.
     */
    private $previousIds = null;
    /**
     * Avoid duplicate call of move action.
     */
    private $previousFolderId = null;
    /**
     * C'tor.
     */
    private function __construct()
    {
        \add_action('DevOwl/Utils/NewVersionInstallation/' . RML_SLUG, [$this, 'dbDeltaCountCache']);
    }
    /**
     * Initialize actions and filters.
     */
    public function init()
    {
        global $sitepress;
        if ($this->isActive()) {
            \add_action('wpml_media_create_duplicate_attachment', [$this, 'wpml_media_create_duplicate_attachment'], 10, 2);
            \add_action('RML/Options/Register', [$this, 'options_register']);
            \add_action('RML/Item/MoveFinished', [$this, 'item_move_finished'], 10, 4);
            \add_action('wpml_update_active_languages', [$this, 'wpml_update_active_languages']);
            \add_action('RML/Count/Update', [$this, 'updateCountCache'], 10, 3);
            \add_action('RML/Count/Reset', [$this, 'resetCountCache']);
            \add_filter('RML/Tree/SQLStatement/SELECT', [$this, 'sqlstatement_select_fields']);
            \add_filter('RML/Tree/SQLStatement/JOIN', [$this, 'sqlstatement_join']);
            \add_filter('RML/Tree/CountAttachments', [$this, 'wpml_count_attachments']);
            \add_filter('RML/Localize', [$this, 'localize']);
            \add_filter('RML/Scripts/Skip', [$this, 'scripts_skip'], 10, 3);
            // Set the RML + WPML language to the user
            if (\is_user_logged_in()) {
                $userId = \get_current_user_id();
                $userLanguage = \get_user_meta($userId, 'rml_wpml_lang', \true);
                $currentLanguage = $sitepress->get_current_language();
                if ($userLanguage !== $currentLanguage) {
                    $saveLanguage = $currentLanguage === 'all' ? $sitepress->get_default_language() : $currentLanguage;
                    // "all" is not allowed for WPML / RML
                    \update_user_meta($userId, 'rml_wpml_lang', $saveLanguage);
                }
            }
            $this->overrideInit();
        }
    }
    /**
     * Skip RML scripts in String Translations as it leads to conflicts with `antd`.
     *
     * @param boolean $skip True for skip and false for load
     * @param string $type The context type
     * @param string $hook_suffix The current admin page
     */
    public function scripts_skip($skip, $type, $hook_suffix)
    {
        if ($hook_suffix === 'wpml-string-translation/menu/string-translation.php') {
            return \false;
        }
        return $skip;
    }
    /**
     * Localize frontend.
     *
     * @param array $arr
     * @return array
     */
    public function localize($arr)
    {
        global $sitepress;
        if (!$this->isDefaultLanguage()) {
            $arr['restQuery']['lang'] = $sitepress->get_current_language();
        }
        return $arr;
    }
    /**
     * Add a JOIN to the WPML count cache table.
     *
     * @param string[] $joins
     * @return string[]
     */
    public function sqlstatement_join($joins)
    {
        global $wpdb;
        $table_name = $this->getTableName('icl_count');
        // Check if table exists and create it
        // phpcs:disable WordPress.DB.PreparedSQL
        $exists = $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") === $table_name;
        // phpcs:enable WordPress.DB.PreparedSQL
        if (!$exists) {
            $this->dbDeltaCountCache();
        }
        $joins[] = "LEFT JOIN {$table_name} AS rmlicl ON tn.id = rmlicl.fid";
        return $joins;
    }
    /**
     * Load the cnt from the WPML count cache table.
     *
     * @param string[] $fields
     * @return string[]
     */
    public function sqlstatement_select_fields($fields)
    {
        global $sitepress;
        $currentLanguage = $sitepress->get_current_language();
        $escaped = Util::getInstance()->esc_sql_name($currentLanguage);
        $fields[1] = "rmlicl.`cnt_{$escaped}` AS cnt, IFNULL(rmlicl.`cnt_{$escaped}`, (\n            " . $this->getSingleCountSql($currentLanguage, 'tn.id') . '
        )) AS cnt_result';
        return $fields;
    }
    /**
     * Get the single SQL for the subquery of count getter.
     *
     * @param string $code
     * @param string $fieldId
     * @return string
     */
    public function getSingleCountSql($code, $fieldId = 'tn.fid')
    {
        global $wpdb;
        $table_name_posts = $this->getTableName('posts');
        $where = empty($fieldId) ? '' : "WHERE rmlpostscnt.fid = {$fieldId}";
        return "SELECT COUNT(*) FROM {$table_name_posts} AS rmlpostscnt\n        \tINNER JOIN " . $wpdb->prefix . "icl_translations AS wpmlt\n        \tON wpmlt.element_id = rmlpostscnt.attachment\n        \tAND wpmlt.element_type = 'post_attachment'\n        \tAND wpmlt.language_code = '{$code}'\n        \t{$where}";
    }
    /**
     * Update the count cache for WPML regarding the active languages.
     *
     * @param int[] $folders
     * @param int[] $attachments
     * @param string $where
     */
    public function updateCountCache($folders, $attachments, $where)
    {
        global $wpdb, $sitepress;
        $table_name = $this->getTableName();
        $table_name_icl = $this->getTableName('icl_count');
        $table_name_posts = $this->getTableName('posts');
        $langs = $this->getActiveLanguages();
        $where = $where !== 'tn.cnt IS NULL' ? \str_replace('tn.id', 'tn.fid', $where) : '1=1';
        // Keep both tables synced (performance should be good because both queries use the best available index)
        // phpcs:disable WordPress.DB.PreparedSQL
        $wpdb->query("INSERT IGNORE INTO {$table_name_icl} (`fid`) SELECT id FROM {$table_name}");
        $wpdb->query("DELETE rmlicl FROM {$table_name_icl} AS rmlicl WHERE NOT EXISTS(SELECT * FROM {$table_name} AS rml WHERE rml.id = rmlicl.fid)");
        // phpcs:enable WordPress.DB.PreparedSQL
        // Sync available languages counts
        $setters = [];
        $readers = [];
        foreach ($langs as $code) {
            $escaped = Util::getInstance()->esc_sql_name($code);
            $setters[] = "tn.`cnt_{$escaped}` = curr.`cnt_{$escaped}`";
            // phpcs:disable WordPress.DB.PreparedSQL
            $readers[] = $wpdb->prepare("SUM(IF(wpmlt.language_code = %s, 1, 0)) AS `cnt_{$escaped}`", $code);
            // phpcs:enable WordPress.DB.PreparedSQL
        }
        // Create UPDATE query
        // phpcs:disable WordPress.DB.PreparedSQL
        $sqlStatement = "UPDATE {$table_name_icl} AS tn\n            INNER JOIN (\n                SELECT rmlic.fid, " . \join(',', $readers) . "\n                FROM {$table_name_icl} rmlic\n                LEFT JOIN {$table_name_posts} rmlpostscnt\n                    ON rmlpostscnt.fid = rmlic.fid\n                LEFT JOIN " . $wpdb->prefix . "icl_translations wpmlt\n                    ON wpmlt.element_id = rmlpostscnt.attachment\n                    AND wpmlt.element_type = 'post_attachment'\n                GROUP BY rmlic.fid\n            ) curr\n            ON curr.fid = tn.fid\n            SET " . \join(',', $setters) . "\n            WHERE {$where}";
        $wpdb->query($sqlStatement);
        // phpcs:enable WordPress.DB.PreparedSQL
        $this->debug('WPML: Update count cache table', __METHOD__);
    }
    /**
     * Reset the count cache for WPML regarding the active languages.
     *
     * @param int $folderId
     */
    public function resetCountCache($folderId)
    {
        global $wpdb;
        $table_name = $this->getTableName('icl_count');
        // phpcs:disable WordPress.DB.PreparedSQL
        if (\is_array($folderId)) {
            $wpdb->query("DELETE FROM {$table_name} WHERE fid IN (" . \implode(',', $folderId) . ')');
        } else {
            $wpdb->query("DELETE FROM {$table_name}");
        }
        // phpcs:enable WordPress.DB.PreparedSQL
    }
    /**
     * Fired when wpml language gets activated.
     */
    public function wpml_update_active_languages()
    {
        $this->debug('WPML: Update active languages in count cache table', __METHOD__);
        $this->dbDeltaCountCache();
    }
    /**
     * Create a count cache table with dbDelta functionality.
     */
    public function dbDeltaCountCache()
    {
        if (!$this->isActive()) {
            return \false;
        }
        $this->getCore()->getActivator()->install(\false, [$this, '_dbDeltaCountCache']);
        return \true;
    }
    /**
     * Create the icl_count table.
     */
    public function _dbDeltaCountCache()
    {
        $charset_collate = $this->getCore()->getActivator()->getCharsetCollate();
        $table_name = $this->getTableName('icl_count');
        $langs = $this->getActiveLanguages();
        if (\count($langs) > 0) {
            $keys = '';
            foreach ($langs as $code) {
                $escaped = Util::getInstance()->esc_sql_name($code);
                $keys .= "`cnt_{$escaped}` mediumint(10) DEFAULT NULL,\n    \t\t    ";
            }
            $sql = "CREATE TABLE {$table_name} (\n    \t\t  fid mediumint(9) NOT NULL,\n    \t\t  " . \trim($keys) . "\n    \t\t  PRIMARY KEY  (fid)\n    \t\t) {$charset_collate};";
            \dbDelta($sql);
            // CountCache::getInstance()->updateCountCache();
        }
    }
    /**
     * Register option for PolyLang.
     */
    public function options_register()
    {
        \register_setting('media', 'rml_wpml_move', 'esc_attr');
        \add_settings_field('rml_wpml_move', '<label for="rml_wpml_move">' . \__('WPML: Automatically move translations', RML_TD) . '</label>', [$this, 'html_options_move'], 'media', 'rml_options_general');
    }
    /**
     * Option to move files also when a translation gets moved.
     */
    public function html_options_move()
    {
        $value = \get_option('rml_wpml_move', '1');
        echo '<input type="checkbox" id="rml_wpml_move"
                name="rml_wpml_move" value="1" ' . \checked(1, $value, \false) . ' />
                <label>' . \__('If you move a file, the corresponding translated file will also be moved.', RML_TD) . '</label>';
    }
    /**
     * A file is moved (not copied) and then move also all the translations.
     *
     * @param int $folderId
     * @param int[] $ids
     * @param IFolder $folder
     * @param boolean $isShortcut
     */
    public function item_move_finished($folderId, $ids, $folder, $isShortcut)
    {
        if (\get_option('rml_wpml_move', '1') === '1' && \json_encode($ids) !== \json_encode($this->previousIds) && $folderId !== $this->previousFolderId) {
            global $sitepress;
            $moveToFolder = [];
            $this->previousFolderId = $folderId;
            $this->previousIds = $ids;
            // Iterate all moved ids
            foreach ($ids as $post_id) {
                $trid = $sitepress->get_element_trid($post_id);
                $translations = $sitepress->get_element_translations($trid, 'post_attachment');
                // Iterate all translation ids
                foreach ($translations as $tr) {
                    $tr_id = \intval($tr->element_id);
                    if (!\in_array($tr_id, $ids, \true)) {
                        $moveToFolder[] = $tr_id;
                    }
                }
            }
            if (\count($moveToFolder) > 0) {
                $this->debug("WPML: While moving to folder {$folderId} there are some translations which also must be moved: " . \json_encode($moveToFolder), __METHOD__);
                \wp_rml_move($folderId, $moveToFolder);
            }
        }
    }
    /**
     * New translation created => synchronize with original post.
     * Then reset the count cache for the unorganized folder.
     *
     * @param int $post_id
     * @param int $tr_id
     */
    public function wpml_media_create_duplicate_attachment($post_id, $tr_id)
    {
        $folderId = \wp_attachment_folder($post_id);
        \_wp_rml_synchronize_attachment($tr_id, $folderId);
        $this->debug('WPML: Move translation id ' . $tr_id . ' to the original file (' . $post_id . ') folder id ' . $folderId, __METHOD__);
        CountCache::getInstance()->addNewAttachment($tr_id)->resetCountCacheOnWpDie(\_wp_rml_root());
    }
    /**
     * Count attachments through WPML tables.
     *
     * @param int $count
     * @return int
     * @see https://wpml.org/forums/topic/wp_count_posts/
     */
    public function wpml_count_attachments($count)
    {
        global $wpdb, $sitepress;
        $lang = $sitepress->get_current_language();
        $table_name = $wpdb->prefix . 'icl_translations';
        // phpcs:disable WordPress.DB.PreparedSQL
        return (int) $wpdb->get_var("SELECT COUNT(*)\n            FROM {$table_name} AS wpmlt\n            INNER JOIN {$wpdb->posts} AS p ON p.id = wpmlt.element_id\n            WHERE wpmlt.element_type =  'post_attachment'\n            AND wpmlt.language_code =  '{$lang}'");
        // phpcs:enable WordPress.DB.PreparedSQL
    }
    /**
     * Check if current active language is default.
     *
     * @return boolean
     */
    public function isDefaultLanguage()
    {
        global $sitepress;
        return $sitepress->get_current_language() === $sitepress->get_default_language();
    }
    /**
     * Get the default id for an attachment.
     *
     * @param int $attachment
     * @return int
     */
    public function getDefaultId($attachment)
    {
        global $sitepress;
        return \apply_filters('wpml_object_id', $attachment, 'post', \true, $sitepress->get_default_language());
    }
    /**
     * Get active WPML language codes.
     *
     * @return string[]
     */
    public function getActiveLanguages()
    {
        return \array_keys(\apply_filters('wpml_active_languages', []));
    }
    /**
     * Check if WPML plugin is active.
     */
    public function isActive()
    {
        return \is_plugin_active('sitepress-multilingual-cms/sitepress.php') && \apply_filters('wpml_is_translated_post_type', \false, 'attachment');
    }
    /**
     * Get instance.
     *
     * @return WPML
     */
    public static function getInstance()
    {
        return self::$me === null ? self::$me = new \MatthiasWeb\RealMediaLibrary\comp\WPML() : self::$me;
    }
}
