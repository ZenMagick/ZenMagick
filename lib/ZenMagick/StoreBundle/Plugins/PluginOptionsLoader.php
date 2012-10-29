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
namespace ZenMagick\StoreBundle\Plugins;

use ZenMagick\Base\Plugins\PluginOptionsLoader as BasePluginOptionsLoader;
use ZenMagick\StoreBundle\Services\ConfigService;
use ZenMagick\Base\Plugins\Plugin;

/**
 * Loader for store plugin options.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class PluginOptionsLoader extends BasePluginOptionsLoader {
    const KEY_PREFIX = 'PLUGIN_';
    const KEY_ENABLED = 'ENABLED';
    const KEY_SORT_ORDER = 'SORT_ORDER';
    protected $configService;


    /**
     * Set the config service.
     *
     * @param ConfigService configService The service.
     */
    public function setConfigService(ConfigService $configService) {
        $this->configService = $configService;
    }

    /**
     * {@inheritDoc}
     */
    public function load($id, $config) {
        $config = parent::load($id, $config);

        // default
        $config['meta']['context'] = isset($config['meta']['context']) ? $config['meta']['context'] : 'admin,storefront';
        $config['meta']['installed'] = false;
        $config['meta']['enabled'] = false;

        $configPrefix = strtoupper(self::KEY_PREFIX . $id . '_');
        $config['meta']['options'] = isset($config['meta']['options']) ? $config['meta']['options'] : array();
        $config['meta']['options']['properties'] = isset($config['meta']['options']['properties']) ? $config['meta']['options']['properties'] : array();

        foreach ($this->configService->getConfigValues($configPrefix.'%') as $configValue) {
            // once values are stored as-is, the prefix will come back...
            $name = str_replace($configPrefix, '', $configValue->getKey());
            if (self::KEY_ENABLED == $name) {
                $config['meta']['enabled'] = $configValue->getValue('boolean');
                // enabled key in the db is the indicator for installed plugins
                $config['meta']['installed'] = true;
            } elseif (self::KEY_SORT_ORDER == $name) {
                $config['meta']['sortOrder'] = $configValue->getValue();
            } else {
                // find matching name and type
                $type = null;
                $properties = $config['meta']['options']['properties'];
                foreach ($properties as $pname => $property) {
                    if (strtoupper($pname) == strtoupper($name)) {
                        $name = $pname;
                        $type = isset($property['type']) ? $property['type'] : null;
                        break;
                    }
                }
                $config['meta']['options']['properties'][$name]['value'] = $configValue->getValue($type);
            }
        }

        return $config;
    }

}
