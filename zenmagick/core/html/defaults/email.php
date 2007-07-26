<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006,2007 ZenMagick
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
 *
 * $Id$
 */
?>
<?php


    /**
     * Format additional email content for internal copies.
     *
     * @package net.radebatz.zenmagick.html.defaults
     * @param ZMAccount account The current account.
     * @param ZMSession session The current session.
     * @return array Hash of extra information.
     */
    function zm_email_copy_context($account, $session) {
        $context = array();

        // try hostname
        $hostname = $session->getClientHostname();
        if (null == $hostname) {
            if (zm_setting('isResolveClientIP')) {
                $hostname = gethostbyaddr($session->getClientAddress());
            } else {
                $hostname = zm_l10n_get("Disabled");
            }
        }

        $context['office_only_text'] = 
          zm_l10n_get("Office Use Only:") . "\n" .
          zm_l10n_get("From:") . $account->getFullname() . "\n" .
          zm_l10n_get("Email:") . $account->getEmail() . "\n" .
          zm_l10n_get("Remote:") . $session->getClientAddress() . " - " . $hostname . "\n" .
          zm_l10n_get("Date:") . date("D M j Y G:i:s T") . "\n\n";
        $context['office_only_html'] = nl2br($context['office_only_text']);
    }

?>
