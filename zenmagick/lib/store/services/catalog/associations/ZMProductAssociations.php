<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
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
 * Manage pluggable product associations.
 *
 * <p>Handler can be explicitely registered using the <code>registerHandler(..)</code> method, or, preferrably
 * via the setting '<em>defaultProductAssociationHandler</em>' - as comma separated list of class names.</p>
 * 
 * @author DerManoMann
 * @package org.zenmagick.store.services.catalog.associations
 * @version $Id$
 */
class ZMProductAssociations extends ZMObject {
    private $handler_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->handler_ = array();
        $defaultHandler = ZMSettings::get('defaultProductAssociationHandler');
        if (is_string($defaultHandler)) {
            $defaultHandler = explode(',', $defaultHandler);
        }
        foreach ($defaultHandler as $handler) {
            $this->registerHandler($handler);
        }
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }

    /**
     * Get instance.
     */
    public static function instance() {
        return ZMObject::singleton('ProductAssociations');
    }


    /**
     * Register an association handler.
     *
     * <p>The optional <code>$type</code> parameter allows to register a 
     * handler as string. If omitted, an instance of the handler will be
     * created immediately in order to query the type. This also allows to
     * register the same handler for different types, if required.</p>
     *
     * @param mixed handler This can be either a <code>ZMProductAssociationHandler</code> instance, or a class definition compatible with
     *  <code>ZMBeanUtils::getBean(..)</code>.
     * @param string type Optional association type/name; default is <code>null</code>.
     */
    public function registerHandler($handler, $type=null) {
    	if (null == $type) {
            if (!is_object($handler)) {
                $handler = ZMBeanUtils::getBean($handler);
            } 
            $type = $handler->getType();
    	}
        $this->handler_[$type] = $handler;
    }

    /**
     * Get all handler types.
     *
     * @return array A list of all registered handler types.
     */
    public function getHandlerTypes() {
        return array_keys($this->handler_);
    }

    /**
     * Get a handler for the given type.
     *
     * @param string type The association type/name.
     * @return ZMProductAssociationHandler A handler instance or <code>null</code>.
     */
    public function getHandlerForType($type) {
        $handler = null;
        if (array_key_exists($type, $this->handler_)) {
            if (is_string($this->handler_[$type])) {
                // instantiate on demand only
                $this->handler_[$type] = ZMBeanUtils::getBean($this->handler_[$type]);
            }
            $handler = $this->handler_[$type];
        }

        return $handler;
    }

    /**
     * Get product associations for the given product, type and parameter.
     *
     * @param int productId The source product id.
     * @param int type The association type.
     * @param array args Optional parameter that might be required by the used type; default is <code>null</code> for none.
     * @param boolean all Optional flag to load all configured products, regardless of start/end date, etc; default is <code>false</code>.
     * @return array A list of <code>ZMProductAssociation</code> instances.
     */
    public function getProductAssociationsForProductId($productId, $type, $args=null, $all=false) {
        if (null != ($handler = $this->getHandlerForType($type))) {
            return $handler->getProductAssociationsForProductId($productId, $args, $all);
        }

        return null;
    }

    /**
     * Get associated products for the given category.
     *
     * <p>This is mostly a convenience method to avoid having to iterate over all products in a given category yourself.</p>
     *
     * <p>This method will also take care of duplicates.</p>
     *
     * @param int categoryId The category.
     * @param int type The association type.
     * @param array args Optional parameter that might be required by the used type; default is <code>null</code> for none.
     * @param boolean all Optional flag to load all configured products, regardless of start/end date, etc; default is <code>false</code>.
     * @return array A list of <code>ZMProductAssociation</code> instances.
     */
    public function getProductAssociationsForCategoryId($categoryId, $type, $args=null, $all=false) {
        if (null != ($handler = $this->getHandlerForType($type))) {
            $defaults = array('includeChildren' => false, 'languageId' => null);
            if (null === $args) {
                $args = $defaults;
            } else {
                $args = array_merge($defaults, $args);
            }

            $assoc = array();
            $products = ZMProducts::instance()->getProductIdsForCategoryId($categoryId, !$all, $args['includeChildren'], $args['languageId']);
            foreach ($products as $product) {
                foreach ($product->getProductAssociationsForType($type, $args, $all) as $pa) {
                    if (!array_key_exists($pa->getProductId(), $assoc)) {
                        $assoc[$pa->getProductId()] = $pa;
                    }
                }
            }

            return $assoc;
        }

        return null;
    }

    /**
     * Get associated products for the given shopping cart.
     *
     * <p>This is mostly a convenience method to avoid having to iterate over all products in the given cart.</p>
     *
     * <p>This method will also take care of duplicates.</p>
     *
     * @param ZMShoppingCart shoppingCart The shopping cart.
     * @param int type The association type.
     * @param array args Optional parameter that might be required by the used type; default is <code>null</code> for none.
     * @param boolean all Optional flag to load all configured products, regardless of start/end date, etc; default is <code>false</code>.
     * @return array A list of <code>ZMProductAssociation</code> instances.
     */
    public function getProductAssociationsForShoppingCart($shoppingCart, $type, $args=null, $all=false) {
        if (null != ($handler = $this->getHandlerForType($type))) {
            $defaults = array('includeChildren' => false, 'languageId' => null);
            if (null === $args) {
                $args = $defaults;
            } else {
                $args = array_merge($defaults, $args);
            }

            $assoc = array();
            foreach ($shoppingCart->getItems as $item) {
                $product = $item->getProduct();
                foreach ($product->getProductAssociationsForType($type, $args, $all) as $pa) {
                    if (!array_key_exists($pa->getProductId(), $assoc)) {
                        $assoc[$pa->getProductId()] = $pa;
                    }
                }
            }

            return $assoc;
        }

        return null;
    }

}

?>
