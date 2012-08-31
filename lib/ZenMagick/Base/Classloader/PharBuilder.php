<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright (C) 2006-2012 zenmagick.org
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
namespace ZenMagick\base\classloader;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

use ZenMagick\base\utils\FolderWhitelistFilterIterator;

/**
 * <code>Phar</code> builder for directories controlled by a <em>classloader.ini</em> file.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class PharBuilder {
    private $path;


    /**
     * Create a new instance.
     *
     * @param string path Path to a folder that <strong>must</strong> contain a <em>classloader.ini</em> file.
     */
    public function __construct($path) {
        $this->path = $path;
    }


    /**
     * Get a list of directories to be included.
     *
     * @return array  A list of directory names to be included in the <code>phar</code>.
     */
    public function getIncludes() {
        // get all directories to include...
        $includes = array();
        foreach (parse_ini_file($this->path.DIRECTORY_SEPARATOR.'classloader.ini', true) as $type => $mappings) {
            foreach ($mappings as $key => $dir) {
                $path = realpath($this->path.DIRECTORY_SEPARATOR.$dir);
                if ('namespaces' == $type) {
                    // check for '@'
                    $token = explode('@', $dir);
                    $path = realpath($this->path.DIRECTORY_SEPARATOR.$token[0]);
                    $path = $path.DIRECTORY_SEPARATOR.str_replace('\\', DIRECTORY_SEPARATOR, $key);
                }
                $path = realpath($path);
                if (!empty($path)) {
                    $includes[$path] = $path;
                }
            }
        }
        $includes = array_keys($includes);
        // add classloader.ini
        $includes[] = realpath($this->path.'/classloader.ini');
        return $includes;
    }

    /**
     * Get the name of the <code>phar</code> file.
     *
     * @return string The full path the the file to be created.
     */
    public function getPharPath() {
        return $this->path.DIRECTORY_SEPARATOR.basename($this->path).'.phar';
    }

    /**
     * Create the <code>phar</code>.
     */
    public function create() {
        $iterator = new RecursiveDirectoryIterator($this->path, FilesystemIterator::SKIP_DOTS | FilesystemIterator::FOLLOW_SYMLINKS);
        // real recursive iterator
        $iterator = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST);
        // filter based on the folder list take from the classloader.ini
        $iterator = new FolderWhitelistFilterIterator($iterator, $this->getIncludes());

        // create phar
        $phar = new \Phar($this->getPharPath());
        $phar->buildFromIterator($iterator, $this->path);
    }

}
