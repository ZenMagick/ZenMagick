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
use ZenMagick\StoreBundle\Entity\Account;

/**
 * Request controller for guest history lookup.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class GuestHistoryController extends DefaultController
{
    /**
     * {@inheritDoc}
     */
    public function processPost($request)
    {
        if (!$this->validate($request, 'guest_history')) {
            return $this->findView();
        }
        $translator = $this->get('translator');
        $orderId = $request->request->getInt('orderId', 0);
        $email = $request->getParameter('email', 0);

        // default
        $account = null;
        // find order first
        $order = $this->container->get('orderService')->getOrderForId($orderId, $request->getSession()->getLanguageId());

        if (null != $order) {
            $accountId = $order->getAccountId();
            if (null != $accountId) {
                $account = $this->container->get('accountService')->getAccountForId($accountId);
            }
        }

        if (null != $account && null != $order && Account::GUEST == $account->getType() && $account->getEmail() == $email) {
            return $this->findView('success', array('currentOrder' => $order));
        } else {
            $this->get('session.flash_bag')->warn($translator->trans('No order information found'));

            return $this->findView();
        }
    }

}
