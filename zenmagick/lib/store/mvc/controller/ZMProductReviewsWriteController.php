<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 zenmagick.org
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
 * @author DerManoMann
 * @package org.zenmagick.store.mvc.controller
 * @version $Id$
 */
class ZMProductReviewsWriteController extends ZMController {
    private $viewData_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->viewData_ = array();
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
    public function preProcess($request) {
        $product = $this->getProduct($request);
        $this->viewData_['currentProduct'] = $product;
        $this->viewData_['currentAccount'] = $request->getAccount();
        $this->handleCrumbtrail($this->product_, $request);
    }

    /**
     * {@inheritDoc}
     */
    public function processGet($request) {
        if (null == $this->getProduct($request)) {
            return $this->findView('product_not_found', $this->viewData_);
        }
        return $this->findView(null, $this->viewData_);
    }

    /**
     * {@inheritDoc}
     */
    public function processPost($request) {
        if (null == $this->getProduct($request)) {
            return $this->findView('product_not_found', $this->viewData_);
        }

        $review = $this->getFormData($request);
        $account = $request->getAccount();
        $session = $request->getSession();
        ZMReviews::instance()->createReview($review, $account, $session->getLanguageId());

        $product = ZMProducts::instance()->getProductForId($review->getProductId());

        // account email
        if (ZMSettings::get('isApproveReviews') && ZMSettings::get('isEmailAdminReview')) {
            $subject = zm_l10n_get("Product Review Pending Approval: %s", $product->getName());
            $context = $request->getToolbox()->macro->officeOnlyEmailFooter($account->getFullName(), $account->getEmail(), $session);
            $context['currentAccount'] = $account;
            $context['currentReview'] = $review;
            $context['currentProduct'] = $product;
            zm_mail($subject, 'review', $context, ZMSettings::get('emailAdminReview'));
        }

        ZMMessages::instance()->success(zm_l10n_get("Thank you for your submission"));
        return $this->findView('success', $this->viewData_, array('parameter' => 'products_id='.$product->getId()));
    }

    /**
     * Get the product.
     *
     * @param ZMRequest request The current request.
     * @return ZMProduct The product or <code>null</code>.
     */
    protected function getProduct($request) {
        $product = null;
        if ($request->getProductId()) {
            $product = ZMProducts::instance()->getProductForId($request->getProductId());
        } else if ($request->getModel()) {
            $product = ZMProducts::instance()->getProductForModel($request->getModel());
        }
        return $product;
    }

    /**
     * Handle crumbtrail.
     *
     * @param ZMProduct product The current product.
     * @param ZMRequest request The current request.
     */
    protected function handleCrumbtrail($product, $request) {
        $request->getToolbox()->crumbtrail->addCategoryPath($request->getCategoryPathArray());
        $request->getToolbox()->crumbtrail->addManufacturer($request->getManufacturerId());
        $request->getToolbox()->crumbtrail->addProduct($product->getId());
        $request->getToolbox()->crumbtrail->addCrumb("Reviews");
    }

}
