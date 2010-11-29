<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2010 zenmagick.org
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
 * Admin controller for email previews.
 *
 * @author DerManoMann
 * @package zenmagick.store.admin.mvc.controller
 */
class ZMEmailPreviewController extends ZMController {

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
        $templateInfo = array();
        // get a store view to lookup all email templates...
        $view = ZMUrlManager::instance()->findView(null, 'store-view');
        foreach ($view->find($request, 'views/emails') as $template) {
            $file = basename($template);
            $tokens = explode('.', $file);
            if (3 == count($tokens)) {
                if (!array_key_exists($tokens[0], $templateInfo)) {
                    $templateInfo[$tokens[0]] = array();
                }
                $templateInfo[$tokens[0]][] = $tokens[1];
            }
        }

        return array('templateInfo' => $templateInfo);
    }

    /**
     * {@inheritDoc}
     */
    public function processGet($request) {
        if (null != ($template = $request->getParameter('template'))) {
            $format = $request->getParameter('format', 'text');
            $emails = ZMEmails::instance();
            $emails->setViewViewId('store-view');

            $context = $this->getInitialContext($request);
            $args = ZMEvents::instance()->fireEvent($this, 'email_preview', array('template' => $template, 'format' => $format, 'context' => $context, 'request' => $request));
            $context = $args['context'];

            $content = $emails->createContents($template, 'html'==$format, $request, $context);
            if ('text' == $format) {
                $this->setContentType('text/plain');
            }
            echo $content;
            return null;
        }

        return $this->findView();
    }

    /**
     * Set up an initial context for emails with everything we might need.
     *
     * @param ZMRequest request The current request.
     * @return array The context map.
     */
    protected function getInitialContext($request) {
        $order = new ZMDemoOrder();
        return array(
            'language' => $request->getSelectedLanguage(),
            'password' => 'THE_NEW_PASSWORD',
            'currentAccount' => new ZMDemoAccount(),
            'order' => $order,
            'currentProduct' => new ZMDemoProduct(),
            'currentOrder' => $order,
            'comment' => 'Some comment',
            'adminName' => 'SOME_ADMIN_NAME',
            'textMessage' => 'SOME_MESSAGE',
            'htmlMessage' => 'SOME_MESSAGE',
            'shippingAddress' => $order->getShippingAddress(),
            'billingAddress' => $order->getBillingAddress(),
            'paymentType' => $order->getPaymentType(),
            'couponQueue' => new ZMDemoCouponQueue(),
            'gvReceiver' => new ZMDemoGVReceiver(),
            'emailMessage' => new ZMDemoEmailMessage(),
            'currentReview' => new ZMDemoReview(),
            'contactInfo' => new ZMContactInfo('foo bar', 'foo@bar.com', 'Congrats on your new store!'),
            'currentCoupon' => new ZMDemoCoupon()
        );
    }

}
