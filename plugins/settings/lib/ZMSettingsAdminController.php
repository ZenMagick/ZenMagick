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

use zenmagick\http\widgets\form\FormWidget;

/**
 * Admin controller.
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package org.zenmagick.plugins.settings
 */
class ZMSettingsAdminController extends ZMPluginAdminController {

    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct('settings');
    }

    /**
     * {@inheritDoc}
     */
    public function processPost($request) {
        $plugin = $this->getPlugin();
        $action = $request->request->get('action', '');
        if ('create' == $action) {
            $title = $request->request->get('title', '');
            $key = $request->request->get('key');
            $value = $request->request->get('value');
            $type = $request->request->get('type');
            // special case for generic select where the initial value gets added to the widget definition
            $parValue = '';
            if (0 === strpos($type, 'selectFormWidget#')) {
                // urlencode to allow to set multiple value=name pairs
                $parValue = '&options='.urlencode($value);
                $value = '';
            }

            if (!empty($key) && !empty($type)) {
                $plugin->addConfigValue($title, $key, $value, '', 'widget@'.$type.'&id='.$key.'&name='.$key.$parValue);
            }
        } else if ('update' == $action) {
            foreach ($plugin->getConfigValues() as $widget) {
                $sanitize = !($widget instanceof ZMWysiwygFormWidget);
                if ($widget instanceof FormWidget && null != ($value = $request->request->get($widget->getName(), null, $sanitize))) {
                    if (!$widget->compare($value)) {
                        // value changed, use widget to (optionally) format value
                        $widget->setValue($value);
                        $plugin->set($widget->getName(), $widget->getStringValue());
                    }
                }
            }
        }

        return $this->findView('success');
    }

}
