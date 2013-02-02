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

use ZenMagick\Base\Toolbox;
use ZenMagick\ZenMagickBundle\Controller\DefaultController;

use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Request controller for account newsletter subscription page.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class AccountNotificationsController extends DefaultController
{
    /**
     * {@inheritDoc}
     */
    public function processGet($request)
    {
        return $this->findView(null, array('currentAccount' => $this->getUser()));
    }

    /**
     * {@inheritDoc}
     */
    public function processPost($request)
    {
        $globalProductSubscriber = Toolbox::asBoolean($request->request->get('product_global', false));

        $account = $this->getUser();
        $isGlobalUpdate = false;
        if ($globalProductSubscriber != $account->isGlobalProductSubscriber()) {
            $account->setGlobalProductSubscriber($globalProductSubscriber);
            $this->container->get('accountService')->setGlobalProductSubscriber($account->getId(), $globalProductSubscriber);
            $isGlobalUpdate = true;
        }

        $notifyType = $request->request->get('notify_type');
        $subscribedProducts = $request->request->get('notify', array());
        if (!$isGlobalUpdate && 'set' == $notifyType) {
            // if global update is on, products are not listed in the form,
            // therefore, they would all be removed if updated!
            $account = $this->container->get('accountService')->setSubscribedProductIds($account, $subscribedProducts);
        }

        $this->messageService->success(_zm('Your product subscriptions have been updated.'));

        switch ($notifyType) {
            case 'add':
                $account = $this->container->get('accountService')->addSubscribedProductIds($account, $subscribedProducts);
                return new RedirectResponse($request->headers->get('referer'));
                break;
            case 'remove':
                $account = $this->container->get('accountService')->removeSubscribedProductIds($account, $subscribedProducts);
                return new RedirectResponse($request->headers->get('referer'));
                break;
        }

        return $this->findView('success', array('currentAccount' => $account));
    }

}
