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

/**
 * Request controller for unsubscribe page.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class UnsubscribeController extends DefaultController
{
    /**
     * {@inheritDoc}
     */
    public function processGet($request)
    {
        return $this->findView();
    }

    /**
     * {@inheritDoc}
     */
    public function processPost($request)
    {
        if (!$this->container->get('settingsService')->get('isAllowAnonymousUnsubscribe')) {
            $this->messageService->error(_zm('In order to unsubscribe you need to login first.'));

            return $this->findView();
        }

        if (!$this->validate($request, 'unsubscribe')) {
            return $this->findView();
        }

        $emailAddress = $request->request->get('email_address');
        $account = $this->get('accountService')->getAccountForEmailAddress($emailAddress);

        if (null == $account) {
            $this->messageService->error(_zm('Email address not found.'));
        } else {
            if ($account->isNewsletterSubscriber()) {
                // unsubscribe
                $account->setNewsletterSubscriber(false);
                $this->container->get('accountService')->updateAccount($account);
                $this->messageService->success(sprintf(_zm('Email %s unsubscribed.'), $emailAddress));
            } else {
                $this->messageService->warn(_zm('You are already unsubscribed.'));
            }
        }

        return $this->findView();
    }

}
