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
 * @package org.zenmagick.core.utils
 * @version $Id$
 */
class ZMFileUtils {
    private static $fileOwner_ = null;
    private static $fileGroup_ = null;

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
            $perms = ZMSettings::get('zenmagick.core.fs.permissions.defaults.folder', '0755');
        }

        $result = @mkdir($dir, $perms);
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
        if (!ZMSettings::get('zenmagick.core.fs.permissions.fix')) {
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

        $filePerms = array_merge(array('file' => ZMSettings::get('zenmagick.core.fs.permissions.defaults.file', '0644'),
                                    'folder' => ZMSettings::get('zenmagick.core.fs.permissions.defaults.folder', '0755')), $perms);

        foreach ($files as $file) {
            if (0 < count($perms)) {
                @chgrp($file, self::$fileGroup_);
            }
            @chown($file, self::$fileOwner_);
            $mod = $filePerms[(is_dir($file) ? 'folder' : 'file')];
            @chmod($file, octdec($mod));

            if (is_dir($file) && $recursive) {
                $dir = $file;
                if (!ZMLangUtils::endsWith($dir, DIRECTORY_SEPARATOR)) {
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
     * Normalize filename.
     *
     * <p>Fix OS specific directory separator characters.</p>
     *
     * @param string filename The filename.
     * @return string The normalized filename.
     */
    public static function normalizeFilename($filename) {
        if (strpos($filename, '\\')) {
            $filename = preg_replace('/\\\\+/', '\\', $filename);
            $filename = str_replace('\\', DIRECTORY_SEPARATOR, $filename);
        }

        return realpath($filename);
    }

    /**
     * Make the given absolute filename relative to the installation path.
     *
     * @param string filename The filename.
     * @return string A relative filename (if within the installation folder).
     */
    public static function mkRelativePath($filename) {
        $root = self::normalizeFilename(ZMRuntime::getInstallationPath());
        $filename = self::normalizeFilename($filename);
        // make filename relative
        return str_replace($root, '', $filename);
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

}

?>
