<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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

use zenmagick\base\ZMObject;

/**
 * A single attribute.
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.model.catalog
 */
class ZMAttribute extends ZMObject {
    private $productId_;
    private $name_;
    private $type_;
    private $sortOrder_;
    private $comment_;
    private $values_;


    /**
     * Create new attribute.
     *
     * @param int id The id.
     * @param string name The name.
     * @param string type The type.
     */
    public function __construct($id=0, $name=null, $type=null) {
        parent::__construct();
        $this->set('attributeId', $id);
        $this->name_ = $name;
        $this->type_ = $type;
        $this->values_ = array();
    }


    /**
     * Get the attribute id.
     *
     * @return int The attribute id.
     */
    public function getId() { return $this->get('attributeId'); }

    /**
     * Get the product id.
     *
     * @return int The product id.
     */
    public function getProductId() { return $this->productId_; }

    /**
     * Get the attribute name.
     *
     * @return string The attribute name.
     */
    public function getName() { return $this->name_; }

    /**
     * Get the attribute type.
     *
     * @return string The attribute type.
     */
    public function getType() { return $this->type_; }

    /**
     * Get the attribute sort order.
     *
     * @return int The attribute sort order.
     */
    public function getSortOrder() { return $this->sortOrder_; }

    /**
     * Get the attribute comment.
     *
     * @return string The attribute comment.
     */
    public function getComment() { return $this->comment_; }

    /**
     * Get the attribute values.
     *
     * @return array A list of <code>ZMAttributeValue</code> objects.
     */
    public function getValues() { return $this->values_; }

    /**
     * Set the attribute id.
     *
     * @param int id The attribute id.
     */
    public function setId($id) { $this->set('attributeId', $id); }

    /**
     * Set the product id.
     *
     * @param int productId The product id.
     */
    public function setProductId($productId) { $this->productId_ = $productId; }

    /**
     * Set the attribute name.
     *
     * @param string name The attribute name.
     */
    public function setName($name) { $this->name_ = $name; }

    /**
     * Set the attribute type.
     *
     * @return string The attribute type.
     */
    public function setType($type) { $this->type_ = $type; }

    /**
     * Set the attribute sort order.
     *
     * @param int sortOrder The attribute sort order.
     */
    public function setSortOrder($sortOrder) { $this->sortOrder_ = $sortOrder; }

    /**
     * Set the attribute comment.
     *
     * @param string comment The attribute comment.
     */
    public function setComment($comment) { $this->comment_ = $comment; }

    /**
     * Add an attribute value.
     *
     * @param ZMAttributeValue value A <code>ZMAttributeValue</code>.
     */
    public function addValue($value) { $this->values_[] = $value; }

    /**
     * Clear all values.
     */
    public function clearValues() {
        $this->values_ = array();
    }

    /**
     * Remove an attribute value.
     *
     * @param mixed value Either a <code>ZMAttributeValue</code> instance or a value id.
     */
    public function removeValue($value) {
        for ($ii=0, $size=count($this->values_); $ii < $size; ++$ii) {
            if ((is_object($value) && $value === $this->values_[$ii] ) || (is_numeric($value) && (int)$value == $this->values_[$ii]->getId())) {
                array_splice($this->values_, $ii, 1);
                break;
            }
        }
    }

    /**
     * Check if this attribute is virtual.
     *
     * <p>An attribute can be virtual if, for example, the value is a downloadable file.</p>
     *
     * @return boolean <code>true</code> if, and only if, the attribute is virtual.
     */
    public function isVirtual() {
        $attributeService = $this->container->get('attributeService');
        return $attributeService->hasDownloads($this);
    }

}
