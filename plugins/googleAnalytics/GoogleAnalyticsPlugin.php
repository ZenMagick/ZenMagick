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
namespace zenmagick\plugins\googleAnalytics;

use zenmagick\apps\store\plugins\Plugin;
use zenmagick\base\Runtime;
use zenmagick\base\Toolbox;

/**
 * Plugin providing functionallity to add Goggle Analytics code to the store.
 *
 * <p>This plugin is based on features of the 'Simple Google Analytics' mod'
 * by Dayne Larsen (info@barracudaproductions.com) and
 * Andrew Berezin (andrew@ecommerce-service.com).</p>
 *
 * <p>For more about Google Analytics see https://www.google.com/support/analytics/bin/answer.py?answer=27203.</p>
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class GoogleAnalyticsPlugin extends Plugin {
    private $eol_;
    private $order_;


    /**
     * Create new instance.
     */
    public function __construct(array $config) {
        parent::__construct($config);
        $this->eol_ = "\n";
        $this->order_ = null;
    }


    /**
     * Event handler.
     */
    public function onViewStart($event) {
        $request = $event->get('request');
        if ('checkout_success' == $request->getRequestId()) {
            $view = $event->get('view');
            $vars = $view->getVariables();
            $this->order_ = $vars['currentOrder'];
        }
    }

    /**
     * Event handler.
     */
    public function onFinaliseContent($event) {
        $request = $event->get('request');

        $trackerCode = $this->getTrackerCodeGa($request);
        $checkoutCode = $this->getCheckoutCodeGa($request);
        $code = !empty($checkoutCode) ? $checkoutCode : $trackerCode;
        $code .= $this->getConversionCode($request);

        if (Toolbox::asBoolean($this->get('debug'))) {
            $code = str_replace('<script', '<!--script', $code);
            $code = str_replace('</script>', '/script-->', $code);
        }

        $content = $event->get('content');
        $content = preg_replace('/<\/body>/', $code . '</body>', $content, 1);
        $event->set('content', $content);
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
     * Generate usable pageview identifiers.
     *
     * @param zenmagick\http\Request request The current request.
     * @return string A string.
     */
    protected function getPageview($request) {
        $view = '';
        switch ($this->get('pagenameFormat')) {
        case 'custom':
            // TODO: make this smarter??
            $themeId = $this->container->get('themeService')->getActiveThemeId();
            $customClass = 'zenmagick\\themes\\'.$themeId.'\\CustomGoogleAnalytics';
            if (class_exists($customClass)) {
                $custom = new $customClass;
                if (null !== ($view = $custom->getPageview($request))) {
                    // done
                    break;
                }
            }

            // fallthrough to pagename
        case 'pagename':
            $args = array('reviews_id', 'manufacturers_id', 'cPath', 'id', 'cat', 'productId');
            $view = $request->getRequestId();
            foreach ($args as $name) {
                $attr = '[';
                if (null != ($value = $request->query->get($name))) {
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
            break;
        case 'uri':
            $view = $request->getRequestUri();
            break;
        case 'none':
            // nothing
            break;
        }
        return $view;
    }

    /**
     * Format the generic tracking code using the ga format.
     *
     * @param zenmagick\http\Request request The current request.
     * @return string The tracking code.
     */
    protected function getTrackerCodeGa($request) {
        $tracker = $this->get('uacct');
        $pageview = $this->getPageview($request);
        $setDomainName = '';
        $domainName = trim($this->get('domainName'));
        if (!empty($domainName)) {
            $setDomainName = sprintf('pageTracker._setDomainName("%s")', $domainName);
        }

        $code = <<<EOT
<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
var pageTracker = _gat._getTracker("$tracker");
pageTracker._initData();
pageTracker._trackPageview("$pageview");
$setDomainName;
</script>
EOT;

        return $code;
    }

    /**
     * Format the checkout order tracking code using the ga format.
     *
     * @param zenmagick\http\Request request The current request.
     * @return string The order tracking code or empty string if not applicable.
     */
    protected function getCheckoutCodeGa($request) {
        if ('checkout_success' != $request->getRequestId()) {
            return '';
        }
        if (null == $this->order_) {
            Runtime::getLogging()->warn('no order to process');
            return;
        }

        $tracker = $this->get('uacct');
        $pageview = $this->getPageview($request);
        $affiliation = $this->get('affiliation');

        $setDomainName = '';
        $domainName = trim($this->get('domainName'));
        if (!empty($domainName)) {
            $setDomainName = sprintf('pageTracker._setDomainName("%s")', $domainName);
        }

        // order
        $address = $this->getAddress($this->order_);
        $city = $address->getCity();
        $state = $address->getState();
        $country = $address->getCountry();
        $countryCode = $address->getCountry()->getIsoCode3();

        // totals
        $orderId = $this->order_->getId();
        $totalValue = number_format($this->order_->getOrderTotalLineAmountForType('total'), 2, '.', '');
        $taxValue = number_format($this->order_->getOrderTotalLineAmountForType('tax'), 2, '.', '');
        $shippingValue = number_format($this->order_->getOrderTotalLineAmountForType('shipping'), 2, '.', '');

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
$setDomainName;
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
        foreach ($this->order_->getOrderItems() as $orderItem) {
            $identifier = 'model' == $this->get('identifier') ? $orderItem->getModel() : $orderItem->getProductId();
            $name = $orderItem->getName();
            $categoryName = $this->container->get('categoryService')->getDefaultCategoryForProductId($orderItem->getProductId(), $request->getSession()->getLanguageId())->getName();
            $price = number_format($orderItem->getCalculatedPrice(), 2, '.', '');
            $qty = $orderItem->getQuantity();
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

    /**
     * Create code for conversion tracking.
     *
     * @param zenmagick\http\Request request The current request.
     * @return string The conversion code.
     */
    protected function getConversionCode($request) {
        $code = '';
        if ('checkout_success' == $request->getRequestId()) {
            if (null == $this->order_) {
                Runtime::getLogging()->warn('no order to process');
                return;
            }

            $conversionId = $this->get('conversionId');
            if (!empty($conversionId)) {
                if ($request->isSecure()) {
                    $baseUrl = "https://www.googleadservices.com/pagead";
                } else {
                    $baseUrl = "http://www.googleadservices.com/pagead";
                }
                $totalValue = number_format($this->order_->getOrderTotalLineAmountForType('total'), 2, '.', '');
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
<noscript><img height=1 width=1 border=0 src="$baseUrl/conversion/$conversionId/?value=$totalValue&label=Purchase&script=0'; ?>" /></noscript>
EOT;
            }
        }

        return $code;
    }

}
