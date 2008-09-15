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

define('_ZM_ZEN_BASE_PHP', DIR_FS_CATALOG.DIR_WS_CLASSES."class.base.php");

/**
 * Patch to register <code>ZMEvents</code> as global event handler.
 *
 * @author DerManoMann
 * @package org.zenmagick.admin.installation.patches.file
 * @version $Id$
 */
class ZMEventProxyPatch extends ZMFilePatch {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('eventProxy');
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
        if (null == $lines) {
            $lines = $this->getFileLines(_ZM_ZEN_BASE_PHP);
        }

        // look for ZenMagick code...
        foreach ($lines as $line) {
            if (false !== strpos($line, '$zm_events') || false !== strpos($line, 'ZMEvents::instance()')) {
                return false;
            }
        }

        return true;
    }

    /**
     * Checks if this patch is ready to be applied.
     *
     * @return boolean <code>true</code> if this patch is ready and all preconditions are met.
     */
    function isReady() {
        return is_writeable(_ZM_ZEN_BASE_PHP);
    }

    /**
     * Get the precondition message.
     *
     * <p>This will return an empty string when <code>isReady()</code> returns <code>true</code>.</p>
     *
     * @return string The preconditions message or an empty string.
     */
    function getPreconditionsMessage() {
        return $this->isReady() ? "" : "Need permission to write " . _ZM_ZEN_BASE_PHP;
    }

    /**
     * Execute this patch.
     *
     * @param boolean force If set to <code>true</code> it will force patching even if
     *  disabled as per settings.
     * @return boolean <code>true</code> if patching was successful, <code>false</code> if not.
     */
    function patch($force=false) {
        $lines = $this->getFileLines(_ZM_ZEN_BASE_PHP);
        if (!$this->isOpen($lines)) {
            return true;
        }

        if ((ZMSettings::get('isEnablePatching')) || $force) {
            if (is_writeable(_ZM_ZEN_BASE_PHP)) {
                $patchedLines = array();
                foreach ($lines as $line) {
                    array_push($patchedLines, $line);
                    if (false !== strpos($line, "function notify(")) {
                        // need to insert after the matched line
                        array_push($patchedLines, '    if(class_exists("ZMEvents")) { ZMEvents::instance()->update($this, $eventID, $paramArray); } /* added by ZenMagick installation patcher */');
                    }
                }

                return $this->putFileLines(_ZM_ZEN_BASE_PHP, $patchedLines);
            } else {
                ZMLogging::instance()->log("** ZenMagick: no permission to patch event proxy support into class.base.php", ZMLogging::ERROR);
                return false;
            }
        } else {
            // disabled
            ZMLogging::instance()->log("** ZenMagick: patch event proxy support disabled - skipping");
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
        $lines = $this->getFileLines(_ZM_ZEN_BASE_PHP);
        if ($this->isOpen($lines)) {
            return true;
        }

        if (is_writeable(_ZM_ZEN_BASE_PHP)) {
            $unpatchedLines = array();
            foreach ($lines as $line) {
                if (false !== strpos($line, '$zm_events') || false !== strpos($line, 'ZMEvents::instance()')) {
                    continue;
                }
                array_push($unpatchedLines, $line);
            }

            return $this->putFileLines(_ZM_ZEN_BASE_PHP, $unpatchedLines);
        } else {
            ZMLogging::instance()->log("** ZenMagick: no permission to patch class.base.php for uninstall", ZMLogging::ERROR);
            return false;
        }

        return true;
    }
    
}

?>
