<?php

namespace MatthiasWeb\RealMediaLibrary\Vendor\MatthiasWeb\Utils;

\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request.
/**
 * The activator class handles the plugin relevant activation hooks: Uninstall, activation,
 * deactivation and installation. The "installation" means installing needed database tables.
 * @internal
 */
trait Activator
{
    /**
     * See `$this->getCharsetCollate()`.
     *
     * @var string
     */
    private $charsetCollate = null;
    /**
     * Install tables, stored procedures or whatever in the database.
     * This method is always called when the version bumps up or for
     * the first initial activation.
     *
     * Use the following relevant functions to create your database tables:
     *
     * - https://developer.wordpress.org/reference/functions/dbdelta/
     * - `$this->getCharsetCollate()` instead of `$wpdb->get_charset_collate()`
     * - `$this->getTableName()` (from `UtilsProvider`) instead of `$wpdb->prefix`
     * - `$this->getMaxIndexLength()` if you want to create an index from multiple `varchar` columns
     *
     * @param boolean $errorlevel If true throw errors.
     */
    public abstract function dbDelta($errorlevel);
    /**
     * Return the first ever created database table created by this plugin.
     *
     * This will be used to get the charset collate via `$this->getCharsetCollate()` to
     * keep the charset collate intact within our plugin. Example: User updates the database version;
     * and avoid mixed collates when a new database table gets collate `utf8mb4_unicode_520_ci`
     * instead of `utf8mb4_unicode_ci`.
     *
     * Return an empty string if your plugin does not create any database tables!
     *
     * @see https://github.com/WordPress/WordPress/blob/d8c4000c53d4186b74a14c435ae44f547fde48d3/wp-includes/class-wpdb.php#L906C44-L908
     * @see https://github.com/WordPress/WordPress/blob/d8c4000c53d4186b74a14c435ae44f547fde48d3/wp-includes/class-wpdb.php#L4077-L4078
     * @return string
     */
    public abstract function getFirstDatabaseTableName();
    /**
     * Register custom capabilities with e.g. `wp_roles()` and `->add_cap()`. If you add capabilities, please also remove
     * them through the `deactivate` method.
     */
    public function registerCapabilities()
    {
        // Silence is golden.
    }
    /**
     * Indexes have a maximum size of 767 bytes. Historically, we haven't need to be concerned about that.
     * As of 4.2, however, we moved to utf8mb4, which uses 4 bytes per character. This means that an index which
     * used to have room for floor(767/3) = 255 characters, now only has room for floor(767/4) = 191 characters.
     *
     * @see https://github.com/WordPress/WordPress/blob/5f9cf0141e2e32f47ae7f809b7a6bbc0d4bd4ef2/wp-admin/includes/schema.php#L48-L53
     * @codeCoverageIgnore
     */
    public function getMaxIndexLength()
    {
        return 191;
    }
    /**
     * Get the charset collate definition for the SQL command. Similar to `$wpdb->get_charset_collate()` but
     * it ensures to **not** mix the collates of our plugin database tables.
     *
     * @see https://github.com/WordPress/WordPress/blob/9e0f2faa28f1aa04a69e0b4eaa410a38adcd2e1e/wp-includes/class-wpdb.php#L4000-L4010
     */
    public function getCharsetCollate()
    {
        global $wpdb;
        if ($this->charsetCollate !== null) {
            return $this->charsetCollate;
        }
        $tableName = $this->getFirstDatabaseTableName();
        if (!empty($tableName)) {
            // phpcs:disable WordPress.DB.PreparedSQL
            $tableDetails = $wpdb->get_row("SHOW TABLE STATUS LIKE '{$tableName}'");
            // phpcs:enable WordPress.DB.PreparedSQL
            if ($tableDetails) {
                // Table exists, read the charset collate from that table
                $tableDetails = $wpdb->get_row($wpdb->prepare('SELECT t.TABLE_COLLATION AS `collate`, ccsa.CHARACTER_SET_NAME AS `charset`
                            FROM information_schema.TABLES t,
                                 information_schema.COLLATION_CHARACTER_SET_APPLICABILITY ccsa
                            WHERE ccsa.collation_name = t.table_collation
                                AND t.table_schema = %s
                                AND t.table_name = %s', \constant('DB_NAME'), $tableName), ARRAY_A);
                if ($tableDetails && !empty($tableDetails['collate']) && !empty($tableDetails['charset'])) {
                    $this->charsetCollate = 'DEFAULT CHARACTER SET ' . $tableDetails['charset'];
                    $this->charsetCollate .= ' COLLATE ' . $tableDetails['collate'];
                }
            }
        }
        if (empty($this->charsetCollate)) {
            // Fallback to current if the above existing table returned no valid charset
            $this->charsetCollate = $wpdb->get_charset_collate();
        }
        return $this->charsetCollate;
    }
    /**
     * Remove database tables if they exist.
     *
     * @param string[] $tableNames This is not escaped, so use only the result of `$this->getTableName()`!
     */
    public function removeTables($tableNames)
    {
        global $wpdb;
        foreach ($tableNames as $tableName) {
            // phpcs:disable WordPress.DB.PreparedSQL
            $tableDetails = $wpdb->get_row("SHOW TABLE STATUS LIKE '{$tableName}'");
            // phpcs:enable WordPress.DB.PreparedSQL
            if (!$tableDetails) {
                continue;
            }
            // phpcs:disable WordPress.DB.PreparedSQL
            $wpdb->query("DROP TABLE {$tableName}");
            // phpcs:enable WordPress.DB.PreparedSQL
        }
    }
    /**
     * `dbDelta` does currently not support removing indices from tables so updating e.g. `UNIQUE KEYS` does not work.
     * For this, you need to add a new index name and remove the old one.
     *
     * The index needs to be configured like this:
     *
     * ```
     * $indexConfigurations = [
     *   'PRIMARY' = ['myColumn1', 'myColumn2']
     * ]
     * ```
     *
     * @param string $tableName This is not escaped, so use only the result of `$this->getTableName()`!
     * @param array[] $indexConfigurations
     * @see https://whtly.com/2010/04/02/wp-dbdelta-function-cannot-modify-unique-keys/
     */
    public function removeIndicesFromTable($tableName, $indexConfigurations)
    {
        global $wpdb;
        // phpcs:disable WordPress.DB.PreparedSQL
        $existingIndexesNonGrouped = $wpdb->get_results("SHOW INDEX FROM {$tableName}", ARRAY_A);
        // phpcs:enable WordPress.DB.PreparedSQL
        if ($existingIndexesNonGrouped) {
            $removeIndexes = [];
            $existingIndexes = [];
            foreach ($existingIndexesNonGrouped as $idxRow) {
                $existingIndexes[$idxRow['Key_name']] = $existingIndexes[$idxRow['Key_name']] ?? [];
                $existingIndexes[$idxRow['Key_name']][] = $idxRow['Column_name'];
            }
            $indexNames = \array_keys($indexConfigurations);
            foreach ($existingIndexes as $keyName => $columns) {
                if (\in_array($keyName, $indexNames, \true) && \join(',', $columns) === \join(',', $indexConfigurations[$keyName])) {
                    $removeIndexes[] = $keyName;
                }
            }
            $removeIndexes = \array_unique($removeIndexes);
            foreach ($removeIndexes as $rm) {
                if ($rm === 'PRIMARY') {
                    $rm = 'PRIMARY KEY';
                } else {
                    $rm = "INDEX {$rm}";
                }
                // phpcs:disable WordPress.DB.PreparedSQL
                $wpdb->query("ALTER TABLE {$tableName} DROP {$rm}");
                // phpcs:enable WordPress.DB.PreparedSQL
            }
        }
    }
    /**
     * `dbDelta` does currently not support removing columns from tables. For this, we need to read the structure of the
     * table and remove the column accordingly on existence.
     *
     * @param string $tableName This is not escaped, so use only the result of `$this->getTableName()`!
     * @param string[] $columnNames
     */
    public function removeColumnsFromTable($tableName, $columnNames)
    {
        global $wpdb;
        // phpcs:disable WordPress.DB.PreparedSQL
        $existingColumns = $wpdb->get_results("SHOW COLUMNS FROM {$tableName}", ARRAY_A);
        // phpcs:enable WordPress.DB.PreparedSQL
        if ($existingColumns) {
            $removeColumns = [];
            $columnNames = \array_map('strtolower', $columnNames);
            foreach ($existingColumns as $existingColumn) {
                if (\in_array(\strtolower($existingColumn['Field']), $columnNames, \true)) {
                    $removeColumns[] = $existingColumn['Field'];
                }
            }
            $removeColumns = \array_unique($removeColumns);
            foreach ($removeColumns as $rm) {
                // phpcs:disable WordPress.DB.PreparedSQL
                $wpdb->query("ALTER TABLE {$tableName} DROP COLUMN {$rm}");
                // phpcs:enable WordPress.DB.PreparedSQL
            }
        }
    }
    /**
     * Run an installation or dbDelta within a callable.
     *
     * @param boolean $errorlevel Set true to throw errors.
     * @param callable $installThisCallable Set a callable to install this one instead of the default.
     * @return bool Returns `false` when e.g. the migration got locked
     */
    public function install($errorlevel = \false, $installThisCallable = null)
    {
        global $wpdb;
        // @codeCoverageIgnoreStart
        if (!\defined('PHPUNIT_FILE')) {
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        }
        // @codeCoverageIgnoreEnd
        // Avoid errors printed out.
        if ($errorlevel === \false) {
            $show_errors = $wpdb->show_errors(\false);
            $suppress_errors = $wpdb->suppress_errors(\false);
            $errorLevel = \error_reporting(0);
        }
        if ($installThisCallable === null) {
            $this->dbDelta($errorlevel);
        } else {
            \call_user_func($installThisCallable);
        }
        if ($errorlevel === \false) {
            $wpdb->show_errors($show_errors);
            $wpdb->suppress_errors($suppress_errors);
            \error_reporting($errorLevel);
        }
        if ($installThisCallable === null) {
            $this->persistPreviousVersion();
            \update_option($this->getPluginConstant(Constants::PLUGIN_CONST_OPT_PREFIX) . '_db_version', $this->getPluginConstant(Constants::PLUGIN_CONST_VERSION));
        }
        return \true;
    }
    /**
     * Check if the migration is locked. It uses a time span of 10 minutes (like Yoast SEO plugin).
     *
     * @see https://github.com/Yoast/wordpress-seo/blob/a5fd83173bf56bf7841d72bb6d3d33ecc4caa825/src/config/migration-status.php#L34-L46
     * @param int $set
     * @return If `$set` is a numeric, it returns a boolean indicating if the update of the migration was successful, otherwise it returns a boolean
     *         if the migration is locked.
     */
    public function isMigrationLocked($set = null)
    {
        $optionName = $this->getPluginConstant(Constants::PLUGIN_CONST_OPT_PREFIX) . '_db_migration';
        if (\is_numeric($set)) {
            return \update_option($optionName, $set);
        }
        $latestMigration = \intval(\get_option($optionName, 0));
        if ($latestMigration > 0) {
            return $latestMigration > \strtotime('-10 minutes');
        } else {
            return \false;
        }
    }
    /**
     * Get the current persisted database version.
     */
    public function getDatabaseVersion()
    {
        return \get_option($this->getPluginConstant(Constants::PLUGIN_CONST_OPT_PREFIX) . '_db_version');
    }
    /**
     * Get a list of previous installed database versions.
     *
     * @return string[]
     */
    public function getPreviousDatabaseVersions()
    {
        return \get_option($this->getPluginConstant(Constants::PLUGIN_CONST_OPT_PREFIX) . '_db_previous_version', []);
    }
    /**
     * Persist the previous installed versions of this plugin so we can e.g. start migrations.
     */
    public function persistPreviousVersion()
    {
        $currentVersion = \get_option($this->getPluginConstant(Constants::PLUGIN_CONST_OPT_PREFIX) . '_db_version');
        if ($currentVersion !== \false) {
            $previousVersionsOptionName = $this->getPluginConstant(Constants::PLUGIN_CONST_OPT_PREFIX) . '_db_previous_version';
            $previousVersions = $this->getPreviousDatabaseVersions();
            // Extract only "real" versioning in semver format (x.y.z), but no prereleases
            \preg_match('/(\\d+\\.\\d+\\.\\d+)/', $currentVersion, $matches, \PREG_OFFSET_CAPTURE, 0);
            $pureVersion = $matches[0][0];
            $previousVersions[] = $pureVersion;
            $previousVersions = \array_unique($previousVersions);
            \update_option($previousVersionsOptionName, $previousVersions);
        }
    }
    /**
     * Remove the previous persisted versions from the saved option. This is useful if you have
     * successfully finished your migration.
     *
     * @param callback $filter
     */
    public function removePreviousPersistedVersions($filter)
    {
        $versions = $this->getPreviousDatabaseVersions();
        $versions = \array_filter($versions, $filter);
        return \update_option($this->getPluginConstant(Constants::PLUGIN_CONST_OPT_PREFIX) . '_db_previous_version', $versions);
    }
}
