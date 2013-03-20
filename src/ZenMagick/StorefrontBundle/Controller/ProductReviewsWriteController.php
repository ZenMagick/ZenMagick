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
namespace ZenMagick\StorefrontBundle\Controller;

use ZenMagick\ZenMagickBundle\Controller\DefaultController;

use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Request controller for write review page.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class ProductReviewsWriteController extends DefaultController
{
    /**
     * {@inheritDoc}
     */
    public function getViewData($request)
    {
        $product = $this->getProduct($request);

        return array('currentProduct' => $product, 'currentAccount' => $this->getUser());
    }

    /**
     * {@inheritDoc}
     */
    public function processGet($request)
    {
        if (null == $this->getProduct($request)) {
            return $this->findView('product_not_found');
        }

        return $this->findView();
    }

    /**
     * {@inheritDoc}
     */
    public function processPost($request)
    {
        if (null == $this->getProduct($request)) {
            return $this->findView('product_not_found');
        }

        $settingsService = $this->container->get('settingsService');
        $review = $this->getFormData($request);
        $account = $this->getUser();
        $this->container->get('reviewService')->createReview($review, $account, $review->getLanguageId());

        $product = $this->container->get('productService')->getProductForId($review->getProductId(), $review->getLanguageId());

        // account email
        if ($settingsService->get('isApproveReviews') && $settingsService->get('isEmailAdminReview')) {
            $subject = sprintf(_zm("Product Review Pending Approval: %s"), $product->getName());
            $context = $this->get('macroTool')->officeOnlyEmailFooter($account->getFullName(), $account->getEmail(), $request->getSession());
            $context['currentAccount'] = $account;
            $context['currentReview'] = $review;
            $context['currentProduct'] = $product;

            $message = $this->container->get('messageBuilder')->createMessage('review', true, $request, $context);
            $message->setSubject($subject)->setFrom($settingsService->get('storeEmail'));
            foreach ($settingsService->get('emailAdminReview') as $email => $name) {
                $message->addTo($email, $name);
            }
            $this->container->get('mailer')->send($message);
        }

        $args = array('request' => $request, 'controller' => $this, 'account' => $account, 'review' => $review, 'product' => $product);
        $this->container->get('event_dispatcher')->dispatch('review_submitted', new GenericEvent($this, $args));

        $this->get('session.flash_bag')->success(_zm("Thank you for your submission"));

        return $this->findView('success', array(), array('parameter' => 'productId='.$product->getId()));
    }

    /**
     * Get the product.
     *
     * @param ZenMagick\Http\Request request The current request.
     * @return ZenMagick\StoreBundle\Entity\Catalog\Product The product or <code>null</code>.
     */
    protected function getProduct($request)
    {
        $product = null;
        $productService = $this->container->get('productService');
        $languageId = $request->getSession()->getLanguageId();
        if ($request->get('productId')) {
            $product = $productService->getProductForId($request->get('productId'), $languageId);
        } elseif ($request->get('model')) {
            $product = $productService->getProductForModel($request->get('model'), $languageId);
        }

        return $product;
    }

}
