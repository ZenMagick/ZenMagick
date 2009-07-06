<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
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
 * Filter orders by status id.
 *
 * @author DerManoMann
 * @package org.zenmagick.store.mvc.resultlist.filter
 * @version $Id: ZMOrderStatusIdFilter.php 2332 2009-06-26 04:05:17Z dermanomann $
 */
class ZMOrderStatusIdFilter extends ZMResultListFilter implements ZMSQLAware {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('ofilter', zm_l10n_get('Order Status'), ZMRequest::getParameter('ofilter'));
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Return <code>true</code> if the given object is to be excluded.
     *
     * @param mixed obj The obecjt to examine.
     * @return boolean <code>true</code> if the object is to be excluded, <code>false</code> if not.
     */
    public function exclude($obj) { return !in_array($obj->getOrderStatusId(), $this->filterValues_); }

    /**
     * {@inheritDoc}
     */
    public function getQueryDetails($method=null, $args=array()) {
        //TODO: allow mapped names
        return new ZMQueryDetails(Runtime::getDatabase(), 'o.orders_status = 2');
    }

}

?>
