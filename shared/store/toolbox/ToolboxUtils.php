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
namespace zenmagick\apps\store\toolbox;

use zenmagick\base\Runtime;
use zenmagick\base\Beans;
use zenmagick\http\toolbox\ToolboxTool;
use zenmagick\apps\store\model\checkout\ShoppingCart;

/**
 * Generic utilities.
 *
 * @author DerManoMann
 */
class ToolboxUtils extends ToolboxTool {

    /**
     * Simple title generator based on the page name.
     *
     * @param string page The page name; default is <code>null</code> for the current page.
     * @return string A reasonable page title.
     */
    public function getTitle($page=null) {
        $title = null == $page ? $this->getRequest()->getRequestId() : $page;
        // special case for static pages
        $title = 'static' != $title ? $title : $this->getRequest()->query->get('cat');

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
        return \ZMXmlTools::encodeXML($s);
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
        $currencyService = $this->container->get('currencyService');
        if (Runtime::isContextMatch('storefront')) {
            // @todo we shouldn't be getting it from the request
            $code = $this->getRequest()->getSession()->getCurrencyCode();
        } else {
            $code = Runtime::getSettings()->get('defaultCurrency');
        }
        $currency = $currencyService->getCurrencyForCode($code);
        if (null == $currency) {
            Runtime::getLogging()->warn('no currency found - using default currency');
            $currency = $currencyService->getCurrencyForCode(Runtime::getSettings()->get('defaultCurrency'));
        }
        $money = $currency->format($amount, $convert);
        return $money;
    }

    /**
     * Check if the given shopping cart qualifies for free shipping (as per free shipping ot).
     *
     * @param ShoppingCart shoppingCart The cart to examine.
     * @return boolean <code>true</code> if this cart qualifies for free shipping.
     */
    public function isFreeShipping(ShoppingCart $shoppingCart) {
        return $shoppingCart->getCheckoutHelper()->isFreeShipping();
    }

    /**
     * Get the content of a static (define) page.
     *
     * @param string pageName The page name.
     * @return string The content or <code>null</code>.
     */
    public function staticPageContent($pageName) {
        $languageId = $this->getRequest()->getSession()->getLanguageId();
        if (empty($languageId)) {
            // XXX: when called in admin
            $languageId = $this->container->get('languageService')->getLanguageForCode($this->container->get('settingsService')->get('defaultLanguageCode'))->getLanguageId();
        }
        // most specific first
        $themeChain = array_reverse($this->container->get('themeService')->getThemeChain($languageId));
        foreach ($themeChain as $theme) {
            if (null != ($content = $theme->staticPageContent($pageName, $languageId))) {
                return $content;
            }
        }

        return null;
    }

   /**
     * Flattens any given object.
     *
     * <p>Criteria for the included data is the ZenMagick naming convention that access methods start with
     * either <code>get</code>, <code>is</code> or <code>has</code>.</p>
     *
     * <p>If the given object is an array, all elements will be converted, too. Generally speaking, this method works
     * recursively. Arrays are preserved, array values, in turn, will be flattened.</p>
     *
     * <p>The methods array may contain nested arrays to allow recursiv method mapping. The Ajax product controller is
     * a good example for this.</p>
     *
     * @param mixed obj The object.
     * @param array methods Optional list of methods to include as properties.
     * @param function formatter Optional formatting method for all values; signature is <code>formatter($obj, $name, $value)</code>.
     * @return array Associative array of methods values.
     */
    public function flattenObject($obj, $properties=null, $formatter=null) {
        $props = null;

        if (is_array($obj)) {
            $props = array();
            foreach ($obj as $k => $o) {
                $props[$k] = $this->flattenObject($o, $properties, $formatter);
            }
            return $props;
        }

        if (!is_object($obj)) {
            // as is
            return $obj;
        }

        // properties may be a mix of numeric and string key - ugh!
        $beanProperties = array();
        foreach ($properties as $key => $value) {
            $beanProperties[] = is_array($value) ? $key : $value;
        }
        $props = Beans::obj2map($obj, $beanProperties, false);
        foreach ($props as $key => $value) {
            if (is_object($value) || is_array($value)) {
                $sub = is_array($properties[$key]) ? $properties[$key] : null;
                $value = $this->flattenObject($value, $sub, $formatter);
            }
            $props[$key] = null != $formatter ? $formatter($obj, $key, $value) : $value;
        }
        return $props;
    }



}
