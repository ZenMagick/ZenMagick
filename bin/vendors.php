#!/usr/bin/env php
<?php

/*
 * This file is based on vendor.php of the Symfony framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$baseDir = dirname(dirname(__FILE__)).'/lib/vendor';
$vendorDeps = array(
    // symfony
    $baseDir => array(
        array('symfony', 'git://github.com/ZenMagick/symfony.git', 'origin/HEAD', false),
    ),
    // symfony submodules
    $baseDir.'/symfony/vendor' => array(
        array('doctrine', 'git://github.com/doctrine/doctrine2.git', 'origin/2.1.x', false),
        array('doctrine-dbal', 'git://github.com/doctrine/dbal.git', 'origin/2.1.x', false),
        array('doctrine-common', 'git://github.com/doctrine/common.git', 'origin/3.0.x', false),
        //array('monolog', 'http://github.com/Seldaek/monolog.git', 'origin/HEAD', false),
        array('swiftmailer', 'git://github.com/swiftmailer/swiftmailer.git', 'origin/4.1', false),
        //array('twig', 'http://github.com/fabpot/Twig.git', 'origin/HEAD', false),
    ),

    // others
    $baseDir.'/doctrine-utils' => array(
        array('DoctrineExtensions', 'git://github.com/l3pp4rd/DoctrineExtensions.git', 'origin/master', false),
        array('migrations', 'git://github.com/doctrine/migrations.git', 'origin/HEAD', false),
    ),
);

foreach ($vendorDeps as $vendorDir => $deps) {
    if (!is_dir($vendorDir)) {
        mkdir($vendorDir, 0777, true);
    }
    foreach ($deps as $dep) {
        list($name, $url, $rev, $recsub) = $dep;

        echo "> Installing/Updating $name\n";

        $installDir = $vendorDir.'/'.$name;
        if (is_dir($installDir) && !file_exists($installDir.'/.git')) {
            die(sprintf('%s exists but is not a valid repository', $installDir));
        }
        if (!is_dir($installDir)) {
            system(sprintf('git clone %s %s', escapeshellarg($url), escapeshellarg($installDir)));
        }

        system(sprintf('cd %s && git fetch origin && git reset --hard %s', escapeshellarg($installDir), escapeshellarg($rev)));

        if ($recsub) {
            system(sprintf('cd %s && git submodule update --init --recursive', escapeshellarg($installDir)));
        }
    }
}
