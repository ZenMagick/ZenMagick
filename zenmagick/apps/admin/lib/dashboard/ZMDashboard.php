<?php
/*
 * ZenMagick - Extensions for zen-cart
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
     * @return int The number of columns.
     */
    public static function getColumns() {
        $config = self::getConfig();
        return $config['columns'];
    }

    /**
     * Get the configured widgets for the given column.
     *
     * @param int column The column.
     * @return array List of widget definitions.
     */
    public static function getWidgetsForColumn($column) {
        $config = self::getConfig();
        return $config['widgets'][$column];
    }

    /**
     * Get widget list.
     *
     * @return array List of all available widgets.
     */
    public static function getWidgetList() {
        //TODO: load from setting
        return array('OrderStatsDashboardWidget', 'RecentSearchesDashboardWidget', 'LatestOrdersDashboardWidget', 'LatestAccountsDashboardWidget');
    }

    /**
     * Get dashboad state.
     *
     * @return string The current dashboard state as JavaScript structure.
     */
    public static function getState() {

// store widget state in js array
/*
id => (def => class, params => open=false&...),
id => (def => class, params => open=false&...),
id => (def => class, params => open=false&...),

update that with UI events, convert into something like dashboardConfig, jsonify and send to backend
 */
        return '';
    }

    /**
     * Get dashboard config.
     *
     * @return array config map.
     */
    public static function getConfig() {
        // todo: load from db
        return array(
            'columns' => 3,
            'widgets' => array(
                array('OrderStatsDashboardWidget#open=false', 'RecentSearchesDashboardWidget#optionsUrl=abc'),
                array('LatestOrdersDashboardWidget'),
                array('LatestAccountsDashboardWidget')
            )
        );
    }

}
