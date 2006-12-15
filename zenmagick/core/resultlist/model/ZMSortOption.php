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
 * A single sort option.
 *
 * <p>The returned id will automatically reflect the current status, the id the sort
 * order.</p>
 *
 * @author mano
 * @package net.radebatz.zenmagick.resultlist.model
 * @version $Id$
 */
class ZMSortOption {
    var $name_;
    var $id_;
    var $active_;
    var $decending_;


    // create new instance
    function ZMSortOption($name, $id, $active=false, $decending=false) {
        $this->name_ = $name;
        $this->id_ = $id;
        $this->active_ = $active;
        $this->decending_ = $decending;
    }

    // create new instance
    function __construct($name, $id, $active=false, $decending=false) {
        $this->ZMSortOption($name, $id, $active, $decending);
    }

    function __destruct() {
    }


    //getter
    function getId() { return $this->id_ . ($this->active_ ? ($this->decending_ ? '_a' : '_d') : ""); }
    function getName() { return zm_l10n_get($this->name_) . ($this->active_ ? ($this->decending_ ? '-' : '+') : ""); }
    function isActive() { return $this->active_; }
    function isDecending() { return $this->decending_; }
}

?>
