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

use ZenMagick\Base\Beans;
use ZenMagick\ZenMagickBundle\Controller\DefaultController;

/**
 * Request controller for account page.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class AccountController extends DefaultController
{
    /**
     * {@inheritDoc}
     */
    public function processGet($request)
    {
        // orders are sorted desc...
        $account = $this->getUser();
        $resultSource = new \ZMObjectResultSource('ZenMagick\StoreBundle\Entity\Order\Order', 'orderService', "getOrdersForAccountId", array($account->getId(), $request->getSession()->getLanguageId()));
        $resultList = Beans::getBean('ZMResultList');
        $resultList->setResultSource($resultSource);
        $resultList->setPageNumber($request->query->getInt('page'));

        $data = array('resultList' => $resultList, 'currentAccount' => $account);

        return $this->findView(null, $data);
    }

}
