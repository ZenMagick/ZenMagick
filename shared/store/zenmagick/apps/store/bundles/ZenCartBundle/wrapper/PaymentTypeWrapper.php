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
namespace zenmagick\apps\store\bundles\ZenCartBundle\wrapper;

use zenmagick\base\ZMObject;

use zenmagick\apps\store\bundles\ZenCartBundle\Mock\ZenCartMock;

/**
 * A payment type wrapper for Zen Cart payment modules.
 *
 * @author DerManoMann
 * @package zenmagick.apps.store.bundles.ZenCartBundle.wrapper
 */
class PaymentTypeWrapper extends ZMObject implements \ZMPaymentType {
    private $module_;
    private $selection_;
    private $fields_;


    /**
     * Create a new payment type.
     *
     * @param object module The payment module; default is <code>null</code>.
     */
    function __construct($module=null) {
        parent::__construct();
        $this->setModule($module);
        $this->fields_ = null;
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Set the zencart module to wrap.
     *
     * @param mixed module A zen-cart payment module; default is <code>null</code>.
     */
    public function setModule($module) {
        $this->module_ = $module;
        if (null != $module) {
            $this->selection_ = $module->selection();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getId() {
        return $this->module_->code;
    }

    /**
     * {@inheritDoc}
     */
    public function getName() {
        return $this->selection_['module'];
    }

    /**
     * {@inheritDoc}
     */
    public function getTitle() {
        return $this->module_->title;
    }

    /**
     * {@inheritDoc}
     */
    public function getDescription() {
        return $this->module_->description;
    }

    /**
     * {@inheritDoc}
     */
    public function getError() { return $this->module_->get_error(); }

    /**
     * {@inheritDoc}
     */
    public function getFields() {
        if (null === $this->fields_) {
            if (array_key_exists('fields', $this->selection_)) {
                foreach ($this->selection_['fields'] as $field) {
                    $this->fields_[] = new \ZMPaymentField($field['title'], $field['field']);
                }
            }
        }

        return $this->fields_;
    }

    /**
     * {@inheritDoc}
     */
    public function getInfo() {
        if (isset($this->module_->email_footer) && !\ZMLangUtils::isEmpty($this->module_->email_footer)) {
            return $this->module_->email_footer;
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getFormValidationJS($request) {
        return $this->module_->javascript_validation();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrderFormContent($request) {
        // TODO: move into controller
        ZenCartMock::startMock($request->getShoppingCart());
        $this->module_->confirmation();
        $button =  $this->module_->process_button();
        ZenCartMock::cleanupMock();
        return $button;
    }

    /**
     * {@inheritDoc}
     */
    public function getOrderFormUrl($request) {
        return isset($this->module_->form_action_url) ? $this->module_->form_action_url : $request->url('checkout_process', '', true);
    }

}
