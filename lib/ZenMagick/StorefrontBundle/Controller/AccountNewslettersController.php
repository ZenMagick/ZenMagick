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

/**
 * Request controller for account newsletter subscription page.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class AccountNewslettersController extends DefaultController
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
        $newsletterSubscriber = Toolbox::asBoolean($request->request->get('newsletter_general', false));

        $account = $this->getUser();
        if ($newsletterSubscriber != $account->isNewsletterSubscriber()) {
            $account->setNewsletterSubscriber($newsletterSubscriber);
            $this->container->get('accountService')->updateAccount($account);
        }

        $this->get('session.flash_bag')->success(_zm('Your newsletter subscription has been updated.'));

        return $this->findView('success', array('currentAccount' => $account));
    }

}
