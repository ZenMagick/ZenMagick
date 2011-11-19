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

use zenmagick\base\classloader\ClassLoader;


    /**
     * Simple command line packer script.
     */

    $installDir = dirname(dirname(dirname(__FILE__)));
    $baseDir = $installDir;
    chdir($installDir);
    include 'bootstrap.php';
    $classLoader = new ClassLoader();
    $classLoader->addPath($installDir.'/lib/build');
    $classLoader->register();

    if (6 > $argc) {
        echo PHP_EOL."  usage: php packer.php [ZMLibraryPacker implementation] [source dir] [target dir] [version] [true|false] [target base directory] [classpath]".PHP_EOL;
        exit;
    }

    $class = $argv[1];
    $source = $argv[2];
    $targetBaseDir = $argv[3];
    $target = $argv[4];
    $version = $argv[5];
    $strip = ZMLangUtils::asBoolean($argv[6]);
    if (7 < $argc) {
        foreach (explode(';', $argv[7]) as $path) {
            $classLoader->addPath($path);
        }
    }

    $packer = new $class();
    $packer->process($source, $targetBaseDir.$target, $version, $strip);

    exit;
