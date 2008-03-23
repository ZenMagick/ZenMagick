<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2008 ZenMagick
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
 *
 * $Id$
 */
?>
<?php


/**
 * Plugin providing functionallity to add Goggle Analytics code to the store.
 *
 * <p>This plugin is based on features of the 'Simple Google Analytics' mod'
 * by Dayne Larsen (info@barracudaproductions.com) and 
 * Andrew Berezin (andrew@ecommerce-service.com).</p>
 *
 * <p>For more about Google Analytics see https://www.google.com/support/analytics/bin/answer.py?answer=27203.</p>
 *
 * @author mano
 * @package org.zenmagick.plugins.zm_google_analytics
 * @version $Id$
 */
class zm_google_analytics extends ZMPlugin {
    private $eol_;


    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct('Google Analytics', 'Adds Google Analytics.', '${plugin.version}');
        $this->setKeys(array('uacct', 'affiliation',/*'target', */ 'identifier', "debug"));
        $this->eol_ = "\n";
    }

    /**
     * Destruct instance.
     */
    public function __destruct() {
        parent::__destruct();
    }


    /**
     * Install this plugin.
     */
    public function install() {
        parent::install();

        $this->addConfigValue('Google Analytics Account', 'uacct', 'UA-XXXXXX-X', 'Your Google Analytics account number');
        $this->addConfigValue('Google Analytics Account', 'affiliation', '', 'Optional partner or store affiliation');
        /*
        $this->addConfigValue('Target Address', 'target', 'customers',
            'Which order adress (City/State/Country) should be used to correlate the transaction with?',
            'zen_cfg_select_option(array(\'Customer\', \'Delivery\', \'Billing\'),');
         */
        $this->addConfigValue('Configure product identifier', 'identifier', 'ProductId', 'Select whether to use productId or model to identify products',
           'zen_cfg_select_option(array(\'ProductId\', \'Model\'),');

        $this->addConfigValue('Debug', "debug", 'Disabled', 'Generate code, but make inactive.',
          'zen_cfg_select_option(array(\''.'Enabled'.'\', \''.'Disabled'.'\'), ');
    }


    /**
     * Filter the response contents.
     *
     * @param string contents The contents.
     * @return string The modified contents.
     */
    public function filterResponse($contents) {
        if (zm_setting('plugins.zm_google_analytics.urchin', false)) {
            $trackerCode = $this->getTrackerCodeUrchin();
            $checkoutCode = $this->getCheckoutCodeUrchin();
        } else {
            $trackerCode = $this->getTrackerCodeGa();
            $checkoutCode = $this->getCheckoutCodeGa();
        }
        $code = !empty($checkoutCode) ? $checkoutCode : $trackerCode;
        return preg_replace('/<\/body>/', $code . '</body>', $contents, 1);
    }

    /**
     * Check for debug flag.
     */
    public function isDebug() {
        return 'Enabled' == $this->get('debug');
    }

    /**
     * Format the generic tracking code using the deprecated urchin format.
     *
     * @return string The tracking code.
     */
    protected function getTrackerCodeUrchin() {
        if (ZMRequest::isSecure()) {
            $url = "https://ssl.google-analytics.com/urchin.js";
        } else {
            $url = "http://www.google-analytics.com/urchin.js";
        }

        $code = '<script src="' . $url . '" type="text/javascript"></script>' . $this->eol_;
        $code .= '<script type="text/javascript">';
        $code .= '_uacct = "' . $this->get('uacct') . '";';
        if ($this->isDebug()) {
            $code .= '//';
        }
        $code .= 'urchinTracker(); </script>' . $this->eol_;

        return $code;
    }

    /**
     * Format the checkout order tracking code using the deprecated urchin format.
     *
     * @return string The order tracking code or empty string if not applicable.
     */
    protected function getCheckoutCodeUrchin() {
    global $zm_order;

        if ('checkout_success' != ZMRequest::getPageName()) {
            return '';
        }

        $address = $zm_order->hasShippingAddress() ? $zm_order->getShippingAddress() : $zm_order->getBillingAddress();
        $country = $address->getCountry();
        // totals
        $total = $zm_order->getOrderTotal('total', true);
        $totalValue = number_format($total->getAmount(), 2, '.', '');
        $tax = $zm_order->getOrderTotal('tax', true);
        $taxValue = number_format($tax->getAmount(), 2, '.', '');
        $shipping = $zm_order->getOrderTotal('shipping', true);
        $shippingValue = number_format($shipping->getAmount(), 2, '.', '');

        $code = '<form style="display:none;" name="utmform"><textarea id="utmtrans">' . $this->eol_;
        // UTM:T|[order-id]|[affiliation]|[total]|[tax]|[shipping]|[city]|[state]|[country]
        $code .= 'UTM:T|'.$zm_order->getId().'|'.$this->get('affiliation').'|'.$totalValue.'|'.$taxValue.'|'.
            $shippingValue.'|'.$address->getCity().'|'.$address->getState().'|'.$country->getIsoCode3() . $this->eol_;

        //UTM:I|[order-id]|[sku/code]|[productname]|[category]|[price]|[quantity]
        foreach ($zm_order->getOrderItems() as $orderItem) {
            $identifier = 'Model' == $this->get('identifier') ? $orderItem->getModel() : $orderItem->getProductId();
            $category = ZMCategories::instance()->getDefaultCategoryForProductId($orderItem->getProductId());
            $price = number_format($orderItem->getCalculatedPrice(), 2, '.', '');
            $code .= 'UTM:I|'.$zm_order->getId().'|'.$identifier.'|'.$orderItem->getName().'|'.$category->getName().'|'.$price.'|'.$orderItem->getQty() .$this->eol_;
        }

        $code .= '</textarea></form>' . $this->eol_;
        $code .= '<script type="text/javascript">';
        if ($this->isDebug()) {
            $code .= '//';
        }
        $code .= '__utmSetTrans();</script>' . $this->eol_;
        
        return $code;
    }

    /**
     * Format the generic tracking code using the ga format.
     *
     * @return string The tracking code.
     */
    protected function getTrackerCodeGa() {
        $tracker = $this->get('uacct');

        $code = <<<EOT
<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
var pageTracker = _gat._getTracker("$tracker");
pageTracker._initData();
pageTracker._trackPageview();
</script>
EOT;

        if ($this->isDebug()) {
            $code = '<!-- DEBUG: '.$code.' -->';
        }
        return $code;
    }

    /**
     * Format the checkout order tracking code using the ga format.
     *
     * @return string The order tracking code or empty string if not applicable.
     */
    protected function getCheckoutCodeGa() {
    global $zm_order;

        if ('checkout_success' != ZMRequest::getPageName()) {
            return '';
        }

        $tracker = $this->get('uacct');
        $affiliation = $this->get('affiliation');

        // order
        $address = $zm_order->hasShippingAddress() ? $zm_order->getShippingAddress() : $zm_order->getBillingAddress();
        $city = $address->getCity();
        $state = $address->getState();
        $country = $address->getCountry();
        $countryCode = $address->getCountry()->getIsoCode3();

        // totals
        $orderId = $zm_order->getId();
        $total = $zm_order->getOrderTotal('total', true);
        $totalValue = number_format($total->getAmount(), 2, '.', '');
        $tax = $zm_order->getOrderTotal('tax', true);
        $taxValue = number_format($tax->getAmount(), 2, '.', '');
        $shipping = $zm_order->getOrderTotal('shipping', true);
        $shippingValue = number_format($shipping->getAmount(), 2, '.', '');

        // order code
        $code = <<<EOT
<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ?
"https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost +
"google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
var pageTracker = _gat._getTracker("$tracker");
pageTracker._initData();
pageTracker._trackPageview();
pageTracker._addTrans(
"${orderId}", // order ID - required
"${affiliation}", // affiliation or store name
"${totalValue}", // total - required
"${taxValue}", // tax
"${shippingValue}", // shipping
"${city}", // city
"${state}", // state or province
"${countryCode}" // country
);
EOT;
        // items
        foreach ($zm_order->getOrderItems() as $orderItem) {
            $identifier = 'Model' == $this->get('identifier') ? $orderItem->getModel() : $orderItem->getProductId();
            $name = $orderItem->getName();
            $categoryName = ZMCategories::instance()->getDefaultCategoryForProductId($orderItem->getProductId())->getName();
            $price = number_format($orderItem->getCalculatedPrice(), 2, '.', '');
            $qty = $orderItem->getQty();
            $code .= <<<EOT
pageTracker._addItem(
"${orderId}", // order ID - required
"${identifier}", // SKU/code
"${name}", // product name
"${categoryName}", // category or variation
"${price}", // unit price - required
"${qty}" // quantity - required
);
EOT;
        }

        $code .= <<<EOT
pageTracker._trackTrans();
</script>
EOT;

        if ($this->isDebug()) {
            $code = '<!-- DEBUG: '.$code.' -->';
        }

        return $code;
    }

}

?>
