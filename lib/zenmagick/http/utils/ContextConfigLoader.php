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
namespace zenmagick\http\utils;

use zenmagick\base\Runtime;
use zenmagick\base\Toolbox;
use zenmagick\base\dependencyInjection\loader\YamlLoader;
use zenmagick\base\utils\ContextConfigLoader as BaseContextConfigLoader;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Yaml\Yaml;

/**
 * Loader for multi-content config files.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class ContextConfigLoader extends BaseContextConfigLoader {
    // collect all loaded routings to be processed later
    private static $routing = array();
    private static $urlManagerRoutes = array();

    /**
     * {@inheritDoc}
     */
    public function apply(array $config) {
        parent::apply($config);

        // traditional router
        if (array_key_exists('router', $config) && is_array($config['router'])) {
            // keep for later
            self::$urlManagerRoutes[] = $config['router'];
        }
        if (array_key_exists('routing', $config) && is_array($config['routing'])) {
            // keep for later
            self::$routing[] = $config['routing'];
        }
    }

    /**
     * Get additional routing maps.
     *
     * @return array List of routing maps.
     */
    public function getRouting() {
        return self::$routing;
    }

    /**
     * Get legacy urlManager routes.
     *
     * @return array List of routing maps.
     */
    public function getUrlManagerRoutes() {
        return self::$urlManagerRoutes;
    }

}
