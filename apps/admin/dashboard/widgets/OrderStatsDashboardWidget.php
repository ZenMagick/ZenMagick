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
namespace ZenMagick\apps\admin\dashboard\widgets;

use ZenMagick\apps\admin\dashboard\DashboardWidget;

/**
 * Order stats dashboard widget.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class OrderStatsDashboardWidget extends DashboardWidget {

    /**
     * Create new user.
     */
    public function __construct() {
        parent::__construct(_zm('Order Stats'));
    }


    /**
     * {@inheritDoc}
     */
    public function getContents($request) {
        $net = $request->getToolbox()->net;
        $contents = '<table class="grid" cellspacing="0">';
        $contents .= '<tr><th>'._zm('Status').'</th><th>'._zm('Number of Orders').'</th></tr>';
        $language = $request->getSelectedLanguage();
        $sql = "SELECT count(*) AS count FROM %table.orders%
                WHERE orders_status = :orderStatusId";
        foreach ($this->container->get('orderService')->getOrderStatusList($language->getId()) as $status) {
            $args = array('orderStatusId' => $status->getOrderStatusId());
            $result = \ZMRuntime::getDatabase()->querySingle($sql, $args, 'orders');
            $contents .= '<tr>';
            $contents .= '<td><a href="'.$net->url('orders', 'orderStatusId='.$status->getOrderStatusId()).'">'._zm($status->getName()).'</a></td>';
            $contents .= '<td>'.$result['count'].'</td>';
            $contents .= '</tr>';
        }
        $contents .= '</table>';
        return $contents;
    }

}
