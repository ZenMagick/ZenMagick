<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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
namespace zenmagick\apps\store\storefront\http;

use zenmagick\base\Beans;
use zenmagick\base\Runtime;

/**
 * Store request wrapper.
 *
 * <p><strong>NOTE:</strong</strong> For the time of transition between static and instance
 * usage of request methods this will have a temp. name of <code>ZMRequest</code>.</p>
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class Request extends \ZMRequest {
    private $shoppingCart_ = null;

    /**
     * Get the current shopping cart.
     *
     * @return ShoppingCart The current shopping cart (may be empty).
     */
    public function getShoppingCart() {
        if (null == $this->shoppingCart_) {
            // TODO: enable
            if ($this->isAnonymous() || true) {
                $this->shoppingCart_ = Runtime::getContainer()->get('shoppingCart');
            } else {
                $this->shoppingCart_ = $this->container->get('shoppingCartService')->loadCartForAccountId($this->getAccountId());
            }
        }

        return $this->shoppingCart_;
    }

    /**
     * Get the product id.
     *
     * @return int The request product id or <code>0</code>.
     */
    public function getProductId() { return (int)$this->getParameter('products_id', $this->getParameter('productId', 0)); }

    /**
     * Get the category path arry.
     *
     * @return array The current category path broken into an array of category ids.
     */
    public function getCategoryPathArray() {
        $path = $this->getParameter('cPath');
        $cPath = array();
        if (null !== $path) {
            $path = explode('_', $path);
            foreach ($path as $categoryId) {
                $categoryId = (int)$categoryId;
                if (!in_array($categoryId, $cPath)) {
                    $cPath[] = $categoryId;
                }
            }
        }
        return $cPath;
    }

    /**
     * Get the current category id.
     *
     * @return int The current category id or <code>0</code>.
     */
    public function getCategoryId() {
        $cPath = $this->getCategoryPathArray();

        if (0 < count($cPath)) {
            return end($cPath);
        }

        return 0;
    }

    /**
     * Get the account id.
     *
     * @return int The account id for the currently logged in user or <code>0</code>.
     */
    public function getAccountId() { return (int)$this->getSession()->getAccountId(); }

    /**
     * Get the account.
     *
     * @return ZMAccount The account or <code>null</code>.
     */
    public function getAccount() {
        $accountId = $this->getAccountId();
        if (0 == $accountId) {
            return null;
        }

        return $this->container->get('accountService')->getAccountForId($accountId);
    }

    /**
     * Get the current order id.
     *
     * @return int The current order id or <code>0</code>.
     */
    public function getOrderId() { return (int)$this->getParameter('order_id',  $this->getParameter('orderId', 0)); }

    /**
     * Returns <code>true</code> if the user is not logged in at all.
     *
     * @return boolean <code>true</code> if the current user is guest, <code>false</code> if not.
     */
    public function isAnonymous() { return $this->getSession()->isAnonymous(); }

    /**
     * Returns <code>true</code> if the user is fully registered and logged in.
     *
     * @return boolean <code>true</code> if the current user is fully registered and logged in, <code>false</code> if not.
     */
    public function isRegistered() { return $this->getSession()->isRegistered(); }

    /**
     * Returns <code>true</code> if the user is a guest user.
     *
     * @return boolean <code>true</code> if the current user is guest, <code>false</code> if not.
     */
    public function isGuest() { return $this->getSession()->isGuest(); }

    /**
     * Returns <code>true</code> if the user is logged in.
     *
     * @return boolean <code>true</code> if the current user is logged in, <code>false</code> if not.
     */
    public function isLoggedIn() { return $this->getSession()->isLoggedIn(); }

    /**
     * Set the last URL.
     */
    public function setLastUrl() {
        // save url to be used as redirect in some cases
        if ('GET' == $this->getMethod()) {
            $this->getSession()->setValue('lastUrl', $this->url());
        } else {
            $this->getSession()->setValue('lastUrl', null);
        }
    }

    /**
     * Get the last URL.
     *
     * @return string The last URL or <code>null</code>.
     */
    public function getLastUrl() {
        return $this->getSession()->getValue('lastUrl');
    }

    /**
     * Get the selected language.
     *
     * <p>Determine the currently active language, with respect to potentially selected language from a dropdown in admin UI.</p>
     *
     * @return ZMLanguage The selected language.
     */
    public function getSelectedLanguage() {
        $session = $this->getSession();
        $language = null;
        if (null != ($id = $session->getValue('languages_id'))) {
            $languageService = $this->container->get('languageService');
            // try session language code
            if (null == ($language = $languageService->getLanguageForId($id))) {
                // try store default
                $language = $languageService->getLanguageForId(Runtime::getSettings()->get('storeDefaultLanguageId'));
            }
        }

        if (null == $language) {
            Runtime::getLogging()->warn('no default language found - using en as fallback');
            $language = Beans::getBean('apps\\store\\entities\\locale\\Language');
            $language->setId(1);
            $language->setDirectory('english');
            $language->setCode('en');
        }
        return $language;
    }

}
