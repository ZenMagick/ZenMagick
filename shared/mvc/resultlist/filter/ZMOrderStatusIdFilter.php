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
 * Filter orders by status id.
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.mvc.resultlist.filter
 */
class ZMOrderStatusIdFilter extends ZMResultListFilter implements ZMSQLAware {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('ofilter', _zm('Order Status'), ZMRequest::instance()->getParameter('ofilter'));
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
    public function exclude($obj) {
        return !in_array($obj->getOrderStatusId(), $this->filterValues_);
    }

    /**
     * {@inheritDoc}
     */
    public function getQueryDetails($method=null, $args=array()) {
        return new ZMQueryDetails(ZMRuntime::getDatabase(), 'o.orders_status = 2');
    }

}
