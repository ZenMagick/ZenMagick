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
 * Latest orders dashboard widget.
 *
 * @author DerManoMann
 * @package org.zenmagick.store.admin.dashbord.widgets
 */
class ZMLatestOrdersDashboardWidget extends ZMDashboardWidget {

    /**
     * Create new user.
     * 
     */
    function __construct() {
        parent::__construct('latestOrders', _zm('Latest Orders'));
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
    public function getContents($request) {
        $admin2 = $request->getToolbox()->admin2;
        $utils = $request->getToolbox()->utils;
        $language = $request->getSelectedLanguage();
        $contents = '';
        $contents .= '<table>';
        foreach (ZMOrders::instance()->getAllOrders($language->getId(), 5) as $order) {
            $contents .= '  <tr>';
            $actualAccount = ZMAccounts::instance()->getAccountForId($order->getAccountId());
            $name = $actualAccount->getType() == ZMAccount::REGISTERED ? $order->getAccount()->getFullName() : _zm('** Guest **');
            $contents .= '    <td><a href="'.$admin2->url('order', 'orderId='.$order->getId()).'">'.$order->getId().'</a></td>';
            $contents .= '    <td><a href="'.$admin2->url('account', 'accountId='.$order->getAccountId()).'">'.$name.'</a></td>';
            $contents .= '    <td>'.$order->getOrderDate().'</td>';
            $contents .= '    <td>'.$order->getStatusName().'</td>';
            $contents .= '    <td>'.$utils->formatMoney($order->getTotal()).'</td>';
            $contents .= '  </tr>';
        }
        $contents .= '</table>';
        return $contents;
    }

}
