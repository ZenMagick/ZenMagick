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
     * Add the given CSS file to the final contents.
     *
     * <p>The underlying code (<code>ZMTemplateManager</code>) will ensure that each CSS file is included once only.</p>
     *
     * @param string filename A relative CSS filename.
     * @param array attr Optional attribute map.
     */
    public function cssFile($filename, $attr=array()) {
        ZMTemplateManager::instance()->cssFile($filename, $attr);
    }

    /**
     * Add the given JS file to the header section of the final contents.
     *
     * <p>The underlying code (<code>ZMTemplateManager</code>) will ensure that each JS file is included once only.</p>
     *
     * @param string filename A relative JavaScript filename.
     */
    public function jsTop($filename) {
        ZMTemplateManager::instance()->jsFile($filename, ZMTemplateManager::PAGE_TOP);
    }

    /**
     * Add the given JS file to the end of the final contents (before the closing <body> element).
     *
     * <p>The underlying code (<code>ZMTemplateManager</code>) will ensure that each JS file is included once only.</p>
     * <p>Also, if the same JS file is requested for both <em>top</em> and <em>bottom</em>, it will be included at the
     * top only. The same applies to calling <code>jsNow()</code> on a file that has been already included or is marked for
     * inclusion at the bottom.</p>
     *
     * @param string filename A relative JavaScript filename.
     */
    public function jsBottom($filename) {
        ZMTemplateManager::instance()->jsFile($filename, ZMTemplateManager::PAGE_BOTTOM);
    }

    /**
     * Add the given JS file now.
     *
     * @param string filename A relative JavaScript filename.
     */
    public function jsNow($filename) {
        ZMTemplateManager::instance()->jsFile($filename, ZMTemplateManager::PAGE_NOW);
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
     * Get a list of all available editors.
     *
     * @return array A class/name map of editors.
     */
    public function getEditorMap() {
        $map = array('plain' => 'Plain');
        $tokens = explode(',', ZMSettings::get('editorList'));
        foreach ($tokens as $token) {
            $nc = explode(':', $token);
            $map[$nc[1]] = $nc[0];
        }

        return $map;
    }

    /**
     * Get the current editor class.
     *
     * @return ZMTextAreaFormWidget A text editor widget.
     */
    public function getCurrentEditor() {
        if (null == ($editor = $this->getRequest()->getSession()->getValue('currentEditor'))) {
            $editor = $this->getDefaultEditor();
        }

        if (null != ($obj = ZMBeanUtils::getBean($editor))) {
            return $obj;
        }

        return ZMBeanUtils::getBean('TextAreaFormWidget');
    }

    /**
     * Set the current editor class.
     *
     * @param string class The editor class name.
     */
    public function setCurrentEditor($class) {
        $this->getRequest()->getSession()->setValue('currentEditor', $class);
    }

    /**
     * Get the default editor class.
     *
     * @return string The default editor class name.
     */
    public function getDefaultEditor() {
        return ZMSettings::get('defaultEditor', 'TextAreaFormWidget');
    }

    /**
     * Get the content of a static (define) page.
     *
     * <p>If the file is not found and <code>isEnableThemeDefaults</code> is set to <code>true</code>,
     * the method will try to resolve the name in the default theme.</p>
     *
     * @param string pageName The page name.
     * @return string The content or <code>null</code>.
     */
    public function staticPageContent($pageName) {
        $theme = Runtime::getTheme();
        $language = $this->getRequest()->getSession()->getLanguage();
        return $theme->staticPageContent($pageName, $language->getId());
    }

}
