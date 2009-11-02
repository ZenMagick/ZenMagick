<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
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
     * @package org.zenmagick.deprecated
     * @param string name The sender name.
     * @param string email The sender email.
     * @param ZMSession session The current session.
     * @return array Hash of extra information.
     * @deprecated use the new toolbox instead!
     */
    function zm_email_copy_context($name, $email, $session) {
        return ZMRequest::instance()->getToolbox()->macro->officeOnlyEmailFooter($name, $email, $session);
    }

?>
