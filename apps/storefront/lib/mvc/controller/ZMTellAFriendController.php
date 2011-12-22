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

use zenmagick\base\Beans;
use zenmagick\base\Runtime;

/**
 * Request controller for tell a friend form.
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package zenmagick.store.sf.mvc.controller
 */
class ZMTellAFriendController extends ZMController {
    private $product_;
    private $viewData_;


    /**
     * {@inheritDoc}
     */
    public function preProcess($request) {
        $languageId = $request->getSession()->getLanguageId();
        $productService = $this->container->get('productService');
        if ($request->getProductId()) {
            $this->product_ = $productService->getProductForId($request->getProductId(), $languageId);
        } else if ($request->getModel()) {
            $this->product_ = $productService->getProductForModel($request->getModel(), $languageId);
        }
        if (null != $this->product_) {
            $this->viewData_['currentProduct'] = $this->product_;
            $this->handleCrumbtrail($this->product_, $request);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function processGet($request) {
        if (null == $this->product_) {
            return $this->findView('product_not_found', $this->viewData_);
        }

        $account = $request->getAccount();
        $emailMessage = $this->getFormData($request);
        if (null != $account) {
            $emailMessage->setFromEmail($account->getEmail());
            $emailMessage->setFromName($account->getFullName());
        }

        return $this->findView(null, $this->viewData_);
    }

    /**
     * {@inheritDoc}
     */
    public function processPost($request) {
        if (null == $this->product_) {
            return $this->findView('product_not_found', $this->viewData_);
        }

        $emailMessage = $this->getFormData($request);

        $context = array('emailMessage' => $emailMessage, 'currentProduct' => $this->product_, 'office_only_html' => '', 'office_only_text' => '');
        $subject = sprintf(_zm("Your friend %s has recommended this great product from %s"), $emailMessage->getFromName(), Runtime::getSettings()->get('storeName'));

        $message = $this->container->get('messageBuilder')->createMessage('tell_a_friend', true, $request, $context);
        $message->setSubject($subject)->setTo($emailMessage->getToEmail(), $emailMessage->getToName())->setFrom(Runtime::getSettings()->get('storeEmail'));
        $this->container->get('mailer')->send($message);

        if (Runtime::getSettings()->get('isEmailAdminTellAFriend')) {
            // store copy
            $session = $request->getSession();
            $context = $request->getToolbox()->macro->officeOnlyEmailFooter($emailMessage->getFromName(), $emailMessage->getFromEmail(), $session);
            $context['emailMessage'] = $emailMessage;
            $context['currentProduct'] = $this->product_;

            $message = $this->container->get('messageBuilder')->createMessage('tell_a_friend', false, $request, $context);
            $message->setSubject(sprintf(_zm('[TELL A FRIEND] %s'), $subject))->setTo(Runtime::getSettings()->get('emailAdminTellAFriend'))->setFrom(Runtime::getSettings()->get('storeEmail'));
            $this->container->get('mailer')->send($message);
        }

        $this->messageService->success(_zm("Message send successfully"));
        $emailMessage = Beans::getBean("ZMEmailMessage");

        $data = array_merge($this->viewData_, array('emailMessage' => $emailMessage));
        return $this->findView('success', $data, array('parameter' => 'products_id='.$this->product_->getId()));
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
        $request->getToolbox()->crumbtrail->addCrumb("Tell A Friend");
    }

}
