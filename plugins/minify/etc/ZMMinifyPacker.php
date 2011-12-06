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

/**
 * Packer for the <em>minify</em> library.
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package org.zenmagick.mvc
 */
class ZMMinifyPacker extends ZMPhpPackagePacker implements ZMLibraryPacker {

    /**
     * {@inheritDoc}
     */
    public function process($sourceDir, $targetDir, $version, $strip) {
        $this->rootFolder_ = $sourceDir.'min'.DIRECTORY_SEPARATOR;
        $this->outputFilename_ = $targetDir.'minify-'.$version.'.packed.php';

        // run the parent package packer; strip/leave references
        $this->packFiles($strip, true);
    }

    /**
     * {@inheritDoc}
     */
    public function patchFile($filename, $lines) {
        if ('Minify.php' == basename($filename)) {
            /* modify Minify::serve(..): 
            * remove:
            *          require_once "Minify/Controller/" 
            *              . str_replace('_', '/', $controller) . ".php";    
            */
            foreach ($lines as $ii => $line) {
                if (false !== strpos($line, 'require_once "Minify/Controller/"')) {
                    // drop this and next line
                    $stripped = array_splice($lines, $ii, 2);
                    return $lines;
                }
            }
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function ignoreFile($filename) {
        $bname = basename($filename);
        return false !== strpos($filename, 'builder') || in_array(basename($filename), array('index.php', 'config.php', 'groupConfig.php'));
    }

}
