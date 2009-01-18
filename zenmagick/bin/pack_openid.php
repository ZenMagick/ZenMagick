<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2008 ZenMagick
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

    // load ZenMagick core
    require dirname(dirname(__FILE__)) . '/external.php';
    $coreDir = dirname(dirname(__FILE__)) . '/core/';
    ZMLoader::instance()->addPath($coreDir);
    ZMLoader::resolve('ZMObject');
    ZMLoader::resolve('ZMPhpPackagePacker');

    /**
     * Custom class for OpenID specific dependency handling.
     */
    class OpenIDPacker extends ZMPhpPackagePacker {
        /**
         * {@inheritDoc}
         */
        public function finalizeDependencies($dependencies, $files) {
            foreach ($dependencies as $class => $list) {
                foreach ($list as $ii => $name) {
                    if ('OpenID' == $name) {
                        unset($dependencies[$class][$ii]);
                    }
                }
            }
            return $dependencies;
        }
    }


    $dir = 'C:/Program Files/Apache Group/Apache2/htdocs/php-openid-2.1.2/Auth/';
    $packer = new OpenIDPacker($dir, dirname($dir).'/openid.packed.php');
    $packer->setDebug(false);
    $packer->packFiles(true);

?>
