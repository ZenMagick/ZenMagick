<?php
/*
 * ZenMagick - Smart e-commerce
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

use zenmagick\base\Beans;
use zenmagick\base\Runtime;
use zenmagick\base\ZMObject;

/**
 * The dashboard.
 *
 * @author DerManoMann
 * @package zenmagick.store.admin.dashbord
 */
class ZMDashboard extends ZMObject {

    /**
     * Get the dashboard layout.
     *
     * @param int adminId The admin id.
     * @return string The layout.
     */
    public function getLayout($adminId) {
        $config = $this->getConfig($adminId);
        return $config['layout'];
    }

    /**
     * Get the number of columns.
     *
     * @param int adminId The admin id.
     * @return int The number of columns.
     */
    public function getColumns($adminId) {
        $config = $this->getConfig($adminId);
        return preg_replace('/[^\d]/', '', $config['layout']);
    }

    /**
     * Get the configured widgets for the given column.
     *
     * @param int adminId The admin id.
     * @param int column The column.
     * @return array List of widgets.
     */
    public function getWidgetsForColumn($adminId, $column) {
        $config = $this->getConfig($adminId);
        $widgets = array();
        foreach ($config['widgets'][$column] as $def) {
            $widget = Beans::getBean($def);
            // adjust id
            $token = explode('#', $def);
            $widget->setId($token[0]);
            $widgets[] = $widget;
        }
        return $widgets;
    }

    /**
     * Get widget list.
     *
     * @param int adminId The admin id.
     * @return array List of all available widgets.
     */
    public function getWidgetList($adminId) {
        // first collect **class** info for all used widgets
        $config = $this->getConfig($adminId);
        $inUse = array();
        foreach ($config['widgets'] as $column => $widgets) {
            foreach ($widgets as $def) {
                $tmp = explode('#', $def);
                $widget = strtolower(str_replace('ref::', '', $tmp[0]));
                $inUse[$widget] = $widget;
            }
        }

        // get list of all widgets
        $allWidgets = array();
        foreach ($this->container->findTaggedServiceIds('apps.store.admin.dashboard.widget') as $id => $args) {
            $allWidgets[] = $id;
        }

        // figure out the difference
        $available = array();
        foreach (array_values(array_diff($allWidgets, $inUse)) as $id) {
            $widget = $this->container->get($id);
            $widget->setId($id);
            $available[] = $widget;
        }
        return $available;
    }

    /**
     * Set dashboad state.
     *
     * @param int adminId The admin id.
     * @param string state The state as JSON.
     */
    public function setState($adminId, $state) {
        Runtime::getContainer()->get('adminUserPrefService')->setPrefForName($adminId, 'dashboard', $state);
    }

    /**
     * Get dashboard config.
     *
     * @param int adminId The admin id.
     * @return array config map.
     */
    public function getConfig($adminId) {
        $config = array();
        $dashboard = Runtime::getContainer()->get('adminUserPrefService')->getPrefForName($adminId, 'dashboard');
        if (empty($dashboard)) {
            $dashboard = ZMSettings::get('apps.store.dashboad.default');
        }
        $obj = json_decode($dashboard);
        foreach ($obj as $name => $value) {
            $config[$name] = $value;
        }
        // and again, just in case
        $config = array_merge(array('layout' => "col2l", 'widgets' => array()), $config);
        return $config;
    }

}
