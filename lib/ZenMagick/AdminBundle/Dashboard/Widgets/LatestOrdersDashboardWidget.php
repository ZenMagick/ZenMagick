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
namespace ZenMagick\AdminBundle\Dashboard\Widgets;

use ZenMagick\AdminBundle\Dashboard\DashboardWidget;
use ZenMagick\StoreBundle\Entity\Account\Account;

/**
 * Latest orders dashboard widget.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class LatestOrdersDashboardWidget extends DashboardWidget
{
    /**
     * Create new user.
     */
    public function __construct()
    {
        parent::__construct(_zm('Latest Orders'));
    }

    /**
     * {@inheritDoc}
     */
    public function getContents($request)
    {
        $net = $this->container->get('toolbox')->net;
        $utils = $this->container->get('toolbox')->utils;
        $language = $request->getSelectedLanguage();
        $contents = '';
        $contents .= '<table class="grid" cellspacing="0">';
        $contents .= '<tr><th>'._zm('Order').'</th><th>'._zm('Account').'</th><th>'._zm('Placed').'</th><th>'._zm('Total').'</th></tr>';
        $accountService = $this->container->get('accountService');
        foreach ($this->container->get('orderService')->getAllOrders($language->getId(), 5) as $order) {
            $contents .= '<tr>';
            $actualAccount =$accountService->getAccountForId($order->getAccountId());
            $name = '???';
            if (null != ($actualAccount = $accountService->getAccountForId($order->getAccountId()))) {
                $name = $actualAccount->getType() == Account::REGISTERED ? $order->getAccount()->getFullName() : _zm('** Guest **');
            }
            $contents .= '    <td><a href="'.$net->url('zc_admin_orders', 'action=edit&oID='.$order->getId()).'">'.$order->getId().'</a></td>';
            $contents .= '    <td><a href="'.$net->url('zc_admin_customers', 'action=edit&cID='.$order->getAccountId()).'">'.$name.'</a></td>';
            $contents .= '    <td>'.$this->container->get('localeService')->shortDate($order->getOrderDate()).'</td>';
            $contents .= '    <td>'.$utils->formatMoney($order->getTotal()).'</td>';
            $contents .= '  </tr>';
        }
        $contents .= '</table>';
        return $contents;
    }

}
