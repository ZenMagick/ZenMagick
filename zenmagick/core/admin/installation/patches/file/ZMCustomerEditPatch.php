<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2008 ZenMagick
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

define('_ZM_ZEN_CUSTOMERS_PHP', DIR_FS_ADMIN . 'customers.php');

/**
 * Patch to enable editing customers if the same email exists as guest checkout.
 *
 * @author mano
 * @package org.zenmagick.admin.installation.patches.file
 * @version $Id$
 */
class ZMCustomerEditPatch extends ZMFilePatch {

    /**
     * Default c'tor.
     */
    function ZMCustomerEditPatch() {
        parent::__construct('customerEdit');
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMCustomerEditPatch();
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
     * @param array lines The file contents of <code>index.php</code>.
     * @return boolean <code>true</code> if this patch can still be applied.
     */
    function isOpen($lines=null) {
        if (null == $lines) {
            $lines = $this->getFileLines(_ZM_ZEN_CUSTOMERS_PHP);
        }

        // look for ZenMagick code...
        $patched = false;
        foreach ($lines as $line) {
            if (false !== strpos($line, "and NOT customers_password = ''")) {
                $patched = true;
                break;
            }
        }

        return !($patched);
    }

    /**
     * Checks if this patch is ready to be applied.
     *
     * @return boolean <code>true</code> if this patch is ready and all preconditions are met.
     */
    function isReady() {
        return is_writeable(_ZM_ZEN_CUSTOMERS_PHP);
    }

    /**
     * Get the precondition message.
     *
     * <p>This will return an empty string when <code>isReady()</code> returns <code>true</code>.</p>
     *
     * @return string The preconditions message or an empty string.
     */
    function getPreconditionsMessage() {
        return $this->isReady() ? "" : "Need permission to write " . _ZM_ZEN_CUSTOMERS_PHP;
    }

    /**
     * Execute this patch.
     *
     * @param boolean force If set to <code>true</code> it will force patching even if
     *  disabled as per settings.
     * @return boolean <code>true</code> if patching was successful, <code>false</code> if not.
     */
    function patch($force=false) {
        $lines = $this->getFileLines(_ZM_ZEN_CUSTOMERS_PHP);
        if (!$this->isOpen($lines)) {
            return true;
        }

        if ((zm_setting('isEnablePatching')) || $force) {
            if (is_writeable(_ZM_ZEN_CUSTOMERS_PHP)) {
                $patchedLines = array();
                foreach ($lines as $line) {
                    array_push($patchedLines, $line);
                    // need to insert after the match
                    if (false !== strpos($line, "where customers_email_address = '")) {
                        array_push($patchedLines, "  and NOT customers_password = ''");
                    }
                }

                return $this->putFileLines(_ZM_ZEN_CUSTOMERS_PHP, $patchedLines);
            } else {
                zm_log("** ZenMagick: no permission to patch edit fix into customers.php", ZM_LOG_ERROR);
                return false;
            }
        } else {
            // disabled
            zm_log("** ZenMagick: patch customer edit support disabled - skipping");
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
        $lines = $this->getFileLines(_ZM_ZEN_CUSTOMERS_PHP);
        if ($this->isOpen($lines)) {
            return true;
        }

        if (is_writeable(_ZM_ZEN_CUSTOMERS_PHP)) {
            $unpatchedLines = array();
            foreach ($lines as $line) {
                if (false !== strpos($line, "  and NOT customers_password = ''")) {
                    continue;
                }
                array_push($unpatchedLines, $line);
            }

            return $this->putFileLines(_ZM_ZEN_CUSTOMERS_PHP, $unpatchedLines);
        } else {
            zm_log("** ZenMagick: no permission to patch customers.php for uninstall", ZM_LOG_ERROR);
            return false;
        }

        return true;
    }
    
}

?>
