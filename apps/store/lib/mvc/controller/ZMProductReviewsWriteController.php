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
 * Request controller for write review page.
 *
 * @author DerManoMann
 * @package zenmagick.store.sf.mvc.controller
 */
class ZMProductReviewsWriteController extends ZMController {

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
    public function getViewData($request) {
        $product = $this->getProduct($request);
        return array('currentProduct' => $product, 'currentAccount' => $request->getAccount());
    }

    /**
     * {@inheritDoc}
     */
    public function preProcess($request) {
        $product = $this->getProduct($request);
        $request->getToolbox()->crumbtrail->addCategoryPath($request->getCategoryPathArray());
        $request->getToolbox()->crumbtrail->addManufacturer($request->getManufacturerId());
        $request->getToolbox()->crumbtrail->addProduct($product->getId());
        $request->getToolbox()->crumbtrail->addCrumb("Reviews");
    }

    /**
     * {@inheritDoc}
     */
    public function processGet($request) {
        if (null == $this->getProduct($request)) {
            return $this->findView('product_not_found');
        }
        return $this->findView();
    }

    /**
     * {@inheritDoc}
     */
    public function processPost($request) {
        if (null == $this->getProduct($request)) {
            return $this->findView('product_not_found');
        }

        $review = $this->getFormData($request);
        $account = $request->getAccount();
        $session = $request->getSession();
        ZMReviews::instance()->createReview($review, $account, $session->getLanguageId());

        $product = ZMProducts::instance()->getProductForId($review->getProductId());

        // account email
        if (ZMSettings::get('isApproveReviews') && ZMSettings::get('isEmailAdminReview')) {
            $subject = sprintf(_zm("Product Review Pending Approval: %s"), $product->getName());
            $context = $request->getToolbox()->macro->officeOnlyEmailFooter($account->getFullName(), $account->getEmail(), $session);
            $context['currentAccount'] = $account;
            $context['currentReview'] = $review;
            $context['currentProduct'] = $product;
            zm_mail($subject, 'review', $context, ZMSettings::get('emailAdminReview'));
        }

        ZMMessages::instance()->success(_zm("Thank you for your submission"));
        return $this->findView('success', array(), array('parameter' => 'products_id='.$product->getId()));
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

}
