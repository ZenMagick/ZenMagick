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
namespace ZenMagick\plugins\howDidYouHear;

use ZenMagick\Base\Plugins\Plugin;
use ZenMagick\Base\Runtime;
use ZenMagick\Base\Toolbox;
use ZenMagick\Base\ZMObject;
use ZenMagick\Http\View\TemplateView;

define('ID_SOURCE_OTHER', 9999);

/**
 * Plugin to add and handle a <em>How did you hear about us</em> drop down in the create account page.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class HowDidYouHearPlugin extends Plugin {

    /**
     * {@inheritDoc}
     */
    public function remove($keepSettings=false) {
        $conn = \ZMRuntime::getDatabase();
        $sm = $conn->getSchemaManager();
        $sm->dropTable($conn->getPrefix().'sources');
        $sm->dropTable($conn->getPrefix().'sources_other');
        //parent::remove($keepSettings);
    }

    /**
     * {@inheritDoc}
     */
    public function init() {
        parent::init();

        $info = array('column' => 'customers_info_source_id', 'type' => 'integer');
        \ZMRuntime::getDatabase()->getMapper()->addPropertyForTable('customers_info', 'sourceId', $info);
    }

    /**
     * Check whether we need to handle this request.
     *
     * @param string requestId The current request id.
     * @return boolean <code>true</code> if we need to handle this request.
     */
    protected function handleRequest($requestId) {
        return in_array($requestId, array('create_account', 'checkout_shipping_address'));
    }

    /**
     * Add validation rules.
     */
    public function onContainerReady($event) {
        $request = $event->get('request');
        if ($this->handleRequest($request->getRequestId())) {
            if ($this->isRequired()) {
                // add validation rules
                $rules = array(
                    array("ZMRequiredRule", 'sourceId', 'Please select/provide the source where you first heard about us.'),
                    array("ZenMagick\\\plugins\\\howDidYouHear\\validation\\rules\\SourceOtherRule", 'sourceOther', 'Please provide a description about where you first heard about us.')
                );
                $this->container->get('zmvalidator')->addRules('registration', $rules);
                $this->container->get('zmvalidator')->addRules('shippingAddress', $rules);
            }
        }
    }

    /**
     * Add custom view data.
     */
    public function onViewStart($event) {
        $request = $event->get('request');
        if ($this->handleRequest($request->getRequestId())) {
            // create sources list
            $howDidYouHearSources = array();
            $source = new ZMObject();
            $source->setId('');
            $source->setName(_zm('Please select a source'));
            $howDidYouHearSources[] = $source;

            $sql = "SELECT sources_id, sources_name
                    FROM %table.sources%
                    ORDER BY sources_name";
            foreach (\ZMRuntime::getDatabase()->fetchAll($sql, array()) as $result) {
                $source = new ZMObject();
                $source->setId($result['sources_id']);
                $source->setName($result['sources_name']);
                $howDidYouHearSources[] = $source;
            }
            if ($this->isDisplayOther()) {
                $source = new ZMObject();
                $source->setId(ID_SOURCE_OTHER);
                $source->setName(_zm('Other - (please specifiy)'));
                $howDidYouHearSources[] = $source;
            }

            // create reliable form reference
            if (null != ($view = $event->get('view')) && $view instanceof TemplateView) {
                $view->setVariable('howDidYouHearSources', $howDidYouHearSources);
                if (null != ($registration = $view->getVariable('registration'))) {
                    $view->setVariable('howDidYouHearForm', $registration);
                } else if (null != ($shippingAddress = $view->getVariable('shippingAddress'))) {
                    // if we have an address we should have got the source as well...
                    $account = $request->getAccount();
                    $addressList = $this->container->get('addressService')->getAddressesForAccountId($account->getId());
                    if ($this->isEnableOnGuestCheckout() && \ZMAccount::GUEST == $account->getType() && 0 == count($addressList)) {
                        $view->setVariable('howDidYouHearForm', $shippingAddress);
                    }
                }
            }
        }
    }

    /**
     * Custom create account processing
     */
    public function onCreateAccount($event) {
        $account = $event->get('account');
        if (ID_SOURCE_OTHER == $account->getSourceId() && \ZMAccount::GUEST != $account->getType()) {
            // need to store sourceOther
            $sql = "INSERT INTO %table.sources_other%
                    VALUES (:customers_id, :sources_other_name)";
            \ZMRuntime::getDatabase()->updateObj($sql, array('customers_id' => $account->getId(), 'sources_other_name' => $account->getSourceOther()), 'sources_other');
        }
    }

    /**
     * Check if displayOther is set.
     *
     * @return boolean <code>true</code> if displayOther is set.
     */
    public function isDisplayOther() {
        return Toolbox::asBoolean($this->get('displayOther'));
    }

    /**
     * Check if this is required.
     *
     * @return boolean <code>true</code> if an answer is required.
     */
    public function isRequired() {
        return Toolbox::asBoolean($this->get('requireSource'));
    }

    /**
     * Check if guest checkout should be handled as well.
     *
     * @return boolean <code>true</code> if enabled.
     */
    public function isEnableOnGuestCheckout() {
        return Toolbox::asBoolean($this->get('enableOnGuestCheckout'));
    }

}
