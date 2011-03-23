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
 * A payment type wrapper for Zen Cart payment modules.
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.model.checkout
 */
class ZMPaymentTypeWrapper extends ZMObject implements ZMPaymentType {
    private $module_;
    private $selection_;
    private $fields_;


    /**
     * Create a new payment type.
     *
     * @param object module The payment module.
     */
    function __construct($module) {
        parent::__construct();
        $this->module_ = $module;
        $this->selection_ = $module->selection();
        $this->fields_ = null;
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
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
                    $this->fields_[] = ZMLoader::make("ZMPaymentField", $field['title'], $field['field']);
                }
            }
        }

        return $this->fields_;
    }

    /**
     * {@inheritDoc}
     */
    public function getInfo() {
        if (isset($this->module_->email_footer) && !ZMLangUtils::isEmpty($this->module_->email_footer)) {
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
        $this->module_->confirmation();
        return $this->module_->process_button();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrderFormUrl($request) {
        return isset($this->module_->form_action_url) ? $this->module_->form_action_url : $request->url('checkout_process', '', true);
    }

}
