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
namespace zenmagick\base\plugins;

/**
 * Loader for plugin options stored in the db.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class PluginOptionsLoader {

    /**
     * Get options and stuff for the given id and config.
     *
     * @return array List of [enabled], [sortOrder], [options], [config].
     */
    public function getOptionsForId($id, $config) {
        $options = isset($config['meta']) && isset($config['meta']['options']) ? $config['meta']['options'] : array();
        $enabled = false;
        $sortOrder = 0;

        $values = array();
        if (isset($options['properties'])) {
            // setup initial values
            foreach ($options['properties'] as $name => $property) {
                $type = isset($property['type']) ? $property['type'] : 'text';
                $config = isset($property['config']) ? $property['config'] : array();
                $value = isset($property['value']) ? $value : (isset($config['default']) ? $config['default'] : ('boolean' == $type ? false : ''));
                $options['properties'][$name]['value'] = $value;
                switch ($name) {
                case 'enabled':
                    $enabled = $value;
                    break;
                case 'sortOrder':
                    $sortOrder = $value;
                    break;
                }
            }
        }

        return array($enabled, $sortOrder, $options, $config);
    }

}
