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

use ZenMagick\ZenMagickBundle\Test\BaseTestCase;

/**
 * Test cart service.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class ShoppingCartServiceTest extends BaseTestCase
{
    /**
     * {@inheritDoc}
     */
    public function skip()
    {
        $account = $this->getRequest()->getAccount();
        if (null == $account) {
            $account = $this->get('accountService')->getAccountForId(1);
            $this->getRequest()->getSession()->setAccount($account);
        }
    }

    /**
     * Get the account id to test.
     *
     * @return int An account id.
     */
    protected function getAccountId()
    {
        $account = $this->getRequest()->getAccount();

        return $account ? $account->getId() : 0;
    }

    /**
     * Test load cart.
     */
    public function testLoadCart()
    {
        $contents = $this->get('shoppingCartService')->getContentsForAccountId($this->getAccountId());
        $_SESSION['cart']->reset(false);
        $_SESSION['cart']->restore_contents();
        $this->assertEquals($_SESSION['cart']->contents(), $contents);
    }

}
