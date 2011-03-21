<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2010 zenmagick.org
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


/**
 * Admin controller.
 *
 * @author DerManoMann
 * @package org.zenmagick.plugins.settings
 */
class ZMSettingsAdminController extends ZMPluginAdmin2Controller {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('settings');
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * {@inheritDoc}
     */
    public function processPost($request) {
        $plugin = $this->getPlugin();
        $action = $request->getParameter('action', '');
        if ('create' == $action) {
            $title = $request->getParameter('title', '');
            $key = $request->getParameter('key');
            $value = $request->getParameter('value');
            $type = $request->getParameter('type');
            // special case for generic select where the initial value gets added to the widget definition
            $parValue = '';
            if (0 === strpos($type, 'ZMSelectFormWidget#')) {
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
                if ($widget instanceof ZMFormWidget && null != ($value = $request->getParameter($widget->getName(), null, $sanitize))) {
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
