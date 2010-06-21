<?php
/*
 * ZenMagick - Smart e-commerce
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
 * Admin controller.
 *
 * @author DerManoMann
 * @package org.zenmagick.plugins.merchandizingAssociations
 * @version $Id$
 */
class ZMMerchandizingAssociationsAdminTabController extends ZMPluginAdminController {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('merchandizing_associations_admin', _zm('Merchandizing Associations'), 'merchandizingAssociations');
    }


    /**
     * Get shared page data.
     *
     * @param ZMRequest request The current request.
     * @return array Some data map.
     */
    protected function getCommonViewData($request) {
        return array();
    }

    /**
     * {@inheritDoc}
     */
    public function processGet($request) {
        // need to do this to for using PluginAdminView rather than SimplePluginFormView
        return $this->findView(null, $this->getCommonViewData($request));
    }

    /**
     * {@inheritDoc}
     */
    public function processPost($request) {
        // need to do this to for using PluginAdminView rather than SimplePluginFormView
        return $this->findView(null, $this->getCommonViewData($request));
    }

}
