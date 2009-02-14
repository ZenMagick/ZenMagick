<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
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

        $this->addConfigValue('Account', 'uacct', 'UA-XXXXXX-X', 'Your Google Analytics account number');

        $this->addConfigValue('Store affiliation', 'affiliation', '', 'Optional partner or store affiliation');

        $this->addConfigValue('AdWords Conversion id', 'conversionId', '', 'Optional AdWords conversion id (leave empty to ignore)');
        $this->addConfigValue('AdWords Conversion language', 'conversionLang', 'en_US', 'Optional AdWords conversion language');

        $this->addConfigValue('Product identifier', 'identifier', 'ProductId', 'Select whether to use productId or model to identify products',
           'zen_cfg_select_option(array(\'ProductId\', \'Model\'),');

        $this->addConfigValue('Track pagenames', 'usePagename', 'true', 'Select whether to use pagenames to track individual URLs',
           'zen_cfg_select_option(array(\'true\', \'false\'),');

        $this->addConfigValue('Transaction Address', 'address', 'shipping', 'Select which address to use for transaction (order) logging',
           'zen_cfg_select_option(array(\'shipping\', \'billing\'),');

        $this->addConfigValue('Debug', "debug", 'true', 'Generate code, but make inactive.',
          'zen_cfg_select_option(array(\''.'true'.'\', \''.'false'.'\'), ');
    }

    /**
     * Init this plugin.
     */
    public function init() {
        parent::init();
        $this->zcoSubscribe();
    }

    /**
     * {@inheritDoc}
     */
    public function onZMFinaliseContents($args) {
        $contents = $args['contents'];

        if (ZMSettings::get('plugins.zm_google_analytics.urchin', false)) {
            $trackerCode = $this->getTrackerCodeUrchin();
            $checkoutCode = $this->getCheckoutCodeUrchin();
        } else {
            $trackerCode = $this->getTrackerCodeGa();
            $checkoutCode = $this->getCheckoutCodeGa();
        }
        $code = !empty($checkoutCode) ? $checkoutCode : $trackerCode;
        $code .= $this->getConversionCode();

        if (ZMTools::asBoolean($this->get('debug'))) {
            $code = str_replace('<script', '<!--script', $code);
            $code = str_replace('</script>', '/script-->', $code);
        }

        $args['contents'] = preg_replace('/<\/body>/', $code . '</body>', $contents, 1);
        return $args;
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
        $code .= 'urchinTracker(); </script>' . $this->eol_;

        return $code;
    }

    /**
     * Get the address object to be used for order logging.
     *
     * @return mixed Either a <code>ZMAddress</code> or <code>ZMAccound</code> instance.
     */
    protected function getAddress($order) {
        $address = $order->hasShippingAddress() ? $order->getShippingAddress() : $order->getBillingAddress();
        switch ($this->get('address')) {
        case 'shipping':
            if ($order->hasShippingAddress()) {
                $address = $order->getShippingAddress();
            }
            break;
        case 'billing':
            $address = $order->getBillingAddress();
            break;
        }
        return $address;
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

        $address = $this->getAddress($zm_order);
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
        $code .= '__utmSetTrans();</script>' . $this->eol_;
        
        return $code;
    }

    /**
     * Generate usable pageview identifiers.
     *
     * @return string A string.
     */
    protected function getPageview() {
        $view = '';
        if (ZMTools::asBoolean($this->get('usePagename'))) {
            $args = array('reviews_id', 'manufacturers_id', 'cPath', 'id', 'cat', 'products_id');
            $view = ZMRequest::getPageName();
            foreach ($args as $name) {
                $attr = '[';
                if (null != ($value = ZMRequest::getParameter($name))) {
                    if ('[' != $attr) {
                        $attr .= ',';
                    }
                    $attr .= $name.'='.$value;
                }
                $attr .= ']';
            }
            if ('[]' != $attr) {
                $view .= $attr;
            }
        }
        return $view;
    }

    /**
     * Format the generic tracking code using the ga format.
     *
     * @return string The tracking code.
     */
    protected function getTrackerCodeGa() {
        $tracker = $this->get('uacct');
        $pageview = $this->getPageview();

        $code = <<<EOT
<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
var pageTracker = _gat._getTracker("$tracker");
pageTracker._initData();
pageTracker._trackPageview("$pageview");
</script>
EOT;

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
        $pageview = $this->getPageview();
        $affiliation = $this->get('affiliation');

        // order
        $address = $this->getAddress($zm_order);
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
pageTracker._trackPageview("$pageview");
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

        return $code;
    }

    protected function getConversionCode() {
    global $zm_order;

        $code = '';
        if ('checkout_success' == ZMRequest::getPageName()) {
            $conversionId = $this->get('conversionId');
            if (!empty($conversionId)) {
                if (ZMRequest::isSecure()) {
                    $baseUrl = "https://www.googleadservices.com/pagead";
                } else {
                    $baseUrl = "http://www.googleadservices.com/pagead";
                }
                $total = $zm_order->getOrderTotal('total', true);
                if (0 >= $total) {
                    $total = 1;
                }
                $totalValue = number_format($total->getAmount(), 2, '.', '');
                $conversionLang = $this->get('conversionLang');
                $code = <<<EOT
<script type="text/javascript">
var google_conversion_id = "$conversionId";
var google_conversion_language = "$conversionLang";
var google_conversion_format = "1";
var google_conversion_color = "FFFFFF";
var google_conversion_value = "$totalValue";
var google_conversion_label = "Purchase";
</script>
<script type="text/javascript" src="$url/conversion.js">
</script>
<noscript><img height=1 width=1 border=0 src="$baseUrl/conversion$conversionId/?value=$totalValue&label=Purchase&script=0'; ?>" /></noscript>
EOT;
            }
        }

        return $code;
    }

}

?>
