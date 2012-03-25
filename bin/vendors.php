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

$baseDir = dirname(dirname(__FILE__)).'/vendor';
$vendorDeps = array(
    $baseDir => array(
        array('doctrine', 'git://github.com/doctrine/doctrine2.git', 'origin/2.2', false),
        array('doctrine-dbal', 'git://github.com/doctrine/dbal.git', 'origin/2.2', false),
        array('doctrine-common', 'git://github.com/doctrine/common.git', 'origin/2.2', false),
        array('doctrine-migrations', 'git://github.com/doctrine/migrations.git', 'origin/HEAD', false),
        array('gedmo-doctrine-extensions', 'git://github.com/l3pp4rd/DoctrineExtensions.git', 'origin/master', false),
        array('glotpress', 'git://github.com/buddypress/glotpress.git', 'origin/HEAD', false),
        //array('monolog', 'http://github.com/Seldaek/monolog.git', 'origin/HEAD', false),
        array('swiftmailer', 'git://github.com/swiftmailer/swiftmailer.git', 'origin/HEAD', false),
        array('symfony', 'git://github.com/ZenMagick/symfony.git', 'origin/HEAD', false),
        //array('phprules', 'git://github.com/DerManoMann/phprules.git', 'origin/master', false),
        array('phpass', 'git://github.com/rchouinard/phpass.git', 'origin/master', false),
        array('twig', 'git://github.com/fabpot/Twig.git', 'origin/master', false),
        array('CacheLite', 'git://github.com/ZenMagick/CacheLite.git', 'origin/master', false)
    ),
    $baseDir .'/bundles/Doctrine/Bundle' => array(
            array('MigrationsBundle', 'git://github.com/doctrine/DoctrineMigrationsBundle.git', 'origin/HEAD', false),
            array('DoctrineBundle', 'git://github.com/doctrine/DoctrineBundle.git', 'origin/HEAD', false)
    ),
    $baseDir .'/bundles/Symfony/Bundle' => array(
            array('SwiftmailerBundle', 'git://github.com/symfony/SwiftmailerBundle.git', 'origin/HEAD', false)
    )
);

if (isset($extraDeps)) $vendorDeps = array_merge($vendorDeps, (array)$extraDeps);
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
