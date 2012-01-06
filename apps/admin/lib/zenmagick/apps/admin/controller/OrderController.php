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
namespace zenmagick\apps\admin\controller;


/**
 * Admin controller for order page.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class OrderController extends \ZMController {

    /**
     * {@inheritDoc}
     */
    public function processGet($request) {
        $orderId = $request->getParameter('orderId');
        // TODO: language
        if (null == ($order = $this->container->get('orderService')->getOrderForId($orderId, 1))) {
            $this->messageService->error(sprintf(_zm('Order for orderId id %s not found'), $orderId));
            return $this->findView(null, array('orderId' => $orderId));
        }

        return $this->findView(null, array('order' => $order));
    }

}
