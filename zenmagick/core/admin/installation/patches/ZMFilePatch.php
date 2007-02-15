<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006 ZenMagick
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
 * Generic file patch.
 *
 * @author mano
 * @package net.radebatz.zenmagick.admin.installation.patches
 * @version $Id$
 */
class ZMFilePatch extends ZMInstallationPatch {

    /**
     * Create new patch.
     *
     * @param string id Id of the patch.
     */
    function ZMFilePatch($id) {
        parent::__construct($id);
    }

    /**
     * Create new patch.
     *
     * @param string id Id of the patch.
     */
    function __construct($id) {
        $this->ZMFilePatch($id);
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
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
     * Load the given file line by line.
     *
     * @param string file The filename.
     * @return array File contents as lines or <code>null</code>.
     */
    function getFileLines($file) {
        $lines = array();
        if (file_exists($file)) {
            $handle = @fopen($file, 'rb');
            if ($handle) {
                while (!feof($handle)) {
                    $line = rtrim(fgets($handle, 4096));
                    array_push($lines, $line);
                }
                fclose($handle);
            }
        }

        return $lines;
    }

    /**
     * Write the given lines to file.
     *
     * @param string file The filename.
     * @param array lines The  lines to write.
     * @return bool <code>true</code> if successful, <code>false</code> if not.
     */
    function putFileLines($file, $lines) {
        $handle = fopen($file, 'wb');
        if ($handle) {
            foreach ($lines as $line) {
                fwrite($handle, $line."\n");
            }
            fclose($handle);
            return true;
        }

        return false;
    }

    /**
     * Read the given file.
     *
     * @param string file The filename.
     * @return string The file contents or <code>null</code>.
     */
    function readFile($file) {
        $handle = @fopen($file, 'rb');
        if ($handle) {
            $contents = fread($handle, filesize($file));
            fclose($handle);
            return $contents;
        }

        return null;
    }

    /**
     * Write the given contents to file.
     *
     * @param string file The filename.
     * @param string contents The file contents.
     * @return bool <code>true</code> if successful, <code>false</code> if not.
     */
    function writeFile($file, $contents) {
        $handle = @fopen($file, 'wb');
        if ($handle) {
            fwrite($handle, $contents);
            fclose($handle);
            return true;
        }

        return false;
    }

}

?>
