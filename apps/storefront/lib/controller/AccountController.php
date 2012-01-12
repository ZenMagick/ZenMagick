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
namespace zenmagick\apps\store\storefront\controller;

use zenmagick\base\Runtime;

/**
 * Request controller for account page.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class AccountController extends \ZMController {

    /**
     * {@inheritDoc}
     */
    public function processGet($request) {
        $request->getToolbox()->crumbtrail->addCrumb($request->getToolbox()->utils->getTitle());

        // orders are sorted desc...
        $resultSource = new \ZMObjectResultSource('ZMOrder', 'orderService', "getOrdersForAccountId", array($request->getAccountId(), $request->getSession()->getLanguageId()));
        $resultList = Runtime::getContainer()->get('ZMResultList');
        $resultList->setResultSource($resultSource);
        $resultList->setPageNumber($request->getPageIndex());

        $data = array('resultList' => $resultList, 'currentAccount' => $request->getAccount());

        return $this->findView(null, $data);
    }

}
