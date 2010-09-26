<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright (C) 2006-2010 zenmagick.org
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
 * Packer for the <em>simpletest</em> library.
 *
 * @author DerManoMann
 * @package org.zenmagick.mvc
 */
class ZMSimpletestPacker extends ZMPhpPackagePacker implements ZMLibraryPacker {

    /**
     * {@inheritDoc}
     */
    public function process($sourceDir, $targetDir, $version, $strip) {
        $this->rootFolder_ = $sourceDir;
        $this->outputFilename_ = $targetDir.'simpletest-'.$version.'.packed.php';

        // run the parent package packer; strip/leave references
        $this->packFiles($strip, false);
    }

    /**
     * {@inheritDoc}
     */
    protected function getFileList() {
        // just the main folder excl. a few specific files.
        $exclude = array('autorun.php', 'eclipse.php', 'reflection_php4.php');
        $files = ZMFileUtils::findIncludes($this->rootFolder_, '.php', false);
        foreach ($files as $ii => $file) {
            $name = basename($file);
            if (in_array($name, $exclude)) {
                unset($files[$ii]);
            }
        }

        return $files;
    }

}
