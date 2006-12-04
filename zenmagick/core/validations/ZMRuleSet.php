<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006 ZenMagick
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
 * A set of validation rules.
 *
 * @author mano
 * @package net.radebatz.zenmagick.validations
 * @version $Id$
 */
class ZMRuleSet {
    var $id_;
    var $rules_;


    // create new instance
    function ZMRuleSet($id, $rules=null) {
        $this->id_ = $id;
        $this->rules_ = null != $rules ? $rules : array();
    }

    // create new instance
    function __construct($id, $rules=null) {
        $this->ZMRuleSet($id, $rules);
    }

    function __destruct() {
    }


    // getter
    function getId() { return $this->id_; }


    /**
     * Add a new <code>ZMRule</code>.
     *
     * @param ZMRule rule A new rule.
     */
    function addRule($rule) {
        array_push($this->rules_, $rule);
    }


    /**
     * Get the validation rules.
     *
     * @return array A list of <code>ZMRule</code> objects.
     */
    function getRules() {
        return $this->rules_;
    }

}

?>
