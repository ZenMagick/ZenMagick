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

define('_ZM_HTACCESS', DIR_FS_CATALOG.".htaccess");


/**
 * Patch to update the <code>.htaccess</code> <code>RewriteBase</code>.
 *
 * @author DerManoMann
 * @package zenmagick.store.admin.installation.patches.file
 */
class ZMRewriteBasePatch extends ZMFilePatch {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('rewriteBase');
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
     * @return boolean <code>true</code> if this patch can still be applied.
     */
    function isOpen() {
        if (file_exists(_ZM_HTACCESS)) {
            $lines = $this->getFileLines(_ZM_HTACCESS);
            foreach ($lines as $line) {
                $words = explode(' ', $line);
                if (2 == count($words) && 'RewriteBase' == trim($words[0])) {
                    return ZM_DIR_WS_CATALOG != $words[1];
                }
            }
            return false;
        }
        return false;
    }

    /**
     * Checks if this patch is ready to be applied.
     *
     * @return boolean <code>true</code> if this patch is ready and all preconditions are met.
     */
    function isReady() {
        return !file_exists(_ZM_HTACCESS) || is_writeable(_ZM_HTACCESS);
    }

    /**
     * Get the patch group id.
     *
     * @return string The patch group id.
     */
    function getGroupId() {
        return 'file';
    }

    /**
     * Get the precondition message.
     *
     * <p>This will return an empty string when <code>isReady()</code> returns <code>true</code>.</p>
     *
     * @return string The preconditions message or an empty string.
     */
    function getPreconditionsMessage() {
        return ($this->isReady() || !file_exists(_ZM_HTACCESS)) ? "" : "Need permission to write " . _ZM_HTACCESS;
    }

    /**
     * Execute this patch.
     *
     * @param boolean force If set to <code>true</code> it will force patching even if
     *  disabled as per settings.
     * @return boolean <code>true</code> if patching was successful, <code>false</code> if not.
     */
    function patch($force=false) {
        if (!$this->isReady()) {
            return false;
        }

        $lines = $this->getFileLines(_ZM_HTACCESS);
        $lines = $this->_fixLines($lines);
        $lines = $this->putFileLines(_ZM_HTACCESS, $lines);
        return true;
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
     * Fix lines.
     */
    function _fixLines($lines) {
        foreach ($lines as $ii => $line) {
            $words = explode(' ', $line);
            if (2 == count($words) && 'RewriteBase' == trim($words[0])) {
                if (ZM_DIR_WS_CATALOG != $words[1]) {
                    // fix (might not get written, though
                    $lines[$ii] = 'RewriteBase ' . ZM_DIR_WS_CATALOG;
                }
                break;
            }
        }

        return $lines;
    }

}
