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
namespace zenmagick\apps\store\utils;

use zenmagick\base\ZMObject;
use zenmagick\base\Runtime;
use zenmagick\base\Toolbox;
use zenmagick\base\dependencyInjection\loader\YamlLoader;
use zenmagick\http\utils\ContextConfigLoader as HttpContextConfigLoader;

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

        // locale
        if (array_key_exists('locale', $config) && is_array($config['locale'])) {
            $localeService = $this->container->get('localeService');
            $locale = $localeService->getLocale();
            foreach ($localeService->getValidLocaleCodes() as $code) {
                if (array_key_exists($code, $config['locale'])) {
                    $locale->setFormats($config['locale'][$code]);
                }
            }
        }
        if (array_key_exists('menu', $config) && is_array($config['menu'])) {
            self::$menus[] = $config['menu'];
        }
    }

    public function getMenus() {
        return self::$menus;
    }

}
