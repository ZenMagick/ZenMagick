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
namespace zenmagick\apps\store\http;

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
            if ($this->getSession()->isAnonymous() || true) {
                $this->shoppingCart_ = Runtime::getContainer()->get('shoppingCart');
            } else {
                $this->shoppingCart_ = $this->container->get('shoppingCartService')->loadCartForAccountId($this->getAccountId());
            }
        }

        return $this->shoppingCart_;
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
