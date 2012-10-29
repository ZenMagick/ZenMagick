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
namespace ZenMagick\Base\Plugins;

/**
 * Loader for plugin options stored in the db.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class PluginOptionsLoader
{
    /**
     * Load options and stuff for the given id and config.
     *
     * @param string id The plugin id.
     * @param array config The configuration so far.
     * @return array The full configuration.
     */
    public function load($id, $config)
    {
        $options = isset($config['meta']) && isset($config['meta']['options']) ? $config['meta']['options'] : array();

        $config['meta']['enabled'] = isset($config['meta']['enabled']) ? $config['meta']['enabled'] : true;
        $config['meta']['context'] = isset($config['meta']['context']) ? $config['meta']['context'] : null;

        // populate option values based on set values, set defaults and type defaults
        $values = array();
        if (isset($options['properties'])) {
            // setup initial values
            foreach ($options['properties'] as $name => $property) {
                $type = isset($property['type']) ? $property['type'] : 'text';
                $pconfig = isset($property['config']) ? $property['config'] : array();
                $value = isset($property['value']) ? $value : (isset($pconfig['default']) ? $pconfig['default'] : ('boolean' == $type ? false : ''));
                $options['properties'][$name]['value'] = $value;
            }
        }

        $config['meta']['options'] = $options;

        return $config;
    }

}
