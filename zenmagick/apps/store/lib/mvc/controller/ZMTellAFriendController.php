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
 * Request controller for tell a friend form.
 *
 * @author DerManoMann
 * @package zenmagick.store.sf.mvc.controller
 */
class ZMTellAFriendController extends ZMController {
    private $product_;
    private $viewData_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->product_ = null;
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
        if ($request->getProductId()) {
            $this->product_ = ZMProducts::instance()->getProductForId($request->getProductId());
        } else if ($request->getModel()) {
            $this->product_ = ZMProducts::instance()->getProductForModel($request->getModel());
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
        $subject = sprintf(_zm("Your friend %s has recommended this great product from %s"), $emailMessage->getFromName(), ZMSettings::get('storeName'));
        zm_mail($subject, 'tell_a_friend', $context, $emailMessage->getToEmail(), $emailMessage->getToName());
        if (ZMSettings::get('isEmailAdminTellAFriend')) {
            // store copy
            $session = $request->getSession();
            $context = $request->getToolbox()->macro->officeOnlyEmailFooter($emailMessage->getFromName(), $emailMessage->getFromEmail(), $session);
            $context['emailMessage'] = $emailMessage;
            $context['currentProduct'] = $this->product_;
            zm_mail("[TELL A FRIEND] ".$subject, 'tell_a_friend', $context, ZMSettings::get('emailAdminTellAFriend'));
        }

        ZMMessages::instance()->success(_zm("Message send successfully"));
        $emailMessage = ZMLoader::make("EmailMessage");

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
