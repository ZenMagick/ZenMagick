<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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

use zenmagick\base\Runtime;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

/**
 * ZenMagick Lift Suggest Recommendation implementation.
 *
 * @author mano
 * @package org.zenmagick.plugins.liftSuggest
 */
class ZMLiftSuggestLookup extends LiftSuggestLookup implements ContainerAwareInterface {
    const SESSION_NAMESPACE = null;
    private $plugin_;
    private $container;

    /**
     * Create new instance.
     *
     * @param ZMPlugin plugin The related plugin or <code>null</code>.
     */
	  public function __construct($plugin=null) {
        $this->plugin_ = null != $plugin ? $plugin : Runtime::getContainer()->get('pluginService')->getPluginForId('liftSuggest');
        parent::__construct($this->plugin_->getLiftSuggestConfig());
    }

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container=null) {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function log($message, $e=null) {
        Runtime::getLogging()->debug($message.(null != $e ? 'ex: '.$e : ''));
    }

    /**
     * {@inheritDoc}
     */
    public function storeInSession($key, $value) {
        $session = $this->container->get('session');
        $session->setValue($key, $value, self::SESSION_NAMESPACE);
    }

    /**
     * {@inheritDoc}
     */
    public function getFromSession($key, $default=null) {
        $session = $this->container->get('session');
        $session->getValue($key, self::SESSION_NAMESPACE, $default);
    }

    /**
     * {@inheritDoc}
     */
    public function populate($raw) {
        $recommendations = new ZMLiftSuggestRecommendations();
        $recommended = $this->getFromSession('reco_prods', array());

        // process product infos
        $products = array();
		    foreach ($raw['products'] as $details) {
            if (array_key_exists('sku', $details)) {
                $product = $this->container->get('productService')->getProductForId($details['sku'], Runtime::getSettings()->get('storeDefaultLanguageId'));
                if (null != $product && $product->getStatus()) {
                    $info = array('product' => null, 'info' => array());
                    foreach ($details as $key => $value) {
                        if ('sku' == $key) {
                            $info['product'] = $product;
                            $recommended[] = $product->getId();
                        } else {
                            // other details
                            $info['info'][$key] = $value;
                        }
                    }
                    $products[] = $info;
                }
            }
        }
        $recommendations->set('productDetails', $products);

        // keep other
		    foreach ($raw as $key => $details) {
            if ('products' != $key) {
                $recommendations->set($key, $details);
                if ('popular_perc' == $key) {
                    $recommendations->set('popularity', $details);
                }
            }
        }

        $this->storeInSession('reco_prods', $recommended);
        return $recommendations;
    }

}
