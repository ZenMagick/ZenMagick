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
 * Single Feature.
 *
 * @author mano
 * @package org.zenmagick.model.catalog
 * @version $Id$
 */
class ZMFeature extends ZMModel {
    var $id_;
    var $type_;
    var $name_;
    var $description_;
    var $hidden_;
    var $values_;


    /**
     * Create new instance.
     */
    function ZMFeature() {
        parent::__construct();

        $this->id_ = 0;
		    $this->values_ = array();
    }

    /**
     * Create new instance.
     */
    function __construct() {
        $this->ZMFeature();
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get the feature id.
     *
     * @return int The feature id.
     */
    function getId() { return $this->id_; }

    /**
     * Get the feature name.
     *
     * @return string The feature name.
     */
    function getName() { return $this->name_; }

    /**
     * Get the feature description.
     *
     * @return string The feature description.
     */
    function getDescription() { return $this->description_; }

    /**
     * Check if the feature is hidden.
     *
     * @return boolean <code>true</code> if the feature is hidden, <code>false</code> if not.
     */
    function isHidden() { return $this->hidden_; }

    /**
     * Get the feature values.
     *
     * @return array The feature values.
     */
    function getValues() { return $this->values_; }

    /**
     * Get the feature type.
     *
     * @return string The feature type.
     */
    function getType() { global $zm_features; return $zm_features->getFeatureTypeForId($this->type_); }

}

?>
