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
?>
<?php


/**
 * Library packer.
 *
 * <p>Simple interface to be implemented for all library packer that want to be
 * available via the build script.</p>
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package org.zenmagick.core.build
 */
interface ZMLibraryPacker {

    /**
     * Process.
     *
     * @param string sourceDir The source dir of the package to pack.
     * @param string targetDir The target/output directory.
     * @param string version The version we are processing.
     * @param boolean strip Indicate whether to strip the generated PHP code or not.
     */
    public function process($sourceDir, $targetDir, $version, $strip);

}
