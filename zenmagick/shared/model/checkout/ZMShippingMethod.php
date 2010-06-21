<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2010 zenmagick.org
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
 * Shipping method.
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.model.checkout
 */
class ZMShippingMethod extends ZMObject {
    private $provider_;
    private $taxRate_;
    private $zenMethod_;


    /**
     * Create new shipping method.
     *
     * @param ZMShippingProvider provider The shipping provider for this method.
     * @param array zenMethod The zen-cart method infos.
     */
    function __construct($provider, $zenMethod) {
        parent::__construct();
        $this->provider_ = $provider;
        $this->zenMethod_ = $zenMethod;
        $this->taxRate_ = ZMLoader::make("TaxRate"); 
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get the shipping method id.
     *
     * @return int The shipping method id.
     */
    public function getId() { return $this->zenMethod_['id']; }

    /**
     * Set the shipping method id.
     *
     * @param int id The shipping method id.
     */
    public function setId($id) {  }

    /**
     * Get the shipping method name.
     *
     * @return string The shipping method name.
     */
    public function getName() { return $this->zenMethod_['title']; }

    /**
     * Get the shipping cost.
     *
     * @return float The shipping cost.
     */
    public function getCost() { return $this->taxRate_->addTax($this->zenMethod_['cost']); }

    /**
     * Get the shipping provider.
     *
     * @return ZMShippingProvider The shipping provider.
     */
    public function getProvider() { return $this->provider_; }

    /**
     * Set the shipping provider.
     *
     * @param ZMShippingProvider provider The shipping provider.
     */
    public function setProvider($provider) { $this->provider_ = $provider; }

    /**
     * Set the tax rate.
     *
     * @param ZMTaxRate taxRate The tax rate.
     */
    public function setTaxRate($taxRate) { $this->taxRate_ = $taxRate; }

    /**
     * Get the shipping id.
     *
     * @return string The shipping id as used by the shopping cart.
     */
    public function getShippingId() {
        $id = $this->provider_->getId() . '_' . $this->getId();
        return $id;
    }

}
