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
use ZenMagick\StoreBundle\Entity\Coupons\Coupon;

/**
 * Request controller for gv confirmation page.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class GvSendConfirmController extends DefaultController
{
    /**
     * {@inheritDoc}
     */
    public function processGet($request)
    {
        $data = array();
        $data['currentAccount'] = $this->getUser();
        $coupon = new Coupon();
        $coupon->setCode(_zm('THE_COUPON_CODE'));
        $data['currentCoupon'] = $coupon;

        return $this->findView(null, $data);
    }

    /**
     * {@inheritDoc}
     */
    protected function validateFormData($request, $formBean)
    {
        // need specific view to go back to in case of validation errors
        $result = parent::validateFormData($request, $formBean);
        if (null != $result) {
            return $this->findView('edit');
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function processPost($request)
    {
        if (null != $request->request->get('edit')) {
            return $this->findView('edit');
        }

        // the form data
        $gvReceiver = $this->getFormData($request);
        // the sender account
        $account = $this->getUser();
        // current balance
        $balance = $account->getVoucherBalance();
        // coupon amount
        $amount = $gvReceiver->getAmount();

        $settingsService = $this->container->get('settingsService');
        $currentCurrencyCode = $request->getSession()->getCurrencyCode();
        if ($settingsService->get('defaultCurrency') != $currentCurrencyCode) {
            // need to convert amount to default currency as GV values are in default currency
            $currency = $this->container->get('currencyService')->getCurrencyForCode($currentCurrencyCode);
            $amount = $currency->convertFrom($amount);
        }

        // update balance
        $newBalance = $balance - $amount;
        $couponService = $this->container->get('couponService');
        $couponService->setVoucherBalanceForAccountId($account->getId(), $newBalance);

        // create the new voucher
        $couponCode = $couponService->createCouponCode($account->getEmail());
        $coupon = $couponService->createCoupon($couponCode, $amount, Coupon::TYPPE_GV);

        // create coupon tracker
        $couponService->createCouponTracker($coupon, $account, $gvReceiver);

        // create gv_send email
        $context = array('currentAccount' => $account, 'gvReceiver' => $gvReceiver, 'currentCoupon' => $coupon, 'office_only_html' => '', 'office_only_text' => '');

        $message = $this->container->get('messageBuilder')->createMessage('gv_send', true, $request, $context);
        $message->setSubject(sprintf(_zm("A gift from %s"), $account->getFullName()))->setTo($gvReceiver->getEmail())->setFrom($settingsService->get('storeEmail'));
        $this->container->get('mailer')->send($message);

        if ($settingsService->get('isEmailAdminGvSend')) {
            // store copy
            $session = $request->getSession();
            $context = $this->get('macroTool')->officeOnlyEmailFooter($account->getFullName(), $account->getEmail(), $session);
            $context['currentAccount'] = $account;
            $context['gvReceiver'] = $gvReceiver;
            $context['currentCoupon'] = $coupon;

            $message = $this->container->get('messageBuilder')->createMessage('gv_send', false, $request, $context);
            $message->setSubject(sprintf(_zm("[GIFT CERTIFICATE] A gift from %s"), $account->getFullName()))->setFrom($settingsService->get('storeEmail'));
            foreach ($settingsService->get('emailAdminGvSend') as $email => $name) {
                $message->addTo($email, $name);
            }
            $this->container->get('mailer')->send($message);
        }

        $this->messageService->success(_zm("Gift Certificate successfully send!"));

        return $this->findView('success');
    }

}
