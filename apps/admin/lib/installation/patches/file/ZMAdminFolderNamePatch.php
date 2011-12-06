<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
 */
?>
<?php

use zenmagick\base\Runtime;


define('_ZM_ADMIN_INDEX_PHP', Runtime::getInstallationPath() . "apps/admin/web/index.php");
if (!defined('DIR_WS_ADMIN')) define('DIR_WS_ADMIN', '/admin/');
/**
 * Patch to update the admin folder name in apps/admin/web/index.php.
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package zenmagick.store.admin.installation.patches.file
 */
class ZMAdminFolderNamePatch extends \ZMFilePatch {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('adminFolderName');
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Checks if this patch can still be applied.
     *
     * @param array lines The file contents of <code>index.php</code>.
     * @return boolean <code>true</code> if this patch can still be applied.
     */
    function isOpen($lines=null) {
        $lines = $this->getFileLines(_ZM_ADMIN_INDEX_PHP);
        if (!defined('ZC_ADMIN_FOLDER')) {
            foreach ($lines as $line) {
                if (false !== strpos($line, "define('ZC_ADMIN_FOLDER'")) {
                    eval($line);
                    break;
                }
            }
        }
        return basename(DIR_WS_ADMIN) != ZC_ADMIN_FOLDER;
    }

    /**
     * Checks if this patch is ready to be applied.
     *
     * @return boolean <code>true</code> if this patch is ready and all preconditions are met.
     */
    function isReady() {
        return is_writeable(_ZM_ADMIN_INDEX_PHP);
    }

    /**
     * Get the precondition message.
     *
     * <p>This will return an empty string when <code>isReady()</code> returns <code>true</code>.</p>
     *
     * @return string The preconditions message or an empty string.
     */
    function getPreconditionsMessage() {
        return $this->isReady() ? "" : "Need permission to write " . _ZM_ADMIN_INDEX_PHP;
    }

   /**
     * Check if this patch supports undo.
     *
     * @return boolean <code>true</code> if undo is supported, <code>false</code> if not.
     */
    function canUndo() {
        return false;
    }

    /**
     * Execute this patch.
     *
     * @param boolean force If set to <code>true</code> it will force patching even if
     *  disabled as per settings.
     * @return boolean <code>true</code> if patching was successful, <code>false</code> if not.
     */
    function patch($force=false) {
        $lines = $this->getFileLines(_ZM_ADMIN_INDEX_PHP);
        if (!$this->isOpen($lines)) {
            return true;
        }

        if (is_writeable(_ZM_ADMIN_INDEX_PHP)) {
            $patchedLines = array();
            foreach ($lines as $line) {
                if (false !== strpos($line, "define('ZC_ADMIN_FOLDER'")) {
                    $line = "     define('ZC_ADMIN_FOLDER', '".basename(DIR_WS_ADMIN)."'); /* added by ZenMagick installation patcher */";
                }
                array_push($patchedLines, $line);
            }

            return $this->putFileLines(_ZM_ADMIN_INDEX_PHP, $patchedLines);
        } else {
            Runtime::getLogging()->error("** ZenMagick: no permission to patch admin folder name");
            return false;
        }

        return true;
    }

    /**
     * Revert the patch.
     *
     * @return boolean <code>true</code> if patching was successful, <code>false</code> if not.
     */
    function undo() {
        $lines = $this->getFileLines(_ZM_ADMIN_INDEX_PHP);
        if ($this->isOpen($lines)) {
            return true;
        }

        if (is_writeable(_ZM_ADMIN_INDEX_PHP)) {
            $unpatchedLines = array();
            foreach ($lines as $line) {
                if (false !== strpos($line, '$zm_events') || false !== strpos($line, 'ZMEvents::instance()') || false !== strpos($line, 'getEventDispatcher()')) {
                    continue;
                }
                array_push($unpatchedLines, $line);
            }

            return $this->putFileLines(_ZM_ADMIN_INDEX_PHP, $unpatchedLines);
        } else {
            Runtime::getLogging()->error("** ZenMagick: no permission to patch admin index.php for uninstall");
            return false;
        }

        return true;
    }

}
