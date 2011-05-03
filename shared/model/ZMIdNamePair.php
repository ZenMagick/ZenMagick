<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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
 * Simple id/name container.
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.model
 */
class ZMIdNamePair extends ZMObject {
    var $id_;
    var $name_;


    /**
     * Create new id - name pair.
     *
     * @param int id The id.
     * @param string name The name.
     */
    function __construct($id, $name) {
        parent::__construct();
        $this->id_ = $id;
        $this->name_ = $name;
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get the id.
     *
     * @return string The id.
     */
    function getId() { return $this->id_; }

    /**
     * Get the name.
     *
     * @return string The name.
     */
    function getName() { return $this->name_; }

}
