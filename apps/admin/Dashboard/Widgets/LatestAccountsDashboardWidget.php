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
namespace ZenMagick\apps\admin\Dashboard\Widgets;

use ZenMagick\apps\admin\Dashboard\DashboardWidget;
use ZenMagick\StoreBundle\Entity\Account\Account;

/**
 * Latest accounts dashboard widget.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class LatestAccountsDashboardWidget extends DashboardWidget {

    /**
     * Create new user.
     */
    public function __construct() {
        parent::__construct(_zm('Latest Accounts'));
    }


    /**
     * {@inheritDoc}
     */
    public function getContents($request) {
        $net = $request->getToolbox()->net;
        $contents = '';
        $contents .= '<table class="grid" cellspacing="0">';
        $contents .= '<tr><th>'._zm('Name').'</th><th>'._zm('Registered').'</th></tr>';
        foreach ($this->container->get('accountService')->getAllAccounts(null, 5) as $account) {
            $contents .= '<tr>';
            $name = $account->getType() == Account::REGISTERED ? $account->getFullName() : _zm('** Guest **');
            $contents .= '<td><a href="'.$net->url('account_show', 'accountId='.$account->getId()).'">'.$name.'</a></td>';
            $contents .= '<td>'.$this->container->get('localeService')->shortDate($account->getAccountCreateDate()).'</td>';
            $contents .= '</tr>';
        }
        $contents .= '</table>';
        return $contents;
    }

}
