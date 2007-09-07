<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006,2007 ZenMagick
 *
 * Portions Copyright (c) 2003 The zen-cart developers
 * Portions Copyright (c) 2003 osCommerce
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

define('_ZM_ZEN_HEADER_FILE', DIR_FS_ADMIN . DIR_WS_INCLUDES . 'header.php');
define('_ZM_ZEN_FOOTER_FILE', DIR_FS_ADMIN . DIR_WS_INCLUDES . 'footer.php');

/**
 * Patch to enable a dynamic admin structure.
 *
 * @author mano
 * @package net.radebatz.zenmagick.admin.installation.patches.file
 * @version $Id$
 */
class ZMDynamicAdminPatch extends ZMFilePatch {

    /**
     * Default c'tor.
     */
    function ZMDynamicAdminPatch() {
        parent::__construct('dynamicAdmin');
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMDynamicAdminPatch();
    }

    /**
     * Default d'tor.
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
        $header = file_get_contents(_ZM_ZEN_HEADER_FILE);
        $footer = file_get_contents(_ZM_ZEN_FOOTER_FILE);
        return false === strpos($header, "ZenMagick") || false === strpos($footer, "ZenMagick");
    }

    /**
     * Checks if this patch is ready to be applied.
     *
     * @return boolean <code>true</code> if this patch is ready and all preconditions are met.
     */
    function isReady() {
        return is_writeable(_ZM_ZEN_HEADER_FILE) && is_writeable(_ZM_ZEN_FOOTER_FILE);
    }

    /**
     * Get the precondition message.
     *
     * <p>This will return an empty string when <code>isReady()</code> returns <code>true</code>.</p>
     *
     * @return string The preconditions message or an empty string.
     */
    function getPreconditionsMessage() {
        return $this->isReady() ? "" : "Need permission to write " . _ZM_ZEN_HEADER_FILE . " and " . _ZM_ZEN_FOOTER_FILE;
    }

    /**
     * Execute this patch.
     *
     * @param boolean force If set to <code>true</code> it will force patching even if
     *  disabled as per settings.
     * @return boolean <code>true</code> if patching was successful, <code>false</code> if not.
     */
    function patch($force=false) {
        if (!$this->isOpen()) {
            return true;
        }

        if ((zm_setting('isEnablePatching') && zm_setting('isDynamicAdminPatchSupport')) || $force) {
            if (is_writeable(_ZM_ZEN_HEADER_FILE) && is_writeable(_ZM_ZEN_FOOTER_FILE)) {
                $lines = $this->getFileLines(_ZM_ZEN_HEADER_FILE);
                $patchedLines = array();
                array_push($patchedLines, "<?php return; /* added by ZenMagick installation patcher */ ?>");
                $finalLines = array_merge($patchedLines, $lines);
                $status = $this->putFileLines(_ZM_ZEN_HEADER_FILE, $finalLines);

                $lines = $this->getFileLines(_ZM_ZEN_FOOTER_FILE);
                $patchedLines = array();
                array_push($patchedLines, "<?php return; /* added by ZenMagick installation patcher */ ?>");
                $finalLines = array_merge($patchedLines, $lines);
                $status &= $this->putFileLines(_ZM_ZEN_FOOTER_FILE, $finalLines);
                return $status;
            } else {
                zm_log("** ZenMagick: no permission to patch dynamic admin support", ZM_LOG_ERROR);
                return false;
            }
        } else {
            // disabled
            zm_log("** ZenMagick: Dynamic Admin Menu support disabled - skipping");
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
        if (!$this->isOpen()) {
            $status = true;
            $lines = $this->getFileLines(_ZM_ZEN_HEADER_FILE);
            if (false !== strpos($lines[0], "ZenMagick")) {
                $status = $this->putFileLines(_ZM_ZEN_HEADER_FILE, array_slice($lines, 1));
            }

            $lines = $this->getFileLines(_ZM_ZEN_FOOTER_FILE);
            if (false !== strpos($lines[0], "ZenMagick")) {
                $status &= $this->putFileLines(_ZM_ZEN_FOOTER_FILE, array_slice($lines, 1));
            }

            return $status;
        }

        return true;
    }
    
}

?>
