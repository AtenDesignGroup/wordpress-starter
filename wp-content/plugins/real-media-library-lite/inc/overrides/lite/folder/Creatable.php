<?php

namespace MatthiasWeb\RealMediaLibrary\lite\folder;

use Exception;
use MatthiasWeb\RealMediaLibrary\exception\FolderAlreadyExistsException;
use MatthiasWeb\RealMediaLibrary\exception\OnlyInProVersionException;
use MatthiasWeb\RealMediaLibrary\Util;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/** @internal */
trait Creatable
{
    // Documented in IFolderActions
    public function resetSubfolderOrder()
    {
        throw new OnlyInProVersionException(__METHOD__);
    }
    // Documented in IFolderActions
    public function orderSubfolders($orderby, $writeMetadata = \true)
    {
        throw new OnlyInProVersionException(__METHOD__);
    }
    // Documented in IFolderActions
    protected function applySubfolderOrderBy()
    {
        return \false;
    }
    // Documented in IOverrideCreatable
    public function persistCheckParent()
    {
        // The Lite version can currently not handle hierarchical folders
        if ($this->parent > -1) {
            throw new OnlyInProVersionException(__METHOD__);
        }
    }
    // Documented in IFolderActions
    public function reindexChildrens($resetData = \false)
    {
        return \false;
    }
    // Documented in IFolder
    public function isValidChildrenType($type)
    {
        // In Lite version we can only have subfolders in Unorganized, so allow all folder types
        return \true;
    }
    // Documented in IFolderActions
    public function setParent($id, $ord = -1, $force = \false)
    {
        // Always root as the Lite version does not support subfolders
        $rootFolderId = \_wp_rml_root();
        $rootFolder = \wp_rml_get_object_by_id($rootFolderId);
        $this->debug("Try to set parent of {$this->id} from {$this->parent} to {$rootFolderId}...", __METHOD__);
        // Get the parent object
        if ($rootFolderId === $this->parent) {
            $this->debug('The parent is the same, probably only the order is changed...', __METHOD__);
        } elseif ($rootFolder->hasChildren($this->name)) {
            // For backwards compatibility (4.12.0) we need to do this checks in Lite version, too
            // Check, if the parent has already the given folder name
            throw new FolderAlreadyExistsException($rootFolderId, $this->name);
        }
        $newOrder = $ord > -1 ? $ord : $rootFolder->getMaxOrder() + 1;
        $isRelocate = $rootFolderId === $this->parent;
        /**
         * This action is called when a folder was relocated in the folder tree. That
         * means the parent was not changed, only the order was changed.
         *
         * @param {IFolder} $folder The folder object
         * @param {int} $id The new parent folder id
         * @param {int} $order The (new) order number
         * @param {boolean} $force If true the relocating was forced
         * @hook RML/Folder/Relocate
         * @since 4.0.7
         */
        // For backwards compatibility (4.12.0) we need the other action, too
        \do_action($isRelocate ? 'RML/Folder/Relocate' : 'RML/Folder/Move', $this, $rootFolderId, $newOrder, $force);
        $oldData = $this->getRowData();
        $beforeId = $this->parent;
        $this->parent = $rootFolderId;
        $this->order = $newOrder;
        $this->debug("Use {$this->order} (passed {$ord} as parameter) as new order value", __METHOD__);
        // Save in database
        if ($this->id > -1) {
            global $wpdb;
            // Update childrens absolute pathes (backwards compatible 4.12.0)
            if ($beforeId !== $this->parent) {
                $this->updateThisAndChildrensAbsolutePath();
            }
            // Update order
            // phpcs:disable WordPress.DB.PreparedSQL
            $table_name = $this->getTableName();
            $wpdb->query($wpdb->prepare("UPDATE {$table_name} SET parent=%d, ord=%d WHERE id = %d", $rootFolderId, $this->order, $this->id));
            // phpcs:enable WordPress.DB.PreparedSQL
            /**
             * This action is called when a folder was relocated in the folder tree. That
             * means the parent was not changed, only the order was changed.
             *
             * @param {IFolder} $folder The folder object
             * @param {int} $id The new parent folder id
             * @param {int} $order The (new) order number
             * @param {boolean} $force If true the relocating was forced
             * @param {object} $oldData The old SQL row data (raw) of the folder
             * @hook RML/Folder/Relocated
             */
            // For backwards compatibility (4.12.0) we need the other action, too
            \do_action($isRelocate ? 'RML/Folder/Relocated' : 'RML/Folder/Moved', $this, $rootFolderId, $this->order, $force, $oldData);
            $this->debug('Successfully moved and saved in database', __METHOD__);
            Util::getInstance()->doActionAnyParentHas($this, 'Folder/RelocatedOrMoved', [$this, $rootFolderId, $this->order, $force, $oldData]);
        } else {
            $this->debug('Successfully setted the new parent', __METHOD__);
            $this->getAbsolutePath(\true, \true);
        }
        return \true;
    }
    // Documented in IFolderActions
    public function relocate($parentId, $nextFolderId = \false)
    {
        global $wpdb;
        // Always root as the Lite version does not support subfolders
        $rootFolderId = \_wp_rml_root();
        // Collect data
        $table_name = $this->getTableName();
        $this->debug($nextFolderId === \false ? 'The folder should take place at the end of the list...' : "The folder should take place before folder id {$nextFolderId}...", __METHOD__);
        $rootFolder = $rootFolderId === $this->id ? $this : \wp_rml_get_object_by_id($rootFolderId);
        $next = $nextFolderId === \false ? null : \wp_rml_get_object_by_id($nextFolderId);
        // At end of the list
        try {
            if ($next === null && \is_rml_folder($rootFolderId)) {
                // Only update the order to the end of the list
                $this->setParent(null);
            } elseif (\is_rml_folder($next) && \is_rml_folder($rootFolder)) {
                // Reget
                $_this = \wp_rml_structure_reset(null, \false, $this->id);
                $next = \wp_rml_get_object_by_id($next->id);
                // Get the order of the next folder
                $newOrder = $next->order;
                // Count up the next ids
                // phpcs:disable WordPress.DB.PreparedSQL
                $sql = "UPDATE {$table_name} SET ord = ord + 1 WHERE parent = {$rootFolderId} AND ord >= {$newOrder}";
                $wpdb->query($sql);
                // phpcs:enable WordPress.DB.PreparedSQL
                // Set the new order
                $_this->setParent(null, $newOrder);
            } else {
                // There is nothing given
                throw new Exception(\__('Something went wrong.', RML_TD));
            }
            $this->debug('Successfully relocated', __METHOD__);
            return \true;
        } catch (Exception $e) {
            $this->debug('Error: ' . $e->getMessage(), __METHOD__);
            return [$e->getMessage()];
        }
    }
}
