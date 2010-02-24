<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 zenmagick.org
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
 * Admin controller for block management.
 *
 * @author DerManoMann
 * @package org.zenmagick.plugins.blockHandler
 * @version $Id$
 */
class ZMBlockHandlerAdminController extends ZMPluginAdminController {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('block_handler_admin', zm_l10n_get('Block Hander Admin'), 'blockHandler');
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
    public function processGet($request) {
        return $this->findView(null, array());
    }

    /**
     * {@inheritDoc}
     */
    public function processPost($request) {
        // todo: process form submit, etc
        return $this->getRedirectView($request);
    }

}

?>
