<?php
/*
 * ZenMagick - Another PHP framework.
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
 * File utilities.
 *
 * @author DerManoMann <mano@zenmagick.org> <mano@zenmagick.org>
 * @package org.zenmagick.core.utils
 */
class ZMFileUtils {
    private static $fileOwner_ = null;
    private static $fileGroup_ = null;

    /**
     * Unzip a file into the given directory.
     *
     * @param string filename The zip filename.
     * @param string target The target directory.
     * @return boolean <code>true</code> on success.
     */
    public static function unzip($filename, $target) {
        if (!function_exists('zip_open')) {
            return false;
        }
        if (DIRECTORY_SEPARATOR != substr($target, -1)) {
            $target .= DIRECTORY_SEPARATOR;
        }

        if ($zhandle = zip_open($filename)) {
            while ($zentry = zip_read($zhandle)) {
                if (zip_entry_open($zhandle, $zentry, 'r')) {
                    $entryFilename = $target.zip_entry_name($zentry);
                    // ensure folder exists, otherwise things get dropped silently
                    self::mkDir(dirname($entryFilename));
                    $buffer = zip_entry_read($zentry, zip_entry_filesize($zentry));
                    zip_entry_close($zentry);
                    $fp = fopen($entryFilename, 'wb');
                    fwrite($fp, "$buffer");
                    fclose($fp);
                    self::setFilePerms($entryFilename);
                } else {
                    return false;
                }
            }
            zip_close($zhandle);
            return true;
        }
    }

    /**
     * Apply user/group settings to file(s) that should allow ftp users to modify/delete them.
     *
     * <p>The file group attribute is only going to be changed if the <code>$perms</code> parameter is not empty.</p>
     *
     * <p>This method may be disabled by setting <em>zenmagick.core.fs.permissions.fix</em> to <code>false</code>.</p>
     *
     * @param mixed files Either a single filename or list of files.
     * @param boolean recursive Optional flag to recursively process all files/folders in a given directory; default is <code>false</code>.
     * @param array perms Optional file permissions; defaults are taken from the settings <em>fs.permissions.defaults.folder</em> for folder,
     *  <em>fs.permissions.defaults.file</em> for files.
     */
    public static function setFilePerms($files, $recursive=false, $perms=array()) {
        $settingsService = Runtime::getSettings();
        if (!$settingsService->get('zenmagick.core.fs.permissions.fix')) {
            return;
        }
        if (null == self::$fileOwner_ || null == self::$fileGroup_) {
            clearstatcache();
            self::$fileOwner_ = fileowner(__FILE__);
            self::$fileGroup_ = filegroup(__FILE__);
            if (0 == self::$fileOwner_ && 0 == self::$fileGroup_) {
                return;
            }
        }

        if (!is_array($files)) {
            $files = array($files);
        }

        $filePerms = array_merge(array('file' => $settingsService->get('zenmagick.core.fs.permissions.defaults.file', '0644'),
                                    'folder' => $settingsService->get('zenmagick.core.fs.permissions.defaults.folder', '0755')), $perms);
        foreach ($filePerms as $type => $perms) {
            if (is_string($perms)) {
                $filePerms[$type] = intval($perms, 8);
            }
        }

        foreach ($files as $file) {
            if (0 < count($perms)) {
                @chgrp($file, self::$fileGroup_);
            }
            @chown($file, self::$fileOwner_);
            $mod = $filePerms[(is_dir($file) ? 'folder' : 'file')];
            @chmod($file, $mod);

            if (is_dir($file) && $recursive) {
                $dir = $file;
                if (!\ZMLangUtils::endsWith($dir, DIRECTORY_SEPARATOR)) {
                    $dir .= '/';
                }
                $subfiles = array();
                $handle = @opendir($dir);
                while (false !== ($file = readdir($handle))) {
                    if ("." == $file || ".." == $file) {
                        continue;
                    }
                    $subfiles[] = $dir.$file;
                }
                @closedir($handle);
                self::setFilePerms($subfiles, $recursive, $perms);
            }
        }
    }

    /**
     * Load the given file line by line.
     *
     * @param string file The filename.
     * @return array File contents as lines or <code>null</code>.
     */
    public static function getFileLines($file) {
        $lines = array();

        if (file_exists($file)) {
            $lines = file($file, FILE_IGNORE_NEW_LINES);
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
    public static function putFileLines($file, $lines) {
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
                self::setFilePerms($file);
            }
            return true;
        }

        return false;
    }

    /**
     * Parse a single CSV line.
     *
     * <p>As found at: http://uk3.php.net/manual/en/function.fgetcsv.php#62524</p>
     *
     * @param string str The csv string.
     * @return array A token list.
     */
    public static function csvString2Array($str) {
        $results = preg_split("/,(?=(?:[^\"]*\"[^\"]*\")*(?![^\"]*\"))/", trim($str));
        return preg_replace("/^\"(.*)\"$/", "$1", $results);
    }

    /**
     * Load CSV file.
     *
     * <p>If the <code>$keys</code> parameter is not set, the first line will be assumed to contain the column names.</p>
     *
     * @param string filename The filename.
     * @param array keys Optional list of keys; default is <code>null</code> to parse keys from the first line.
     * @return array List of row data with each element being an assoc. array/map.
     */
    public static function loadCSV($filename, $keys=null) {
        $csvLines = file($filename);
        $keyOffset = 0;
        if (null === $keys) {
            $keys = self::csvString2Array($csvLines[0]);
            $keyOffset = 1;
        }

        // some totals
        $keyCount = count($keys);
        $rowCount = count($csvLines) - $keyOffset;
        $rows = array();
        for ($ii=$keyOffset; $ii < ($rowCount+$keyOffset); ++$ii) {
            $line = self::csvString2Array($csvLines[$ii]);
            if (count($line) != $keyCount) {
                Runtime::getLogging()->warn( 'invalid line count; skipping line'.$ii . '; expected='.$keyCount.', actual='.count($line));
                continue;
            }
            $row = array();
            for ($jj=0; $jj < $keyCount; ++$jj) {
                $row[$keys[$jj]] = $line[$jj];
            }
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * Scan (recursively) for <code>.php</code> files.
     *
     * <p>It is worth mentioning that directories will always be processed only after
     * all plain files in a directory are done.</p>
     *
     * @param string dir The name of the root directory to scan.
     * @param string ext Optional file suffix/extension; default is <em>.php</em>.
     * @param boolean recursive If <code>true</code>, scan recursively.
     * @return array List of full filenames of <code>.php</code> files.
     */
    public static function findIncludes($dir, $ext='.php', $recursive=false, $level=0) {
        $includes = array();

        // sanity check
        if (!file_exists($dir) || !is_dir($dir)) {
            return $includes;
        }

        $handle = @opendir($dir);
        while (false !== ($name = readdir($handle))) {
            if ("." == $name || ".." == $name) {
                continue;
            }
            $file = $dir.$name;
            if (is_dir($file) && $recursive) {
                $includes = array_merge($includes, self::findIncludes($file.DIRECTORY_SEPARATOR, $ext, $recursive, $level+1));
            } else if ($ext == substr($name, -strlen($ext))) {
                $includes[] = $file;
            }
        }
        @closedir($handle);

        return $includes;
    }

}
