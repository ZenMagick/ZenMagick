#!/usr/bin/env php
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

use ZenMagick\Base\Application;
use ZenMagick\Base\Classloader\PharBuilder;


    /**
     * Simple command line phar builder script.
     */

    $installDir = dirname(dirname(dirname(__FILE__)));
    $baseDir = $installDir;
    chdir($installDir);

    include_once 'lib/base/Application.php';
    $application = new Application();
    $application->boot();

    if (2 > $argc) {
        echo PHP_EOL."  usage: php phar_builder.php [path-to-classloader.ini-directory]".PHP_EOL;
        exit;
    }

    $pharPath = $argv[1];
    if (2 < $argc) {
        $baseDir = $argv[2];
    }

    $path = $baseDir.DIRECTORY_SEPARATOR.$pharPath;
    echo 'Run builder with path: '.$path.PHP_EOL;
    $path = realpath($path);
    $pharBuilder = new PharBuilder($path);
    $pharBuilder->create();

    exit;
