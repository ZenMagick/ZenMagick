<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2010 zenmagick.org
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

define('_ZM_ZEN_ADMIN_FILE', DIR_FS_ADMIN . DIR_WS_BOXES . "extras_dhtml.php");


/**
 * Admin menu patch.
 *
 * @author DerManoMann
 * @package org.zenmagick.store.admin.installation.patches.file
 */
class ZMAdminMenuPatch extends ZMFilePatch {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('adminMenu');
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
        $contents = file_get_contents(_ZM_ZEN_ADMIN_FILE);
        return false === strpos($contents, "zenmagick_dhtml.php");
    }

    /**
     * Checks if this patch is ready to be applied.
     *
     * @return boolean <code>true</code> if this patch is ready and all preconditions are met.
     */
    function isReady() {
        return is_writeable(_ZM_ZEN_ADMIN_FILE);
    }

    /**
     * Get the precondition message.
     *
     * <p>This will return an empty string when <code>isReady()</code> returns <code>true</code>.</p>
     *
     * @return string The preconditions message or an empty string.
     */
    function getPreconditionsMessage() {
        return $this->isReady() ? "" : "Need permission to write " . _ZM_ZEN_ADMIN_FILE;
    }

    /**
     * Execute this patch.
     *
     * @param boolean force If set to <code>true</code> it will force patching even if
     *  disabled as per settings.
     * @return boolean <code>true</code> if patching was successful, <code>false</code> if not.
     */
    function patch($force=false) {
        if ($this->isOpen()) {
            if ((ZMSettings::get('isEnablePatching')) || $force) {
                // patch
                if ($this->isReady()) {
                    ZMLogging::instance()->log("** ZenMagick: patching zen-cart admin to auto-enable ZenMagick admin menu", ZMLogging::INFO);
                    $handle = fopen(_ZM_ZEN_ADMIN_FILE, "ab");
                    fwrite($handle, "\n<?php require(DIR_WS_BOXES.'zenmagick_dhtml.php'); /* added by ZenMagick installation patcher */ ?>\n");
                    fclose($handle);
                    ZMFileUtils::setFilePerms(_ZM_ZEN_ADMIN_FILE);
                    return true;
                } else {
                    ZMLogging::instance()->log("** ZenMagick: no permission to patch zen-cart admin extras_dhtml.php", ZMLogging::ERROR);
                    return false;
                }
            } else {
                // disabled
                ZMLogging::instance()->log("** ZenMagick: rebuild admin disabled - skipping");
                return false;
            }
        }

        return true;
    }
    
    /**
     * Revert the patch.
     *
     * @return boolean <code>true</code> if patching was successful, <code>false</code> if not.
     */
    function undo() {
        if ($this->isOpen()) {
            return true;
        }

        $contents = $this->readFile(_ZM_ZEN_ADMIN_FILE);
        $contents = str_replace("\n<?php require(DIR_WS_BOXES . 'zenmagick_dhtml.php'); /* added by ZenMagick installation patcher */ ?>", "", $contents);
        $contents = str_replace("\n<?php require(DIR_WS_BOXES.'zenmagick_dhtml.php'); /* added by ZenMagick installation patcher */ ?>", "", $contents);

        return $this->writeFile(_ZM_ZEN_ADMIN_FILE, $contents);
    }
    
}
