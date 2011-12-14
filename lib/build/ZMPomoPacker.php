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
 * Packer for the <em>pomo</em> tools as included in Wordpress and glotpress.
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package org.zenmagick.core.build
 */
class ZMPomoPacker extends ZMPhpPackagePacker implements ZMLibraryPacker {
    private $nsAdded = false;

    /**
     * {@inheritDoc}
     */
    public function process($sourceDir, $targetDir, $version, $strip) {
        $this->rootFolder_ = $sourceDir;
        $this->outputFilename_ = $targetDir.'zm-pomo-'.$version.'.packed.php';

        // run the parent package packer; strip/leave references
        $this->packFiles($strip, false);
    }

    /**
     * {@inheritDoc}
     */
    public function patchFile($filename, $lines) {
        if (!$this->nsAdded) {
            $this->nsAdded = true;
            return array_merge(array("<?php namespace pomo; ?>"), $lines);
        }
        return $lines;
    }

}
