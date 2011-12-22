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

use zenmagick\base\Runtime;

/**
 * Request controller for write review page.
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package zenmagick.store.sf.mvc.controller
 */
class ZMProductReviewsWriteController extends ZMController {

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
        $this->container->get('reviewService')->createReview($review, $account, $review->getLanguageId());

        $product = $this->container->get('productService')->getProductForId($review->getProductId(), $review->getLanguageId());

        // account email
        if (Runtime::getSettings()->get('isApproveReviews') && Runtime::getSettings()->get('isEmailAdminReview')) {
            $subject = sprintf(_zm("Product Review Pending Approval: %s"), $product->getName());
            $context = $request->getToolbox()->macro->officeOnlyEmailFooter($account->getFullName(), $account->getEmail(), $request->getSession());
            $context['currentAccount'] = $account;
            $context['currentReview'] = $review;
            $context['currentProduct'] = $product;

            $message = $this->container->get('messageBuilder')->createMessage('review', true, $request, $context);
            $message->setSubject($subject)->setTo(Runtime::getSettings()->get('emailAdminReview'))->setFrom(Runtime::getSettings()->get('storeEmail'));
            $this->container->get('mailer')->send($message);
        }

        $this->messageService->success(_zm("Thank you for your submission"));
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
        $productService = $this->container->get('productService');
        $languageId = $request->getSession()->getLanguageId();
        if ($request->getProductId()) {
            $product = $productService->getProductForId($request->getProductId(), $languageId);
        } else if ($request->getModel()) {
            $product = $productService->getProductForModel($request->getModel(), $languageId);
        }
        return $product;
    }

}
