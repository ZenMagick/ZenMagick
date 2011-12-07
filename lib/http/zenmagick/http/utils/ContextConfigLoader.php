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
namespace zenmagick\http\utils;

use zenmagick\base\Runtime;
use zenmagick\base\Toolbox;
use zenmagick\base\ioc\loader\YamlLoader;
use zenmagick\base\utils\ContextConfigLoader as BaseContextConfigLoader;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Yaml\Yaml;

/**
 * Loader for multi-content config files.
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package zenmagick.base.utils
 */
class ContextConfigLoader extends BaseContextConfigLoader {
    // collect all loaded routings to be processed later
    private static $routing = array();


    /**
     * {@inheritDoc}
     */
    public function apply($config) {
        parent::apply($config);

        // traditional router
        if (array_key_exists('router', $config) && is_array($config['router'])) {
            // merge
            \ZMUrlManager::instance()->setMappings($config['router'], false);
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

}
