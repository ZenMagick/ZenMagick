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
 * Latest accounts dashboard widget.
 *
 * @author DerManoMann
 * @package org.zenmagick.store.admin.dashbord.widgets
 */
class ZMLatestAccountsDashboardWidget extends ZMDashboardWidget {

    /**
     * Create new user.
     * 
     */
    function __construct() {
        parent::__construct(_zm('Latest Accounts'));
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
        $contents = '';
        $contents .= '<table class="grid" cellspacing="0">';
        $contents .= '<tr><th>'._zm('Name').'</th><th>'._zm('Registered').'</th></tr>';
        $odd = true;
        foreach (ZMAccounts::instance()->getAllAccounts(null, 5) as $account) {
            $contents .= '<tr class="'.($odd?'odd':'even').'">';
            $odd = !$odd;
            $name = $account->getType() == ZMAccount::REGISTERED ? $account->getFullName() : _zm('** Guest **');
            $contents .= '<td><a href="'.$admin2->url('account', 'accountId='.$account->getId()).'">'.$name.'</a></td>';
            $contents .= '<td>'.$account->getAccountCreateDate().'</td>';
            $contents .= '</tr>';
        }
        $contents .= '</table>';
        return $contents;
    }

}
