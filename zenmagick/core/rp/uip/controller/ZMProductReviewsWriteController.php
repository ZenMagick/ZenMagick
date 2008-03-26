<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2008 ZenMagick
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
 * Request controller for write review page.
 *
 * @author mano
 * @package org.zenmagick.rp.uip.controller
 * @version $Id$
 */
class ZMProductReviewsWriteController extends ZMController {

    /**
     * Create new instance.
     */
    function ZMProductReviewsWriteController() {
        parent::__construct();
    }

    /**
     * Create new instance.
     */
    function __construct() {
        $this->ZMProductReviewsWriteController();
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Process a HTTP GET request.
     * 
     * @return ZMView A <code>ZMView</code> that handles presentation or <code>null</code>
     * if the controller generates the contents itself.
     */
    function processGet() {
        $product = $this->_getProduct();
        if (null == $product) {
            return $this->findView('error');
        }

        $this->exportGlobal("zm_product", $product);
        $this->exportGlobal("zm_account", ZMRequest::getAccount());

        $this->_handleCrumbtrail($product);

        return $this->findView();
    }

    /**
     * Process a HTTP POST request.
     * 
     * @return ZMView A <code>ZMView</code> that handles presentation or <code>null</code>
     * if the controller generates the contents itself.
     */
    function processPost() {
        $review = $this->create("Review");
        $review->populate();

        $product = $this->_getProduct();
        if (null == $product) {
            return $this->findView('error');
        }

        if (!$this->validate('review')) {
            $this->_handleCrumbtrail($product);
            $this->exportGlobal("zm_product", $product);
            $this->exportGlobal("zm_account", ZMRequest::getAccount());
            $this->exportGlobal("zm_review", $review);
            return $this->findView();
        }

        $account = ZMRequest::getAccount();
        $session = ZMRequest::getSession();
        ZMReviews::instance()->createReview($review, $account, $session->getLanguageId());

        // account email
        if (ZMSettings::get('isApproveReviews') && ZMSettings::get('isEmailAdminReview')) {
            $subject = zm_l10n_get("Product Review Pending Approval: %s", $product->getName());
            $session = ZMRequest::getSession();
            $context = zm_email_copy_context($account->getFullName(), $account->getEmail(), $session);
            $context['zm_account'] = $account;
            $context['zm_review'] = $review;
            $context['zm_product'] = $product;
            zm_mail($subject, 'review', $context, ZMSettings::get('emailAdminReview'));
        }

        ZMMessages::instance()->success(zm_l10n_get("Thank you for your submission"));
        return $this->findView('success', array('parameter' => 'products_id='.$product->getId()));
    }

    /**
     * Get the product.
     *
     * @return ZMProduct The product or <code>null</code>.
     */
    function _getProduct() {
        $product = null;
        if (ZMRequest::getProductId()) {
            $product = ZMProducts::instance()->getProductForId(ZMRequest::getProductId());
        } else if (ZMRequest::getModel()) {
            $product = ZMProducts::instance()->getProductForModel(ZMRequest::getModel());
        }
        return $product;
    }

    /**
     * Handle crumbtrail.
     *
     * @param ZMProduct product The current product.
     */
    function _handleCrumbtrail($product) {
        ZMCrumbtrail::instance()->addCategoryPath(ZMRequest::getCategoryPathArray());
        ZMCrumbtrail::instance()->addManufacturer(ZMRequest::getManufacturerId());
        ZMCrumbtrail::instance()->addProduct($product->getId());
        ZMCrumbtrail::instance()->addCrumb("Reviews");
    }

}

?>
