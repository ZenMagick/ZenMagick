<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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
namespace apps\store\bundles\ZenCartBundle\wrapper;

use zenmagick\base\Beans;
use zenmagick\base\ZMObject;

use apps\store\bundles\ZenCartBundle\mock\ZenCartMock;
use apps\store\bundles\ZenCartBundle\wrapper\ShippingMethodWrapper;

/**
 * Shipping provider wrapper for zen cart shipping modules.
 *
 * @author DerManoMann
 * @package apps.store.bundles.ZenCartBundle.wrapper
 */
class ShippingProviderWrapper extends ZMObject implements \ZMShippingProvider {
    private $zenModule_;
    private $errors_;


    /**
     * Create a new shipping provider.
     *
     * @param mixed module A zen-cart shipping module; default is <code>null</code>.
     */
    public function __construct($module=null) {
        parent::__construct();
        $this->zenModule_ = $module;
        $this->errors_ = array();
    }


    /**
     * Set the zencart module to wrap.
     *
     * @param mixed module A zen-cart shipping module.
     */
    public function setModule($module) {
        $this->zenModule_ = $module;
    }

    /**
     * {@inheritDoc}
     */
    public function getId() { return $this->zenModule_->code; }

    /**
     * {@inheritDoc}
     */
    public function getName() { return $this->zenModule_->title; }

    /**
     * {@inheritDoc}
     */
    public function hasIcon() { return !\ZMLangUtils::isEmpty($this->zenModule_->icon); }

    /**
     * {@inheritDoc}
     */
    public function getIcon() { return $this->hasIcon() ? $this->zenModule_->icon : null; }

    /**
     * {@inheritDoc}
     */
    public function isInstalled() { return $this->zenModule_->check(); }

    /**
     * {@inheritDoc}
     */
    public function hasErrors() { return 0 < count($this->errors_); }

    /**
     * {@inheritDoc}
     */
    public function getErrors() { return $this->errors_; }

    /**
     * {@inheritDoc}
     */
    public function getShippingMethodForId($id, $shoppingCart, $address=null) {
        $methods = $this->getShippingMethods($shoppingCart, $address);
        return (array_key_exists($id, $methods) ? $methods[$id] : null);
    }

    /**
     * {@inheritDoc}
     */
    public function getShippingMethods($shoppingCart, $address=null) {
        if (null == $address) {
            // now we just want the shipping method, but we need an address right now...
            $address = $shoppingCart->getShippingAddress();
        }

        $this->errors_ = array();

        ZenCartMock::startMock($shoppingCart, $address);

        // create new instance for each quote!
        // this is required as most modules do stuff in the c'tor (for example zone checks)
        $clazzName = get_class($this->zenModule_);
        $module = new $clazzName();

        $quotes = $module->quote();

        // capture error(s)
        if (is_array($quotes) && array_key_exists('error', $quotes)) {
            $this->errors_ = array($quotes['error']);
            return array();
        }

        // capture tax
        $taxRate = Beans::getBean("ZMTaxRate");
        $taxRate->setRate(isset($quotes['tax']) ? $quotes['tax'] : 0);

        $methods = array();
        if (is_array($quotes) && array_key_exists('methods', $quotes)) {
            foreach ($quotes['methods'] as $method) {
                $shippingMethod = new ShippingMethodWrapper($this, $method);
                $shippingMethod->setProvider($this);
                $shippingMethod->setTaxRate($taxRate);
                $methods[$shippingMethod->getId()] = $shippingMethod;
            }
        }

        ZenCartMock::cleanupMock();

        return $methods;
    }

}
