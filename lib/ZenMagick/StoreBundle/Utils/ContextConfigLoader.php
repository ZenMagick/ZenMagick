<?php
/*
 * ZenMagick - Smart e-commerce
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
namespace ZenMagick\StoreBundle\Utils;

use ZenMagick\Base\ZMObject;
use ZenMagick\Base\Runtime;
use ZenMagick\Base\Toolbox;
use ZenMagick\Base\DependencyInjection\Loader\YamlLoader;
use ZenMagick\Http\Utils\ContextConfigLoader as HttpContextConfigLoader;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Yaml\Yaml;

/**
 * Store loader for multi-content config files.
 *
 * @author DerManoMann
 */
class ContextConfigLoader extends HttpContextConfigLoader {

    private static $menus = array();
    /**
     * {@inheritDoc}
     */
    public function apply(array $config) {
        parent::apply($config);
        if (array_key_exists('menu', $config) && is_array($config['menu'])) {
            self::$menus[] = $config['menu'];
        }
    }

    public function getMenus() {
        return self::$menus;
    }

}
