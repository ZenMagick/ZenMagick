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
namespace ZenMagick\AdminBundle\Controller;

use ZenMagick\ZenMagickBundle\Controller\DefaultController;

/**
 * Admin controller for order page.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class OrderController extends DefaultController
{
    /**
     * {@inheritDoc}
     */
    public function processGet($request)
    {
        $orderId = $request->query->getInt('orderId');
        // TODO: language
        if (null == ($order = $this->container->get('orderService')->getOrderForId($orderId, 1))) {
            $message = $this->get('translator')->trans('Order for order id %id% not found', array('%id%' => $orderId));
            $this->get('session.flash_bag')->error($message);

            return $this->findView(null, array('orderId' => $orderId));
        }

        return $this->findView(null, array('order' => $order));
    }

}
