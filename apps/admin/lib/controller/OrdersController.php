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

/**
 * Admin controller for orders page.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class OrdersController extends \ZMController {

    /**
     * {@inheritDoc}
     */
    public function processGet($request) {
        $orderStatusId = $request->getParameter('orderStatusId');
        //TODO: languageId
        $languageId = 1;

        // get the corresponding orderStatus
        $orderStatusList = $this->container->get('orderService')->getOrderStatusList($languageId);
        $orderStatus = null;
        foreach ($orderStatusList as $tmp) {
            if ($tmp->getOrderStatusId() == $orderStatusId) {
                $orderStatus = $tmp;
                break;
            }
        }

        if (null != $orderStatus) {
            $resultSource = new \ZMObjectResultSource('ZMOrder', 'orderService', "getOrdersForStatusId", array($orderStatusId, $languageId));
        } else {
            $resultSource = new \ZMObjectResultSource('ZMOrder', 'orderService', "getAllOrders", array($languageId));
        }
        $resultList = Runtime::getContainer()->get('ZMResultList');
        $resultList->setResultSource($resultSource);
        $resultList->setPageNumber($request->getParameter('page', 1));

        $data = array('resultList' => $resultList, 'orderStatus' => $orderStatus);
        return $this->findView(null, $data);
    }

}
