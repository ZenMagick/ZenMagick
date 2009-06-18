<?php
/*
 * ZenMagick Core - Another PHP framework.
 * Copyright (C) 2006,2009 ZenMagick
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
 * File utilities.
 *
 * @author DerManoMann
 * @package org.zenmagick.utils
 * @version $Id$
 */
class ZMFileUtils {

    /**
     * Remove a directory (tree).
     *
     * @param string dir The directory name.
     * @param boolean recursive Optional flag to enable/disable recursive deletion; (default is <code>true</code>)
     * @return boolean <code>true</code> on success.
     */
    public static function rmdir($dir, $recursive=true) {
        if (is_dir($dir)) {
            if (substr($dir, -1) != DIRECTORY_SEPARATOR) {
                $dir .= DIRECTORY_SEPARATOR;
            }
            $handle = opendir($dir);
            while (false !== ($file = readdir($handle))) {
                if ('.' != $file && '..' != $file) {
                    $path = $dir.$file;
                    if (is_dir($path) && $recursive) {
                        self::rmdir($path, $recursive);
                    } else {
                        unlink($path);
                    }
                }
            }
            closedir($handle);
            rmdir($dir);
        }
        return true;
    }


    /**
     * Make directory.
     *
     * @param string dir The folder name.
     * @param int perms The file permisssions (octal); default: <code>null</code> to use the value of setting
     *  <em>fs.permissions.defaults.folder</em>.
     * @param boolean recursive Optional recursive flag; (default: <code>true</code>)
     * @return boolean <code>true</code> on success.
     */
    public static function mkdir($dir, $perms=null, $recursive=true) {
    	clearstatcache();
        if (null == $dir || empty($dir)) {
            return false;
        }
        if (file_exists($dir) && is_dir($dir)) {
            return true;
        }

        $parent = dirname($dir);
        if (!file_exists($parent) && $recursive) {
            if (!self::mkdir($parent, $perms, $recursive))
                return false;
        }
        
        if (null === $perms) {
        	$perms = ZMSettings::get('fs.permissions.defaults.folder');
        }

        $result = @mkdir($dir, $perms);
        // somehow this always ends up 0755, even with 0777
        self::setFilePerms($dir, $recursive, array('folder' => $perms));

        if (!$result) {
            ZMLogging::instance()->log("insufficient permission to create directory: '".$dir.'"', ZMLogging::WARN);
        }
        
        return $result;
    }

    /**
     * Move files and folders.
     *
     * @param string src The source (file or folder).
     * @param string target The target (file or folder).
     * @return boolean <code>true</code> on success.
     */
    public static function move($src, $target) {
        if (is_dir($src)) {
            if (is_file($target)) {
                return false;
            }
            if (DIRECTORY_SEPARATOR != substr($src, -1)) {
                $src .= DIRECTORY_SEPARATOR;
            }
            if (DIRECTORY_SEPARATOR != substr($target, -1)) {
                $target .= DIRECTORY_SEPARATOR;
            }

            self::mkdir($target);
            $handle = opendir($src);
            if ($handle = opendir($src)) {
                while (false !== ($file = readdir($handle))) {
                    if ("." == $file || ".." == $file) {
                        continue;
                    }
                    $fullfile = $src.$file;
                    if (is_dir($fullfile)) {
                        if (!self::move($fullfile.DIRECTORY_SEPARATOR, $target.$file.DIRECTORY_SEPARATOR)) {
                            return false;
                        }
                    } else {
                        if (!copy($fullfile, $target.$file)) {
                            return false;
                        }
                    }
                }
                closedir($handle);
                return self::rmdir($src, true);
            } else {
                return false;
            }
        } else {
            if (is_dir($target)) {
                if (DIRECTORY_SEPARATOR != substr($target, -1)) {
                    $target .= DIRECTORY_SEPARATOR;
                }
                self::mkdir($target);
                if (!copy($src, $target.basename($src))) {
                    return false;
                }
            } else {
                self::mkdir(dirname($target));
                if (!copy($src, $target)) {
                    return false;
                }
            }
            return unlink($src);
        }
    }

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

}

?>
