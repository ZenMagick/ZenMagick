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
 * Packer for the <em>SwiftMailer</em> library.
 *
 * <p>This packer will:</p>
 * <ul>
 *  <li>Create the actual packaged library file</li>
 *  <li>Create a small class ZMSwiftInit that contains all the required init code in a single static method <code>init()</code>.</li>
 *  <li>Create mime_types.yaml. This file is loaded by the init code.</li>
 * </ul>
 *
 * @author DerManoMann
 * @package org.zenmagick.core.build
 */
class ZMSwiftMailerPacker extends ZMPhpPackagePacker implements ZMLibraryPacker {

    /**
     * {@inheritDoc}
     */
    public function process($sourceDir, $targetDir, $version, $strip) {
        $this->rootFolder_ = $sourceDir.'classes'.DIRECTORY_SEPARATOR;
        $this->outputFilename_ = $targetDir.'swift-'.$version.'.packed.php';

        // run the parent package packer; strip/leave references
        $this->packFiles($strip, false);

        $this->createInitContainer($sourceDir, $targetDir);
        $this->createMimeTypes($sourceDir, $targetDir);
    }

    /**
     * Create init container.
     *
     * @param string sourceDir The source dir of the package to pack.
     * @param string targetDir The target/output directory.
     */
    protected function createInitContainer($sourceDir, $targetDir) {
        $staticInit = array(
            "<?php",
            "/* GENERATED CODE - DO NOT EDIT! */",
            "class ZMSwiftInit {\n",
            "public static function init() {"
        );
        $init = file($sourceDir.'swift_init.php', FILE_IGNORE_NEW_LINES);
        foreach ($init as $line) {
            // extract dependency_map filename
            if (false !== ($pos = strpos($line, 'dependency_maps/'))) {
                $file = substr($line, $pos+16, -2);
                $staticInit[] = '// '.$file."\n";
                foreach (file($sourceDir.'dependency_maps/'.$file, FILE_IGNORE_NEW_LINES) as $l) {
                    if (false !== strpos($l, '<?') || false !== strpos($l, 'unset')) {
                        continue;
                    }
                    if (false !== strpos($l, 'require')) {
                        $l = '$swift_mime_types = ZMRuntime::yamlLoad(file_get_contents(ZMFileUtils::mkPath(array(ZMRuntime::getInstallationPath(), "lib", "core", "external", "mime_types.yaml"))));';
                    }
                    
                    $staticInit[] = $l;
                }
            }
        }
        $staticInit[] = "}}";
        ZMFileUtils::putFileLines($targetDir.'ZMSwiftInit.php', $staticInit);
    }

    /**
     * Create mime type yaml.
     *
     * @param string sourceDir The source dir of the package to pack.
     * @param string targetDir The target/output directory.
     */
    protected function createMimeTypes($sourceDir, $targetDir) {
        $mtlines = array();
        $mimetypes = file($sourceDir.'mime_types.php', FILE_IGNORE_NEW_LINES);
        foreach ($mimetypes as $line) {
            if (false !== strpos($line, '=>')) {
                $mtlines[] = trim(str_replace(array("'", ',', '=>'), array('', '', ':'), $line));
            }
        }
        ZMFileUtils::putFileLines($targetDir.'mime_types.yaml', $mtlines);
    }

}
