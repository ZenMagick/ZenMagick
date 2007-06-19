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

define('_ZM_ZEN_FUNCTIONS_FILE', DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'html_output.php');

/**
 * Patch to enable replace zen_href_link to allow full pretty link support.
 *
 * @author mano
 * @package net.radebatz.zenmagick.admin.installation.patches.file
 * @version $Id$
 */
class ZMLinkGenerationPatch extends ZMFilePatch {
    var $fktFilesCfg_ = array(
        _ZM_ZEN_FUNCTIONS_FILE => array(
            array('zen_href_link', '_DISABLED')
        )
    );



    /**
     * Default c'tor.
     */
    function ZMLinkGenerationPatch() {
        parent::__construct('linkGeneration');
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMLinkGenerationPatch();
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
     * @return bool <code>true</code> if this patch can still be applied.
     */
    function isOpen() {
        return $this->isFilesFktOpen($this->fktFilesCfg_);
    }

    /**
     * Checks if this patch is ready to be applied.
     *
     * @return bool <code>true</code> if this patch is ready and all preconditions are met.
     */
    function isReady() {
        return is_writeable(_ZM_ZEN_FUNCTIONS_FILE);
    }

    /**
     * Get the precondition message.
     *
     * <p>This will return an empty string when <code>isReady()</code> returns <code>true</code>.</p>
     *
     * @return string The preconditions message or an empty string.
     */
    function getPreconditionsMessage() {
        return $this->isReady() ? "" : "Need permission to write " . _ZM_ZEN_FUNCTIONS_FILE;
    }

    /**
     * Execute this patch.
     *
     * @param bool force If set to <code>true</code> it will force patching even if
     *  disabled as per settings.
     * @return bool <code>true</code> if patching was successful, <code>false</code> if not.
     */
    function patch($force=false) {
        if (!$this->isOpen()) {
            return true;
        }

        if ((zm_setting('isEnablePatching') && zm_setting('isPrettyLinkPatchSupport')) || $force) {
            return $this->patchFilesFkt($this->fktFilesCfg_);
        } else {
            // disabled
            zm_log("** ZenMagick: Pretty Link patch support disabled - skipping");
            return false;
        }

        return true;
    }

    /**
     * Revert the patch.
     *
     * @return bool <code>true</code> if patching was successful, <code>false</code> if not.
     */
    function undo() {
        return $this->undoFilesFkt($this->fktFilesCfg_);
    }
    
}

?>
