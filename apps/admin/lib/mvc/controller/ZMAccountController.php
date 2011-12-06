<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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
 * Admin controller for account page.
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package zenmagick.store.admin.mvc.controller
 */
class ZMAccountController extends ZMController {

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
        $priceGroups = $this->container->get('groupPricingService')->getPriceGroups();
        return array('priceGroups' => array_merge(array(new ZMIdNamePair(0, _zm('-- none --'))), $priceGroups));
    }

    /**
     * {@inheritDoc}
     */
    public function processGet($request) {
        $accountId = $request->getParameter('accountId');
        if (null == ($account = $this->container->get('accountService')->getAccountForId($accountId))) {
            $this->messageService->error(sprintf(_zm('Account for account id %s not found'), $accountId));
            return $this->findView(null, array('accountId' => $accountId));
        }

        return $this->findView(null, array('account' => $account));
    }

}
