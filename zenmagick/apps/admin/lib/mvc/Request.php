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
 * Admin request wrapper.
 *
 * @author DerManoMann
 * @package org.zenmagick.store.admin.mvc
 * @version $Id$
 */
class Request extends ZMRequest {

    /**
     * Create new instance.
     *
     * @param array parameter Optional request parameter; if <code>null</code>,
     *  <code>$_GET</code> and <code>$_POST</code> will be used.
     */
    function __construct($parameter=null) {
        parent::__construct($parameter);
        $this->setSession(ZMLoader::make('Session', null, 'zmAdmin'));
        if ('db' == ZMSettings::get('sessionPersistence')) {
            $this->getSession()->registerSessionHandler(ZMLoader::make('ZenCartSessionHandler'));
        }
    }


    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }

}
