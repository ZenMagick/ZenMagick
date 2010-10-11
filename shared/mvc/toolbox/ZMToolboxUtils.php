<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2010 zenmagick.org
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


/**
 * Generic utilities.
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.mvc.tools
 */
class ZMToolboxUtils extends ZMToolboxTool {

    /**
     * Simple title generator based on the page name.
     *
     * @param string page The page name; default is <code>null</code> for the current page.
     * @return string A reasonable page title.
     */
    public function getTitle($page=null) {
        $title = null == $page ? $this->getRequest()->getRequestId() : $page;
        // special case for static pages
        $title = 'static' != $title ? $title : $this->getRequest()->getSubPageName();

        // format
        $title = str_replace('_', ' ', $title);
        // capitalise words
        $title = ucwords($title);
        $title = _zm($title);

        return $title;
    }

    /**
     * Encode XML control characters.
     *
     * @param string s The input string.
     * @return string The encoded string.
     */
    public function encodeXML($s) {
        return ZMXmlTools::encodeXML($s);
    }

    /**
     * Format the given amount according to the current currency.
     *
     * @param float amount The amount.
     * @param boolean convert If <code>true</code>, consider <code>$amount</code> to be in default currency and
     *  convert before formatting.
     * @return string The formatted amount.
     */
    public function formatMoney($amount, $convert=true) {
        $currency = ZMCurrencies::instance()->getCurrencyForCode($this->getRequest()->getCurrencyCode());
        if (null == $currency) {
            ZMLogging::instance()->log('no currency found - using default currency', ZMLogging::WARN);
            $currency = ZMCurrencies::instance()->getCurrencyForCode(ZMSettings::get('defaultCurrency'));
        }
        $money = $currency->format($amount, $convert);

        return $money;
    }

    /**
     * Check if the given shopping cart qualifies for free shipping (as per free shipping ot).
     *
     * @param ZMShoppingCart shoppingCart The cart to examine.
     * @return boolean <code>true</code> if this cart qualifies for free shipping.
     */
    public function isFreeShipping($shoppingCart) {
        $checkouthelper = ZMLoader::make('CheckoutHelper', $shoppingCart);
        return $checkouthelper->isFreeShipping();
    }

    /**
     * Get the content of a static (define) page.
     *
     * @param string pageName The page name.
     * @return string The content or <code>null</code>.
     */
    public function staticPageContent($pageName) {
        $language = $this->getRequest()->getSession()->getLanguage();
        $theme = ZMThemes::instance()->getActiveTheme($language->getId());
        return $theme->staticPageContent($pageName, $language->getId());
    }

}
