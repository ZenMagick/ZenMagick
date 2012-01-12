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


/**
 * Request controller for account history info page.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class AccountHistoryInfoController extends \ZMController {

    /**
     * {@inheritDoc}
     */
    public function processGet($request) {
        $request->getToolbox()->crumbtrail->addCrumb("Account", $request->url('account', '', true));
        $request->getToolbox()->crumbtrail->addCrumb($request->getToolbox()->utils->getTitle(), $request->url('account_history', '', true));
        $request->getToolbox()->crumbtrail->addCrumb("Order # ".$request->getOrderId());

        $order = $this->container->get('orderService')->getOrderForId($request->getOrderId(), $request->getSession()->getLanguageId());
        $account = $request->getAccount();
        if ($account->getId() != $order->getAccountId()) {
            $this->messageService->error(_zm('Order not found'));
            return $this->findView('error');
        }

        return $this->findView(null, array('currentOrder' => $order));
    }

}
