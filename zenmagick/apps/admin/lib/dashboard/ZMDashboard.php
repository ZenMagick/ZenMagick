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
 * The dashboard.
 *
 * @author DerManoMann
 * @package org.zenmagick.store.admin.dashbord
 */
class ZMDashboard {

    /**
     * Get the number of dashboard columns.
     *
     * @param int adminId The admin id.
     * @return int The number of columns.
     */
    public static function getColumns($adminId) {
        $config = self::getConfig($adminId);
        return $config['columns'];
    }

    /**
     * Get the configured widgets for the given column.
     *
     * @param int adminId The admin id.
     * @param int column The column.
     * @return array List of widget definitions.
     */
    public static function getWidgetsForColumn($adminId, $column) {
        $config = self::getConfig($adminId);
        return $config['widgets'][$column];
    }

    /**
     * Get widget list.
     *
     * @param int adminId The admin id.
     * @return array List of all available widgets.
     */
    public static function getWidgetList($adminId) {
        //TODO: load from setting
        return array('OrderStatsDashboardWidget', 'RecentSearchesDashboardWidget', 'LatestOrdersDashboardWidget', 'LatestAccountsDashboardWidget');
    }

    /**
     * Set dashboad state.
     *
     * @param int adminId The admin id.
     * @param string state The state as JSON.
     */
    public static function setState($adminId, $state) {
        ZMAdminUserPrefs::instance()->setPrefForName($adminId, 'dashboard', $state);
    }

    /**
     * Get dashboard config.
     *
     * @param int adminId The admin id.
     * @return array config map.
     */
    public static function getConfig($adminId) {
        $config = array();
        $dashboard = ZMAdminUserPrefs::instance()->getPrefForName($adminId, 'dashboard');
        if (empty($dashboard)) {
            $dashboard = ZMSettings::get('apps.store.dashboad.default');
        }
        $obj = json_decode($dashboard);
        foreach ($obj as $name => $value) {
            $config[$name] = $value;
        }
        return $config;
    }

}
