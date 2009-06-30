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
 * Request controller for tell a friend form.
 *
 * @author DerManoMann
 * @package org.zenmagick.store.mvc.controller
 * @version $Id: ZMTellAFriendController.php 2350 2009-06-29 04:22:59Z dermanomann $
 */
class ZMTellAFriendController extends ZMController {
    private $product_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->product_ = null;
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
    public function handleRequest() { 
        if (ZMRequest::getProductId()) {
            $this->product_ = ZMProducts::instance()->getProductForId(ZMRequest::getProductId());
        } else if (ZMRequest::getModel()) {
            $this->product_ = ZMProducts::instance()->getProductForModel(ZMRequest::getModel());
        }
        $this->exportGlobal("zm_product", $this->product_);
        $this->handleCrumbtrail($this->product_);
    }

    /**
     * {@inheritDoc}
     */
    public function processGet() {
        if (null == $this->product_) {
            return $this->findView('error');
        }

        $account = ZMRequest::getAccount();
        $emailMessage = $this->getFormBean();
        if (null != $account) {
            $emailMessage->setFromEmail($account->getEmail());
            $emailMessage->setFromName($account->getFullName());
        }

        return $this->findView();
    }

    /**
     * {@inheritDoc}
     */
    public function processPost() {
        if (null == $this->product_) {
            return $this->findView('error');
        }

        $emailMessage = $this->getFormBean();

        $context = array('zm_emailMessage' => $emailMessage, 'zm_product' => $this->product_, 'office_only_html' => '', 'office_only_text' => '');
        $subject = zm_l10n_get("Your friend %s has recommended this great product from %s", $emailMessage->getFromName(), ZMSettings::get('storeName'));
        zm_mail($subject, 'tell_a_friend', $context, $emailMessage->getToEmail(), $emailMessage->getToName());
        if (ZMSettings::get('isEmailAdminTellAFriend')) {
            // store copy
            $session = ZMRequest::getSession();
            $context = ZMToolbox::instance()->macro->officeOnlyEmailFooter($emailMessage->getFromName(), $emailMessage->getFromEmail(), $session);
            $context['zm_emailMessage'] = $emailMessage;
            $context['zm_product'] = $this->product_;
            zm_mail("[TELL A FRIEND] ".$subject, 'tell_a_friend', $context, ZMSettings::get('emailAdminTellAFriend'));
        }

        ZMMessages::instance()->success(zm_l10n_get("Message send successfully"));
        $emailMessage = ZMLoader::make("EmailMessage");
        $this->exportGlobal("zm_emailMessage", $emailMessage);

        return $this->findView('success', array(), array('parameter' => 'products_id='.$this->product_->getId()));
    }

    /**
     * Handle crumbtrail.
     *
     * @param ZMProduct product The current product.
     */
    protected function handleCrumbtrail($product) {
        ZMCrumbtrail::instance()->addCategoryPath(ZMRequest::getCategoryPathArray());
        ZMCrumbtrail::instance()->addManufacturer(ZMRequest::getManufacturerId());
        ZMCrumbtrail::instance()->addProduct($product->getId());
        ZMCrumbtrail::instance()->addCrumb("Tell A Friend");
    }

}

?>
