<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006,2007 ZenMagick
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
 * @package net.radebatz.zenmagick.rp.uip.controller
 * @version $Id$
 */
class ZMProductReviewsWriteController extends ZMController {

    /**
     * Default c'tor.
     */
    function ZMProductReviewsWriteController() {
        parent::__construct();
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMProductReviewsWriteController();
    }

    /**
     * Default d'tor.
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
    global $zm_request;

        $product = $this->_getProduct();
        if (null == $product) {
            return $this->findView('error');
        }

        $this->exportGlobal("zm_product", $product);
        $this->exportGlobal("zm_account", $zm_request->getAccount());

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
    global $zm_runtime, $zm_request, $zm_messages, $zm_accounts, $zm_reviews;

        $review =& $this->create("Review");
        $review->populate();

        $product = $this->_getProduct();
        if (null == $product) {
            return $this->findView('error');
        }

        if (!$this->validate('review')) {
            $this->_handleCrumbtrail($product);
            $this->exportGlobal("zm_product", $product);
            $this->exportGlobal("zm_account", $zm_request->getAccount());
            $this->exportGlobal("zm_review", $review);
            return $this->findView();
        }

        $account = $zm_request->getAccount();
        $zm_reviews->createReview($review, $account, $zm_runtime->getLanguageId());

        // account email
        if (zm_setting('isApproveReviews') && zm_setting('isEmailAdminReview')) {
            $subject = zm_l10n_get("Product Review Pending Approval: %s", $product->getName());
            $context = zm_email_copy_context($account->getFullName(), $account->getEmail(), new ZMSession());
            $context['zm_account'] = $account;
            $context['zm_review'] = $review;
            $context['zm_product'] = $product;
            zm_mail($subject, 'review', $context, zm_setting('emailAdminReview'));
        }

        $zm_messages->success("Thank you for your submission");
        return $this->findView('success', 'products_id='.$product->getId());
    }

    /**
     * Get the product.
     *
     * @return ZMProduct The product or <code>null</code>.
     */
    function _getProduct() {
    global $zm_request, $zm_products;

        $product = null;
        if ($zm_request->getProductId()) {
            $product = $zm_products->getProductForId($zm_request->getProductId());
        } else if ($zm_request->getModel()) {
            $product = $zm_products->getProductForModel($zm_request->getModel());
        }
        return $product;
    }

    /**
     * Handle crumbtrail.
     *
     * @param ZMProduct product The current product.
     */
    function _handleCrumbtrail($product) {
    global $zm_request, $zm_crumbtrail;

        $zm_crumbtrail->addCategoryPath($zm_request->getCategoryPathArray());
        $zm_crumbtrail->addManufacturer($zm_request->getManufacturerId());
        $zm_crumbtrail->addProduct($product->getId());
        $zm_crumbtrail->addCrumb("Reviews");
    }

}

?>
