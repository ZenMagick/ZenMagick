<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006,2007 ZenMagick
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
 * Features.
 *
 * @author mano
 * @package org.zenmagick.service
 * @version $Id$
 */
class ZMFeatures extends ZMService {
    var $features_;
    var $productFeatures_;
    var $featureTypes_;


    /**
     * Default c'tor.
     */
    function ZMFeatures() {
        parent::__construct();

        $this->features_ = array();
        $this->productFeatures_ = array();
        $this->featureTypes_ = null;
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMFeatures();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    function _loadFeatures() {
    global $zm_runtime;

        if (null != $this->features_)
            return;

        $db = $this->getDB();
        $sql = "select f.feature_id, f.feature_type_id, f.language_id, f.feature_name, f.feature_description,
                f.hidden
                from " .ZM_TABLE_FEATURES . " f
                where f.language_id = :languageId";
        $sql = $db->bindVars($sql, ':languageId', $zm_runtime->getLanguageId(), 'integer');

        $results = $db->Execute($sql);

        $this->features_ = array();
        while (!$results->EOF) {
            $feature = $this->create("Feature");
            $feature->id_ = $results->fields['feature_id'];
            $feature->type_ = $results->fields['feature_type_id'];
            $feature->name_ = $results->fields['feature_name'];
            $feature->description_ = $results->fields['feature_description'];
            $feature->hidden_ = $results->fields['hidden'];
            $this->features_[$feature->id_] = $feature;
            $results->MoveNext();
        }
        ksort($this->features_);

        return;
    }

    function _loadFeatureTypes() {
        if (null != $this->featureTypes_)
            return;

        $db = $this->getDB();
        $query = "select feature_type_id, feature_type
                  from " . ZM_TABLE_FEATURE_TYPES;

        $results = $db->Execute($query);

        $this->featureTypes_ = array();
        while (!$results->EOF) {
            $this->featureTypes_[$results->fields['feature_type_id']] = $results->fields['feature_type'];
            $results->MoveNext();
        }

        return;
    }

    // get a list of all features
    function getFeatureList() {
        $this->_loadFeatures();

        return $this->features_;
    }

    // get feature types
    function getFeatureTypes() {
        $this->_loadFeatureTypes();

        $types = array();
        foreach ($this->featureTypes_ as $id => $name) {
            $types[$id] = $this->create("IdNamePair", $id, $name);
        }

        return $types;
    }

    // get feature type for id
    function &getFeatureTypeForId($id) {
        $this->_loadFeatureTypes();

        $type = null;
        if (array_key_exists($id, $this->featureTypes_)) {
            $type = $this->create("IdNamePair", $id, $this->featureTypes_[$id]);
        }

        return $type;
    }

    // get feature for id
    function &getFeatureForId($id) {
        $this->_loadFeatures();

        $feature = null;
        if (array_key_exists($id, $this->features_)) {
            $feature = $this->features_[$id];
        }

        return $feature;
    }

    // remove feature for the given id
    function removeFeatureForId($featureId) {
        $db = $this->getDB();
        $sql = "delete from " . ZM_TABLE_FEATURES . "
                where feature_id = :featureId";
        $sql = $db->bindVars($sql, ':featureId', $featureId, 'integer');

        $db->Execute($sql);
        $this->featureDescriptions_ = null;
        return true;
    }

    // add feature
    function addFeature($type, $languageId, $name, $description, $hidden=false) {
        $db = $this->getDB();
        $sql = "insert into " . ZM_TABLE_FEATURES . "
                (feature_type_id, language_id, feature_name, feature_description, hidden)
                values (:type, :languageId, :name, :description, :hidden)";
        $sql = $db->bindVars($sql, ':type', $type, 'integer');
        $sql = $db->bindVars($sql, ':languageId', $languageId, 'integer');
        $sql = $db->bindVars($sql, ':name', $name, 'string');
        $sql = $db->bindVars($sql, ':description', $description, 'string');
        $sql = $db->bindVars($sql, ':hidden', $hidden, 'integer');

        $db->Execute($sql);
        return true;
    }

    // update feature
    function updateFeature($featureId, $languageId, $name, $description, $hidden) {
        $db = $this->getDB();
        $sql = "update " . ZM_TABLE_FEATURES . "
                set feature_name = :name,
                    feature_description = :description,
                    hidden = :hidden
                where feature_id = :featureId
                and language_id = :languageId";
        $sql = $db->bindVars($sql, ':name', $name, 'string');
        $sql = $db->bindVars($sql, ':description', $description, 'string');
        $sql = $db->bindVars($sql, ':hidden', $hidden, 'integer');
        $sql = $db->bindVars($sql, ':featureId', $featureId, 'integer');
        $sql = $db->bindVars($sql, ':languageId', $languageId, 'integer');

        $db->Execute($sql);
        // invalidate cache
        $this->features_ = null;
        return true;
    }

    // add feature for product
    function addFeatureForProduct($productId, $featureId, $value, $index=1) {
        $db = $this->getDB();
        $sql = "insert into " . ZM_TABLE_PRODUCT_FEATURES . "
                (product_id, feature_id, feature_index_id, feature_value)
                values (:productId, :featureId, :index, :value)";
        $sql = $db->bindVars($sql, ':productId', $productId, 'integer');
        $sql = $db->bindVars($sql, ':featureId', $featureId, 'integer');
        $sql = $db->bindVars($sql, ':index', $index, 'integer');
        $sql = $db->bindVars($sql, ':value', $value, 'string');

        $db->Execute($sql);
        return true;
    }

    // remove feature for product
    function removeFeatureForProduct($productId, $featureId, $index=null) {
        $db = $this->getDB();
        $sql = "delete from " . ZM_TABLE_PRODUCT_FEATURES . "
                where product_id = :productId
                and feature_id = :featureId";
        $sql = $db->bindVars($sql, ':productId', $productId, 'integer');
        $sql = $db->bindVars($sql, ':featureId', $featureId, 'integer');
        if (null != $index) {
            $sql .= " and feature_index_id = :index";
            $sql = $db->bindVars($sql, ':index', $index, 'integer');
        }
        $db->Execute($sql);
        return true;
    }

    // update feature for product
    function updateFeatureForProduct($productId, $featureId, $oldIndex, $value, $index) {
        $db = $this->getDB();
        $sql = "update " . ZM_TABLE_PRODUCT_FEATURES . "
                set feature_value = :value,
                    feature_index_id = :index
                where product_id = :productId
                and feature_id = :featureId
                and feature_index_id = :oldIndex";
        $sql = $db->bindVars($sql, ':value', $value, 'string');
        $sql = $db->bindVars($sql, ':index', $index, 'integer');
        $sql = $db->bindVars($sql, ':productId', $productId, 'integer');
        $sql = $db->bindVars($sql, ':featureId', $featureId, 'integer');
        $sql = $db->bindVars($sql, ':oldIndex', $oldIndex, 'integer');

        $db->Execute($sql);
        // invalidate cache
        unset($this->productFeatures_[$productId]);
        return true;
    }

    // get features for id
    function getFeaturesForProductId($productId) {
        if (array_key_exists($productId, $this->productFeatures_))
            return $this->productFeatures_[$productId];

        $db = $this->getDB();
        $sql = "select f.product_feature_id, f.feature_id, f.feature_index_id, f.feature_value
                  from " . ZM_TABLE_PRODUCT_FEATURES . " f
                  where f.product_id = :productId
                  order by f.feature_id, f.feature_index_id";
        $sql = $db->bindVars($sql, ':productId', $productId, 'integer');
        $results = $db->Execute($sql);

        $this->_loadFeatures();

        $features = array();
        $feature = null;
        $currentFeatureId = -1;
        while (!$results->EOF) {
            if ($results->fields['feature_id'] != $currentFeatureId) {
                if (null != $feature) {
                    $features[$feature->getName()] = $feature;
                }
                $feature = $this->create("Feature");
            }
            $feature->id_ = $results->fields['feature_id'];
            $tmp = $this->features_[$feature->id_];
            $feature->type_ = $tmp->type_;
            $feature->name_ = $tmp->name_;
            $feature->description_ = $tmp->description_;
            $feature->hidden_ = $tmp->hidden_;
            $feature->values_[$results->fields['feature_index_id']] =  $results->fields['feature_value'];

            $currentFeatureId = $results->fields['feature_id'];
            $results->MoveNext();
        }
        if (null != $feature) {
            // last one
            $features[$feature->getName()] = $feature;
        }

        $this->productFeatures_[$productId] = $features;
        return $features;
    }

}

?>
