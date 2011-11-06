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
 * Demo payment type.
 *
 * @author DerManoMann
 * @package zenmagick.store.admin.model.demo
 */
class ZMDemoPaymentType extends ZMObject implements ZMPaymentType {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
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
        return 'demo';
    }

    /**
     * {@inheritDoc}
     */
    public function getName() {
        return 'Demo Payment';
    }

    /**
     * {@inheritDoc}
     */
    public function getTitle() {
        return 'Demo Payment';
    }

    /**
     * {@inheritDoc}
     */
    public function getDescription() {
        return 'Demo Payment Type';
    }

    /**
     * {@inheritDoc}
     */
    public function getError() {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getFields() {
      return array();
    }

    /**
     * {@inheritDoc}
     */
    public function getInfo() {
        return '';
    }

    /**
     * {@inheritDoc}
     */
    public function getFormValidationJS($request) {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getOrderFormContent($request) {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getOrderFormUrl($request) {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getOrderStatus() {
        return 1;
    }

}
