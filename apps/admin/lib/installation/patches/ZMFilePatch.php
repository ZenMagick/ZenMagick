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

/**
 * Generic file patch.
 *
 * @author DerManoMann
 * @package zenmagick.store.admin.installation.patches
 */
class ZMFilePatch extends ZMInstallationPatch {

    /**
     * Create new patch.
     *
     * @param string id Id of the patch.
     */
    function __construct($id) {
        parent::__construct($id);
        clearstatcache();
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
     * @return boolean <code>true</code> if successful, <code>false</code> if not.
     */
    function putFileLines($file, $lines) {
    	$fileExists = file_exists($file);
        $handle = fopen($file, 'wb');
        if ($handle) {
            $lineCount = count($lines) - 1;
            foreach ($lines as $ii => $line) {
                $eol = $ii < $lineCount ? "\n" : '';
                fwrite($handle, $line.$eol);
            }
            fclose($handle);
            if (!$fileExists) {
                ZMFileUtils::setFilePerms($file);
            }
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
     * @return boolean <code>true</code> if successful, <code>false</code> if not.
     */
    function writeFile($file, $contents) {
        $fileExists = file_exists($file);
    	$handle = @fopen($file, 'wb');
        if ($handle) {
            fwrite($handle, $contents);
            fclose($handle);
            if (!$fileExists) {
                ZMFileUtils::setFilePerms($file);
            }
            return true;
        }

        return false;
    }

    /**
     * <code>isOpen()</code> check for function renaming.
     *
     * @param array fktFilesCfg The file / function name / function suffix mapping(s).
     * @return boolean <code>true</code> if any patches are open.
     */
    function isFilesFktOpen($fktFilesCfg) {
        foreach ($fktFilesCfg as $file => $fktCfgs) {
            // for each file...
            $lines = $this->getFileLines($file);
            foreach ($fktCfgs as $fktCfg) {
                // for each function mapping
                $fktPatched = false;
                foreach ($lines as $line) {
                    if (false !== strpos($line, "function ") && false !== strpos($line, $fktCfg[0].$fktCfg[1])) {
                        $fktPatched = true;
                        break;
                    }
                }
                if (!$fktPatched) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Execute function renaming patch.
     *
     * @param array fktFilesCfg The file / function name / function suffix mapping(s).
     * @return boolean <code>true</code> if patching was successful, <code>false</code> if not.
     */
    function patchFilesFkt($fktFilesCfg) {
        $patchOk = true;
        foreach ($fktFilesCfg as $file => $fktCfgs) {
            // for each file...
            $lines = $this->getFileLines($file);
            $fileNeedsPatch = false;
            foreach ($fktCfgs as $fktCfg) {
                // for each function mapping
                foreach ($lines as $ii => $line) {
                    if (false !== strpos($line, "function ")
                        && false !== strpos($line, $fktCfg[0]."(")
                        && false === strpos($line, $fktCfg[1])
                        && ZMLangUtils::endsWith(trim($line), "{")) {
                        // modify
                        $lines[$ii] = str_replace($fktCfg[0], $fktCfg[0].$fktCfg[1], $line);
                        $lines[$ii] = trim($lines[$ii]) . " /* modified by ZenMagick installation patcher */";
                        $fileNeedsPatch = true;
                        break;
                    }
                }
            }

            if ($fileNeedsPatch) {
                if (is_writeable($file)) {
                    $this->putFileLines($file, $lines);
                } else {
                    Runtime::getLogging()->error("** ZenMagick: no permission to patch ".basename($file));
                    $patchOk = false;
                }
            }
        }

        return $patchOk;
    }

    /**
     * Undo function renaming patch.
     *
     * @param array fktFilesCfg The file / function name / function suffix mapping(s).
     * @return boolean <code>true</code> if patching was successful, <code>false</code> if not.
     */
    function undoFilesFkt($fktFilesCfg) {
        $undoOk = true;
        foreach ($fktFilesCfg as $file => $fktCfgs) {
            // for each file...
            $lines = $this->getFileLines($file);
            $fileNeedsUndo = false;
            foreach ($fktCfgs as $fktCfg) {
                // for each function mapping
                foreach ($lines as $ii => $line) {
                    if (false !== strpos($line, "function ") && false !== strpos($line, $fktCfg[1])) {
                        // undo
                        $lines[$ii] = str_replace($fktCfg[1], '', $lines[$ii]);
                        $lines[$ii] = str_replace(' /* modified by ZenMagick installation patcher */', '', $lines[$ii]);
                        $fileNeedsUndo = true;
                        break;
                    }
                }
            }

            if ($fileNeedsUndo) {
                if (is_writeable($file)) {
                    $this->putFileLines($file, $lines);
                } else {
                    Runtime::getLogging()->error("** ZenMagick: no permission to patch ".basename($file)." for uninstall");
                    $undoOk = false;
                }
            }
        }

        return $undoOk;
    }
}
