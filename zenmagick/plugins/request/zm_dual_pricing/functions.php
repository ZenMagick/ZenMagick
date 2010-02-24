<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2010 zenmagick.org
 *
 * Portions Copyright (c) 2003 The zen-cart developers
 * Portions Copyright (c) 2003 osCommerce
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
     * Get the wholesale level for the given account or the current account.
     *
     * @package org.zenmagick.plugins.zm_dual_pricing
     * @param ZMAccount account Optional account; default is <code>null</code> for request account.
     * @return int The wholesale level or <code>null</code>.
     */
    function zm_dp_get_level($account=null) {
        if (null == $account) {
            $account = ZMAccounts::instance()->getAccountForId(ZMRequest::instance()->getAccountId());
        }
        $level = null;
        if (null != $account) {
            $wsLevel = $account->get('customers_whole');
            if (!empty($wsLevel)) {
                $level = (int)$wsLevel;
                --$level;
            }
        }
        return $level;
    }

    /**
     * Get wholesale price.
     *
     * @package org.zenmagick.plugins.zm_dual_pricing
     * @param int level The wholesale level.
     * @param float price The regular price.
     * @param string wsLevels The wholesale levels.
     * @return float The price.
     */
    function zm_dp_get_price($level, $price, $wsLevels) {
        $wsPrice = $price;

        if (null !== $level) {
            $wsLevels = explode('-', $wsLevels);
            $wsPrice = $wsLevels[$level];
            if ($wsPrice == '0' || $wsPrice == '') {
                $wsPrice = $wsLevels[0];
            }
            if ($wsPrice == '0'){
                $wsPrice = $price;
            }
        }

        return $wsPrice;
    }

?>
