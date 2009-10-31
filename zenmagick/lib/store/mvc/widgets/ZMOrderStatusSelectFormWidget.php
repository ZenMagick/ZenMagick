<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright (C) 2006,2009 ZenMagick
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
 * <p>A order status select form widget.</p>
 *
 * <p>This widget will append a list of all available order stati to the options list. That
 * means the generic <em>options</em> propert may be used to set custom options that will show
 * up at the top of the list.</p>
 *
 * <p>One typical use is to prepend an empty option if required.</p>
 *
 * @author DerManoMann
 * @package org.zenmagick.store.mvc.widgets
 * @version $Id$
 */
class ZMorderStatusSelectFormWidget extends ZMSelectFormWidget {
    private $showKey_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->showKey_ = true;
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }

    /**
     * Show/hide the numeric key.
     *
     * @param boolean enable <code>true</code> to append the numeric value to the description.
     */
    public function setShowKey($enable) {
        $this->showKey_ = $enable;
    }

    /**
     * Check if the numeric key should be appended to the description.
     *
     * @return boolean <code>true</code> to append, <code>false</code> to hide.
     */
    public function isShowKey() {
        return $this->showKey_;
    }

    /**
     * Get the options map.
     *
     * @return array Map of value/name pairs.
     */
    public function getOptions() {
        $options = parent::getOptions();
        foreach (ZMOrders::instance()->getOrderStatusList() as $idp) {
            $options[$idp->getOrderStatusId()] = $idp->getStatusName() . ($this->showKey_ ? ' ('.$idp->getOrderStatusId().')': '');
        }
        return $options;
    }

}

?>
