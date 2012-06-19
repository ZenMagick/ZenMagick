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
namespace zenmagick\apps\store\admin\controller;

use zenmagick\base\Runtime;
use zenmagick\base\events\Event;
use zenmagick\http\view\View;

use zenmagick\apps\store\model\mock\MockAccount;
use zenmagick\apps\store\model\mock\MockOrder;
use zenmagick\apps\store\model\mock\MockProduct;
use zenmagick\apps\store\model\mock\MockCoupon;
use zenmagick\apps\store\model\mock\MockReview;
use zenmagick\apps\store\model\mock\MockEmailMessage;
use zenmagick\apps\store\model\mock\MockGVReceiver;

/**
 * Admin controller for email previews.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class EmailPreviewController extends \ZMController {

    /**
     * {@inheritDoc}
     */
    public function getViewData($request) {
        $templateInfo = array();
        // get a store view to lookup all email templates...
        $view = $this->container->get('storeEmailView');
        foreach ($view->getResourceResolver()->find('views/emails', null, View::TEMPLATE) as $template) {
            $file = basename($template);
            $tokens = explode('.', $file);
            if (3 == count($tokens)) {
                list($template, $format, $type) = $tokens;
                if (!array_key_exists($template, $templateInfo)) {
                    $templateInfo[$template] = array();
                }
                $templateInfo[$template][$format] = $type;
            }
        }

        return array('templateInfo' => $templateInfo);
    }

    /**
     * {@inheritDoc}
     */
    public function processGet($request) {
        if (null != ($template = $request->query->get('template'))) {
            $format = $request->query->get('format');
            $type = $request->query->get('type');
            $messageBuilder = $this->container->get('messageBuilder');

            $context = $this->getInitialContext($request);
            $event = new Event($this, array('template' => $template, 'format' => $format, 'type' => $type, 'request' => $request, 'context' => $context));
            Runtime::getEventDispatcher()->dispatch('email_preview', $event);
            $context = $event->get('context');

            $content = $messageBuilder->createContents($template, 'html'==$format, $request, $context);
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
        $order = new MockOrder();
        return array(
            'office_only_html' => true,
            'office_only_text' => true,
            'newOrderStatus' => 'processing',
            'language' => $request->getSelectedLanguage(),
            'password' => 'THE_NEW_PASSWORD',
            'newPassword' => 'THE_NEW_PASSWORD',
            'currentAccount' => new MockAccount(),
            'order' => $order,
            'currentProduct' => new MockProduct(),
            'currentOrder' => $order,
            'comment' => 'Some comment',
            'adminName' => 'SOME_ADMIN_NAME',
            'textMessage' => 'SOME_MESSAGE',
            'htmlMessage' => 'SOME_MESSAGE',
            'message' => 'THE_MESSAGE',
            'shippingAddress' => $order->getShippingAddress(),
            'billingAddress' => $order->getBillingAddress(),
            'paymentType' => $order->getPaymentType(),
            'couponQueue' => new MockGVReceiver(),
            'gvReceiver' => new MockGVReceiver(),
            'emailMessage' => new MockEmailMessage(),
            'currentReview' => new MockReview(),
            'contactInfo' => new \ZMContactInfo('foo bar', 'foo@bar.com', 'Congrats on your new store!'),
            'currentCoupon' => new MockCoupon()
        );
    }

}
