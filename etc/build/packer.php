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
     * Simple command line packer script.
     */

    $coreDir = dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'zenmagick'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'core'.DIRECTORY_SEPARATOR;
    include $coreDir.'ZMLoader.php';
    ZMLoader::instance()->addPath($coreDir);
    ZMLoader::instance()->loadStatic();
    spl_autoload_register('ZMLoader::resolve');

    if (5 != $argc) {
        echo PHP_EOL."  usage: php packer.php [cli packer class] [source dir] [target dir] [version]".PHP_EOL;
        exit;
    }

    $class = $argv[1];
    $source = $argv[2];
    $target = $argv[3];
    $version = $argv[4];

    $packer = new $class();
    $packer->process($source, $target, $version);

    exit;
