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
namespace zenmagick\apps\store\services\catalog;

use zenmagick\base\Beans;
use zenmagick\base\Runtime;
use zenmagick\base\ZMObject;

/**
 * Manage pluggable product associations.
 *
 * <p>Handler are looked up in the container with a tag of '<em>apps.store.associations.handler</em>'.</p>
 *
 * @author DerManoMann
 */
class ProductAssociationService extends ZMObject {

    /**
     * Get all handlers.
     *
     * @return array List of all handlers.
     */
    public function getHandlers() {
        $handlers = array();
        foreach ($this->container->findTaggedServiceIds('apps.store.associations.handler') as $id => $args) {
            $handler = $this->container->get($id);
            $handlers[$handler->getType()] = $handler;
        }
        return $handlers;
    }

    /**
     * Get all handler types.
     *
     * @return array A list of all registered handler types.
     */
    public function getHandlerTypes() {
        return array_keys($this->getHandlers());
    }

    /**
     * Get a handler for the given type.
     *
     * @param string type The association type/name.
     * @return ProductAssociationHandler A handler instance or <code>null</code>.
     */
    public function getHandlerForType($type) {
        $handlers = $this->getHandlers();
        if (array_key_exists($type, $handlers)) {
            return $handlers[$type];
        }
        return null;
    }

    /**
     * Get product associations for the given product, type and parameter.
     *
     * @param int productId The source product id.
     * @param int type The association type.
     * @param array args Optional parameter that might be required by the used type; default is an empty array.
     * @return array A list of <code>ProductAssociation</code> instances.
     */
    public function getProductAssociationsForProductId($productId, $type, $args=array()) {
        if (null != ($handler = $this->getHandlerForType($type))) {
            return $handler->getProductAssociationsForProductId($productId, $args);
        }

        return array();
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
     * @return array A list of <code>ProductAssociation</code> instances.
     */
    public function getProductAssociationsForCategoryId($categoryId, $type, $args=null) {
        if (null != ($handler = $this->getHandlerForType($type))) {
            $defaults = array('includeChildren' => false, 'languageId' => null);
            if (null === $args) {
                $args = $defaults;
            } else {
                $args = array_merge($defaults, $args);
            }

            $assoc = array();
            $products = $this->container->get('productService')->getProductIdsForCategoryId($categoryId, $args['languageId'], $args['includeChildren']);
            foreach ($products as $product) {
                foreach ($product->getProductAssociationsForType($type, $args) as $pa) {
                    if (!array_key_exists($pa->getProductId(), $assoc)) {
                        $assoc[$pa->getProductId()] = $pa;
                    }
                }
            }

            return $assoc;
        }

        return array();
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
     * @return array A list of <code>ProductAssociation</code> instances.
     */
    public function getProductAssociationsForShoppingCart($shoppingCart, $type, $args=null) {
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
                foreach ($product->getProductAssociationsForType($type, $args) as $pa) {
                    if (!array_key_exists($pa->getProductId(), $assoc)) {
                        $assoc[$pa->getProductId()] = $pa;
                    }
                }
            }

            return $assoc;
        }

        return array();
    }

}
