<?php
/*
 * ZenMagick - Another PHP framework.
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
namespace ZenMagick\apps\storefront\Services;

use ZenMagick\Base\ZMObject;
use ZenMagick\Http\Session\UserFactory;

/**
 * User factory for customers.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class CustomerUserFactory extends ZMObject implements UserFactory {

    /**
     * {@inheritDoc}
     */
    public function getUser($request) {
        $session = $request->getSession();
        if (null != ($accountId = $session->getValue('customer_id'))) {
            return $this->container->get('accountService')->getAccountForId($accountId);
        }
        return null;
    }

}
