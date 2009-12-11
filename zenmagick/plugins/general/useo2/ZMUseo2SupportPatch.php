<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 zenmagick.org
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

/**
 * Patch to enable/disable Ultimate SEO.
 *
 * <p>Depends on replacement of <code>zen_href_link</code> with the ZenMagick implementation.</p>
 *
 * <p>Patched to work inside a plugin.</p>
 *
 * @author mano
 * @package org.zenmagick.plugins.useo2
 * @version $Id$
 */
class ZMUseo2SupportPatch extends ZMFilePatch {
    private $adminFiles_ = array(
        "categories.php",
        "product.php",
    );


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('uSeo2Support');
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get the patch group id.
     *
     * @return string The patch group id.
     */
    public function getGroupId() {
        return 'ultimateSeo';
    }


    /**
     * Returns a list of other patches it depends on.
     *
     * @return array List of patch names.
     */
    function dependsOn() { return array('linkGeneration'); }


    /**
     * Checks if this patch can still be applied.
     *
     * @return boolean <code>true</code> if this patch can still be applied.
     */
    function isOpen() {
        $files = $this->_getUnpatchedAdminFiles();

        return 0 < count($files);
    }

    /**
     * Checks if this patch is ready to be applied.
     *
     * @return boolean <code>true</code> if this patch is ready and all preconditions are met.
     */
    function isReady() {
        foreach ($this->adminFiles_ as $file) {
            if (!is_writeable(DIR_FS_ADMIN.$file)) {
                return false;
            }
        }

        return class_exists('SEO_URL_INSTALLER');
    }

    /**
     * Get the precondition message.
     *
     * <p>This will return an empty string when <code>isReady()</code> returns <code>true</code>.</p>
     *
     * @return string The preconditions message or an empty string.
     */
    function getPreconditionsMessage() {
        $list = '';
        foreach ($this->adminFiles_ as $file) {
            if (0 < strlen($list)) { $list .= " and "; }
            $list .= $file;
        }

        return $this->isReady() ? "" : "Need permission to write " . $list . " in " . DIR_FS_ADMIN . " and USEO2 plugin";
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

        if (true /*(ZMSettings::get('isEnablePatching') && ZMSettings::get('isUltimateSeoPatchSupport'))*/ || $force) {
            $files = $this->_getUnpatchedAdminFiles();
            foreach ($files as $file => $lines) {
                if (is_writeable($file)) {
                    $this->putFileLines($file, $lines);
                } else {
                    ZMLogging::instance()->log("** ZenMagick: no permission to patch Ultimate SEO support into ".$file, ZMLogging::ERROR);
                    return false;
                }
            }

            // force init
            if (class_exists('SEO_URL_INSTALLER')) {
                $seoInstaller = new SEO_URL_INSTALLER();
                $seoInstaller->init();
            }

            return true;
        } else {
            // disabled
            ZMLogging::instance()->log("** ZenMagick: Ultimate SEO patch support disabled - skipping");
            return false;
        }

        return true;
    }

    /*
	// Ultimate SEO URLs v2.100
	// If the action will affect the cache entries
	if (preg_match("/(insert|update|setflag)/i", $action)) {
		include_once(DIR_WS_INCLUDES . 'reset_seo_cache.php');
	}
     */


    /**
     * Generates a list of all unpatched zen-cart admin files.
     *
     * @return array Hash with filename as key and contents as array of lines as value.
     */
    function _getUnpatchedAdminFiles() {
        $files = array();
        foreach ($this->adminFiles_ as $file) {
            $lines = $this->getFileLines(DIR_FS_ADMIN.$file);
            $isPatched = false;
            foreach ($lines as $ii => $line) {
                if (false !== strpos($line, "insert|update|setflag") && false !== strpos($lines[$ii+1], "reset_seo_cache")) {
                    $isPatched = true;
                    break;
                }
            }
            if (!$isPatched) {
                // apply patch just in case
                $patchedLines = array();
                foreach ($lines as $line) {
                    array_push($patchedLines, $line);
                    if (false !== strpos($line, 'isset($_GET[\'action\'])') && false === strpos($lines[$ii+1], "insert|update|setflag")) {
                        // add stuff
                        array_push($patchedLines, ' if (preg_match("/(insert|update|setflag)/i", $action)) { /* START ZenMagick useo2 plugin */');
                        array_push($patchedLines, '  if(function_exists("reset_seo_cache")) { reset_seo_cache(); }');
                        array_push($patchedLines, ' } /* END ZenMagick useo2 plugin */');
                    }
                }
                $files[DIR_FS_ADMIN.$file] = $patchedLines;
            }
        }

        return $files;
    }
    
    /**
     * Revert the patch.
     *
     * @return boolean <code>true</code> if patching was successful, <code>false</code> if not.
     */
    function undo() {
        foreach ($this->adminFiles_ as $file) {
            $lines = $this->getFileLines(DIR_FS_ADMIN.$file);
            $unpatchedLines = array();
            $inPatch = false;
            foreach ($lines as $ii => $line) {
                if (!$inPatch) {
                    if (false !== strpos($line, "insert|update|setflag") && false !== strpos($lines[$ii+1], "reset_seo_cache")) {
                        $inPatch = true;
                    } else {
                        array_push($unpatchedLines, $line);
                    }
                } else {
                    if ('}' == trim($line) || '} /* END ZenMagick useo2 plugin */' == trim($line)) {
                        $inPatch = false;
                    }
                }
            }
            if (count($lines) != count($unpatchedLines)) {
                $this->putFileLines(DIR_FS_ADMIN.$file, $unpatchedLines);
            }
        }

        if (class_exists('SEO_URL_INSTALLER')) {
            $seoInstaller = new SEO_URL_INSTALLER();
            $seoInstaller->uninstall_settings();
        }

        return true;
    }

}

?>
