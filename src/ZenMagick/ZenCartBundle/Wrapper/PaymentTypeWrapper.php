<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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
namespace ZenMagick\ZenCartBundle\Wrapper;

use ZenMagick\Base\ZMObject;
use ZenMagick\Base\Toolbox;
use ZenMagick\Base\Runtime;

use ZenMagick\ZenCartBundle\Mock\ZenCartMock;

/**
 * A payment type wrapper for Zen Cart payment modules.
 *
 * @author DerManoMann
 */
class PaymentTypeWrapper extends ZMObject implements \ZMPaymentType
{
    private $module;
    private $selection;
    private $fields;
    private $prepared;

    /**
     * Create a new payment type.
     *
     * @param object module The payment module; default is <code>null</code>.
     */
    public function __construct($module=null)
    {
        parent::__construct();
        $this->setModule($module);
        $this->fields = null;
        $this->prepared = false;
    }

    /**
     * Set the zencart module to wrap.
     *
     * @param mixed module A zen-cart payment module; default is <code>null</code>.
     */
    public function setModule($module)
    {
        $this->module = $module;
        if (null != $module) {
            $this->selection = $module->selection();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getId()
    {
        return $this->module->code;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return $this->selection['module'];
    }

    /**
     * {@inheritDoc}
     */
    public function getTitle()
    {
        return $this->module->title;
    }

    /**
     * {@inheritDoc}
     */
    public function getDescription()
    {
        return $this->module->description;
    }

    /**
     * {@inheritDoc}
     */
    public function getError() { return $this->module->get_error(); }

    /**
     * {@inheritDoc}
     */
    public function getFields()
    {
        if (null === $this->fields) {
            if (array_key_exists('fields', $this->selection)) {
                foreach ($this->selection['fields'] as $field) {
                    $this->fields[] = new \ZMPaymentField($field['title'], htmlspecialchars_decode(htmlentities($field['field'])));
                }
            }
        }

        return $this->fields;
    }

    /**
     * {@inheritDoc}
     */
    public function getInfo()
    {
        if (isset($this->module->email_footer) && !Toolbox::isEmpty($this->module->email_footer)) {
            return $this->module->email_footer;
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getFormValidationJS($request)
    {
        return $this->module->javascript_validation();
    }

    /**
     * {@inheritDoc}
     */
    public function prepare()
    {
        if (!$this->prepared) {
            $this->module->pre_confirmation_check();
            $this->module->confirmation();
            $this->prepared = true;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getOrderFormContent($request)
    {
        // TODO: move into controller
        ZenCartMock::startMock(Runtime::getContainer()->get('shoppingCart'));
        $this->prepare();
        $button =  $this->module->process_button();
        ZenCartMock::cleanupMock();

        return $button;
    }

    /**
     * {@inheritDoc}
     */
    public function getOrderFormUrl($request)
    {
        $this->prepare();

        return isset($this->module->form_action_url) ? $this->module->form_action_url : Runtime::getContainer()->get('router')->generate('checkout_process');
    }

    /**
     * {@inheritDoc}
     */
    public function getOrderStatus()
    {
        return isset($this->module->order_status) && is_numeric($this->module->order_status) && ($this->module->order_status > 0) ? $this->module->order_status : null;
    }

}
