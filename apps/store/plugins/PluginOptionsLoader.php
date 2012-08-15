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
namespace zenmagick\apps\store\plugins;

use zenmagick\base\plugins\PluginOptionsLoader as BasePluginOptionsLoader;
use zenmagick\apps\store\services\ConfigWidgetService;

/**
 * Loader for store plugin options.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class PluginOptionsLoader extends BasePluginOptionsLoader {
    const KEY_PREFIX = 'PLUGIN_';
    const KEY_ENABLED = 'ENABLED';
    const KEY_SORT_ORDER = 'SORT_ORDER';
    protected $configWidgetService;


    /**
     * Set the config widget service.
     *
     * @param ConfigWidgetService configWidgetService The service.
     */
    public function setConfigWidgetService(ConfigWidgetService $configWidgetService) {
        $this->configWidgetService = $configWidgetService;
    }

    /**
     * {@inheritDoc}
     */
    public function getOptionsForId($id, $config) {
        list($enabled, $sortOrder, $options, $config) = parent::getOptionsForId($id, $config);

        // default
        $config['context'] = 'admin,storefront';

        $configPrefix = strtoupper(self::KEY_PREFIX . $id . '_');

        foreach ($this->configWidgetService->getConfigValues($configPrefix.'%') as $configValue) {
            $name = $configValue->getName();
            if (self::KEY_ENABLED == $name) {
                $enabled = $configValue->getValue();
            } else if (self::KEY_SORT_ORDER == $name) {
                $sortOrder = $configValue->getValue();
            } else {
                $options['properties'][$name]['value'] = $configValue->getValue();
            }
        }

        return array($enabled, $sortOrder, $options, $config);
    }

}
