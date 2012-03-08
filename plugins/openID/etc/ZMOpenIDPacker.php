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

use zenmagick\base\utils\packer\PhpPackagePacker;


/**
 * Packer for the <em>openID</em> library.
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package org.zenmagick.mvc
 */
class ZMOpenIDPacker extends PhpPackagePacker implements ZMLibraryPacker {

    /**
     * {@inheritDoc}
     */
    public function process($sourceDir, $targetDir, $version, $strip) {
        $this->rootFolder_ = $sourceDir.'Auth'.DIRECTORY_SEPARATOR;
        $this->outputFilename_ = $targetDir.'openid-'.$version.'.packed.php';

        // run the parent package packer; strip/leave references
        $this->packFiles($strip, false);
    }

    /**
     * {@inheritDoc}
     */
    public function ignoreFile($file) {
      return in_array(basename($file),
          array('Server.php', 'ServerRequest.php', 'DumbStore.php', 'FileStore.php', 'PostgreSQLStore.php', 'SQLiteStore.php', 'SQLStore.php'));
    }

}
