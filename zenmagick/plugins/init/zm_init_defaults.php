<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2008 ZenMagick
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
 * Init plugin to set up general request defaults.
 *
 * @package org.zenmagick.plugins.init
 * @author DerManoMann
 * @version $Id$
 */
class zm_init_defaults extends ZMPlugin {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('Defaults', 'Set request defaults');
        $this->setScope(ZMPlugin::SCOPE_STORE);
        $this->setPreferredSortOrder(10);
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }

    /**
     * Init this plugin.
     */
    function init() {
        parent::init();

        $parameter = ZMRequest::getParameterMap();

        /** sanitize common parameter **/
        if (isset($parameter['products_id'])) $parameter['products_id'] = preg_replace('/[^0-9a-f:]/', '', $parameter['products_id']);
        if (isset($parameter['manufacturers_id'])) $parameter['manufacturers_id'] = preg_replace('/[^0-9]/', '', $parameter['manufacturers_id']);
        if (isset($parameter['cPath'])) $parameter['cPath'] = preg_replace('/[^0-9_]/', '', $parameter['cPath']);
        if (isset($parameter[ZM_PAGE_KEY])) $parameter[ZM_PAGE_KEY] = preg_replace('/[^0-9a-zA-Z_]/', '', $parameter[ZM_PAGE_KEY]);

        /** sanitize other stuff **/
        $_SERVER['REMOTE_ADDR'] = preg_replace('/[^0-9.%]/', '', $_SERVER['REMOTE_ADDR']);

        if (!isset($parameter[ZM_PAGE_KEY]) || empty($parameter[ZM_PAGE_KEY])) $parameter[ZM_PAGE_KEY] = 'index';

        ZMRequest::setParameterMap($parameter);
    }

}

?>
