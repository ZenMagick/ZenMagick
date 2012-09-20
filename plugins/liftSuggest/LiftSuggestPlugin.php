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
namespace ZenMagick\plugins\liftSuggest;

use ZenMagick\Base\Plugins\Plugin;
use ZenMagick\Base\Beans;
use ZenMagick\Base\Toolbox;

/**
 * Plugin adding support for liftsuggest product suggestions.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class LiftSuggestPlugin extends Plugin {
    private $recomendationsLoadedFor = null;
    private $view = null;

    /**
     * Get config for the Lift Suggest adapter.
     *
     * @return array Config map.
     */
    public function getLiftSuggestConfig() {
        $config = array();
        $config['token'] = $this->get('userToken');
        $config['customerId'] = $this->get('customerId');
        $config['limit'] = $this->get('recommendationLimit');
        $config['domain'] = $this->get('domainName');
        return $config;
    }

    /**
     * Generate the required JS code for success tracking.
     *
     * @param ZenMagick\Http\Request request The current request.
     * @return string The complete code.
     */
    protected function getTrackerCode($request) {
        $code = '';
        $productId = $request->query->get('productId');
        if (null === $productId) {
            // fallback
            $productId = $this->recomendationsLoadedFor;
        }
        $session = $request->getSession();
        $product = $this->container->get('productService')->getProductForId($productId, $session->getLanguageId());
        if (null != $product) {
            // prepare vars
            $trackingType = $this->get('trackingType');
            $uacct = $this->get('uacct');
            $productId = $product->getId();
            $price = $product->getPrice();

            $rec = 'N';
            if (null != ($recommended = $session->get('reco_prods')) && is_array($recommended)) {
                if (in_array($product->getId(), $recommended)) {
                    $rec= 'R';
                }
            }

            if ('ga' == $trackingType) {
                $code = <<< EOT
  // traditional
  var gac = new gaCookies();
  var vid = gac.getUniqueId();
  pageTracker._setCustomVar(5, "LIFT", vid + "_{$productId}_{$price}_{$rec}", 2);

EOT;
            } else if ('as' == $trackingType) {
                $code = <<< EOT
<script type="text/javascript">
  var gac = new gaCookies();
  var vid = gac.getUniqueId();
  // Asynchronous
  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', '{$uacct}']);
  _gaq.push(['_setCustomVar', 5, 'LIFT', vid + "_{$productId}_{$price}_{$rec}", 2]);
</script>
EOT;
            }
        }

        return $code;
    }

    /**
     * Get resources reference.
     */
    public function onViewDone($event) {
        $this->view = $event->get('view');
    }

    /**
     * Event callback to inject the required JS.
     */
    public function onFinaliseContent($event) {
        $request = $event->get('request');
        $trackingType = $this->get('trackingType');

        if (in_array($request->getRequestId(), array('product_info', 'shopping_cart')) && null !== $this->recommendationsLoadedFor) {
            // TODO: won't work with minify
            $scriptFile = ('ga' == $trackingType ? 'liftsuggest.js' : 'liftsuggest_traditional.js');
            $protocol = $request->isSecure() ? 'https://' : 'http://';

            $code1 = sprintf('<script type="text/javascript" src="%swww.liftsuggest.com/js/%s?cache=%s"></script>', $protocol, $scriptFile, Toolbox::random(10, Toolbox::RANDOM_DIGITS));

            $code2 = $this->getTrackerCode($request);
            if (Toolbox::asBoolean($this->get('debug'))) {
                $code1 = str_replace('<script', '<!--script', $code1);
                $code1 = str_replace('</script>', '/script-->', $code1);
                $code2 = str_replace('<script', '<!--script', $code2);
                $code2 = str_replace('</script>', '/script-->', $code2);
            }

            $content = $event->get('content');
            if ('ga' == $trackingType) {
                $content = preg_replace('/<\/head>/', $code1 . '</head>', $content, 1);
                $content = preg_replace('/pageTracker._trackPageview\(/', $code2 . 'pageTracker._trackPageview(', $content, 1);
            } else if ('as' == $trackingType) {
                $content = preg_replace('/<\/body>/', $code1.$code2 . '</body>', $content, 1);
            }
            $event->set('content', $content);
        }
    }

    /**
     * Get recommendations for the given product (id).
     *
     * @param mixed productIds Either a single productId or a list of product Ids.
     * @param int limit Optional limit to override the globally configured limit; default is <code>null</code> to use the global limit.
     * @return array List of maps with product recommendation details or <code>null</code> on failure.
     */
    public function getProductRecommendations($productId, $limit=null) {
        $lsr = Beans::getBean('ZenMagick\plugins\liftSuggest\ZMLiftSuggestLookup');
        if (null === $this->recommendationsLoadedFor) {
            // grab first
            $this->recommendationsLoadedFor = $productId;
        }
        return $lsr->getProductRecommendations($productId, $limit);
    }

}
